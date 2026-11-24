<?php

namespace Database\Seeders;

use App\Models\Escuela;
use Illuminate\Database\Seeder;

class EscuelaSeeder extends Seeder
{
    /**
     * Datos tomados de DATOS ESCUELAS 2026.xlsx.
     */
    public function run(): void
    {
        $escuelas = [
            ['JARDIN 901', 'Inicial', 'MARIA VIVIANA TORRES', '3364-575458', 'jardin901somisa@gmail.com'],
            ['JARDIN 902 “Dr. Gines Garcia”', 'Inicial', 'FELIP SILVINA', '3364-631133', 'jardin902sannicolas@abc.gob.ar'],
            ['JARDIN 906 “Dámaso Valdés”', 'Inicial', 'Lambrisaca, Sandra Fabiana', '336-4389260', 'jardin906sannicolas@abc.gob.ar'],
            ['JARDIN 911 “Tambor de Tacuarí”', 'Inicial', 'Sonia Burgardt', '3364-542072', 'jardin911sannicolas@abc.gob.ar'],
            ['JARDIN 920 “Rafael Obligado”', 'Inicial', 'Evangelina Chiucchi', null, 'jardin920sannicolas@abc.gob.a'],
            ['JARDIN 923 “Juana Paula Manso”', 'Inicial', 'Maricel Lauría', '3364611699', 'jardin923sannicolas@abc.gob.ar'],
            ['JARDIN 926', 'Inicial', 'VIVIANA PELUZZA', '3364-658272', 'jardin926sannicolas@abc.gob.ar'],
            ['JARDIN PANDI', 'Inicial', 'Patricia Puleo (DIRECTORA)', '3364258190', 'pandi2727@hotmail.com'],
            ['JARDIN BELGRANO', 'Inicial', 'Natalia Gerez (Directora)', '3364313748', 'cbjardin@outlook.com'],
            ['JARDIN JEAN PIAGET', 'Inicial', 'Andrea Nardoni (Directora)', '3364-597761', 'jeanpiaget1732mail.com'],
            ['CEC Nº 801 "Maria Otilia Muñoz"', 'Inicial', 'María Eugenia Druscaba', '3364-585549', 'cec801sannicolas@abc.gob.ar'],
            ['EP No 1', 'Primario', 'Luciana Colaluce', '3364591096', 'primaria1sannicolas@abc.gob.ar'],
            ['EP No 4 "Jose Manuel Estrada"', 'Primario', 'Claudia C. Rodríguez', '3364-671150', 'primaria4sannicolas@abc.gob.ar'],
            ['EP No 6', 'Primario', 'Paschero Beltramo M de los Angeles', '3364304299', 'primaria6s annicolas@abc.gob.ar'],
            ['EP No 7', 'Primario', 'Clarisa Bottazi', '3364-587971', 'primaria7sannicolas@abc.gob.ar'],
            ['EP No 10', 'Primario', 'Romay Elida', '3364-664656', 'eromay@abc.gob.ar'],
            ['EP No 11', 'Primario', 'Marisa Villarroel', '3364382945', 'primaria11sannnicolas@abc.gob.ar'],
            ['EP No 12', 'Primario', 'Juarez Maria Rosa', '3364382945', 'primaria12sannicolas@abc.gob.ar'],
            ['EP No 13', 'Primario', 'Delorenzini Melina', '3364-613421', 'escuelaprimaria13gr@hotmail.com'],
            ['EP No 15', 'Primario', 'SILVINA FELIP', '3364-631133', 'primaria15sannicolas@abc.gob.ar'],
            ['EP No 17', 'Primario', 'Rosi Cesari', '3364-662813', 'primaria17sannicolas@abc.gob.ar'],
            ['EP No 19', 'Primario', 'Jimena Alegre', '3364-025689', 'primaria19sannicolas@abc.gob.ar'],
            ['EP No 20', 'Primario', 'ABIGAIL SICHI', '3364-628910', 'primaria20sannicolas@abc.gob.ar'],
            ['EP No 29', 'Primario', 'LUJAN GONZALEZ', '3364-450191', 'primaria29sannicolas@abc.gob.ar'],
            ['EP No 30', 'Primario', 'NATALIA GIANDINOTTO', '3364-617511', 'eoe2023.ep30@gmail.com'],
            ['EP No 31', 'Primario', 'CLAUDIA RODRIGUEZ', '3364-671150', 'primaria31sannicolas@abc.gob.ar'],
            ['EP No 32', 'Primario', 'Alicia Perdiguero', '3364585522', 'PRIMARIA32@ABC.gob.ar'],
            ['EP No 34', 'Primario', 'Juarez Maria Rosa', '3364382945', 'primaria34sannicolas@abc.gob.ar'],
            ['EP No 35', 'Primario', 'Cecilia Tessi', '3364-207782', 'primaria35sannicolas@abc.gob.ar'],
            ['EP No 42', 'Primario', 'Rosa Cerasi', '3364-662813', 'primaria42sannicolas@abc.gob.ar'],
            ['EP No 43', 'Primario', 'Sonia burgardt', '3364542072', 'primaria43sannicolas@abc.gob.ar'],
            ['EP No 44', 'Primario', 'Sivori Ludmila Soledad', '3364347484', 'primaria44sannicolas@abc.gob.ar'],
            ['EP No 45', 'Primario', 'MARTIN VALERIA', '3364-210754', 'primaria45sannicolas@abc.gob.ar'],
            ['EP No 46', 'Primario', 'VIRGINIA LATTANZIO', '3364-679726', 'primaria46sannicolas@abc.gob.ar'],
            ['EP No 47', 'Primario', 'MARICEL LAURIA', '3364611699', 'primaria47sannicolas@abc.gob.ar'],
            ['EP No 48', 'Primario', 'YESICA TROILO', '3364-696435', 'primaria48sannicolas@abc.gob.ar'],
            ['EP No 49 "Rafael Obligado"', 'Primario', 'Laura Santaren', '3364-518622', 'primaria49sannicolas@abc.gob.ar'],
            ['Misericordia', 'Primario', 'Adriana Zapico', '3364-431848', 'primario549@gmail.com'],
            ['ESCUELA CAMINO', 'Primario', 'Araceli Bazan', '3364-187389', 'escuelacamino@outlook.com'],
            ['EP No 503 “Dr. Carlos Covi”(escuela especial)', 'Primario', 'Tatiana Lizarazu', '3364-343471', 'especial503sannicolas@abc.gob.ar'],
            ['EP No 502 (escuela especial)', 'Primario', 'Maria Carla Menichelli', '3364-422069', 'eee502sannicolas@gmail.com'],
            ['EP No 504 "Jorge Susini" (escuela especial)', 'Primario', 'Valeria Martin', '3364210754', 'especial504sannicolas@abc.gob.ar'],
            ['Pompeya', 'Primario', 'MARIANA FERRARO', '341-6023398', '0097pompeya@gmail.com'],
            ['E.E.S. No 1 (Rojo)', 'Secundario', 'DELORENZINI MELINA', '3364-613421', 'secundaria1sannicolas@abc.gob.ar'],
            ['ESCUELA TECNICA Nº 2', 'Secundario', 'PASCHERO MARIA ANGELES', '3364-304299', 'eest2sn@gmail.com'],
            ['ESCUELA TECNICA Nº 4', 'Secundario', 'CHIAPPARI BARBARA', '3364-329544', 'tecnica4sannicolas@abc.gob.ar'],
            ['ESCUELA TECNICA Nº 6', 'Secundario', 'Maria Laura Maldonado', '3364333320', 'eoetecnica6sn@gmail.com'],
            ['E.E.S. No 3', 'Secundario', 'AGUIRRE ANGELES', '3364-011352', 'secundaria3sannicolas@abc.gob.ar'],
            ['E.E.S. No 5', 'Secundario', 'SALARAYAN MONICA', '3364-675650', 'secundaria5sannicolas@abc.gob.ar'],
            ['E.E.S. No 7', 'Secundario', 'CHIAPPARI BARBARA / BARONE VIRGINIA', '3364-329544 / 3364-641018', 'secundaria7sannicolas@abc.gob.ar'],
            ['E.E.S. No 8 (La Emilia)', 'Secundario', 'FLORENCIA GODOY', '3364-207510', 'secundaria8sannicolas@abc.gob.ar'],
            ['E.E.S. No 9', 'Secundario', 'FLORENCIA MANAVELLA', '3364-190878', 'secundaria9sannicolas@abc.gob.ar'],
            ['E.E.S. No 10', 'Secundario', 'NOTTURNO LORENA', '3364-355896', 'secundaria10sannicolas@abc.gob.ar'],
            ['E.E.S. No 14 (Campos Salles)', 'Secundario', 'Karina Saley', '3364539250', 'eesn14.eoe@gmail.com'],
            ['E.E.S. No 15', 'Secundario', 'SONIA VALIENTE', '3364358029', 'secundaria15sannicolas@abc.gob.ar'],
            ['E.E.S. No 18 (Conesa)', 'Secundario', 'MICUCCI ELIANA', '2478514461', 'secundaria18sannicolas@abc.gob.ar'],
            ['E.E.S. No 21', 'Secundario', 'BELEN TROTTA', '3364-679302', 'secundaria21sannicolas@abc.gob.ar'],
            ['E.E.S. No 22', 'Secundario', 'PALMUCHI DAIANA', '3364-534533', 'secundaria22sannicolas@abc.gob.ar'],
            ['E.E.S. No 25', 'Secundario', 'MARIANA BELEN LOPEZ', '3364-206963', 'secundaria25sannicolas@abc.gob.ar'],
            ['E.E.S. No 26', 'Secundario', 'DOMINGUEZ VANESA', '3364-337896', 'secundaria26sannicolas@abc.gob.ar'],
            ['MEDIA 3', 'Secundario', 'ANGELES AGUIRRE', '3364 011352', 'secundaria3sannicolas@abc.gob.ar'],
        ];

        foreach ($escuelas as [$nombre, $nivel, $orientadora, $celular, $mail]) {
            Escuela::query()->updateOrCreate(
                ['nombre_normalizado' => Escuela::normalizarNombre($nombre)],
                [
                    'nombre' => $nombre,
                    'niveles' => [$nivel],
                    'orientadora' => $orientadora,
                    'celular' => $celular,
                    'mail' => $mail,
                    'activo' => true,
                ],
            );
        }
    }
}
