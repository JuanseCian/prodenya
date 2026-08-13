
-- ============================================================
-- 1. ROLES Y USUARIOS
-- ============================================================

CREATE TABLE roles (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50) NOT NULL UNIQUE,   -- ej: 'admin', 'operador', 'consulta'
    descripcion     VARCHAR(255),
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Roles base sugeridos
INSERT INTO roles (nombre, descripcion) VALUES
    ('admin', 'Acceso total al sistema'),
    ('operador', 'Carga y edición de personas y documentos'),
    ('consulta', 'Solo lectura y descarga de documentos');

CREATE TABLE usuarios (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario      VARCHAR(50) NOT NULL UNIQUE,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,      -- guardar siempre con bcrypt/argon2, nunca texto plano
    rol_id              INT NOT NULL,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_acceso       DATETIME NULL,
    creado_en           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 2. DIVISIÓN GEOGRÁFICA: PROVINCIAS -> LOCALIDADES -> BARRIOS
-- ============================================================

CREATE TABLE provincias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE localidades (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    provincia_id    INT NOT NULL,
    CONSTRAINT fk_localidad_provincia FOREIGN KEY (provincia_id) REFERENCES provincias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_localidad (nombre, provincia_id)
) ENGINE=InnoDB;

CREATE TABLE barrios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    localidad_id    INT NOT NULL,
    CONSTRAINT fk_barrio_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    UNIQUE KEY uq_barrio (nombre, localidad_id)
) ENGINE=InnoDB;

-- ============================================================
-- 3. PERSONAS
-- ============================================================

CREATE TABLE personas (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL,
    apellido            VARCHAR(100) NOT NULL,
    dni                 VARCHAR(15) NOT NULL UNIQUE,
    fecha_nacimiento    DATE NOT NULL,               -- la edad se calcula, no se guarda (ver recomendaciones)
    genero              ENUM('M','F','X','NO_ESPECIFICA') DEFAULT 'NO_ESPECIFICA',
    direccion_calle     VARCHAR(150),
    direccion_numero    VARCHAR(20),
    direccion_piso_depto VARCHAR(20),
    barrio_id           INT NULL,
    localidad_id        INT NOT NULL,
    telefono            VARCHAR(30),
    email               VARCHAR(150),
    observaciones       TEXT,
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_persona_barrio FOREIGN KEY (barrio_id) REFERENCES barrios(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_persona_localidad FOREIGN KEY (localidad_id) REFERENCES localidades(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_persona_dni (dni),
    INDEX idx_persona_apellido_nombre (apellido, nombre)
) ENGINE=InnoDB;

-- ============================================================
-- 4. CUD - Certificado Único de Discapacidad
-- ============================================================

CREATE TABLE cud (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    persona_id          INT NOT NULL,
    numero_cud          VARCHAR(50) NOT NULL UNIQUE,
    fecha_emision       DATE NOT NULL,
    fecha_vencimiento   DATE NOT NULL,
    observaciones       TEXT,
    creado_en           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cud_persona FOREIGN KEY (persona_id) REFERENCES personas(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_cud_persona (persona_id),
    INDEX idx_cud_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB;

-- ============================================================
-- 5. LEGAJOS Y SU AUDITORÍA DE ACCESO
-- ============================================================

CREATE TABLE legajos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    persona_id          INT NOT NULL,
    numero_legajo       VARCHAR(30) NOT NULL UNIQUE,
    fecha_apertura      DATE NOT NULL DEFAULT (CURRENT_DATE),
    estado              ENUM('Abierto','Cerrado','Archivado') NOT NULL DEFAULT 'Abierto',
    observaciones       TEXT,
    creado_por          INT NOT NULL,                -- usuario que abrió el legajo
    creado_en           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_legajo_persona FOREIGN KEY (persona_id) REFERENCES personas(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_legajo_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_legajo_persona (persona_id)
) ENGINE=InnoDB;

CREATE TABLE auditoria_accesos_legajo (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    legajo_id       INT NOT NULL,
    usuario_id      INT NOT NULL,                -- quién ingresó o descargó
    tipo_accion     ENUM('Visualizacion','Descarga') NOT NULL,
    fecha_acceso    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_origen       VARCHAR(45),
    user_agent      VARCHAR(255),
    CONSTRAINT fk_auditlegajo_legajo FOREIGN KEY (legajo_id) REFERENCES legajos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_auditlegajo_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_auditlegajo_legajo (legajo_id),
    INDEX idx_auditlegajo_usuario (usuario_id),
    INDEX idx_auditlegajo_fecha (fecha_acceso)
) ENGINE=InnoDB;

-- ============================================================
-- 6. DOCUMENTOS (PDFs) Y AUDITORÍA DE DESCARGAS
-- ============================================================

CREATE TABLE tipos_documento (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL UNIQUE     -- ej: 'CUD', 'DNI', 'Certificado médico'
) ENGINE=InnoDB;

INSERT INTO tipos_documento (nombre) VALUES
    ('CUD'), ('DNI'), ('Certificado médico'), ('Otro');

CREATE TABLE documentos (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    persona_id          INT NULL,                    -- a qué persona pertenece (si aplica)
    legajo_id           INT NULL,                    -- a qué legajo pertenece (si aplica)
    tipo_documento_id   INT NOT NULL,
    nombre_archivo      VARCHAR(255) NOT NULL,        -- nombre original del PDF
    ruta_archivo        VARCHAR(500) NOT NULL,        -- ruta/carpeta interna en el servidor (no exponer directo al público)
    tamanio_bytes        BIGINT,
    subido_por          INT NOT NULL,                -- usuario que lo cargó
    fecha_subida        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo              BOOLEAN NOT NULL DEFAULT TRUE, -- baja lógica en vez de borrar el registro
    CONSTRAINT fk_documento_persona FOREIGN KEY (persona_id) REFERENCES personas(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_documento_legajo FOREIGN KEY (legajo_id) REFERENCES legajos(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_documento_tipo FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_documento_usuario FOREIGN KEY (subido_por) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_documento_persona (persona_id),
    INDEX idx_documento_legajo (legajo_id)
) ENGINE=InnoDB;

-- (auditoría de descargas de documentos individuales, distinta de la de legajos)
CREATE TABLE auditoria_descargas (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    documento_id    INT NOT NULL,
    usuario_id      INT NOT NULL,               -- quién descargó
    fecha_descarga  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_origen       VARCHAR(45),                 -- soporta IPv4 e IPv6
    user_agent      VARCHAR(255),
    CONSTRAINT fk_auditoria_documento FOREIGN KEY (documento_id) REFERENCES documentos(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_auditoria_documento (documento_id),
    INDEX idx_auditoria_usuario (usuario_id),
    INDEX idx_auditoria_fecha (fecha_descarga)
) ENGINE=InnoDB;

-- ============================================================
-- 7. VISTA ÚTIL: personas con su CUD vigente y ubicación completa
-- ============================================================

CREATE VIEW vista_personas_completa AS
SELECT
    p.id,
    p.nombre,
    p.apellido,
    p.dni,
    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad,
    b.nombre AS barrio,
    l.nombre AS localidad,
    pr.nombre AS provincia,
    c.numero_cud,
    c.fecha_vencimiento AS cud_vencimiento,
    CASE WHEN c.fecha_vencimiento >= CURDATE() THEN 'Vigente' ELSE 'Vencido' END AS estado_cud
FROM personas p
LEFT JOIN barrios b ON p.barrio_id = b.id
JOIN localidades l ON p.localidad_id = l.id
JOIN provincias pr ON l.provincia_id = pr.id
LEFT JOIN cud c ON c.persona_id = p.id;

-- ============================================================
-- FIN DEL ESQUEMA
-- ============================================================