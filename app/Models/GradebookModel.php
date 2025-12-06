<?php
namespace App\Models;

require_once 'app/Database.php'; 

class GradebookModel {
    private $db;

    public function __construct() {
        $this->db = new \App\Database(); 
    }

    /**
     * Сохраняет или обновляет оценку студента, используя операцию UPSERT.
     * @param array $data Массив с данными: student_id, subject_id, grade_value, teacher_id
     * @return bool Успех выполнения операции
     */
    public function saveGrade(array $data): bool {
        // Реализация UPSERT (INSERT ... ON DUPLICATE KEY UPDATE)
        $query = "
            INSERT INTO grades 
                (student_id, subject_id, grade_value, teacher_id, date_updated)
            VALUES 
                (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                grade_value = VALUES(grade_value), 
                teacher_id = VALUES(teacher_id),
                date_updated = NOW()
        ";
        
        $params = [
            $data['student_id'],
            $data['subject_id'],
            $data['grade_value'],
            $data['teacher_id']
        ];

        // execute — метод базового класса для выполнения запроса на изменение данных
        return $this->db->execute($query, $params);
    }
    
    /**
     * Получает все оценки для конкретного студента.
     * @param int $studentUserId ID пользователя-студента
     * @return array Список оценок
     */
    public function getStudentGrades(int $studentUserId): array {
        $query = "
            SELECT 
                g.grade_value, sub.subject_name, t.fio AS teacher_fio, g.date_updated
            FROM grades g
            JOIN subjects sub ON g.subject_id = sub.id
            JOIN teachers t ON g.teacher_id = t.user_id
            JOIN students st ON g.student_id = st.user_id
            WHERE st.user_id = ?
            ORDER BY g.date_updated DESC
        ";
        return $this->db->fetchAll($query, [$studentUserId]);
    }
}
?>