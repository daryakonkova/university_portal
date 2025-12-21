<?php
/**
 * API для работы с оценками (учебной ведомостью)
 * Путь: /api/grades.php
 * Поддерживает получение данных (GET) и сохранение/обновление (POST)
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла. Авторизуйтесь снова.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    // ОБРАБОТКА СОХРАНЕНИЯ (POST) - вызывается из journal.php
    if ($method === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_grade') {
            // Проверка прав (только преподаватель или админ)
            if (!in_array($role, ['Teacher', 'Admin'])) {
                throw new Exception("Недостаточно прав для выставления оценок.");
            }

            $studentId = intval($_POST['student_id'] ?? 0);
            $disciplineId = intval($_POST['discipline_id'] ?? 0);
            $grade = trim($_POST['grade'] ?? '');

            if (!$studentId || !$disciplineId) {
                throw new Exception("Некорректные параметры запроса.");
            }

            // 1. Проверяем, существует ли уже оценка для этого студента по этой дисциплине
            $checkStmt = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND discipline_id = ?");
            $checkStmt->execute([$studentId, $disciplineId]);
            $existingGrade = $checkStmt->fetch();

            if (empty($grade)) {
                // Если оценка пустая — удаляем запись (если она была)
                if ($existingGrade) {
                    $delStmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
                    $delStmt->execute([$existingGrade['id']]);
                }
                $message = "Оценка удалена";
            } else {
                if ($existingGrade) {
                    // Обновляем существующую
                    $updStmt = $pdo->prepare("UPDATE grades SET grade = ?, date_given = CURDATE() WHERE id = ?");
                    $updStmt->execute([$grade, $existingGrade['id']]);
                    $message = "Оценка обновлена";
                } else {
                    // Создаем новую запись
                    $insStmt = $pdo->prepare("INSERT INTO grades (student_id, discipline_id, grade, date_given) VALUES (?, ?, ?, CURDATE())");
                    $insStmt->execute([$studentId, $disciplineId, $grade]);
                    $message = "Оценка выставлена";
                }
            }

            // Логируем действие в 10-ю таблицу (audit_logs)
            $logStmt = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, ?)");
            $logStmt->execute([$userId, "Преподаватель изменил оценку студенту (ID: $studentId, Предмет ID: $disciplineId)"]);

            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        }
    }

    // ОБРАБОТКА ПОЛУЧЕНИЯ ДАННЫХ (GET) - для зачетки студента или админки
    if ($method === 'GET') {
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
            $stmt = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
            $stmt->execute([$userId]);
            $student = $stmt->fetch();

            if ($student) {
                $sql = $baseSql . " WHERE g.student_id = ? ORDER BY g.date_given DESC";
                $query = $pdo->prepare($sql);
                $query->execute([$student['id']]);
            } else {
                throw new Exception("Профиль студента не найден.");
            }
        } else {
            // Для админа или преподавателя (общий список)
            $query = $pdo->query($baseSql . " ORDER BY g.date_given DESC LIMIT 100");
        }

        echo json_encode(['success' => true, 'data' => $query->fetchAll()]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}