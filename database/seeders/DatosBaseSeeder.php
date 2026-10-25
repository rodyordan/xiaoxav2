<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatosBaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('tipo_entrega')->count() === 0) {
            DB::table('tipo_entrega')->insert([
                ['id_tipo_entrega' => 1, 'nombre_tipo_entrega' => 'Retiro en tienda'],
                ['id_tipo_entrega' => 2, 'nombre_tipo_entrega' => 'Envío a provincia'],
            ]);
            $this->command->info('Tipos de entrega insertados.');
        }

        if (DB::table('rol')->count() === 0) {
            DB::table('rol')->insert([
                ['id_rol' => 1, 'nombre_rol' => 'Administrador'],
                ['id_rol' => 2, 'nombre_rol' => 'Usuario'],
            ]);
            $this->command->info('Roles insertados.');
        }

        if (DB::table('tipo_documento')->count() === 0) {
            DB::table('tipo_documento')->insert([
                ['nombre_tipo_documento' => 'DNI'],
                ['nombre_tipo_documento' => 'RUC'],
                ['nombre_tipo_documento' => 'CE'],
            ]);
            $this->command->info('Tipos de documento insertados.');
        }

        if (DB::table('usuario')->where('correo', 'i2414151@continental.edu.pe')->count() === 0) {
            DB::table('usuario')->insert([
                'nombres' => 'Rod',
                'apellidos' => 'Carhuancho',
                'correo' => 'i2414151@continental.edu.pe',
                'contrasena' => Hash::make('palkiegt1789'),
                'id_rol' => 1,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Usuario administrador creado.');
        }
    }
}