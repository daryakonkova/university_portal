<?php
/**
 * API для управления учебными группами
 * Путь: /api/groups_api.php
 */

header('Content-Type: application/json; charset=utf-8');

// Подключаем системные файлы
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

session_start();

// Проверка прав администратора (безопасность)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Доступ запрещен. Требуются права администратора.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$adminId = $_SESSION['user_id'];

try {
    switch ($action) {
        // 1. Получение списка всех групп
        case 'list':
            $stmt = $pdo->query("SELECT * FROM groups ORDER BY group_name ASC");
            $groups = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $groups
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 2. Добавление новой группы
        case 'add':
            $groupName = trim($_POST['group_name'] ?? '');

            if (empty($groupName)) {
                throw new Exception('Название группы не может быть пустым');
            }

            // Проверка на дубликаты
            $check = $pdo->prepare("SELECT id FROM groups WHERE group_name = ?");
            $check->execute([$groupName]);
            if ($check->fetch()) {
                throw new Exception('Группа с таким названием уже существует');
            }

            // Вставка записи
            $stmt = $pdo->prepare("INSERT INTO groups (group_name) VALUES (?)");
            $stmt->execute([$groupName]);
            $newId = $pdo->lastInsertId();

            // Логирование действия в 10-ю таблицу (Аудит)
            $log = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, ?)");
            $log->execute([$adminId, "Создана учебная группа: $groupName (ID: $newId)"]);

            echo json_encode(['success' => true, 'message' => 'Группа успешно добавлена']);
            break;

        // 3. Удаление группы
        case 'delete':
            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Некорректный ID группы');
            }

            // Получаем имя группы перед удалением для лога
            $nameStmt = $pdo->prepare("SELECT group_name FROM groups WHERE id = ?");
            $nameStmt->execute([$id]);
            $group = $nameStmt->fetch();

            if ($group) {
                // Удаление группы
                $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
                $stmt->execute([$id]);

                // Логирование в таблицу аудита
                $log = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, ?)");
                $log->execute([$adminId, "Удалена учебная группа: " . $group['group_name']]);

                echo json_encode(['success' => true, 'message' => 'Группа удалена']);
            } else {
                throw new Exception('Группа не найдена в базе данных');
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Метод API не поддерживается']);
            break;
    }
} catch (Exception $e) {
    // Возвращаем ошибку в формате JSON для обработки во фронтенде Canvas
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}