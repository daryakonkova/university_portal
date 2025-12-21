<?php
/**
 * API для управления пользователями (только для роли Admin)
 * Реализует CRUD-операции и логирование действий в таблицу action_logs.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
require_once '../includes/db_connect.php';

session_start();

// 1. ПРОВЕРКА ПРАВ ДОСТУПА
// Только администратор имеет доступ к управлению учетными записями
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка доступа. Данный раздел доступен только администратору.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$currentAdminId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'message' => 'Неизвестная операция'];

try {
    switch ($action) {
        // ПОЛУЧЕНИЕ СПИСКА ПОЛЬЗОВАТЕЛЕЙ
        case 'list':
            $stmt = $pdo->query("
                SELECT u.id, u.username, u.created_at, r.role_name, r.id as role_id 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                ORDER BY u.id DESC
            ");
            $users = $stmt->fetchAll();
            $response = ['success' => true, 'data' => $users];
            break;

        // ДОБАВЛЕНИЕ НОВОГО ПОЛЬЗОВАТЕЛЯ
        case 'add':
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $roleId = intval($_POST['role_id'] ?? 0);

            if (empty($username) || empty($password) || $roleId <= 0) {
                $response['message'] = 'Все поля (логин, пароль, роль) обязательны для заполнения';
                break;
            }

            // Хеширование пароля
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hash, $roleId])) {
                $newUserId = $pdo->lastInsertId();

                // ЛОГИРОВАНИЕ (10-я таблица)
                $log = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, ?)");
                $log->execute([$currentAdminId, "Создан пользователь: $username (ID: $newUserId)"]);

                $response = ['success' => true, 'message' => 'Пользователь успешно создан'];
            }
            break;

        // УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ
        case 'delete':
            $idToDelete = intval($_POST['id'] ?? 0);

            if ($idToDelete === $currentAdminId) {
                $response['message'] = 'Нельзя удалить собственную учетную запись администратора';
                break;
            }

            // Сначала получим имя для лога
            $nameStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $nameStmt->execute([$idToDelete]);
            $userToDelete = $nameStmt->fetch();

            if ($userToDelete) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$idToDelete]);

                // ЛОГИРОВАНИЕ
                $log = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, ?)");
                $log->execute([$currentAdminId, "Удален пользователь: " . $userToDelete['username']]);

                $response = ['success' => true, 'message' => 'Пользователь удален'];
            } else {
                $response['message'] = 'Пользователь не найден';
            }
            break;

        // ПОЛУЧЕНИЕ СПИСКА РОЛЕЙ ДЛЯ ВЫПАДАЮЩЕГО СПИСКА
        case 'get_roles':
            $stmt = $pdo->query("SELECT id, role_name FROM roles ORDER BY id ASC");
            $response = ['success' => true, 'data' => $stmt->fetchAll()];
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Ошибка сервера: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);