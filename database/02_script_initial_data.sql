-- ======================================================
-- 02_SCRIPT_INITIAL_DATA_REESTRUCTURADO.SQL
-- Inserción de Datos Iniciales y de Prueba para Microlearning UC
-- Versión: 1.1 - Noviembre 2025
-- ======================================================

USE `bd_microlearning_uc`;

-- ========================================
-- 1. DATOS MAESTROS: ROLES, USUARIOS, CONTENIDO
-- ========================================

-- INSERTAR ROLES (IDs 1-4)
INSERT INTO roles (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', NOW(), NOW()),
(2, 'Docente', 'Acceso para seguimiento de estudiantes', NOW(), NOW()),
(3, 'Estudiante', 'Acceso para realizar diagnósticos y seguir rutas de aprendizaje', NOW(), NOW()),
(4, 'Sin Rol', 'Usuarios registrados sin rol asignado', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = NOW();

-- INSERTAR USUARIOS INICIALES (IDs 1-10)
-- Contraseña para todos: "12345678"
INSERT INTO users (`id`, `name`, `email`, `email_verified_at`, `password`, `student_code`, `career`, `semester`, `phone`, `role_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Ana Pérez', 'ana.perez@uc.cl', '2024-09-01 10:00:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', NULL, NULL, NULL, '+56912345678', 1, 1, '2024-08-20 12:00:00', '2024-09-25 10:15:00'),
(2, 'Benjamín Soto', 'ben.soto@uc.cl', '2024-09-02 11:30:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', NULL, NULL, NULL, '+56987654321', 2, 1, '2024-08-20 12:05:00', '2024-09-25 10:15:00'),
(3, 'Carla Gómez', 'carla.gomez@uc.cl', '2024-09-03 14:45:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST001', 'Ingeniería de Sistemas', 5, '+56911223344', 3, 1, '2024-08-21 09:00:00', '2024-09-25 10:15:00'),
(4, 'David Rivas', 'david.rivas@uc.cl', NULL, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST002', 'Ingeniería Industrial', 3, '+56955667788', 3, 1, '2024-08-22 15:00:00', '2024-09-25 10:15:00'),
(5, 'Elena Díaz', 'elena.diaz@uc.cl', '2024-09-05 08:20:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST003', 'Administración', 4, '+56944556677', 3, 1, '2024-08-23 10:00:00', '2024-09-25 10:15:00'),
(6, 'Felipe Castro', 'felipe.c@uc.cl', '2024-09-06 17:00:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST004', 'Ingeniería Civil', 2, '+56933445566', 3, 1, '2024-08-24 11:00:00', '2024-09-25 10:15:00'),
(7, 'Gloria Rojas', 'gloria.r@uc.cl', NULL, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST005', 'Contabilidad', 6, '+56922334455', 3, 1, '2024-08-25 14:00:00', '2024-09-25 10:15:00'),
(8, 'Héctor Luna', 'hector.l@uc.cl', '2024-09-08 09:00:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST006', 'Marketing', 1, '+56977889900', 3, 1, '2024-08-26 12:30:00', '2024-09-25 10:15:00'),
(9, 'Iris Vega', 'iris.v@uc.cl', '2024-09-09 13:00:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST007', 'Psicología', 7, '+56966778899', 3, 1, '2024-08-27 16:00:00', '2024-09-25 10:15:00'),
(10, 'Jorge Mena', 'jorge.m@uc.cl', '2024-09-10 16:00:00', '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST008', 'Derecho', 8, '+56955667788', 3, 1, '2024-08-28 08:30:00', '2024-09-25 10:15:00');

-- INSERTAR CONTENIDOS (ID 1-3)
INSERT INTO content_library (`id`, `title`, `description`, `subject_area`, `topic`, `type`, `difficulty_level`, `content_url`, `duration_minutes`, `tags`, `active`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Álgebra Básica', 'Introducción a los conceptos fundamentales del álgebra', 'Matemáticas', 'Álgebra', 'Video', 'Básico', 'https://example.com/algebra-basica', 45, 'álgebra,matemáticas,básico', 1, 156, NOW(), NOW()),
(2, 'Leyes de Newton', 'Explicación detallada de las tres leyes de Newton', 'Física', 'Mecánica', 'Documento', 'Intermedio', 'https://example.com/leyes-newton.pdf', 30, 'física,mecánica,newton', 1, 89, NOW(), NOW()),
(3, 'Tabla Periódica Interactiva', 'Exploración interactiva de la tabla periódica', 'Química', 'Elementos', 'Interactivo', 'Básico', 'https://example.com/tabla-periodica', 60, 'química,elementos,interactivo', 1, 234, NOW(), NOW())
ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = NOW();

-- INSERTAR DIAGNÓSTICOS (ID 1-3)
INSERT INTO diagnostics (`id`, title, description, subject_area, difficulty_level, time_limit_minutes, passing_score, active, created_at, updated_at) VALUES
(1, 'Diagnóstico de Matemáticas Básicas', 'Evaluación de conocimientos fundamentales de aritmética y álgebra', 'Matemáticas', 'Básico', 30, 70.00, 1, NOW(), NOW()),
(2, 'Diagnóstico de Álgebra Intermedia', 'Ecuaciones, funciones y sistemas lineales', 'Matemáticas', 'Intermedio', 45, 75.00, 1, NOW(), NOW()),
(3, 'Diagnóstico de Física General', 'Cinemática, dinámica y leyes de Newton', 'Física', 'Básico', 40, 70.00, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = NOW();

SELECT '✅ Datos Maestros (Roles, Usuarios, Contenidos, Diagnósticos) insertados.' AS status;


-- ========================================
-- 2. PREGUNTAS DE DIAGNÓSTICO
-- ========================================

-- INSERTAR PREGUNTAS - MATEMÁTICAS BÁSICAS (ID 1)
INSERT INTO diagnostic_questions (diagnostic_id, question_text, question_type, options, correct_answer, points, order_index, created_at, updated_at) VALUES
(1, '¿Cuánto es 2 + 2?', 'multiple_choice', JSON_ARRAY('2', '3', '4', '5'), '4', 1.00, 1, NOW(), NOW()),
(1, '¿Cuál es el resultado de 10 - 5?', 'multiple_choice', JSON_ARRAY('3', '4', '5', '6'), '5', 1.00, 2, NOW(), NOW()),
(1, '¿Cuánto es 3 × 4?', 'multiple_choice', JSON_ARRAY('7', '10', '12', '14'), '12', 1.00, 3, NOW(), NOW()),
(1, '¿Cuál es el resultado de 20 ÷ 4?', 'multiple_choice', JSON_ARRAY('4', '5', '6', '7'), '5', 1.00, 4, NOW(), NOW()),
(1, '¿Cuánto es 5 + 3 × 2?', 'multiple_choice', JSON_ARRAY('11', '13', '16', '10'), '11', 1.00, 5, NOW(), NOW()),
(1, 'Resuelve: x + 5 = 12', 'multiple_choice', JSON_ARRAY('5', '6', '7', '8'), '7', 1.00, 6, NOW(), NOW()),
(1, '¿Cuál es el 50% de 100?', 'multiple_choice', JSON_ARRAY('25', '50', '75', '100'), '50', 1.00, 7, NOW(), NOW()),
(1, '¿Cuánto es 2² + 3²?', 'multiple_choice', JSON_ARRAY('10', '11', '12', '13'), '13', 1.00, 8, NOW(), NOW()),
(1, 'Simplifica: 6/12', 'multiple_choice', JSON_ARRAY('1/4', '1/3', '1/2', '2/3'), '1/2', 1.00, 9, NOW(), NOW()),
(1, '¿Cuánto es la raíz cuadrada de 16?', 'multiple_choice', JSON_ARRAY('2', '3', '4', '8'), '4', 1.00, 10, NOW(), NOW()),
(1, 'Orden de operaciones: 10 + 5 × 2', 'multiple_choice', JSON_ARRAY('20', '30', '15', '25'), '20', 1.00, 11, NOW(), NOW()),
(1, '¿Cuánto es 15% de 200?', 'multiple_choice', JSON_ARRAY('15', '20', '30', '50'), '30', 1.00, 12, NOW(), NOW());

-- INSERTAR PREGUNTAS - ÁLGEBRA INTERMEDIA (ID 2)
INSERT INTO diagnostic_questions (diagnostic_id, question_text, question_type, options, correct_answer, points, order_index, created_at, updated_at) VALUES
(2, 'Resuelve: 2x + 6 = 14', 'multiple_choice', JSON_ARRAY('2', '3', '4', '5'), '4', 1.00, 1, NOW(), NOW()),
(2, 'Factoriza: x² - 9', 'multiple_choice', JSON_ARRAY('(x-3)(x-3)', '(x+3)(x-3)', '(x+3)(x+3)', 'x(x-9)'), '(x+3)(x-3)', 1.00, 2, NOW(), NOW()),
(2, '¿Cuál es la pendiente de y = 3x + 2?', 'multiple_choice', JSON_ARRAY('1', '2', '3', '5'), '3', 1.00, 3, NOW(), NOW()),
(2, 'Resuelve: x² - 4 = 0', 'multiple_choice', JSON_ARRAY('x=2', 'x=-2', 'x=±2', 'x=4'), 'x=±2', 1.00, 4, NOW(), NOW()),
(2, 'Simplifica: (x³)²', 'multiple_choice', JSON_ARRAY('x⁵', 'x⁶', 'x⁹', '2x³'), 'x⁶', 1.00, 5, NOW(), NOW()),
(2, '¿Cuál es el vértice de y = (x-2)² + 3?', 'multiple_choice', JSON_ARRAY('(2,3)', '(-2,3)', '(2,-3)', '(3,2)'), '(2,3)', 1.00, 6, NOW(), NOW()),
(2, 'Resuelve el sistema: x+y=5, x-y=1', 'multiple_choice', JSON_ARRAY('x=2,y=3', 'x=3,y=2', 'x=4,y=1', 'x=1,y=4'), 'x=3,y=2', 1.00, 7, NOW(), NOW()),
(2, '¿Cuánto es (a+b)²?', 'multiple_choice', JSON_ARRAY('a²+b²', 'a²+2ab+b²', 'a²+ab+b²', '2a²+2b²'), 'a²+2ab+b²', 1.00, 8, NOW(), NOW()),
(2, 'Evalúa f(x)=2x+1 cuando x=3', 'multiple_choice', JSON_ARRAY('5', '6', '7', '8'), '7', 1.00, 9, NOW(), NOW()),
(2, '¿Cuál es la raíz de 2x-8=0?', 'multiple_choice', JSON_ARRAY('2', '3', '4', '8'), '4', 1.00, 10, NOW(), NOW()),
(2, 'Resuelve: 3(x-2) = 9', 'multiple_choice', JSON_ARRAY('3', '4', '5', '6'), '5', 1.00, 11, NOW(), NOW()),
(2, 'Dominio de f(x) = 1/x:', 'multiple_choice', JSON_ARRAY('Todos los reales', 'x≠0', 'x>0', 'x≥0'), 'x≠0', 1.00, 12, NOW(), NOW());

-- INSERTAR PREGUNTAS - FÍSICA GENERAL (ID 3)
INSERT INTO diagnostic_questions (diagnostic_id, question_text, question_type, options, correct_answer, points, order_index, created_at, updated_at) VALUES
(3, '¿Cuál es la unidad de fuerza en el SI?', 'multiple_choice', JSON_ARRAY('Joule', 'Newton', 'Watt', 'Pascal'), 'Newton', 1.00, 1, NOW(), NOW()),
(3, 'Primera Ley de Newton se refiere a:', 'multiple_choice', JSON_ARRAY('Inercia', 'Acción-Reacción', 'Gravedad', 'Energía'), 'Inercia', 1.00, 2, NOW(), NOW()),
(3, '¿Cuánto vale la aceleración de la gravedad?', 'multiple_choice', JSON_ARRAY('8.8 m/s²', '9.8 m/s²', '10.8 m/s²', '11.8 m/s²'), '9.8 m/s²', 1.00, 3, NOW(), NOW()),
(3, 'Fórmula de velocidad:', 'multiple_choice', JSON_ARRAY('v=d×t', 'v=d/t', 'v=t/d', 'v=d+t'), 'v=d/t', 1.00, 4, NOW(), NOW()),
(3, 'F = m × a es:', 'multiple_choice', JSON_ARRAY('1ra Ley Newton', '2da Ley Newton', '3ra Ley Newton', 'Ley Gravitación'), '2da Ley Newton', 1.00, 5, NOW(), NOW()),
(3, '¿Qué tipo de energía tiene un objeto en movimiento?', 'multiple_choice', JSON_ARRAY('Potencial', 'Cinética', 'Térmica', 'Química'), 'Cinética', 1.00, 6, NOW(), NOW()),
(3, 'Unidad de trabajo:', 'multiple_choice', JSON_ARRAY('Newton', 'Joule', 'Watt', 'Pascal'), 'Joule', 1.00, 7, NOW(), NOW()),
(3, '¿Cuál es la velocidad de la luz?', 'multiple_choice', JSON_ARRAY('3×10⁶ m/s', '3×10⁷ m/s', '3×10⁸ m/s', '3×10⁹ m/s'), '3×10⁸ m/s', 1.00, 8, NOW(), NOW()),
(3, 'La fuerza de rozamiento es:', 'multiple_choice', JSON_ARRAY('Siempre igual', 'Opuesta al movimiento', 'A favor del movimiento', 'Inexistente'), 'Opuesta al movimiento', 1.00, 9, NOW(), NOW()),
(3, '¿Qué es la inercia?', 'multiple_choice', JSON_ARRAY('Resistencia al cambio', 'Fuerza de gravedad', 'Velocidad constante', 'Aceleración'), 'Resistencia al cambio', 1.00, 10, NOW(), NOW()),
(3, 'Momento = ?', 'multiple_choice', JSON_ARRAY('m×v', 'm/v', 'v/m', 'm+v'), 'm×v', 1.00, 11, NOW(), NOW()),
(3, 'Energía potencial:', 'multiple_choice', JSON_ARRAY('mgh', 'mv²/2', 'Fd', 'P/t'), 'mgh', 1.00, 12, NOW(), NOW());

SELECT '✅ Preguntas de Diagnóstico insertadas (36 preguntas en total).' AS status;


-- ========================================
-- 3. DATOS ML Y RUTAS INICIALES (Usuario 3: Carla Gómez)
-- ========================================

-- Progreso del estudiante (Carla Gómez, ID 3)
INSERT INTO student_progress 
    (user_id, subject_area, total_activities, completed_activities, progress_percentage, average_score, total_time_spent, last_activity, created_at, updated_at) 
VALUES 
    (3, 'Matemáticas', 20, 15, 75.00, 88.50, 7200, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    total_activities = VALUES(total_activities), completed_activities = VALUES(completed_activities), updated_at = NOW();

-- Ruta de Aprendizaje (ID 1)
INSERT INTO learning_paths 
    (user_id, subject_area, name, description, difficulty_level, estimated_duration, progress_percentage, is_completed, created_at, updated_at) 
VALUES 
    (
        3, 
        'Matemáticas', 
        'Ruta de Refuerzo de Álgebra', 
        'Ruta generada por el ML para reforzar conceptos básicos de álgebra.', 
        'Intermedio', 
        180, 
        10.00,
        0, 
        NOW(), 
        NOW()
    )
ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = NOW();

-- Contenidos dentro de la Ruta de Aprendizaje 1
-- **IMPORTANTE**: Corregido el nombre de la tabla de 'learning_path_contents' a 'learning_path_content' 
-- y el campo 'order' a 'order_index' para coincidir con el esquema.
INSERT INTO learning_path_content 
    (learning_path_id, content_id, order_index, is_required, is_completed, created_at, updated_at) 
VALUES
    -- Contenido 1: Álgebra Básica
    (1, 1, 1, 1, 0, NOW(), NOW()), 
    -- Contenido 2: Leyes de Newton
    (1, 2, 2, 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE order_index = VALUES(order_index), updated_at = NOW();

-- Recomendaciones de contenido para el Usuario 3
INSERT INTO recommendations 
    (user_id, content_id, reason, priority, is_viewed, is_completed, generated_by, created_at, updated_at) 
VALUES 
    (
        3, 
        1, 
        'Recomendación basada en bajo rendimiento en el último diagnóstico de Álgebra.', 
        1, 
        0, 
        0, 
        'ai', 
        NOW(), 
        NOW()
    ),
    (
        3, 
        2, 
        'Contenido sugerido para el avance curricular en Física.', 
        2, 
        0, 
        0, 
        'system', 
        NOW(), 
        NOW()
    )
ON DUPLICATE KEY UPDATE priority = VALUES(priority), updated_at = NOW();

SELECT '✅ Datos de progreso, ruta de aprendizaje y recomendaciones iniciales insertados para Carla Gómez.' AS status;


-- ========================================
-- 4. DATOS DE MONITOREO Y LOGS (Simulación de actividad)
-- ========================================

-- 1. Actualizar updated_at de usuarios existentes para simular actividad reciente
UPDATE users 
SET updated_at = NOW() - INTERVAL FLOOR(RAND() * 24) HOUR
WHERE active = 1 AND id <= 10;

-- 2. Insertar registros en system_usage_logs (Simular actividad de las últimas 24 horas)
INSERT INTO system_usage_logs (user_id, action, module, ip_address, created_at)
SELECT 
    u.id,
    CASE FLOOR(RAND() * 5)
        WHEN 0 THEN 'page_view'
        WHEN 1 THEN 'content_viewed'
        WHEN 2 THEN 'diagnostic_activity'
        WHEN 3 THEN 'login'
        ELSE 'data_modified'
    END as action,
    CASE FLOOR(RAND() * 4)
        WHEN 0 THEN 'dashboard'
        WHEN 1 THEN 'content'
        WHEN 2 THEN 'diagnostic'
        ELSE 'progress'
    END as module,
    '127.0.0.1' as ip_address,
    NOW() - INTERVAL FLOOR(RAND() * 24) HOUR as created_at
FROM users u
WHERE u.active = 1 AND u.id <= 10
LIMIT 50;

-- 3. Actualizar views en content_library
UPDATE content_library
SET views = views + FLOOR(RAND() * 100) + 10
WHERE active = 1 AND id <= 3;

SELECT '✅ Datos de monitoreo y actividad simulados.' AS status;


-- =================================================================
-- 5. FASE DE INSERCIÓN MASIVA DE DATOS DE PRUEBA (EST011 a EST025)
-- Este bloque es para generar una gran cantidad de datos para testing.
-- =================================================================
START TRANSACTION;

-- Desactivar Safe Update Mode temporalmente para limpieza masiva
SET SQL_SAFE_UPDATES = 0;

-- 1. Identificar y Eliminar estudiantes de prueba EST011 a EST025 si ya existen
DROP TEMPORARY TABLE IF EXISTS tmp_ids_to_delete;
CREATE TEMPORARY TABLE tmp_ids_to_delete (user_id BIGINT PRIMARY KEY); 

INSERT INTO tmp_ids_to_delete (user_id)
SELECT id 
FROM users 
WHERE student_code BETWEEN 'EST011' AND 'EST025';

DELETE dr FROM diagnostic_responses dr JOIN tmp_ids_to_delete t ON dr.user_id = t.user_id;
DELETE sp FROM student_progress sp JOIN tmp_ids_to_delete t ON sp.user_id = t.user_id;
DELETE u FROM users u JOIN tmp_ids_to_delete t ON u.id = t.user_id;

DROP TEMPORARY TABLE IF EXISTS tmp_ids_to_delete;

SELECT '✅ Limpieza de datos de prueba EST011-EST025 previa completada.' AS status;

-- 2. INSERTAR 15 ESTUDIANTES NUEVOS (Administración Semestre 4)
INSERT INTO users (name, email, email_verified_at, password, student_code, career, semester, phone, role_id, active, created_at, updated_at) VALUES
('María González Test', 'maria.gonzalez.test@uc.cl', NOW() - INTERVAL 10 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST011', 'Administración', 4, '+56911111111', 3, 1, NOW() - INTERVAL 60 DAY, NOW()),
('Pedro Ramírez Test', 'pedro.ramirez.test@uc.cl', NOW() - INTERVAL 5 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST012', 'Administración', 4, '+56922222222', 3, 1, NOW() - INTERVAL 55 DAY, NOW()),
('Lucía Fernández Test', 'lucia.fernandez.test@uc.cl', NOW() - INTERVAL 8 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST013', 'Administración', 4, '+56933333333', 3, 1, NOW() - INTERVAL 50 DAY, NOW()),
('Andrés Silva Test', 'andres.silva.test@uc.cl', NOW() - INTERVAL 12 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST014', 'Administración', 4, '+56944444444', 3, 1, NOW() - INTERVAL 70 DAY, NOW()),
('Valentina Torres Test', 'valentina.torres.test@uc.cl', NOW() - INTERVAL 3 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST015', 'Administración', 4, '+56955555555', 3, 1, NOW() - INTERVAL 45 DAY, NOW()),
('Diego Morales Test', 'diego.morales.test@uc.cl', NOW() - INTERVAL 15 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST016', 'Administración', 4, '+56966666666', 3, 1, NOW() - INTERVAL 80 DAY, NOW()),
('Sofía Castro Test', 'sofia.castro.test@uc.cl', NOW() - INTERVAL 6 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST017', 'Administración', 4, '+56977777777', 3, 1, NOW() - INTERVAL 65 DAY, NOW()),
('Matías Vargas Test', 'matias.vargas.test@uc.cl', NOW() - INTERVAL 20 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST018', 'Administración', 4, '+56988888888', 3, 1, NOW() - INTERVAL 90 DAY, NOW()),
('Camila Reyes Test', 'camila.reyes.test@uc.cl', NOW() - INTERVAL 4 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST019', 'Administración', 4, '+56999999999', 3, 1, NOW() - INTERVAL 40 DAY, NOW()),
('Sebastián Herrera Test', 'sebastian.herrera.test@uc.cl', NOW() - INTERVAL 7 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST020', 'Administración', 4, '+56910101010', 3, 1, NOW() - INTERVAL 75 DAY, NOW()),
('Isidora Muñoz Test', 'isidora.munoz.test@uc.cl', NOW() - INTERVAL 2 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST021', 'Administración', 4, '+56920202020', 3, 1, NOW() - INTERVAL 48 DAY, NOW()),
('Martín Sánchez Test', 'martin.sanchez.test@uc.cl', NOW() - INTERVAL 11 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST022', 'Administración', 4, '+56930303030', 3, 1, NOW() - INTERVAL 62 DAY, NOW()),
('Javiera Ortiz Test', 'javiera.ortiz.test@uc.cl', NOW() - INTERVAL 9 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST023', 'Administración', 4, '+56940404040', 3, 1, NOW() - INTERVAL 54 DAY, NOW()),
('Benjamín Pinto Test', 'benjamin.pinto.test@uc.cl', NOW() - INTERVAL 14 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST024', 'Administración', 4, '+56950505050', 3, 1, NOW() - INTERVAL 85 DAY, NOW()),
('Antonia Lagos Test', 'antonia.lagos.test@uc.cl', NOW() - INTERVAL 1 DAY, '$2y$12$uSZ7JN9H8tVPAlr9RgdgeunmDlZl/pNDASFV69AvRLSGGo4LvuNxG', 'EST025', 'Administración', 4, '+56960606060', 3, 1, NOW() - INTERVAL 42 DAY, NOW());

-- Obtener ID del primer estudiante nuevo
SET @first_id = (SELECT id FROM users WHERE student_code = 'EST011');

SELECT CONCAT('✅ 15 Estudiantes de prueba (EST011-EST025) creados. Primer ID: ', @first_id) as info;


-- 3. CREAR RESPUESTAS DE DIAGNÓSTICO DE PRUEBA (200 en total)
SET @row = 0; 
SET @min_q = (SELECT MIN(id) FROM diagnostic_questions);
SET @max_q = (SELECT MAX(id) FROM diagnostic_questions);

INSERT INTO diagnostic_responses (user_id, diagnostic_id, question_id, user_answer, is_correct, points_earned, time_spent_seconds, created_at, updated_at)
SELECT 
    @first_id + MOD(seq, 15) as user_id,
    1 + MOD(seq, 3) as diagnostic_id,
    @min_q + MOD(seq, (@max_q - @min_q + 1)) as question_id,
    CAST(1 + MOD(seq, 4) AS CHAR) as user_answer,
    IF(MOD(seq, 10) < 7, 1, 0) as is_correct, -- 70% de acierto
    IF(MOD(seq, 10) < 7, 1.00, 0.00) as points_earned,
    30 + MOD(seq * 7, 120) as time_spent_seconds,
    NOW() - INTERVAL MOD(seq, 60) DAY as created_at,
    NOW() as updated_at
FROM (
    -- Genera la secuencia de 200 filas 
    SELECT (@row := @row + 1) - 1 as seq
    FROM 
        (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
        (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
        (SELECT 0 UNION ALL SELECT 1) t3 
    LIMIT 200
) seq_table
WHERE @min_q IS NOT NULL AND @max_q IS NOT NULL;

SELECT CONCAT('✅ Registros de progreso: ', COUNT(*)) AS info FROM student_progress WHERE user_id >= @first_id;



-- 4. CREAR PROGRESO DE ESTUDIANTES DE PRUEBA (Matemáticas y Administración)
-- Progreso en Matemáticas (LIMPIO DE CARACTERES OCULTOS)
INSERT INTO student_progress (
    user_id, subject_area, topic, total_activities, completed_activities, 
    progress_percentage, average_score, total_time_spent, last_activity, 
    created_at, updated_at
)
SELECT
    u.id,
    'Matemáticas' as subject_area,
    'Fundamentos' as topic,
    20 as total_activities,
    5 + MOD(u.id, 15) as completed_activities,
    ROUND((5 + MOD(u.id, 15)) / 20 * 100, 2) as progress_percentage,
    50 + MOD(u.id * 7, 45) as average_score,
    120 + MOD(u.id * 13, 500) as total_time_spent,
    NOW() - INTERVAL MOD(u.id, 20) DAY as last_activity,
    NOW() - INTERVAL 50 DAY as created_at,
    NOW() as updated_at
FROM users u
WHERE u.id >= @first_id AND u.role_id = 3;

-- Progreso en Administración (CORREGIDO Y LIMPIO)
INSERT INTO student_progress (
    user_id, subject_area, topic, total_activities, completed_activities, 
    progress_percentage, average_score, total_time_spent, last_activity, 
    created_at, updated_at
)
SELECT
    u.id,
    'Administración' as subject_area,
    'Fundamentos' as topic,
    25 as total_activities,
    8 + MOD(u.id, 17) as completed_activities,
    ROUND((8 + MOD(u.id, 17)) / 25 * 100, 2) as progress_percentage,
    45 + MOD(u.id * 11, 50) as average_score,
    200 + MOD(u.id * 17, 600) as total_time_spent,
    NOW() - INTERVAL MOD(u.id, 25) DAY as last_activity,
    NOW() - INTERVAL 50 DAY as created_at, -- Se añadió esta línea para corregir el Error 1136
    NOW() as updated_at
FROM users u
WHERE u.id >= @first_id AND u.role_id = 3;

SELECT CONCAT('✅ ', (SELECT COUNT(*) FROM student_progress WHERE user_id >= @first_id), ' Registros de progreso de prueba generados.') AS info;

-- Volver a activar Safe Update Mode
SET SQL_SAFE_UPDATES = 1;

COMMIT;

SELECT '==========================================';
SELECT '✅ SCRIPT 02 COMPLETADO. DATOS INICIALES Y DE PRUEBA CARGADOS.' as mensaje;
SELECT '==========================================';