// Файл: app/Controllers/ScheduleController.php

<?php
namespace App\Controllers;

require_once 'app/Database.php'; 

class ScheduleController {
    private $db;

    public function __construct() {
        $this->db = new \App\Database(); 
    }

    /**
     * Возвращает расписание для авторизованного пользователя.
     * @param int $userId ID пользователя
     * @param string $userRole Роль пользователя ('student' или 'teacher')
     * @return array Данные расписания
     */
    public function getSchedule(int $userId, string $userRole): array {
        $data = [];

        if ($userRole === 'student') {
            // Запрос для студента: привязка через группу
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
            $data = $this->db->fetchAll($query, [$userId]);

        } elseif ($userRole === 'teacher') {
            // Запрос для преподавателя: привязка через ID преподавателя
            $query = "
                SELECT 
                    s.day_of_week, s.time_start, s.time_end, sub.subject_name, s.room, g.group_name
                FROM schedule s
                JOIN subjects sub ON s.subject_id = sub.id
                JOIN groups g ON s.group_id = g.id
                WHERE s.teacher_id = ? 
                ORDER BY s.day_of_week, s.time_start
            ";
            $data = $this->db->fetchAll($query, [$userId]);

        } else {
            return ['status' => 'error', 'message' => 'Недостаточно прав доступа.'];
        }

        if (empty($data)) {
             return ['status' => 'success', 'schedule' => [], 'message' => 'Расписание не найдено.'];
        }

        return ['status' => 'success', 'schedule' => $data];
    }
}
?>