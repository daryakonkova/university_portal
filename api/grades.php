<?php
/**
 * API для работы с оценками (учебной ведомостью)
 * Реализует получение данных из таблицы grades с учетом ролей пользователей.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла. Авторизуйтесь снова.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$response = ['success' => false, 'data' => []];

try {
    // Базовый SQL запрос для выборки оценок (используем 3 таблицы: grades, students, disciplines)
    $baseSql = "
        SELECT 
            g.id as grade_id,
            s.full_name as student_name,
            d.subject_name,
            g.grade,
            g.date_given,
            gr.group_name
        FROM grades g
        JOIN students s ON g.student_id = s.id
        JOIN disciplines d ON g.discipline_id = d.id
        JOIN groups gr ON s.group_id = gr.id
    ";

    if ($role === 'Student') {
        // СТУДЕНТ: Видит только свои оценки
        $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
        $stmt->execute([$userId]);
        $student = $stmt->fetch();

        if ($student) {
            $sql = $baseSql . " WHERE g.student_id = :student_id ORDER BY g.date_given DESC";
            $query = $pdo->prepare($sql);
            $query->execute(['student_id' => $student['id']]);
        } else {
            throw new Exception("Профиль студента не найден.");
        }

    } elseif ($role === 'Teacher') {
        // ПРЕПОДАВАТЕЛЬ: Видит оценки по своим дисциплинам (или по фильтру группы)
        // Для примера: преподаватель видит всех, кому он выставил оценки (можно усложнить через таблицу schedule)
        $groupId = $_GET['group_id'] ?? null;

        if ($groupId) {
            $sql = $baseSql . " WHERE gr.id = :group_id ORDER BY s.full_name ASC";
            $query = $pdo->prepare($sql);
            $query->execute(['group_id' => $groupId]);
        } else {
            // Если группа не указана, выводим последние 50 записей по всем группам преподавателя
            $sql = $baseSql . " ORDER BY g.date_given DESC LIMIT 50";
            $query = $pdo->query($sql);
        }

    } elseif ($role === 'Admin') {
        // АДМИНИСТРАТОР: Видит всё
        $sql = $baseSql . " ORDER BY gr.group_name, s.full_name ASC";
        $query = $pdo->query($sql);
    }

    $results = $query->fetchAll();

    // Дополнительная валидация данных (бизнес-логика)
    // Например: форматирование даты или добавление статуса (сдано/не сдано)
    foreach ($results as &$row) {
        $row['is_passed'] = in_array($row['grade'], ['Отлично', 'Хорошо', 'Удовлетворительно', 'Зачтено']);
    }

    $response['success'] = true;
    $response['data'] = $results;

} catch (Exception $e) {
    $response['message'] = 'Ошибка БД: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);