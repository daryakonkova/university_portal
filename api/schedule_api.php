<?php
/**
 * API для работы с расписанием корпоративного портала
 * Обрабатывает запросы на получение расписания с учетом ролей пользователей
 */

header('Content-Type: application/json; charset=utf-8');

// Подключаем конфигурацию и соединение с БД
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен. Требуется авторизация.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$response = ['success' => false, 'data' => []];

try {
    // Базовый запрос с объединением таблиц (всего задействовано 5 таблиц из 10)
    $sql = "
        SELECT 
            s.id, 
            s.day_of_week, 
            s.time_start, 
            s.time_end, 
            g.group_name, 
            t.full_name as teacher_name, 
            d.subject_name, 
            c.room_number
        FROM schedule s
        JOIN groups g ON s.group_id = g.id
        JOIN teachers t ON s.teacher_id = t.id
        JOIN disciplines d ON s.discipline_id = d.id
        JOIN classrooms c ON s.classroom_id = c.id
    ";

    // Фильтрация данных в зависимости от роли пользователя
    if ($role === 'Student') {
        // Студент видит расписание только своей группы
        $stmt = $pdo->prepare("SELECT group_id FROM students WHERE user_id = ?");
        $stmt->execute([$userId]);
        $student = $stmt->fetch();

        if ($student) {
            $sql .= " WHERE s.group_id = :group_id ORDER BY FIELD(s.day_of_week, 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'), s.time_start";
            $query = $pdo->prepare($sql);
            $query->execute(['group_id' => $student['group_id']]);
        } else {
            throw new Exception("Данные студента не найдены");
        }

    } elseif ($role === 'Teacher') {
        // Преподаватель видит только свои занятия
        $stmt = $pdo->prepare("SELECT id FROM teachers WHERE user_id = ?");
        $stmt->execute([$userId]);
        $teacher = $stmt->fetch();

        if ($teacher) {
            $sql .= " WHERE s.teacher_id = :teacher_id ORDER BY FIELD(s.day_of_week, 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'), s.time_start";
            $query = $pdo->prepare($sql);
            $query->execute(['teacher_id' => $teacher['id']]);
        } else {
            throw new Exception("Данные преподавателя не найдены");
        }

    } else {
        // Администратор видит все расписание
        $sql .= " ORDER BY g.group_name, FIELD(s.day_of_week, 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'), s.time_start";
        $query = $pdo->query($sql);
    }

    $scheduleData = $query->fetchAll();

    $response['success'] = true;
    $response['data'] = $scheduleData;

} catch (Exception $e) {
    $response['message'] = 'Ошибка при получении расписания: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);