<?php
/**
 * API для аутентификации пользователей корпоративного портала
 * Обрабатывает запросы на вход (login), регистрацию (register) и выход (logout)
 */

header('Content-Type: application/json; charset=utf-8');

// Подключаем конфигурацию и соединение с БД
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

session_start();

// Получаем данные из POST-запроса
$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Неизвестное действие'];

try {
    switch ($action) {
        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $response['message'] = 'Введите логин и пароль';
                break;
            }

            // Ищем пользователя и его роль (согласно нашей схеме из 10 таблиц)
            $stmt = $pdo->prepare("
                SELECT u.id, u.username, u.password, r.role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Устанавливаем данные сессии
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];

                // Логируем успешный вход (заполняем 10-ю таблицу action_logs)
                $logStmt = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, 'Успешный вход в систему')");
                $logStmt->execute([$user['id']]);

                $response['success'] = true;
                $response['message'] = 'Авторизация успешна';
                $response['redirect'] = 'dashboard.php';
            } else {
                $response['message'] = 'Неверный логин или пароль';
            }
            break;

        case 'register':
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role_id = intval($_POST['role_id'] ?? 3); // По умолчанию "Студент"

            if (empty($username) || empty($password)) {
                $response['message'] = 'Заполните все поля регистрации';
                break;
            }

            // Хешируем пароль для безопасности
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Проверяем, не занят ли логин
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $checkStmt->execute([$username]);
            if ($checkStmt->fetch()) {
                $response['message'] = 'Пользователь с таким логином уже существует';
                break;
            }

            // Вставляем нового пользователя
            $insertStmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
            if ($insertStmt->execute([$username, $hashedPassword, $role_id])) {
                $response['success'] = true;
                $response['message'] = 'Регистрация прошла успешно. Теперь вы можете войти.';
            } else {
                $response['message'] = 'Ошибка при создании пользователя';
            }
            break;

        case 'logout':
            // Логируем выход перед уничтожением сессии
            if (isset($_SESSION['user_id'])) {
                $logStmt = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, 'Выход из системы')");
                $logStmt->execute([$_SESSION['user_id']]);
            }

            session_unset();
            session_destroy();
            $response['success'] = true;
            $response['message'] = 'Вы вышли из системы';
            break;
    }
} catch (Exception $e) {
    $response['message'] = 'Ошибка сервера: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);