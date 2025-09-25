<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['id' => 1, 'name' => 'Administrador', 'description' => 'Acceso completo al sistema'],
            ['id' => 2, 'name' => 'Docente', 'description' => 'Acceso para seguimiento de estudiantes'],
            ['id' => 3, 'name' => 'Estudiante', 'description' => 'Acceso para realizar diagnósticos y seguir rutas de aprendizaje']
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }
    }
}

