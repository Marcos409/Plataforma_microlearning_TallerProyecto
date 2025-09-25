<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Diagnostic;
use App\Models\DiagnosticQuestion;

class DiagnosticSeeder extends Seeder
{
    public function run()
    {
        // Diagnóstico de Matemáticas
        $mathDiagnostic = Diagnostic::create([
            'title' => 'Diagnóstico de Matemáticas Básicas',
            'description' => 'Evaluación inicial de conocimientos en matemáticas para identificar áreas de refuerzo.',
            'subject_area' => 'Matemáticas',
            'total_questions' => 10,
            'passing_score' => 70,
            'active' => true
        ]);

        $mathQuestions = [
            [
                'question' => '¿Cuál es el resultado de 15 + 28?',
                'options' => ['41', '43', '44', '45'],
                'correct_answer' => 1,
                'difficulty_level' => 1,
                'topic' => 'Aritmética Básica'
            ],
            [
                'question' => 'Resolver para x: 2x + 5 = 15',
                'options' => ['x = 3', 'x = 5', 'x = 7', 'x = 10'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Álgebra'
            ],
            [
                'question' => '¿Cuál es el área de un triángulo con base 8 cm y altura 6 cm?',
                'options' => ['24 cm²', '28 cm²', '48 cm²', '14 cm²'],
                'correct_answer' => 0,
                'difficulty_level' => 2,
                'topic' => 'Geometría'
            ],
            [
                'question' => '¿Cuál es el 25% de 80?',
                'options' => ['15', '20', '25', '30'],
                'correct_answer' => 1,
                'difficulty_level' => 1,
                'topic' => 'Porcentajes'
            ],
            [
                'question' => 'Si f(x) = 2x + 3, ¿cuál es f(4)?',
                'options' => ['9', '11', '13', '15'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Funciones'
            ],
            [
                'question' => '¿Cuál es la raíz cuadrada de 144?',
                'options' => ['10', '11', '12', '13'],
                'correct_answer' => 2,
                'difficulty_level' => 1,
                'topic' => 'Raíces'
            ],
            [
                'question' => 'En una progresión aritmética donde a1=5 y d=3, ¿cuál es a5?',
                'options' => ['17', '18', '19', '20'],
                'correct_answer' => 0,
                'difficulty_level' => 3,
                'topic' => 'Progresiones'
            ],
            [
                'question' => '¿Cuál es el perímetro de un rectángulo de 7 cm de largo y 4 cm de ancho?',
                'options' => ['18 cm', '22 cm', '28 cm', '11 cm'],
                'correct_answer' => 1,
                'difficulty_level' => 1,
                'topic' => 'Geometría'
            ],
            [
                'question' => 'Resolver: 3x - 7 = 2x + 8',
                'options' => ['x = 12', 'x = 15', 'x = 18', 'x = 1'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Álgebra'
            ],
            [
                'question' => '¿Cuál es el resultado de (-3)² + 2(-3) + 1?',
                'options' => ['2', '4', '6', '8'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Álgebra'
            ]
        ];

        foreach ($mathQuestions as $questionData) {
            DiagnosticQuestion::create(array_merge($questionData, ['diagnostic_id' => $mathDiagnostic->id]));
        }

        // Diagnóstico de Programación
        $progDiagnostic = Diagnostic::create([
            'title' => 'Diagnóstico de Programación Básica',
            'description' => 'Evaluación de conceptos fundamentales de programación.',
            'subject_area' => 'Programación',
            'total_questions' => 8,
            'passing_score' => 70,
            'active' => true
        ]);

        $progQuestions = [
            [
                'question' => '¿Qué es una variable en programación?',
                'options' => ['Un tipo de bucle', 'Un espacio de memoria para almacenar datos', 'Una función matemática', 'Un error de código'],
                'correct_answer' => 1,
                'difficulty_level' => 1,
                'topic' => 'Conceptos Básicos'
            ],
            [
                'question' => '¿Cuál de estos es un lenguaje de programación?',
                'options' => ['HTML', 'CSS', 'Python', 'JSON'],
                'correct_answer' => 2,
                'difficulty_level' => 1,
                'topic' => 'Lenguajes'
            ],
            [
                'question' => '¿Qué hace un bucle "for" en programación?',
                'options' => ['Declara una variable', 'Repite un bloque de código', 'Termina el programa', 'Imprime en pantalla'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Estructuras de Control'
            ],
            [
                'question' => '¿Qué es un algoritmo?',
                'options' => ['Un lenguaje de programación', 'Una secuencia de pasos para resolver un problema', 'Un tipo de variable', 'Un error de sintaxis'],
                'correct_answer' => 1,
                'difficulty_level' => 1,
                'topic' => 'Algoritmos'
            ],
            [
                'question' => '¿Cuál es el resultado de 10 % 3 en programación?',
                'options' => ['3', '1', '0', '10'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Operadores'
            ],
            [
                'question' => '¿Qué es una función en programación?',
                'options' => ['Un tipo de variable', 'Un bloque de código reutilizable', 'Un error', 'Un comentario'],
                'correct_answer' => 1,
                'difficulty_level' => 2,
                'topic' => 'Funciones'
            ],
            [
                'question' => '¿Cuál de estos es un tipo de datos primitivo?',
                'options' => ['Array', 'Object', 'Integer', 'Class'],
                'correct_answer' => 2,
                'difficulty_level' => 2,
                'topic' => 'Tipos de Datos'
            ],
            [
                'question' => '¿Qué significa "debugging"?',
                'options' => ['Escribir código', 'Encontrar y corregir errores', 'Ejecutar un programa', 'Crear variables'],
                'correct_answer' => 1,
                'difficulty_level' => 1,
                'topic' => 'Depuración'
            ]
        ];

        foreach ($progQuestions as $questionData) {
            DiagnosticQuestion::create(array_merge($questionData, ['diagnostic_id' => $progDiagnostic->id]));
        }
    }
}