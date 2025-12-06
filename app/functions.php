<?php
// Подключаем файл с настройками для доступа к константам (DB_HOST, DEBUG_MODE и т.д.)
require_once __DIR__ . '/config/settings.php';

/**
 * 1. Функция для безопасного вывода JSON-ответа (API Endpoint)
 * Устанавливает корректный заголовок и завершает выполнение скрипта.
 * * @param array $data Массив данных для кодирования в JSON.
 * @param int $statusCode Код HTTP-статуса (по умолчанию 200).
 * @return void
 */
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * 2. Функция для получения данных из тела POST-запроса (для JSON API)
 * * @return array Декодированный массив данных.
 */
function get_post_data(): array {
    $content = file_get_contents('php://input');
    $data = json_decode($content, true);
    
    // Возвращаем пустой массив, если данные невалидны
    return is_array($data) ? $data : [];
}

/**
 * 3. Функция для безопасной обработки ввода данных (экранирование)
 * Защищает от XSS-атак при выводе данных в HTML.
 * * @param string|null $data Строка для очистки.
 * @return string Очищенная строка.
 */
function escape_html(?string $data): string {
    if ($data === null) {
        return '';
    }
    // Используем ENT_QUOTES для экранирования одинарных и двойных кавычек
    return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * 4. Функция проверки авторизации пользователя
 * Используется в начале скриптов, требующих аутентификации.
 * * @return bool True, если пользователь авторизован.
 */
function is_authenticated(): bool {
    // Проверяет наличие user_id в сессии
    return isset($_SESSION['user_id']); 
}

/**
 * 5. Функция проверки роли пользователя (для контроля доступа RBAC)
 * * @param string $requiredRole Требуемая роль ('student', 'teacher', 'admin').
 * @return bool
 */
function has_role(string $requiredRole): bool {
    if (!is_authenticated()) {
        return false;
    }
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $requiredRole;
}

/**
 * 6. Функция для перенаправления (редиректа)
 * * @param string $url Путь для перенаправления.
 * @return void
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}