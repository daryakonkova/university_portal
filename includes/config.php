<?php
/**
 * Глобальный конфигурационный файл корпоративного портала
 * Содержит настройки подключения к БД, параметры сессий и константы.
 */

// 1. Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'university_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// 2. Настройки приложения
define('SITE_NAME', 'Корпоративный портал ВУЗа');
define('APP_ID', 'default-app-id');

// 3. Настройки отображения ошибок (включено для разработки)
error_reporting(0);
ini_set('display_errors', 0);

// 4. Настройки безопасности сессий
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Установите 1, если используете HTTPS

/**
 * Вспомогательная функция для безопасного вывода текста (защита от XSS)
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}