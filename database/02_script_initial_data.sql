-- database/02_initial_data.sql

USE `bd_microlearning_uc`;

-- ========================================
-- INSERTAR ROLES
-- ========================================
INSERT INTO roles (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', NOW(), NOW()),
(2, 'Docente', 'Acceso para seguimiento de estudiantes', NOW(), NOW()),
(3, 'Estudiante', 'Acceso para realizar diagnósticos y seguir rutas de aprendizaje', NOW(), NOW());
INSERT INTO roles (`id`, `name`, `description`, `created_at`, `updated_at`)
VALUES (4, 'Sin Rol', 'Usuarios registrados sin rol asignado', NOW(), NOW());

-- ========================================
-- INSERTAR USUARIOS
-- Contraseña para todos: "12345678"
-- ========================================
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

-- ========================================
-- INSERTAR CONTENIDOS
-- ========================================
INSERT INTO content_library (`title`, `description`, `subject_area`, `topic`, `type`, `difficulty_level`, `content_url`, `duration_minutes`, `tags`, `active`, `views`, `created_at`, `updated_at`) VALUES
('Álgebra Básica', 'Introducción a los conceptos fundamentales del álgebra', 'Matemáticas', 'Álgebra', 'Video', 'Básico', 'https://example.com/algebra-basica', 45, 'álgebra,matemáticas,básico', 1, 156, NOW(), NOW()),
('Leyes de Newton', 'Explicación detallada de las tres leyes de Newton', 'Física', 'Mecánica', 'Documento', 'Intermedio', 'https://example.com/leyes-newton.pdf', 30, 'física,mecánica,newton', 1, 89, NOW(), NOW()),
('Tabla Periódica Interactiva', 'Exploración interactiva de la tabla periódica', 'Química', 'Elementos', 'Interactivo', 'Básico', 'https://example.com/tabla-periodica', 60, 'química,elementos,interactivo', 1, 234, NOW(), NOW());

-- ========================================
-- INSERTAR DIAGNÓSTICOS
-- ========================================
INSERT INTO diagnostics (title, description, subject_area, difficulty_level, time_limit_minutes, passing_score, active, created_at, updated_at) VALUES
('Diagnóstico de Matemáticas Básicas', 'Evaluación de conocimientos fundamentales de aritmética y álgebra', 'Matemáticas', 'Básico', 30, 70.00, 1, NOW(), NOW()),
('Diagnóstico de Álgebra Intermedia', 'Ecuaciones, funciones y sistemas lineales', 'Matemáticas', 'Intermedio', 45, 75.00, 1, NOW(), NOW()),
('Diagnóstico de Física General', 'Cinemática, dinámica y leyes de Newton', 'Física', 'Básico', 40, 70.00, 1, NOW(), NOW());


-- ========================================-- ========================================-- ========================================
USE `bd_microlearning_uc`;

-- ========================================
-- 1. INSERTAR MÉTRICAS DE PROGRESO DEL ESTUDIANTE
--    (Usamos la tabla 'student_progress')
-- ========================================
-- Esto es crucial porque el dashboard suele calcular el progreso (promedio)
-- basado en esta tabla.
INSERT INTO student_progress 
    (user_id, subject_area, total_activities, completed_activities, progress_percentage, average_score, total_time_spent, last_activity, created_at, updated_at) 
VALUES 
    -- Datos de prueba para Carla Gómez (ID 3) en 'Matemáticas'
    (3, 'Matemáticas', 20, 15, 75.00, 88.50, 7200, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    total_activities = VALUES(total_activities), completed_activities = VALUES(completed_activities);
select * from student_progress;

-- ========================================
-- 2. INSERTAR RUTA DE APRENDIZAJE 
--    (Usamos la tabla 'learning_paths')
-- ========================================
INSERT INTO learning_paths 
    (user_id, subject_area, name, description, difficulty_level, estimated_duration, progress_percentage, is_completed, created_at, updated_at) 
VALUES 
    (
        3, 
        'Matemáticas', 
        'Ruta de Refuerzo de Álgebra', 
        'Ruta generada por el ML para reforzar conceptos básicos de álgebra.', 
        'Intermedio', 
        180, -- Duración estimada en minutos
        10.00,
        0, -- No completada
        NOW(), 
        NOW()
    );
select * from  learning_paths;
-- Nota: Si la ruta tiene que mostrar contenidos específicos, la tabla 
-- `learning_path_content` también debe llenarse con el ID de la ruta recién creada.


-- ========================================
-- 3. INSERTAR RECOMENDACIONES DE CONTENIDO
--    (Usamos la tabla 'recommendations')
-- ========================================
select * from recommendations;
INSERT INTO recommendations 
    (user_id, content_id, reason, priority, is_viewed, is_completed, generated_by, created_at, updated_at) 
VALUES 
    -- Recomendación 1: Álgebra Básica (ID 1 de content_library)
    (
        3, 
        1, 
        'Recomendación basada en bajo rendimiento en el último diagnóstico de Álgebra.', 
        1, -- Prioridad alta (1 es la más alta)
        0, 
        0, 
        'ai', 
        NOW(), 
        NOW()
    ),
    -- Recomendación 2: Leyes de Newton (ID 2 de content_library)
    (
        3, 
        2, 
        'Contenido sugerido para el avance curricular en Física.', 
        2, -- Prioridad media
        0, 
        0, 
        'system', 
        NOW(), 
        NOW()
    );
    
-- ========================================
USE `bd_microlearning_uc`;

CREATE TABLE IF NOT EXISTS `learning_path_contents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `learning_path_id` BIGINT UNSIGNED NOT NULL,
    `content_id` BIGINT UNSIGNED NOT NULL,
    `order` INT NOT NULL COMMENT 'Orden en la ruta de aprendizaje',
    `is_completed` BOOLEAN NOT NULL DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    PRIMARY KEY (`id`),
    -- Asegura que un contenido solo esté una vez por ruta
    UNIQUE KEY `path_content_unique` (`learning_path_id`, `content_id`), 
    
    -- Clave Foránea a learning_paths
    FOREIGN KEY (`learning_path_id`) REFERENCES `learning_paths`(`id`) ON DELETE CASCADE,
    
    -- Clave Foránea a content_library
    FOREIGN KEY (`content_id`) REFERENCES `content_library`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ========================================
USE `bd_microlearning_uc`;

-- Enlaza la Ruta ID 1 con los Contenidos ID 1 y ID 2
INSERT INTO learning_path_contents 
    (learning_path_id, content_id, `order`, is_completed, created_at, updated_at) 
VALUES
    -- Contenido 1: Álgebra Básica, como el primer paso
    (1, 1, 1, 0, NOW(), NOW()), 
    -- Contenido 2: Leyes de Newton, como el segundo paso
    (1, 2, 2, 0, NOW(), NOW());