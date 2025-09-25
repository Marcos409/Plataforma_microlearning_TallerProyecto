<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentLibrary;

class ContentLibrarySeeder extends Seeder
{
    public function run()
    {
        $contents = [
            // Contenidos de Matemáticas
            [
                'title' => 'Operaciones Aritméticas Básicas',
                'description' => 'Video tutorial sobre suma, resta, multiplicación y división',
                'type' => 'video',
                'subject_area' => 'Matemáticas',
                'topic' => 'Aritmética Básica',
                'difficulty_level' => 1,
                'external_url' => 'https://www.youtube.com/watch?v=ejemplo1',
                'estimated_duration' => 15,
                'tags' => ['básico', 'operaciones', 'aritmética'],
                'active' => true
            ],
            [
                'title' => 'Introducción al Álgebra',
                'description' => 'Conceptos fundamentales de álgebra y resolución de ecuaciones',
                'type' => 'pdf',
                'subject_area' => 'Matemáticas',
                'topic' => 'Álgebra',
                'difficulty_level' => 2,
                'file_path' => 'content/matematicas/algebra_basica.pdf',
                'estimated_duration' => 30,
                'tags' => ['álgebra', 'ecuaciones', 'variables'],
                'active' => true
            ],
            [
                'title' => 'Geometría Básica: Áreas y Perímetros',
                'description' => 'Cálculo de áreas y perímetros de figuras geométricas básicas',
                'type' => 'interactive',
                'subject_area' => 'Matemáticas',
                'topic' => 'Geometría',
                'difficulty_level' => 2,
                'external_url' => 'https://geogebra.org/ejemplo1',
                'estimated_duration' => 25,
                'tags' => ['geometría', 'áreas', 'perímetros'],
                'active' => true
            ],
            [
                'title' => 'Cálculo de Porcentajes',
                'description' => 'Métodos para calcular porcentajes en diferentes contextos',
                'type' => 'article',
                'subject_area' => 'Matemáticas',
                'topic' => 'Porcentajes',
                'difficulty_level' => 1,
                'file_path' => 'content/matematicas/porcentajes.html',
                'estimated_duration' => 20,
                'tags' => ['porcentajes', 'cálculo', 'aplicaciones'],
                'active' => true
            ],
            [
                'title' => 'Funciones Lineales',
                'description' => 'Introducción a las funciones lineales y su representación gráfica',
                'type' => 'video',
                'subject_area' => 'Matemáticas',
                'topic' => 'Funciones',
                'difficulty_level' => 2,
                'external_url' => 'https://www.youtube.com/watch?v=ejemplo2',
                'estimated_duration' => 35,
                'tags' => ['funciones', 'lineal', 'gráficas'],
                'active' => true
            ],

            // Contenidos de Programación
            [
                'title' => 'Introducción a la Programación',
                'description' => 'Conceptos fundamentales de programación para principiantes',
                'type' => 'video',
                'subject_area' => 'Programación',
                'topic' => 'Conceptos Básicos',
                'difficulty_level' => 1,
                'external_url' => 'https://www.youtube.com/watch?v=ejemplo3',
                'estimated_duration' => 45,
                'tags' => ['programación', 'básico', 'introducción'],
                'active' => true
            ],
            [
                'title' => 'Variables y Tipos de Datos',
                'description' => 'Comprende qué son las variables y los diferentes tipos de datos',
                'type' => 'article',
                'subject_area' => 'Programación',
                'topic' => 'Tipos de Datos',
                'difficulty_level' => 1,
                'file_path' => 'content/programacion/variables_tipos.html',
                'estimated_duration' => 25,
                'tags' => ['variables', 'tipos de datos', 'fundamentos'],
                'active' => true
            ],
            [
                'title' => 'Estructuras de Control: Bucles',
                'description' => 'Aprende a usar bucles for, while y do-while',
                'type' => 'interactive',
                'subject_area' => 'Programación',
                'topic' => 'Estructuras de Control',
                'difficulty_level' => 2,
                'external_url' => 'https://codecademy.com/ejemplo1',
                'estimated_duration' => 40,
                'tags' => ['bucles', 'for', 'while', 'control'],
                'active' => true
            ],
            [
                'title' => 'Fundamentos de Algoritmos',
                'description' => 'Introducción al diseño y análisis de algoritmos',
                'type' => 'pdf',
                'subject_area' => 'Programación',
                'topic' => 'Algoritmos',
                'difficulty_level' => 2,
                'file_path' => 'content/programacion/algoritmos_fundamentos.pdf',
                'estimated_duration' => 50,
                'tags' => ['algoritmos', 'diseño', 'análisis'],
                'active' => true
            ],
            [
                'title' => 'Operadores en Programación',
                'description' => 'Operadores aritméticos, lógicos y de comparación',
                'type' => 'quiz',
                'subject_area' => 'Programación',
                'topic' => 'Operadores',
                'difficulty_level' => 1,
                'external_url' => 'https://kahoot.com/ejemplo1',
                'estimated_duration' => 15,
                'tags' => ['operadores', 'aritmética', 'lógica'],
                'active' => true
            ],
            [
                'title' => 'Creación de Funciones',
                'description' => 'Cómo crear y usar funciones en programación',
                'type' => 'video',
                'subject_area' => 'Programación',
                'topic' => 'Funciones',
                'difficulty_level' => 2,
                'external_url' => 'https://www.youtube.com/watch?v=ejemplo4',
                'estimated_duration' => 30,
                'tags' => ['funciones', 'modularidad', 'reutilización'],
                'active' => true
            ],
            [
                'title' => 'Técnicas de Debugging',
                'description' => 'Estrategias para encontrar y corregir errores en el código',
                'type' => 'article',
                'subject_area' => 'Programación',
                'topic' => 'Depuración',
                'difficulty_level' => 2,
                'file_path' => 'content/programacion/debugging.html',
                'estimated_duration' => 35,
                'tags' => ['debugging', 'errores', 'depuración'],
                'active' => true
            ]
        ];

        foreach ($contents as $content) {
            ContentLibrary::create($content);
        }
    }
}