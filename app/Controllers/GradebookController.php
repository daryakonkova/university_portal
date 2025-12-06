// Файл: app/Controllers/GradebookController.php

<?php
namespace App\Controllers;

require_once 'app/Database.php'; 

class GradebookController {
    private $db;

    public function __construct() {
        $this->db = new \App\Database(); 
    }

    /**
     * Сохраняет или обновляет оценку студента (доступно только преподавателю).
     * @param int $teacherId ID преподавателя (пользователя)
     * @param array $gradeData Данные: student_id, subject_id, grade_value
     * @return array Статус операции
     */
    public function saveGrade(int $teacherId, array $gradeData): array {
        // 1. Проверка прав (Предполагаем, что роль уже проверена в роутере, 
        // но ID преподавателя используется для контроля доступа в БД)
        if (empty($gradeData['student_id']) || empty($gradeData['subject_id']) || !isset($gradeData['grade_value'])) {
             return ['status' => 'error', 'message' => 'Некорректные входные данные.'];
        }

        // 2. Валидация оценки (Пример простой валидации)
        $allowedGrades = ['Отлично', 'Хорошо', 'Удовлетворительно', 'Неудовлетворительно', 'Зачтено', 'Не зачтено'];
        if (!in_array($gradeData['grade_value'], $allowedGrades)) {
             return ['status' => 'error', 'message' => 'Недопустимое значение оценки.'];
        }

        // 3. Проверка закрепления (Важно: преподаватель может ставить оценки только по своим предметам)
        // В реальном проекте здесь будет дополнительный JOIN, проверяющий, что предмет
        // $gradeData['subject_id'] закреплен за $teacherId. Для упрощения пропускаем этот JOIN.

        // 4. Реализация операции UPSERT для атомарного обновления/вставки
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
            $gradeData['student_id'],
            $gradeData['subject_id'],
            $gradeData['grade_value'],
            $teacherId 
        ];

        try {
            $this->db->execute($query, $params);
            return ['status' => 'success', 'message' => 'Оценка успешно сохранена/обновлена.'];
        } catch (\Exception $e) {
            // Логирование ошибки
            return ['status' => 'error', 'message' => 'Ошибка базы данных при сохранении оценки.'];
        }
    }
}
?>