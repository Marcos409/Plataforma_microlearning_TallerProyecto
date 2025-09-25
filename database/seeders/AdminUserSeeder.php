<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@continental.edu.pe'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('admin123'),
                'role_id' => 1,
                'active' => true
            ]
        );

        User::updateOrCreate(
            ['email' => 'docente@continental.edu.pe'],
            [
                'name' => 'Docente Demo',
                'password' => Hash::make('docente123'),
                'role_id' => 2,
                'active' => true
            ]
        );

        User::updateOrCreate(
            ['email' => 'estudiante@continental.edu.pe'],
            [
                'name' => 'Estudiante Demo',
                'student_code' => 'UC20240001',
                'career' => 'Ingeniería de Sistemas',
                'semester' => 5,
                'password' => Hash::make('estudiante123'),
                'role_id' => 3,
                'active' => true
            ]
        );
    }
}