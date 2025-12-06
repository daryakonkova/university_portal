<?php
namespace App\Models;

require_once 'app/Database.php'; 

class ScheduleModel {
    private $db;

    public function __construct() {
        $this->db = new \App\Database(); 
    }

    /**
     * Извлекает расписание для студента, основываясь на его ID и группе.
     * @param int $studentUserId ID пользователя-студента
     * @return array Список занятий
     */
    public function getStudentSchedule(int $studentUserId): array {
        $query = "
            SELECT 
                s.day_of_week, s.time_start, s.time_end, sub.subject_name, s.room, t.fio AS teacher_fio
            FROM schedule s
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN groups g ON s.group_id = g.id
            JOIN students st ON st.group_id = g.id AND st.user_id = ? 
            JOIN teachers t ON s.teacher_id = t.user_id
            ORDER BY s.day_of_week, s.time_start
        ";
        // fetchAll — метод базового класса для получения всех строк
        return $this->db->fetchAll($query, [$studentUserId]);
    }

    /**
     * Извлекает расписание для преподавателя, основываясь на его ID.
     * @param int $teacherUserId ID пользователя-преподавателя
     * @return array Список занятий
     */
    public function getTeacherSchedule(int $teacherUserId): array {
        $query = "
            SELECT 
                s.day_of_week, s.time_start, s.time_end, sub.subject_name, s.room, g.group_name
            FROM schedule s
            JOIN subjects sub ON s.subject_id = sub.id
            JOIN groups g ON s.group_id = g.id
            WHERE s.teacher_id = ?
            ORDER BY s.day_of_week, s.time_start
        ";
        return $this->db->fetchAll($query, [$teacherUserId]);
    }
}
?>