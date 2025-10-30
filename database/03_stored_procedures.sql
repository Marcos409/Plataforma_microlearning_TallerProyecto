-- =============================================
-- STORED PROCEDURES COMPLETOS - MICROLEARNING UC
-- Archivo limpio sin duplicados
-- Versión: 1.0 - Octubre 2024
-- =============================================

USE `bd_microlearning_uc`;

-- =============================================
-- CREAR TABLA FOLLOW_UPS (NUEVO)
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

DELIMITER //

-- =============================================
-- SECCIÓN 1: STORED PROCEDURES PARA USUARIOS
-- =============================================

DROP PROCEDURE IF EXISTS sp_obtener_usuario//
CREATE PROCEDURE sp_obtener_usuario(IN p_id BIGINT)
BEGIN
    DECLARE user_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO user_exists FROM users WHERE id = p_id;
    
    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario no encontrado';
    END IF;
    
    SELECT 
        u.id, u.name, u.email, u.student_code, u.career, u.semester,
        u.phone, u.role_id, u.active, u.created_at, u.updated_at,
        r.name as role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.id = p_id;
END//

DROP PROCEDURE IF EXISTS sp_listar_usuarios//
CREATE PROCEDURE sp_listar_usuarios()
BEGIN
    SELECT 
        u.id, u.name, u.email, u.student_code, u.career, u.active,
        r.name as role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.name;
END//

-- Eliminar el procedimiento si existe
DROP PROCEDURE IF EXISTS sp_buscar_usuarios;

-- Cambiar el delimitador
DELIMITER //

-- Crear el procedimiento
CREATE PROCEDURE sp_buscar_usuarios(
    IN p_search VARCHAR(255),
    IN p_role_id BIGINT,
    IN p_active TINYINT(1)
)
BEGIN
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.student_code, 
        u.career, 
        u.semester,
        u.phone,
        u.active,
        u.role_id,
        r.name as role_name,
        u.created_at,
        u.updated_at
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE 
        (p_search IS NULL OR u.name LIKE CONCAT('%', p_search, '%') OR u.email LIKE CONCAT('%', p_search, '%'))
        AND (p_role_id IS NULL OR u.role_id = p_role_id)
        AND (p_active IS NULL OR u.active = p_active)
    ORDER BY u.name;
END//

-- Restaurar el delimitador
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_crear_usuario//
CREATE PROCEDURE sp_crear_usuario(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_password VARCHAR(255),
    IN p_role_id BIGINT,
    IN p_student_code VARCHAR(255),
    IN p_career VARCHAR(255),
    OUT p_user_id BIGINT
)
BEGIN
    DECLARE email_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO email_exists FROM users WHERE email = p_email;
    
    IF email_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El email ya está registrado';
    END IF;
    
    INSERT INTO users (name, email, password, role_id, student_code, career, active, created_at, updated_at)
    VALUES (p_name, p_email, p_password, p_role_id, p_student_code, p_career, 1, NOW(), NOW());
    
    SET p_user_id = LAST_INSERT_ID();
END//

DROP PROCEDURE IF EXISTS sp_crear_usuario_completo//
CREATE PROCEDURE sp_crear_usuario_completo(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_password VARCHAR(255),
    IN p_role_id BIGINT,
    IN p_student_code VARCHAR(255),
    IN p_career VARCHAR(255),
    IN p_semester INT,
    IN p_phone VARCHAR(255)
)
BEGIN
    DECLARE email_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO email_exists FROM users WHERE email = p_email;
    
    IF email_exists > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El email ya está registrado';
    END IF;
    
    INSERT INTO users (
        name, email, password, role_id, student_code, career, 
        semester, phone, active, created_at, updated_at
    ) VALUES (
        p_name, p_email, p_password, p_role_id, p_student_code, p_career,
        p_semester, p_phone, 1, NOW(), NOW()
    );
    
    SELECT LAST_INSERT_ID() as id;
END//

DROP PROCEDURE IF EXISTS sp_actualizar_usuario//
CREATE PROCEDURE sp_actualizar_usuario(
    IN p_user_id BIGINT,
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_role_id BIGINT,
    IN p_student_code VARCHAR(255),
    IN p_career VARCHAR(255)
)
BEGIN
    DECLARE user_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO user_exists FROM users WHERE id = p_user_id;
    
    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario no encontrado';
    END IF;
    
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email AND id != p_user_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El email ya está en uso';
    END IF;
    
    UPDATE users 
    SET name = p_name, email = p_email, role_id = p_role_id,
        student_code = p_student_code, career = p_career, updated_at = NOW()
    WHERE id = p_user_id;
END//

DROP PROCEDURE IF EXISTS sp_actualizar_usuario_completo//
CREATE PROCEDURE sp_actualizar_usuario_completo(
    IN p_user_id BIGINT,
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_role_id BIGINT,
    IN p_student_code VARCHAR(255),
    IN p_career VARCHAR(255),
    IN p_semester INT,
    IN p_phone VARCHAR(255)
)
BEGIN
    DECLARE user_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO user_exists FROM users WHERE id = p_user_id;
    
    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario no encontrado';
    END IF;
    
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email AND id != p_user_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El email ya está en uso';
    END IF;
    
    UPDATE users 
    SET name = p_name, email = p_email, role_id = p_role_id,
        student_code = p_student_code, career = p_career,
        semester = p_semester, phone = p_phone, updated_at = NOW()
    WHERE id = p_user_id;
    
    SELECT ROW_COUNT() as affected_rows;
END//

DROP PROCEDURE IF EXISTS sp_eliminar_usuario//
CREATE PROCEDURE sp_eliminar_usuario(IN p_user_id BIGINT)
BEGIN
    DECLARE user_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO user_exists FROM users WHERE id = p_user_id;
    
    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario no encontrado';
    END IF;
    
    DELETE FROM users WHERE id = p_user_id;
END//

DROP PROCEDURE IF EXISTS sp_cambiar_estado_usuario//
CREATE PROCEDURE sp_cambiar_estado_usuario(IN p_user_id BIGINT, IN p_active TINYINT(1))
BEGIN
    UPDATE users 
    SET active = p_active, updated_at = NOW()
    WHERE id = p_user_id;
END//

DROP PROCEDURE IF EXISTS sp_actualizar_password//
CREATE PROCEDURE sp_actualizar_password(IN p_user_id BIGINT, IN p_password VARCHAR(255))
BEGIN
    UPDATE users 
    SET password = p_password, updated_at = NOW()
    WHERE id = p_user_id;
END//

DROP PROCEDURE IF EXISTS sp_actualizar_rol_masivo//
CREATE PROCEDURE sp_actualizar_rol_masivo(IN p_user_ids TEXT, IN p_role_id BIGINT)
BEGIN
    UPDATE users 
    SET role_id = p_role_id, updated_at = NOW()
    WHERE FIND_IN_SET(id, p_user_ids) > 0;
END//

DROP PROCEDURE IF EXISTS sp_verificar_email//
CREATE PROCEDURE sp_verificar_email(IN p_email VARCHAR(255))
BEGIN
    SELECT COUNT(*) as exists_flag
    FROM users
    WHERE email = p_email;
END//

DROP PROCEDURE IF EXISTS sp_listar_roles//
CREATE PROCEDURE sp_listar_roles()
BEGIN
    SELECT id, name, description
    FROM roles
    ORDER BY id;
END//

DROP PROCEDURE IF EXISTS sp_contar_usuarios_por_rol//
CREATE PROCEDURE sp_contar_usuarios_por_rol(IN p_role_id BIGINT)
BEGIN
    SELECT COUNT(*) as total
    FROM users
    WHERE role_id = p_role_id AND active = 1;
END//

DROP PROCEDURE IF EXISTS sp_obtener_estudiantes_filtros//
CREATE PROCEDURE sp_obtener_estudiantes_filtros(
    IN p_search VARCHAR(255),
    IN p_status TINYINT(1)
)
BEGIN
    SELECT 
        u.id, u.name, u.email, u.student_code, u.career,
        u.semester, u.active, u.created_at,
        COALESCE(MAX(sp.last_activity), u.created_at) as last_activity
    FROM users u
    LEFT JOIN student_progress sp ON u.id = sp.user_id
    WHERE u.role_id = 3
        AND (p_search IS NULL OR 
             u.name LIKE CONCAT('%', p_search, '%') OR 
             u.email LIKE CONCAT('%', p_search, '%'))
        AND (p_status IS NULL OR u.active = p_status)
    GROUP BY u.id, u.name, u.email, u.student_code, u.career, u.semester, u.active, u.created_at
    ORDER BY u.name;
END//

-- =============================================
-- SECCIÓN 2: STORED PROCEDURES PARA CONTENIDOS
-- =============================================

DROP PROCEDURE IF EXISTS sp_listar_contenidos//
CREATE PROCEDURE sp_listar_contenidos(
    IN p_subject_area VARCHAR(100),
    IN p_difficulty_level VARCHAR(20),
    IN p_type VARCHAR(50)
)
BEGIN
    SELECT 
        id, title, description, subject_area, topic, type,
        difficulty_level, content_url, duration_minutes, tags,
        active, views, created_at, updated_at
    FROM content_library
    WHERE 
        (p_subject_area IS NULL OR subject_area = p_subject_area)
        AND (p_difficulty_level IS NULL OR difficulty_level = p_difficulty_level)
        AND (p_type IS NULL OR type = p_type)
        AND active = 1
    ORDER BY created_at DESC;
END//

DROP PROCEDURE IF EXISTS sp_listar_contenidos_filtrados//
CREATE PROCEDURE sp_listar_contenidos_filtrados(
    IN p_subject_area VARCHAR(100),
    IN p_type VARCHAR(50),
    IN p_difficulty_level VARCHAR(20),
    IN p_search VARCHAR(255)
)
BEGIN
    SELECT 
        id, title, description, subject_area, topic, type,
        difficulty_level, content_url, duration_minutes, tags,
        active, views, created_at, updated_at
    FROM content_library
    WHERE 
        (p_subject_area IS NULL OR subject_area = p_subject_area)
        AND (p_type IS NULL OR type = p_type)
        AND (p_difficulty_level IS NULL OR difficulty_level = p_difficulty_level)
        AND (p_search IS NULL OR 
             title LIKE CONCAT('%', p_search, '%') OR 
             description LIKE CONCAT('%', p_search, '%') OR
             tags LIKE CONCAT('%', p_search, '%'))
    ORDER BY created_at DESC;
END//

DROP PROCEDURE IF EXISTS sp_obtener_contenido//
CREATE PROCEDURE sp_obtener_contenido(IN p_id BIGINT)
BEGIN
    SELECT 
        id, title, description, subject_area, topic, type,
        difficulty_level, content_url, duration_minutes, tags,
        active, views, created_at, updated_at
    FROM content_library WHERE id = p_id;
END//

DROP PROCEDURE IF EXISTS sp_crear_contenido//
CREATE PROCEDURE sp_crear_contenido(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_subject_area VARCHAR(100),
    IN p_topic VARCHAR(100),
    IN p_type ENUM('Video', 'Documento', 'Interactivo', 'Quiz', 'Artículo'),
    IN p_difficulty_level ENUM('Básico', 'Intermedio', 'Avanzado'),
    IN p_content_url VARCHAR(500),
    IN p_duration_minutes INT,
    IN p_tags TEXT,
    IN p_active BOOLEAN
)
BEGIN
    INSERT INTO content_library (
        title, description, subject_area, topic, type,
        difficulty_level, content_url, duration_minutes, tags,
        active, views, created_at, updated_at
    ) VALUES (
        p_title, p_description, p_subject_area, p_topic, p_type,
        p_difficulty_level, p_content_url, p_duration_minutes, p_tags,
        IFNULL(p_active, 1), 0, NOW(), NOW()
    );
    SELECT LAST_INSERT_ID() as id;
END//

DROP PROCEDURE IF EXISTS sp_actualizar_contenido//
CREATE PROCEDURE sp_actualizar_contenido(
    IN p_id BIGINT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_subject_area VARCHAR(100),
    IN p_topic VARCHAR(100),
    IN p_type ENUM('Video', 'Documento', 'Interactivo', 'Quiz', 'Artículo'),
    IN p_difficulty_level ENUM('Básico', 'Intermedio', 'Avanzado'),
    IN p_content_url VARCHAR(500),
    IN p_duration_minutes INT,
    IN p_tags TEXT,
    IN p_active BOOLEAN
)
BEGIN
    UPDATE content_library
    SET title = p_title, description = p_description,
        subject_area = p_subject_area, topic = p_topic, type = p_type,
        difficulty_level = p_difficulty_level, content_url = p_content_url,
        duration_minutes = p_duration_minutes, tags = p_tags,
        active = p_active, updated_at = NOW()
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

DROP PROCEDURE IF EXISTS sp_eliminar_contenido//
CREATE PROCEDURE sp_eliminar_contenido(IN p_id BIGINT)
BEGIN
    UPDATE content_library
    SET active = 0, updated_at = NOW()
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

DROP PROCEDURE IF EXISTS sp_incrementar_vistas_contenido//
CREATE PROCEDURE sp_incrementar_vistas_contenido(IN p_id BIGINT)
BEGIN
    UPDATE content_library SET views = views + 1 WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

DROP PROCEDURE IF EXISTS sp_obtener_areas_unicas//
CREATE PROCEDURE sp_obtener_areas_unicas()
BEGIN
    SELECT DISTINCT subject_area
    FROM content_library
    WHERE active = 1
    ORDER BY subject_area ASC;
END//

DROP PROCEDURE IF EXISTS sp_obtener_tipos_unicos//
CREATE PROCEDURE sp_obtener_tipos_unicos()
BEGIN
    SELECT DISTINCT type
    FROM content_library
    WHERE active = 1
    ORDER BY type ASC;
END//

DROP PROCEDURE IF EXISTS sp_contenidos_mas_vistos//
CREATE PROCEDURE sp_contenidos_mas_vistos(IN p_limit INT)
BEGIN
    SELECT id, title, subject_area, type, difficulty_level, views, content_url, created_at
    FROM content_library
    WHERE active = 1
    ORDER BY views DESC
    LIMIT p_limit;
END//

DROP PROCEDURE IF EXISTS sp_buscar_contenidos//
CREATE PROCEDURE sp_buscar_contenidos(IN p_keyword VARCHAR(255))
BEGIN
    SELECT id, title, description, subject_area, topic, type,
           difficulty_level, content_url, duration_minutes, views, created_at
    FROM content_library
    WHERE active = 1
    AND (title LIKE CONCAT('%', p_keyword, '%')
         OR description LIKE CONCAT('%', p_keyword, '%')
         OR tags LIKE CONCAT('%', p_keyword, '%')
         OR subject_area LIKE CONCAT('%', p_keyword, '%'))
    ORDER BY views DESC, created_at DESC;
END//

DROP PROCEDURE IF EXISTS sp_contenidos_recomendados_usuario//
CREATE PROCEDURE sp_contenidos_recomendados_usuario(IN p_user_id BIGINT, IN p_limit INT)
BEGIN
    SELECT c.id, c.title, c.description, c.subject_area, c.type,
           c.difficulty_level, c.content_url, c.duration_minutes, c.views
    FROM content_library c
    INNER JOIN student_progress sp ON c.subject_area = sp.subject_area
    WHERE sp.user_id = p_user_id AND c.active = 1
    AND c.id NOT IN (
        SELECT content_id FROM learning_path_content lpc
        INNER JOIN learning_paths lp ON lpc.learning_path_id = lp.id
        WHERE lp.user_id = p_user_id AND lpc.is_completed = 1
    )
    ORDER BY c.views DESC, c.created_at DESC
    LIMIT p_limit;
END//

DROP PROCEDURE IF EXISTS sp_contar_contenidos_por_tipo//
CREATE PROCEDURE sp_contar_contenidos_por_tipo()
BEGIN
    SELECT type, COUNT(*) as total,
           SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as activos,
           SUM(views) as total_vistas
    FROM content_library
    GROUP BY type
    ORDER BY total DESC;
END//

DROP PROCEDURE IF EXISTS sp_estadisticas_contenidos//
CREATE PROCEDURE sp_estadisticas_contenidos()
BEGIN
    SELECT COUNT(*) as total_contenidos,
           COUNT(CASE WHEN active = 1 THEN 1 END) as contenidos_activos,
           SUM(views) as total_vistas,
           AVG(duration_minutes) as duracion_promedio,
           COUNT(DISTINCT subject_area) as total_areas,
           COUNT(DISTINCT type) as total_tipos
    FROM content_library;
END//

-- =============================================
-- SECCIÓN 3: STORED PROCEDURES PARA PROGRESO
-- =============================================

DROP PROCEDURE IF EXISTS sp_obtener_progreso_estudiante//
CREATE PROCEDURE sp_obtener_progreso_estudiante(IN p_user_id BIGINT)
BEGIN
    SELECT id, subject_area, topic, total_activities, completed_activities,
           progress_percentage, average_score, total_time_spent, last_activity
    FROM student_progress
    WHERE user_id = p_user_id
    ORDER BY last_activity DESC;
END//

DROP PROCEDURE IF EXISTS sp_actualizar_progreso//
CREATE PROCEDURE sp_actualizar_progreso(
    IN p_user_id BIGINT,
    IN p_subject_area VARCHAR(100),
    IN p_completed_activities INT,
    IN p_score DECIMAL(5,2),
    IN p_time_spent INT
)
BEGIN
    DECLARE progress_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO progress_exists 
    FROM student_progress 
    WHERE user_id = p_user_id AND subject_area = p_subject_area;
    
    IF progress_exists = 0 THEN
        INSERT INTO student_progress (
            user_id, subject_area, total_activities, completed_activities, 
            progress_percentage, average_score, total_time_spent, last_activity, 
            created_at, updated_at
        ) VALUES (
            p_user_id, p_subject_area, p_completed_activities, p_completed_activities,
            100.00, p_score, p_time_spent, NOW(), NOW(), NOW()
        );
    ELSE
        UPDATE student_progress 
        SET completed_activities = completed_activities + p_completed_activities,
            total_time_spent = total_time_spent + p_time_spent,
            average_score = (average_score + p_score) / 2,
            progress_percentage = (completed_activities / total_activities) * 100,
            last_activity = NOW(), updated_at = NOW()
        WHERE user_id = p_user_id AND subject_area = p_subject_area;
    END IF;
END//

DROP PROCEDURE IF EXISTS sp_contar_actividades_usuario//
CREATE PROCEDURE sp_contar_actividades_usuario(IN p_user_id BIGINT)
BEGIN
    SELECT COALESCE(SUM(completed_activities), 0) as total
    FROM student_progress
    WHERE user_id = p_user_id;
END//

-- =============================================
-- SECCIÓN 4: STORED PROCEDURES PARA DIAGNÓSTICOS
-- =============================================

DROP PROCEDURE IF EXISTS sp_listar_diagnosticos//
CREATE PROCEDURE sp_listar_diagnosticos()
BEGIN
    SELECT id, title, description, subject_area, difficulty_level,
           time_limit_minutes, passing_score, active
    FROM diagnostics
    WHERE active = 1
    ORDER BY subject_area, difficulty_level;
END//

DROP PROCEDURE IF EXISTS sp_obtener_diagnostico_completo//
CREATE PROCEDURE sp_obtener_diagnostico_completo(IN p_diagnostic_id BIGINT)
BEGIN
    SELECT id, title, description, subject_area, difficulty_level,
           time_limit_minutes, passing_score
    FROM diagnostics
    WHERE id = p_diagnostic_id AND active = 1;
    
    SELECT id, question_text, question_type, options, points, order_index
    FROM diagnostic_questions
    WHERE diagnostic_id = p_diagnostic_id
    ORDER BY order_index;
END//

DROP PROCEDURE IF EXISTS sp_guardar_respuesta_diagnostico//
CREATE PROCEDURE sp_guardar_respuesta_diagnostico(
    IN p_user_id BIGINT,
    IN p_diagnostic_id BIGINT,
    IN p_question_id BIGINT,
    IN p_user_answer TEXT,
    IN p_time_spent INT
)
BEGIN
    DECLARE correct_ans TEXT;
    DECLARE points DECIMAL(4,2);
    DECLARE is_correct_flag BOOLEAN DEFAULT 0;
    DECLARE points_earned DECIMAL(4,2) DEFAULT 0.00;
    
    SELECT correct_answer, points INTO correct_ans, points
    FROM diagnostic_questions
    WHERE id = p_question_id;
    
    IF TRIM(LOWER(p_user_answer)) = TRIM(LOWER(correct_ans)) THEN
        SET is_correct_flag = 1;
        SET points_earned = points;
    END IF;
    
    INSERT INTO diagnostic_responses (
        user_id, diagnostic_id, question_id, user_answer, 
        is_correct, points_earned, time_spent_seconds, 
        created_at, updated_at
    ) VALUES (
        p_user_id, p_diagnostic_id, p_question_id, p_user_answer,
        is_correct_flag, points_earned, p_time_spent,
        NOW(), NOW()
    )
    ON DUPLICATE KEY UPDATE
        user_answer = p_user_answer,
        is_correct = is_correct_flag,
        points_earned = points_earned,
        time_spent_seconds = p_time_spent,
        updated_at = NOW();
END//

DROP PROCEDURE IF EXISTS sp_finalizar_diagnostico//
CREATE PROCEDURE sp_finalizar_diagnostico(
    IN p_user_id BIGINT,
    IN p_diagnostic_id BIGINT,
    IN p_time_taken_minutes INT
)
BEGIN
    DECLARE total_q INT DEFAULT 0;
    DECLARE correct_a INT DEFAULT 0;
    DECLARE total_pts DECIMAL(8,2) DEFAULT 0.00;
    DECLARE earned_pts DECIMAL(8,2) DEFAULT 0.00;
    DECLARE score_pct DECIMAL(5,2) DEFAULT 0.00;
    DECLARE passing_sc DECIMAL(5,2) DEFAULT 0.00;
    DECLARE passed_flag BOOLEAN DEFAULT 0;
    
    SELECT COUNT(*), SUM(points) INTO total_q, total_pts
    FROM diagnostic_questions
    WHERE diagnostic_id = p_diagnostic_id;
    
    SELECT COUNT(*), SUM(points_earned) INTO correct_a, earned_pts
    FROM diagnostic_responses
    WHERE user_id = p_user_id;
END//

DROP PROCEDURE IF EXISTS sp_rendimiento_por_materia//
CREATE PROCEDURE sp_rendimiento_por_materia()
BEGIN
    SELECT 
        d.subject_area,
        COUNT(DISTINCT dr.user_id) as total_students,
        COUNT(*) as total_attempts,
        AVG(dr.score_percentage) as avg_score,
        SUM(CASE WHEN dr.passed = 1 THEN 1 ELSE 0 END) as passed_count
    FROM diagnostic_results dr
    INNER JOIN diagnostics d ON dr.diagnostic_id = d.id
    GROUP BY d.subject_area
    ORDER BY avg_score DESC;
END//

-- =============================================
-- SECCIÓN 5: STORED PROCEDURES PARA RECOMENDACIONES
-- =============================================

DROP PROCEDURE IF EXISTS sp_obtener_recomendaciones//
CREATE PROCEDURE sp_obtener_recomendaciones(IN p_user_id BIGINT)
BEGIN
    SELECT 
        r.id, r.reason, r.priority, r.is_viewed, r.is_completed,
        r.generated_by, r.created_at,
        c.id as content_id, c.title, c.description, c.subject_area,
        c.type, c.difficulty_level, c.duration_minutes
    FROM recommendations r
    INNER JOIN content_library c ON r.content_id = c.id
    WHERE r.user_id = p_user_id AND r.is_completed = 0
    ORDER BY r.priority, r.created_at DESC;
END//

DROP PROCEDURE IF EXISTS sp_marcar_recomendacion_vista//
CREATE PROCEDURE sp_marcar_recomendacion_vista(IN p_recommendation_id BIGINT)
BEGIN
    UPDATE recommendations 
    SET is_viewed = 1, viewed_at = NOW(), updated_at = NOW()
    WHERE id = p_recommendation_id;
END//

DROP PROCEDURE IF EXISTS sp_crear_recomendacion//
CREATE PROCEDURE sp_crear_recomendacion(
    IN p_user_id BIGINT,
    IN p_content_id BIGINT,
    IN p_reason TEXT,
    IN p_priority TINYINT,
    IN p_generated_by VARCHAR(20)
)
BEGIN
    INSERT INTO recommendations (
        user_id, content_id, reason, priority, 
        generated_by, created_at, updated_at
    ) VALUES (
        p_user_id, p_content_id, p_reason, p_priority,
        p_generated_by, NOW(), NOW()
    );
END//

-- =============================================
-- SECCIÓN 6: STORED PROCEDURES PARA RUTAS DE APRENDIZAJE
-- =============================================

DROP PROCEDURE IF EXISTS sp_obtener_rutas_usuario//
CREATE PROCEDURE sp_obtener_rutas_usuario(IN p_user_id BIGINT)
BEGIN
    SELECT 
        id, subject_area, name, description, difficulty_level,
        estimated_duration, progress_percentage, is_completed,
        completed_at, created_at
    FROM learning_paths
    WHERE user_id = p_user_id
    ORDER BY is_completed, created_at DESC;
END//

DROP PROCEDURE IF EXISTS sp_obtener_contenidos_ruta//
CREATE PROCEDURE sp_obtener_contenidos_ruta(IN p_learning_path_id BIGINT)
BEGIN
    SELECT 
        lpc.id, lpc.order_index, lpc.is_required, lpc.is_completed,
        lpc.time_spent,
        c.id as content_id, c.title, c.description, c.type,
        c.difficulty_level, c.duration_minutes
    FROM learning_path_content lpc
    INNER JOIN content_library c ON lpc.content_id = c.id
    WHERE lpc.learning_path_id = p_learning_path_id
    ORDER BY lpc.order_index;
END//

DROP PROCEDURE IF EXISTS sp_completar_contenido_ruta//
CREATE PROCEDURE sp_completar_contenido_ruta(
    IN p_learning_path_content_id BIGINT,
    IN p_time_spent INT
)
BEGIN
    DECLARE path_id BIGINT;
    DECLARE total_contents INT DEFAULT 0;
    DECLARE completed_contents INT DEFAULT 0;
    DECLARE new_progress DECIMAL(5,2) DEFAULT 0.00;
    
    UPDATE learning_path_content 
    SET is_completed = 1, completed_at = NOW(),
        time_spent = p_time_spent, updated_at = NOW()
    WHERE id = p_learning_path_content_id;
    
    SELECT learning_path_id INTO path_id
    FROM learning_path_content
    WHERE id = p_learning_path_content_id;
    
    SELECT COUNT(*) INTO total_contents
    FROM learning_path_content
    WHERE learning_path_id = path_id;
    
    SELECT COUNT(*) INTO completed_contents
    FROM learning_path_content
    WHERE learning_path_id = path_id AND is_completed = 1;
    
    IF total_contents > 0 THEN
        SET new_progress = (completed_contents / total_contents) * 100;
    END IF;
    
    UPDATE learning_paths 
    SET progress_percentage = new_progress,
        is_completed = IF(new_progress = 100, 1, 0),
        completed_at = IF(new_progress = 100, NOW(), NULL),
        updated_at = NOW()
    WHERE id = path_id;
END//

-- =============================================
-- SECCIÓN 7: STORED PROCEDURES PARA SEGUIMIENTOS
-- =============================================

DROP PROCEDURE IF EXISTS sp_crear_seguimiento//
CREATE PROCEDURE sp_crear_seguimiento(
    IN p_user_id BIGINT,
    IN p_admin_id BIGINT,
    IN p_type VARCHAR(20),
    IN p_scheduled_at DATETIME,
    IN p_notes TEXT
)
BEGIN
    INSERT INTO follow_ups (
        user_id, admin_id, type, scheduled_at, notes,
        status, created_at, updated_at
    ) VALUES (
        p_user_id, p_admin_id, p_type, p_scheduled_at, p_notes,
        'pending', NOW(), NOW()
    );
    SELECT LAST_INSERT_ID() as id;
END//

DROP PROCEDURE IF EXISTS sp_obtener_seguimiento//
CREATE PROCEDURE sp_obtener_seguimiento(IN p_id BIGINT)
BEGIN
    SELECT 
        f.id, f.user_id, f.admin_id, f.type, f.scheduled_at,
        f.completed_at, f.notes, f.status, f.created_at,
        u.name as student_name, u.email as student_email,
        a.name as admin_name
    FROM follow_ups f
    INNER JOIN users u ON f.user_id = u.id
    INNER JOIN users a ON f.admin_id = a.id
    WHERE f.id = p_id;
END//

DROP PROCEDURE IF EXISTS sp_listar_seguimientos_usuario//
CREATE PROCEDURE sp_listar_seguimientos_usuario(IN p_user_id BIGINT)
BEGIN
    SELECT 
        f.id, f.type, f.scheduled_at, f.completed_at,
        f.notes, f.status, f.created_at,
        a.name as admin_name
    FROM follow_ups f
    INNER JOIN users a ON f.admin_id = a.id
    WHERE f.user_id = p_user_id
    ORDER BY f.scheduled_at DESC;
END//

DROP PROCEDURE IF EXISTS sp_listar_seguimientos_pendientes//
CREATE PROCEDURE sp_listar_seguimientos_pendientes()
BEGIN
    SELECT 
        f.id, f.user_id, f.admin_id, f.type, f.scheduled_at,
        f.notes, f.created_at,
        u.name as student_name, u.email as student_email,
        a.name as admin_name
    FROM follow_ups f
    INNER JOIN users u ON f.user_id = u.id
    INNER JOIN users a ON f.admin_id = a.id
    WHERE f.status = 'pending'
    ORDER BY f.scheduled_at ASC;
END//

DROP PROCEDURE IF EXISTS sp_completar_seguimiento//
CREATE PROCEDURE sp_completar_seguimiento(IN p_id BIGINT, IN p_notes TEXT)
BEGIN
    UPDATE follow_ups
    SET status = 'completed', completed_at = NOW(),
        notes = CONCAT(IFNULL(notes, ''), '\n\n[Completado]: ', IFNULL(p_notes, '')),
        updated_at = NOW()
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

DROP PROCEDURE IF EXISTS sp_cancelar_seguimiento//
CREATE PROCEDURE sp_cancelar_seguimiento(IN p_id BIGINT)
BEGIN
    UPDATE follow_ups
    SET status = 'cancelled', updated_at = NOW()
    WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

-- =============================================
-- SECCIÓN 8: STORED PROCEDURES PARA DASHBOARDS
-- =============================================

DROP PROCEDURE IF EXISTS sp_dashboard_estudiante//
CREATE PROCEDURE sp_dashboard_estudiante(IN p_user_id BIGINT)
BEGIN
    SELECT u.id, u.name, u.email, u.student_code, u.career, u.semester, r.name as role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.id = p_user_id;
    
    SELECT 
        COUNT(DISTINCT subject_area) as total_subjects,
        AVG(progress_percentage) as avg_progress,
        SUM(completed_activities) as total_completed_activities,
        SUM(total_time_spent) as total_study_time
    FROM student_progress
    WHERE user_id = p_user_id;
    
    SELECT COUNT(*) as active_paths
    FROM learning_paths
    WHERE user_id = p_user_id AND is_completed = 0;
    
    SELECT COUNT(*) as pending_recommendations
    FROM recommendations
    WHERE user_id = p_user_id AND is_completed = 0;
END//

DROP PROCEDURE IF EXISTS sp_dashboard_docente//
CREATE PROCEDURE sp_dashboard_docente()
BEGIN
    SELECT COUNT(*) as total_students
    FROM users WHERE role_id = 3 AND active = 1;
    
    SELECT COUNT(DISTINCT user_id) as good_progress_students
    FROM student_progress WHERE progress_percentage >= 70;
    
    SELECT COUNT(DISTINCT user_id) as at_risk_students
    FROM student_progress WHERE progress_percentage < 50;
    
    SELECT COUNT(*) as diagnostics_completed_today
    FROM diagnostic_results
    WHERE DATE(completed_at) = CURDATE();
END//

DROP PROCEDURE IF EXISTS sp_dashboard_admin//
CREATE PROCEDURE sp_dashboard_admin()
BEGIN
    SELECT COUNT(*) as total_students FROM users WHERE role_id = 3 AND active = 1;
    SELECT COUNT(*) as total_teachers FROM users WHERE role_id = 2 AND active = 1;
    SELECT COUNT(*) as total_contents FROM content_library WHERE active = 1;
    SELECT COUNT(*) as recent_diagnostics
    FROM diagnostic_results WHERE completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY);
END//

DROP PROCEDURE IF EXISTS sp_estudiantes_por_carrera//
CREATE PROCEDURE sp_estudiantes_por_carrera()
BEGIN
    SELECT career, COUNT(*) as total_students, AVG(semester) as avg_semester
    FROM users
    WHERE role_id = 3 AND active = 1 AND career IS NOT NULL
    GROUP BY career
    ORDER BY total_students DESC;
END//

DROP PROCEDURE IF EXISTS sp_estadisticas_sistema//
CREATE PROCEDURE sp_estadisticas_sistema()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM users WHERE active = 1) as total_users,
        (SELECT COUNT(*) FROM users WHERE role_id = 3 AND active = 1) as total_students,
        (SELECT COUNT(*) FROM users WHERE role_id = 2 AND active = 1) as total_teachers,
        (SELECT COUNT(*) FROM content_library WHERE active = 1) as total_contents,
        (SELECT COUNT(*) FROM diagnostics WHERE active = 1) as total_diagnostics,
        (SELECT COUNT(*) FROM learning_paths) as total_learning_paths
    FROM DUAL;
END//

DELIMITER ;

-- =============================================
-- STORED PROCEDURES ADICIONALES PARA ESTADÍSTICAS
-- =============================================

USE `bd_microlearning_uc`;

DELIMITER //

-- SP: Contar diagnósticos completados por usuario
DROP PROCEDURE IF EXISTS sp_contar_diagnosticos_completados//
CREATE PROCEDURE sp_contar_diagnosticos_completados(IN p_user_id BIGINT)
BEGIN
    SELECT COUNT(*) as total 
    FROM diagnostic_results 
    WHERE user_id = p_user_id;
END//

-- SP: Obtener rendimiento de un estudiante
DROP PROCEDURE IF EXISTS sp_obtener_rendimiento_estudiante//
CREATE PROCEDURE sp_obtener_rendimiento_estudiante(IN p_user_id BIGINT)
BEGIN
    SELECT 
        COUNT(*) as total_responses,
        SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_responses,
        ROUND((SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
    FROM diagnostic_responses
    WHERE user_id = p_user_id;
END//

-- SP: Obtener rendimiento por mes (últimos 6 meses)
DROP PROCEDURE IF EXISTS sp_obtener_rendimiento_por_mes//
CREATE PROCEDURE sp_obtener_rendimiento_por_mes()
BEGIN
    SELECT 
        MONTH(dr.created_at) as month,
        YEAR(dr.created_at) as year,
        COUNT(*) as total_attempts,
        SUM(CASE WHEN dr.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
        ROUND((SUM(CASE WHEN dr.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as avg_score
    FROM diagnostic_responses dr
    WHERE dr.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY year, month
    ORDER BY year DESC, month DESC;
END//

-- SP: Obtener historial de diagnósticos de un usuario
DROP PROCEDURE IF EXISTS sp_historial_diagnosticos_usuario//
CREATE PROCEDURE sp_historial_diagnosticos_usuario(IN p_user_id BIGINT)
BEGIN
    SELECT 
        dr.id,
        dr.completed_at,
        dr.score_percentage,
        dr.passed,
        dr.time_taken_minutes,
        d.title as diagnostic_title,
        d.subject_area,
        d.difficulty_level,
        d.id as diagnostic_id
    FROM diagnostic_results dr
    INNER JOIN diagnostics d ON dr.diagnostic_id = d.id
    WHERE dr.user_id = p_user_id
    ORDER BY dr.completed_at DESC;
END//

DELIMITER ;

SELECT '✅ Stored Procedures adicionales creados correctamente' as status;

USE `bd_microlearning_uc`;

DELIMITER //

-- =============================================
-- SP: Obtener diagnóstico por ID
-- =============================================
DROP PROCEDURE IF EXISTS sp_obtener_diagnostico//
CREATE PROCEDURE sp_obtener_diagnostico(IN p_id BIGINT)
BEGIN
    SELECT 
        id, title, description, subject_area, difficulty_level,
        time_limit_minutes, passing_score, active, created_at, updated_at
    FROM diagnostics
    WHERE id = p_id;
END//

-- =============================================
-- SP: Crear diagnóstico
-- =============================================
DROP PROCEDURE IF EXISTS sp_crear_diagnostico//
CREATE PROCEDURE sp_crear_diagnostico(
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_subject_area VARCHAR(100),
    IN p_difficulty_level VARCHAR(20),
    IN p_time_limit_minutes INT,
    IN p_passing_score DECIMAL(5,2)
)
BEGIN
    INSERT INTO diagnostics (
        title, description, subject_area, difficulty_level,
        time_limit_minutes, passing_score, active, created_at, updated_at
    ) VALUES (
        p_title, p_description, p_subject_area, p_difficulty_level,
        p_time_limit_minutes, p_passing_score, 1, NOW(), NOW()
    );
    
    SELECT LAST_INSERT_ID() as id;
END//

-- =============================================
-- SP: Actualizar diagnóstico
-- =============================================
DROP PROCEDURE IF EXISTS sp_actualizar_diagnostico//
CREATE PROCEDURE sp_actualizar_diagnostico(
    IN p_id BIGINT,
    IN p_title VARCHAR(255),
    IN p_description TEXT,
    IN p_subject_area VARCHAR(100),
    IN p_difficulty_level VARCHAR(20),
    IN p_time_limit_minutes INT,
    IN p_passing_score DECIMAL(5,2),
    IN p_active BOOLEAN
)
BEGIN
    UPDATE diagnostics
    SET title = p_title,
        description = p_description,
        subject_area = p_subject_area,
        difficulty_level = p_difficulty_level,
        time_limit_minutes = p_time_limit_minutes,
        passing_score = p_passing_score,
        active = p_active,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT ROW_COUNT() as affected_rows;
END//

-- =============================================
-- SP: Eliminar diagnóstico
-- =============================================
DROP PROCEDURE IF EXISTS sp_eliminar_diagnostico//
CREATE PROCEDURE sp_eliminar_diagnostico(IN p_id BIGINT)
BEGIN
    DELETE FROM diagnostics WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

-- =============================================
-- SP: Crear pregunta
-- =============================================
DROP PROCEDURE IF EXISTS sp_crear_pregunta//
CREATE PROCEDURE sp_crear_pregunta(
    IN p_diagnostic_id BIGINT,
    IN p_question_text TEXT,
    IN p_question_type VARCHAR(20),
    IN p_options JSON,
    IN p_correct_answer TEXT,
    IN p_points DECIMAL(4,2)
)
BEGIN
    DECLARE max_order INT DEFAULT 0;
    
    SELECT COALESCE(MAX(order_index), 0) INTO max_order
    FROM diagnostic_questions
    WHERE diagnostic_id = p_diagnostic_id;
    
    INSERT INTO diagnostic_questions (
        diagnostic_id, question_text, question_type, options,
        correct_answer, points, order_index, created_at, updated_at
    ) VALUES (
        p_diagnostic_id, p_question_text, p_question_type, p_options,
        p_correct_answer, p_points, max_order + 1, NOW(), NOW()
    );
    
    SELECT LAST_INSERT_ID() as id;
END//

-- =============================================
-- SP: Obtener pregunta por ID
-- =============================================
DROP PROCEDURE IF EXISTS sp_obtener_pregunta//
CREATE PROCEDURE sp_obtener_pregunta(IN p_id BIGINT)
BEGIN
    SELECT 
        id, diagnostic_id, question_text, question_type, options,
        correct_answer, points, order_index, created_at, updated_at
    FROM diagnostic_questions
    WHERE id = p_id;
END//

-- =============================================
-- SP: Actualizar pregunta
-- =============================================
DROP PROCEDURE IF EXISTS sp_actualizar_pregunta//
CREATE PROCEDURE sp_actualizar_pregunta(
    IN p_id BIGINT,
    IN p_question_text TEXT,
    IN p_question_type VARCHAR(20),
    IN p_options JSON,
    IN p_correct_answer TEXT,
    IN p_points DECIMAL(4,2)
)
BEGIN
    UPDATE diagnostic_questions
    SET question_text = p_question_text,
        question_type = p_question_type,
        options = p_options,
        correct_answer = p_correct_answer,
        points = p_points,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT ROW_COUNT() as affected_rows;
END//

-- =============================================
-- SP: Eliminar pregunta
-- =============================================
DROP PROCEDURE IF EXISTS sp_eliminar_pregunta//
CREATE PROCEDURE sp_eliminar_pregunta(IN p_id BIGINT)
BEGIN
    DELETE FROM diagnostic_questions WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

DELIMITER ;

SELECT '✅ Stored Procedures para diagnósticos creados' as status;

DELIMITER //

-- SP: Actualizar seguimiento
DROP PROCEDURE IF EXISTS sp_actualizar_seguimiento//
CREATE PROCEDURE sp_actualizar_seguimiento(
    IN p_id BIGINT,
    IN p_scheduled_at DATETIME,
    IN p_type VARCHAR(20),
    IN p_notes TEXT
)
BEGIN
    UPDATE follow_ups
    SET scheduled_at = p_scheduled_at,
        type = p_type,
        notes = p_notes,
        updated_at = NOW()
    WHERE id = p_id;
    
    SELECT ROW_COUNT() as affected_rows;
END//

-- SP: Eliminar seguimiento
DROP PROCEDURE IF EXISTS sp_eliminar_seguimiento//
CREATE PROCEDURE sp_eliminar_seguimiento(IN p_id BIGINT)
BEGIN
    DELETE FROM follow_ups WHERE id = p_id;
    SELECT ROW_COUNT() as affected_rows;
END//

DELIMITER ;

USE `bd_microlearning_uc`;

DELIMITER //

-- =============================================
-- SP: Obtener alertas de riesgo
-- =============================================
DROP PROCEDURE IF EXISTS sp_obtener_alertas_riesgo//
CREATE PROCEDURE sp_obtener_alertas_riesgo(IN p_user_id BIGINT)
BEGIN
    -- Alerta por bajo rendimiento
    SELECT 
        'performance' as type,
        CONCAT('Rendimiento promedio: ', ROUND(AVG(average_score), 2), '%') as message,
        CASE 
            WHEN AVG(average_score) < 50 THEN 'danger'
            WHEN AVG(average_score) < 70 THEN 'warning'
            ELSE 'info'
        END as severity,
        NOW() as created_at
    FROM student_progress
    WHERE user_id = p_user_id
    HAVING AVG(average_score) < 70
    
    UNION ALL
    
    -- Alerta por inactividad
    SELECT 
        'inactivity' as type,
        CONCAT('Última actividad hace ', DATEDIFF(NOW(), MAX(last_activity)), ' días') as message,
        CASE 
            WHEN DATEDIFF(NOW(), MAX(last_activity)) > 14 THEN 'danger'
            WHEN DATEDIFF(NOW(), MAX(last_activity)) > 7 THEN 'warning'
            ELSE 'info'
        END as severity,
        NOW() as created_at
    FROM student_progress
    WHERE user_id = p_user_id AND last_activity IS NOT NULL
    HAVING DATEDIFF(NOW(), MAX(last_activity)) > 7
    
    UNION ALL
    
    -- Alerta por pocas actividades completadas
    SELECT 
        'low_activity' as type,
        CONCAT('Solo ', SUM(completed_activities), ' actividades completadas') as message,
        'warning' as severity,
        NOW() as created_at
    FROM student_progress
    WHERE user_id = p_user_id
    HAVING SUM(completed_activities) < 5;
END//

-- =============================================
-- SP: Calcular nivel de riesgo
-- =============================================
DROP PROCEDURE IF EXISTS sp_calcular_nivel_riesgo//
CREATE PROCEDURE sp_calcular_nivel_riesgo(IN p_user_id BIGINT)
BEGIN
    SELECT 
        CASE 
            WHEN AVG(sp.average_score) < 50 THEN 'alto'
            WHEN AVG(sp.average_score) < 70 THEN 'medio'
            ELSE 'bajo'
        END as risk_level,
        ROUND(AVG(sp.average_score), 2) as avg_score,
        COUNT(sp.id) as total_subjects,
        SUM(sp.completed_activities) as total_activities,
        MAX(sp.last_activity) as last_activity_date
    FROM student_progress sp
    WHERE sp.user_id = p_user_id
    GROUP BY sp.user_id;
END//

-- =============================================
-- SP: Estadísticas de rutas del estudiante
-- =============================================
DROP PROCEDURE IF EXISTS sp_estadisticas_rutas_estudiante//
CREATE PROCEDURE sp_estadisticas_rutas_estudiante(IN p_user_id BIGINT)
BEGIN
    SELECT 
        COUNT(*) as total_paths,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_paths,
        ROUND(AVG(progress_percentage), 2) as avg_progress,
        SUM(estimated_duration) as total_estimated_duration
    FROM learning_paths
    WHERE user_id = p_user_id;
END//

-- =============================================
-- SP: Obtener estadísticas generales del estudiante
-- =============================================
DROP PROCEDURE IF EXISTS sp_estadisticas_estudiante//
CREATE PROCEDURE sp_estadisticas_estudiante(IN p_user_id BIGINT)
BEGIN
    -- Progreso general
    SELECT 
        COUNT(DISTINCT sp.subject_area) as total_subjects,
        ROUND(AVG(sp.progress_percentage), 2) as avg_progress,
        SUM(sp.completed_activities) as total_completed_activities,
        SUM(sp.total_time_spent) as total_study_time,
        MAX(sp.last_activity) as last_activity
    FROM student_progress sp
    WHERE sp.user_id = p_user_id;
    
    -- Diagnósticos
    SELECT 
        COUNT(*) as total_diagnostics,
        ROUND(AVG(score_percentage), 2) as avg_diagnostic_score,
        SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_diagnostics
    FROM diagnostic_results
    WHERE user_id = p_user_id;
    
    -- Rutas de aprendizaje
    SELECT 
        COUNT(*) as total_paths,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_paths,
        SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as active_paths
    FROM learning_paths
    WHERE user_id = p_user_id;
    
    -- Recomendaciones
    SELECT 
        COUNT(*) as total_recommendations,
        SUM(CASE WHEN is_viewed = 1 THEN 1 ELSE 0 END) as viewed_recommendations,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_recommendations
    FROM recommendations
    WHERE user_id = p_user_id;
END//

DELIMITER ;

SELECT '✅ Stored Procedures adicionales creados correctamente' as status;
-- =============================================
-- VERIFICACIÓN FINAL
-- =============================================
SELECT '✓ SCRIPT EJECUTADO CORRECTAMENTE' as status;
SELECT 'Tabla follow_ups creada/verificada' as tabla_status;
