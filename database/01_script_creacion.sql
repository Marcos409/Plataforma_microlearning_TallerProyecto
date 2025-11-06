-- database/01_create_tables.sql

-- Eliminar base de datos si existe
DROP DATABASE IF EXISTS `bd_microlearning_uc`;
CREATE DATABASE `bd_microlearning_uc`;
USE `bd_microlearning_uc`;

-- ========================================
-- TABLAS BÁSICAS
-- ========================================

-- Tabla: roles
CREATE TABLE roles (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
);

-- Tabla: users
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
    FOREIGN KEY (`role_id`) REFERENCES roles(`id`) ON DELETE SET NULL,
    KEY `users_role_id_index` (`role_id`),
    KEY `idx_users_email` (`email`),
    KEY `idx_users_student_code` (`student_code`),
    KEY `idx_users_active` (`active`)
);

-- Tabla: password_reset_tokens
CREATE TABLE password_reset_tokens (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    PRIMARY KEY (`email`)
);

-- Tabla: sessions
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

-- Tabla: content_library
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
    KEY `content_difficulty_index` (`difficulty_level`),
    KEY `idx_content_active` (`active`)
);

-- Tabla: student_progress
CREATE TABLE student_progress (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subject_area` VARCHAR(100) NOT NULL,
    `topic` VARCHAR(100) NULL,
    `total_activities` INT DEFAULT 0,
    `completed_activities` INT DEFAULT 0,
    `progress_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `average_score` DECIMAL(5,2) DEFAULT 0.00,
    `total_time_spent` INT DEFAULT 0,
    `last_activity` TIMESTAMP NULL,
    `weak_areas` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    KEY `progress_user_subject_index` (`user_id`, `subject_area`),
    KEY `progress_last_activity_index` (`last_activity`),
    KEY `idx_progress_user_updated` (`user_id`, `updated_at`)
);

-- Tabla: learning_paths
CREATE TABLE learning_paths (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subject_area` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `difficulty_level` ENUM('Básico', 'Intermedio', 'Avanzado') NOT NULL,
    `estimated_duration` INT NULL,
    `progress_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `is_completed` BOOLEAN DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    KEY `learning_paths_user_index` (`user_id`)
);

-- Tabla: learning_path_content
CREATE TABLE learning_path_content (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `learning_path_id` BIGINT UNSIGNED NOT NULL,
    `content_id` BIGINT UNSIGNED NOT NULL,
    `order_index` INT NOT NULL DEFAULT 0,
    `is_required` BOOLEAN DEFAULT 1,
    `is_completed` BOOLEAN DEFAULT 0,
    `completed_at` TIMESTAMP NULL,
    `time_spent` INT DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`learning_path_id`) REFERENCES learning_paths(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`content_id`) REFERENCES content_library(`id`) ON DELETE CASCADE,
    KEY `lpc_path_order_index` (`learning_path_id`, `order_index`)
);

-- Tabla: recommendations
CREATE TABLE recommendations (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `content_id` BIGINT UNSIGNED NOT NULL,
    `reason` TEXT NULL,
    `priority` TINYINT DEFAULT 1,
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
    KEY `recommendations_user_priority_index` (`user_id`, `priority`),
    KEY `idx_recommendations_user_viewed` (`user_id`, `is_viewed`)
);

-- Tabla: diagnostics
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

-- Tabla: diagnostic_questions
CREATE TABLE diagnostic_questions (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `diagnostic_id` BIGINT UNSIGNED NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice', 'true_false', 'open_ended') NOT NULL,
    `options` JSON NULL,
    `correct_answer` TEXT NOT NULL,
    `points` DECIMAL(4,2) DEFAULT 1.00,
    `order_index` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`diagnostic_id`) REFERENCES diagnostics(`id`) ON DELETE CASCADE,
    KEY `dq_diagnostic_order_index` (`diagnostic_id`, `order_index`)
);

-- Tabla: diagnostic_responses
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

-- Tabla: diagnostic_results
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

-- ========================================
-- VISTAS
-- ========================================

CREATE VIEW user_stats_by_role AS
SELECT 
    r.name as role_name,
    COUNT(u.id) as total_users,
    COUNT(CASE WHEN u.active = 1 THEN 1 END) as active_users,
    COUNT(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_this_month
FROM roles r
LEFT JOIN users u ON r.id = u.role_id
GROUP BY r.id, r.name;

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

-- =============================================
-- TABLA Y STORED PROCEDURES PARA SEGUIMIENTOS
-- =============================================
USE `bd_microlearning_uc`;

-- =============================================
-- CREAR TABLA FOLLOW_UPS (si no existe)
-- =============================================
CREATE TABLE IF NOT EXISTS `follow_ups` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `admin_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('meeting', 'call', 'video_call', 'email') NOT NULL,
    `scheduled_at` DATETIME NOT NULL,
    `completed_at` DATETIME NULL,
    `notes` TEXT NULL,
    `status` ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    KEY `idx_follow_ups_user_id` (`user_id`),
    KEY `idx_follow_ups_admin_id` (`admin_id`),
    KEY `idx_follow_ups_scheduled_at` (`scheduled_at`),
    KEY `idx_follow_ups_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla ml_analysis
CREATE TABLE IF NOT EXISTS `ml_analysis` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `diagnostico` VARCHAR(50) NULL COMMENT 'basico, intermedio, avanzado',
    `ruta_aprendizaje` VARCHAR(100) NULL,
    `nivel_riesgo` VARCHAR(50) NULL COMMENT 'bajo, medio, alto',
    `metricas` JSON NULL COMMENT 'Métricas del análisis ML',
    `recomendaciones` JSON NULL COMMENT 'Recomendaciones generadas',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`) ON DELETE CASCADE,
    KEY `idx_ml_user_id` (`user_id`),
    KEY `idx_ml_diagnostico` (`diagnostico`),
    KEY `idx_ml_riesgo` (`nivel_riesgo`),
    KEY `idx_ml_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

USE `bd_microlearning_uc`;

CREATE TABLE IF NOT EXISTS `risk_predictions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `nivel_riesgo` VARCHAR(50) NOT NULL, -- bajo, medio, alto
    `tiene_riesgo` BOOLEAN NOT NULL DEFAULT 0,
    `probabilidad_riesgo` DECIMAL(5, 4) NOT NULL,
    `severidad` VARCHAR(50) NOT NULL,
    `actividades_refuerzo` JSON NULL,
    `predicted_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ejecutar este script después de risk_predictions
CREATE TABLE IF NOT EXISTS `risk_alerts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `risk_prediction_id` BIGINT UNSIGNED NULL,
    `description` TEXT NULL,
    `severity` ENUM('bajo', 'medio', 'alto') NOT NULL DEFAULT 'bajo',
    `is_resolved` BOOLEAN NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`risk_prediction_id`) REFERENCES `risk_predictions`(`id`) ON DELETE SET NULL,
    KEY `idx_risk_alerts_user_resolved_severity` (`user_id`, `is_resolved`, `severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

select * from roles;