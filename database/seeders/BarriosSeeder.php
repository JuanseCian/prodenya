<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarriosSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos tomados del dump prodenya.sql (tabla `barrios`).
     * insertOrIgnore para poder correr el seeder de nuevo sin
     * romper por claves primarias duplicadas.
     */
    public function run(): void
    {
        DB::table('barrios')->insertOrIgnore([
            ['id' => 1, 'nombre' => 'Santa Teresita', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 2, 'nombre' => 'Las Mellizas', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 3, 'nombre' => 'Azopardo', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 4, 'nombre' => 'Yaguaron', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 5, 'nombre' => 'San Jorge', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 6, 'nombre' => 'Castelli', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 7, 'nombre' => 'San Martin', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 8, 'nombre' => 'Suizo', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 9, 'nombre' => 'Parque Norte', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 10, 'nombre' => 'Parque Sarmiento', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 11, 'nombre' => '25 de Mayo', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 12, 'nombre' => 'Santa Clara', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 13, 'nombre' => 'Moreno', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 14, 'nombre' => 'Bola de Oro', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 15, 'nombre' => 'San Cayetano', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 16, 'nombre' => 'Prado Español', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 17, 'nombre' => 'Alto Verde', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 18, 'nombre' => 'San Pablo', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 19, 'nombre' => 'La Loma', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 20, 'nombre' => 'Guena', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 21, 'nombre' => 'Asonia', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 22, 'nombre' => 'Fraga', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 23, 'nombre' => 'Martinez', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 24, 'nombre' => 'Lares', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 25, 'nombre' => 'Jose Ingenieros', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 27, 'nombre' => 'Urquiza', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 28, 'nombre' => 'Don Bosco', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 29, 'nombre' => 'Obrero', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 30, 'nombre' => 'Cavalli', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 31, 'nombre' => 'Padre D´Amico', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 32, 'nombre' => 'Química', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 33, 'nombre' => 'Belgrano', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 34, 'nombre' => 'San Isidro', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 35, 'nombre' => 'Ponce de León', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 36, 'nombre' => 'Saavedra', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 37, 'nombre' => 'Los Fresnos', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 38, 'nombre' => 'Alcoholera', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 39, 'nombre' => 'Astul Urquiaga', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 40, 'nombre' => 'Super Usina', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 41, 'nombre' => 'Golf Club', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 42, 'nombre' => 'Oeste', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 43, 'nombre' => 'Lanza', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 44, 'nombre' => 'Garetto', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 45, 'nombre' => 'Evita', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 46, 'nombre' => 'Santa Cecilia', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 47, 'nombre' => 'Ginés García', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 48, 'nombre' => 'ITEC', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 49, 'nombre' => 'San Francisco', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 50, 'nombre' => 'La Florida', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 51, 'nombre' => 'General Mosconi', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 52, 'nombre' => 'Mitre', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 53, 'nombre' => 'Irigoyen', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 54, 'nombre' => 'Villa María', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 55, 'nombre' => 'Los Viñedos', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 57, 'nombre' => 'Don Americo', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 58, 'nombre' => 'Parque Córdoba', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 59, 'nombre' => 'Colombo', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 60, 'nombre' => 'Malvinas', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 61, 'nombre' => 'Virgen del Rosario', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 62, 'nombre' => 'Colombini', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 63, 'nombre' => '21 de Septiembre', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 64, 'nombre' => 'Las Viñas', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 65, 'nombre' => 'Pezzi', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 66, 'nombre' => 'Las Flores', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 67, 'nombre' => 'Juan XXIII', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 68, 'nombre' => 'Del Carmen', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 69, 'nombre' => 'Primavera', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 70, 'nombre' => 'Ponte', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 71, 'nombre' => '17 de Octubre', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 72, 'nombre' => 'Los Pinos', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 73, 'nombre' => 'Savio', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 74, 'nombre' => 'Santa Rosa', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 75, 'nombre' => '9 de Julio', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 76, 'nombre' => 'Virgen de Luján', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 77, 'nombre' => 'Trípoli', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 78, 'nombre' => 'California', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 79, 'nombre' => 'Parque Avamba´e', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 80, 'nombre' => 'Avamba´e', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 81, 'nombre' => '7 de Septiembre', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 82, 'nombre' => 'San Eduardo', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 83, 'nombre' => 'Guemes', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 84, 'nombre' => 'Plastiversal', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 85, 'nombre' => 'Somisa', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 86, 'nombre' => 'Sironi', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 87, 'nombre' => 'Ayres del Sur', 'localidad_id' => 5, 'zona_barrio_id' => 2, 'created_at' => '2024-10-23 14:25:50', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 88, 'nombre' => 'CENTRO', 'localidad_id' => 5, 'zona_barrio_id' => 4, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 89, 'nombre' => 'La Emilia', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 90, 'nombre' => 'Villa Riccio', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 91, 'nombre' => 'Villa Canto', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 92, 'nombre' => 'Villa Campi', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 93, 'nombre' => 'Campos Salles', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 94, 'nombre' => 'General Rojo', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 95, 'nombre' => 'Conesa', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 96, 'nombre' => 'Erezcano', 'localidad_id' => 5, 'zona_barrio_id' => 5, 'created_at' => '2026-01-20 02:28:01', 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 98, 'nombre' => '14 de Abril', 'localidad_id' => 5, 'zona_barrio_id' => 1, 'created_at' => null, 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 99, 'nombre' => '12 de Marzo', 'localidad_id' => 5, 'zona_barrio_id' => 3, 'created_at' => null, 'updated_by' => null, 'deleted_by' => null, 'updated_at' => null, 'deleted_at' => null],
        ]);
    }
}
