-- Eliminar base de datos si existe
DROP DATABASE IF EXISTS `bd_microlearning_uc`;
CREATE DATABASE `bd_microlearning_uc`;
USE `bd_microlearning_uc`;

-- --------------------------------------------------------
-- TABLAS BÁSICAS DE LARAVEL
-- --------------------------------------------------------

-- 1. TABLA: users (con campos adicionales para el sistema)
CREATE TABLE users (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `student_code` VARCHAR(255) NULL UNIQUE,
    `career` VARCHAR(255) NULL,
    `semester` INT NULL,
    `phone` VARCHAR(255) NULL,
    `role_id` BIGINT UNSIGNED NULL,
    `active` BOOLEAN DEFAULT 1,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `users_role_id_index` (`role_id`)
);

-- Actualizar todas las contraseñas a "12345678" (8 caracteres)
-- Hash generado por Laravel para la contraseña "12345678"
-- Insertar usuarios con contraseña "12345678" (8 caracteres)
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

-- 2. TABLA: roles
CREATE TABLE roles (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
);

-- Insertar roles por defecto
INSERT INTO roles (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', NOW(), NOW()),
(2, 'Docente', 'Acceso para seguimiento de estudiantes', NOW(), NOW()),
(3, 'Estudiante', 'Acceso para realizar diagnósticos y seguir rutas de aprendizaje', NOW(), NOW());

-- Agregar foreign key para role_id en users
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL;

-- 3. TABLA: password_reset_tokens
CREATE TABLE password_reset_tokens (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    PRIMARY KEY (`email`)
);

-- 4. TABLA: sessions
CREATE TABLE sessions (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
);

-- 5. TABLA: jobs
CREATE TABLE jobs (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
);

-- 6. TABLA: failed_jobs
CREATE TABLE failed_jobs (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL UNIQUE,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

-- 7. TABLA: job_batches
CREATE TABLE job_batches (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT UNSIGNED NULL,
    `created_at` INT UNSIGNED NOT NULL,
    `finished_at` INT UNSIGNED NULL,
    PRIMARY KEY (`id`)
);

-- 8. TABLA: cache
CREATE TABLE cache (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`key`)
);

-- 9. TABLA: cache_locks
CREATE TABLE cache_locks (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`key`)
);

-- 10. TABLA: migrations
CREATE TABLE migrations (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    PRIMARY KEY (`id`)
);

-- Insertar migraciones ejecutadas
INSERT INTO migrations (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------
-- TABLAS ESPECÍFICAS DEL SISTEMA MICROLEARNING
-- --------------------------------------------------------

-- 11. TABLA: content_library (Biblioteca de contenidos)
CREATE TABLE content_library (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `subject_area` VARCHAR(100) NOT NULL,
    `topic` VARCHAR(100) NULL,
    `type` ENUM('Video', 'Documento', 'Interactivo', 'Quiz', 'Artículo') NOT NULL,
    `difficulty_level` ENUM('Básico', 'Intermedio', 'Avanzado') NOT NULL,
    `content_url` VARCHAR(500) NULL,
    `duration_minutes` INT NULL,
    `tags` TEXT NULL,
    `active` BOOLEAN DEFAULT 1,
    `views` INT DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `content_subject_area_index` (`subject_area`),
    KEY `content_type_index` (`type`),
    KEY `content_difficulty_index` (`difficulty_level`)
);

-- Insertar contenido de ejemplo
INSERT INTO content_library (`title`, `description`, `subject_area`, `topic`, `type`, `difficulty_level`, `content_url`, `duration_minutes`, `tags`, `active`, `views`, `created_at`, `updated_at`) VALUES
('Álgebra Básica', 'Introducción a los conceptos fundamentales del álgebra', 'Matemáticas', 'Álgebra', 'Video', 'Básico', 'https://example.com/algebra-basica', 45, 'álgebra,matemáticas,básico', 1, 156, NOW(), NOW()),
('Leyes de Newton', 'Explicación detallada de las tres leyes de Newton', 'Física', 'Mecánica', 'Documento', 'Intermedio', 'https://example.com/leyes-newton.pdf', 30, 'física,mecánica,newton', 1, 89, NOW(), NOW()),
('Tabla Periódica Interactiva', 'Exploración interactiva de la tabla periódica', 'Química', 'Elementos', 'Interactivo', 'Básico', 'https://example.com/tabla-periodica', 60, 'química,elementos,interactivo', 1, 234, NOW(), NOW());

-- 12. TABLA: student_progress (Progreso de estudiantes)
CREATE TABLE student_progress (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subject_area` VARCHAR(100) NOT NULL,
    `topic` VARCHAR(100) NULL,
    `total_activities` INT DEFAULT 0,
    `completed_activities` INT DEFAULT 0,
    `progress_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `average_score` DECIMAL(5,2) DEFAULT 0.00,
    `total_time_spent` INT DEFAULT 0, -- en minutos
    `last_activity` TIMESTAMP NULL,
    `weak_areas` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    KEY `progress_user_subject_index` (`user_id`, `subject_area`),
    KEY `progress_last_activity_index` (`last_activity`)
);

-- 13. TABLA: learning_paths (Rutas de aprendizaje)
CREATE TABLE learning_paths (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subject_area` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `difficulty_level` ENUM('Básico', 'Intermedio', 'Avanzado') NOT NULL,
    `estimated_duration` INT NULL, -- en horas
    `progress_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `is_completed` BOOLEAN DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    KEY `learning_paths_user_index` (`user_id`)
);

-- 14. TABLA: learning_path_content (Contenidos en rutas de aprendizaje)
CREATE TABLE learning_path_content (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `learning_path_id` BIGINT UNSIGNED NOT NULL,
    `content_id` BIGINT UNSIGNED NOT NULL,
    `order_index` INT NOT NULL DEFAULT 0,
    `is_required` BOOLEAN DEFAULT 1,
    `is_completed` BOOLEAN DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `time_spent` INT DEFAULT 0, -- en minutos
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`learning_path_id`) REFERENCES learning_paths(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_id`) REFERENCES content_library(`id`) ON DELETE CASCADE,
    KEY `lpc_path_order_index` (`learning_path_id`, `order_index`)
);

-- 15. TABLA: recommendations (Recomendaciones para estudiantes)
CREATE TABLE recommendations (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `content_id` BIGINT UNSIGNED NOT NULL,
    `reason` TEXT NULL,
    `priority` TINYINT DEFAULT 1, -- 1=alta, 2=media, 3=baja
    `is_viewed` BOOLEAN DEFAULT 0,
    `viewed_at` TIMESTAMP NULL,
    `is_completed` BOOLEAN DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `generated_by` ENUM('system', 'teacher', 'ai') DEFAULT 'system',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_id`) REFERENCES content_library(`id`) ON DELETE CASCADE,
    KEY `recommendations_user_priority_index` (`user_id`, `priority`)
);

-- 16. TABLA: diagnostics (Diagnósticos)
CREATE TABLE diagnostics (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `subject_area` VARCHAR(100) NOT NULL,
    `difficulty_level` ENUM('Básico', 'Intermedio', 'Avanzado') NOT NULL,
    `time_limit_minutes` INT NULL,
    `passing_score` DECIMAL(5,2) DEFAULT 70.00,
    `active` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `diagnostics_subject_index` (`subject_area`)
);

-- 17. TABLA: diagnostic_questions (Preguntas de diagnósticos)
CREATE TABLE diagnostic_questions (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `diagnostic_id` BIGINT UNSIGNED NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice', 'true_false', 'open_ended') NOT NULL,
    `options` JSON NULL, -- Para preguntas de opción múltiple
    `correct_answer` TEXT NOT NULL,
    `points` DECIMAL(4,2) DEFAULT 1.00,
    `order_index` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`diagnostic_id`) REFERENCES diagnostics(`id`) ON DELETE CASCADE,
    KEY `dq_diagnostic_order_index` (`diagnostic_id`, `order_index`)
);

-- 18. TABLA: diagnostic_responses (Respuestas a diagnósticos)
CREATE TABLE diagnostic_responses (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `diagnostic_id` BIGINT UNSIGNED NOT NULL,
    `question_id` BIGINT UNSIGNED NOT NULL,
    `user_answer` TEXT NOT NULL,
    `is_correct` BOOLEAN DEFAULT 0,
    `points_earned` DECIMAL(4,2) DEFAULT 0.00,
    `time_spent_seconds` INT DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diagnostic_id`) REFERENCES diagnostics(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES diagnostic_questions(`id`) ON DELETE CASCADE,
    KEY `dr_user_diagnostic_index` (`user_id`, `diagnostic_id`)
);

-- 19. TABLA: diagnostic_results (Resultados finales de diagnósticos)
CREATE TABLE diagnostic_results (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `diagnostic_id` BIGINT UNSIGNED NOT NULL,
    `total_questions` INT NOT NULL,
    `correct_answers` INT NOT NULL,
    `score_percentage` DECIMAL(5,2) NOT NULL,
    `total_points` DECIMAL(8,2) NOT NULL,
    `points_earned` DECIMAL(8,2) NOT NULL,
    `time_taken_minutes` INT NOT NULL,
    `passed` BOOLEAN DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diagnostic_id`) REFERENCES diagnostics(`id`) ON DELETE CASCADE,
    KEY `dr_user_index` (`user_id`),
    KEY `dr_diagnostic_index` (`diagnostic_id`)
);

SELECT id, name, email, role_id, active FROM users WHERE email = 'ana.perez@uc.cl';

-- --------------------------------------------------------
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- --------------------------------------------------------

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_student_code ON users(student_code);
CREATE INDEX idx_users_active ON users(active);
CREATE INDEX idx_content_active ON content_library(active);
CREATE INDEX idx_progress_user_updated ON student_progress(user_id, updated_at);
CREATE INDEX idx_recommendations_user_viewed ON recommendations(user_id, is_viewed);

-- --------------------------------------------------------
-- VISTAS ÚTILES PARA REPORTES
-- --------------------------------------------------------

-- Vista para estadísticas de usuarios por rol
CREATE VIEW user_stats_by_role AS
SELECT 
    r.name as role_name,
    COUNT(u.id) as total_users,
    COUNT(CASE WHEN u.active = 1 THEN 1 END) as active_users,
    COUNT(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_this_month
FROM roles r
LEFT JOIN users u ON r.id = u.role_id
GROUP BY r.id, r.name;

-- Vista para progreso general de estudiantes
CREATE VIEW student_progress_summary AS
SELECT 
    u.id as user_id,
    u.name as student_name,
    u.email,
    u.career,
    u.semester,
    COUNT(sp.id) as subjects_enrolled,
    AVG(sp.progress_percentage) as avg_progress,
    SUM(sp.total_time_spent) as total_study_time,
    MAX(sp.last_activity) as last_activity_date
FROM users u
LEFT JOIN student_progress sp ON u.id = sp.user_id
WHERE u.role_id = 3 AND u.active = 1
GROUP BY u.id, u.name, u.email, u.career, u.semester;
