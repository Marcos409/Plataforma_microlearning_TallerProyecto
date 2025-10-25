<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. sp_get_user_by_id
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_by_id');
        DB::unprepared('
            CREATE PROCEDURE sp_get_user_by_id(IN p_user_id INT)
            BEGIN
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = p_user_id;
            END
        ');

        // 2. sp_get_all_users
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_all_users');
        DB::unprepared('
            CREATE PROCEDURE sp_get_all_users()
            BEGIN
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                ORDER BY u.name;
            END
        ');

        // 3. sp_get_users_by_role
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_users_by_role');
        DB::unprepared('
            CREATE PROCEDURE sp_get_users_by_role(IN p_role_id INT)
            BEGIN
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.role_id = p_role_id
                ORDER BY u.name;
            END
        ');

        // 4. sp_get_active_users
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_active_users');
        DB::unprepared('
            CREATE PROCEDURE sp_get_active_users()
            BEGIN
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.active = 1
                ORDER BY u.name;
            END
        ');

        // 5. sp_create_user
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_user');
        DB::unprepared('
            CREATE PROCEDURE sp_create_user(
                IN p_name VARCHAR(255),
                IN p_email VARCHAR(255),
                IN p_password VARCHAR(255),
                IN p_role_id INT,
                IN p_student_code VARCHAR(50),
                IN p_career VARCHAR(255),
                IN p_semester INT,
                IN p_phone VARCHAR(20),
                IN p_active BOOLEAN
            )
            BEGIN
                INSERT INTO users (
                    name, email, password, role_id, student_code, 
                    career, semester, phone, active, 
                    created_at, updated_at
                )
                VALUES (
                    p_name, p_email, p_password, p_role_id, p_student_code,
                    p_career, p_semester, p_phone, p_active,
                    NOW(), NOW()
                );
                
                SELECT LAST_INSERT_ID() as user_id;
            END
        ');

        // 6. sp_update_user
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_user');
        DB::unprepared('
            CREATE PROCEDURE sp_update_user(
                IN p_user_id INT,
                IN p_name VARCHAR(255),
                IN p_email VARCHAR(255),
                IN p_student_code VARCHAR(50),
                IN p_career VARCHAR(255),
                IN p_semester INT,
                IN p_phone VARCHAR(20),
                IN p_active BOOLEAN
            )
            BEGIN
                UPDATE users 
                SET 
                    name = COALESCE(p_name, name),
                    email = COALESCE(p_email, email),
                    student_code = COALESCE(p_student_code, student_code),
                    career = COALESCE(p_career, career),
                    semester = COALESCE(p_semester, semester),
                    phone = COALESCE(p_phone, phone),
                    active = COALESCE(p_active, active),
                    updated_at = NOW()
                WHERE id = p_user_id;
            END
        ');

        // 7. sp_delete_user
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_delete_user');
        DB::unprepared('
            CREATE PROCEDURE sp_delete_user(IN p_user_id INT)
            BEGIN
                -- Eliminar relaciones primero
                DELETE FROM diagnostic_responses WHERE user_id = p_user_id;
                DELETE FROM student_progress WHERE user_id = p_user_id;
                DELETE FROM learning_paths WHERE user_id = p_user_id;
                DELETE FROM recommendations WHERE user_id = p_user_id;
                DELETE FROM risk_alerts WHERE user_id = p_user_id;
                DELETE FROM follow_ups WHERE user_id = p_user_id;
                
                -- Eliminar usuario
                DELETE FROM users WHERE id = p_user_id;
            END
        ');

        // 8. sp_get_user_overall_progress
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_overall_progress');
        DB::unprepared('
            CREATE PROCEDURE sp_get_user_overall_progress(IN p_user_id INT)
            BEGIN
                SELECT 
                    ROUND(COALESCE(AVG(progress_percentage), 0), 2) as overall_progress
                FROM student_progress
                WHERE user_id = p_user_id;
            END
        ');

        // 9. sp_get_user_total_time_spent
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_total_time_spent');
        DB::unprepared('
            CREATE PROCEDURE sp_get_user_total_time_spent(IN p_user_id INT)
            BEGIN
                SELECT 
                    COALESCE(SUM(total_time_spent), 0) as total_time
                FROM student_progress
                WHERE user_id = p_user_id;
            END
        ');

        // 10. sp_get_user_completed_activities
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_completed_activities');
        DB::unprepared('
            CREATE PROCEDURE sp_get_user_completed_activities(IN p_user_id INT)
            BEGIN
                SELECT 
                    COALESCE(SUM(completed_activities), 0) as total_completed
                FROM student_progress
                WHERE user_id = p_user_id;
            END
        ');

        // 11. sp_has_active_risk_alerts
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_has_active_risk_alerts');
        DB::unprepared('
            CREATE PROCEDURE sp_has_active_risk_alerts(IN p_user_id INT)
            BEGIN
                SELECT COUNT(*) > 0 as has_alerts
                FROM risk_alerts
                WHERE user_id = p_user_id
                AND is_resolved = 0;
            END
        ');

        // 12. sp_get_active_risk_alerts
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_active_risk_alerts');
        DB::unprepared('
            CREATE PROCEDURE sp_get_active_risk_alerts(IN p_user_id INT)
            BEGIN
                SELECT *
                FROM risk_alerts
                WHERE user_id = p_user_id
                AND is_resolved = 0
                ORDER BY created_at DESC;
            END
        ');

        // 13. sp_get_pending_recommendations_count
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_pending_recommendations_count');
        DB::unprepared('
            CREATE PROCEDURE sp_get_pending_recommendations_count(IN p_user_id INT)
            BEGIN
                SELECT COUNT(*) as pending_count
                FROM recommendations
                WHERE user_id = p_user_id
                AND is_completed = 0;
            END
        ');

        // 14. sp_update_last_activity
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_last_activity');
        DB::unprepared('
            CREATE PROCEDURE sp_update_last_activity(IN p_user_id INT)
            BEGIN
                UPDATE users
                SET last_activity = NOW(), updated_at = NOW()
                WHERE id = p_user_id;
            END
        ');

        // 15. sp_is_user_inactive
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_is_user_inactive');
        DB::unprepared('
            CREATE PROCEDURE sp_is_user_inactive(
                IN p_user_id INT,
                IN p_days INT
            )
            BEGIN
                SELECT 
                    CASE
                        WHEN last_activity IS NULL THEN 1
                        WHEN DATEDIFF(NOW(), last_activity) > p_days THEN 1
                        ELSE 0
                    END as is_inactive
                FROM users
                WHERE id = p_user_id;
            END
        ');

        // 16. sp_count_users_by_role
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_count_users_by_role');
        DB::unprepared('
            CREATE PROCEDURE sp_count_users_by_role(IN p_role_id INT)
            BEGIN
                SELECT COUNT(*) as user_count
                FROM users
                WHERE role_id = p_role_id;
            END
        ');

        // 17. sp_search_users
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_users');
        DB::unprepared('
            CREATE PROCEDURE sp_search_users(
                IN p_search VARCHAR(255),
                IN p_role_id INT,
                IN p_active BOOLEAN
            )
            BEGIN
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE 
                    (p_search IS NULL OR u.name LIKE CONCAT("%", p_search, "%") OR u.email LIKE CONCAT("%", p_search, "%"))
                    AND (p_role_id IS NULL OR u.role_id = p_role_id)
                    AND (p_active IS NULL OR u.active = p_active)
                ORDER BY u.name;
            END
        ');

        // 18. sp_get_students_with_filters
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_students_with_filters');
        DB::unprepared('
            CREATE PROCEDURE sp_get_students_with_filters(
                IN p_search VARCHAR(255),
                IN p_status VARCHAR(20)
            )
            BEGIN
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.role_id = 3
                AND (
                    p_search IS NULL 
                    OR u.name LIKE CONCAT("%", p_search, "%")
                    OR u.email LIKE CONCAT("%", p_search, "%")
                )
                AND (
                    p_status IS NULL
                    OR (p_status = "active" AND u.last_activity >= DATE_SUB(NOW(), INTERVAL 7 DAY))
                    OR (p_status = "inactive" AND (u.last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY) OR u.last_activity IS NULL))
                )
                ORDER BY u.name;
            END
        ');
    }

    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_by_id');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_all_users');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_users_by_role');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_active_users');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_delete_user');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_overall_progress');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_total_time_spent');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_user_completed_activities');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_has_active_risk_alerts');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_active_risk_alerts');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_pending_recommendations_count');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_last_activity');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_is_user_inactive');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_count_users_by_role');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_users');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_get_students_with_filters');
    }
};