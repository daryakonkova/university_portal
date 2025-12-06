
<?php

/**
 * Общие настройки приложения
 */
define('APP_NAME', 'Корпоративный портал ВУЗа');
define('APP_VERSION', '1.0.0');
define('DEFAULT_ROLE', 'student'); // Роль по умолчанию для новых пользователей

/**
 * Настройки подключения к Базе Данных (СУБД MySQL)
 */
define('DB_HOST', 'localhost');      // Адрес сервера БД
define('DB_USER', 'user_portal');    // Имя пользователя БД
define('DB_PASS', 'secure_password_123'); // Пароль пользователя БД
define('DB_NAME', 'university_portal');  // Имя базы данных
define('DB_CHARSET', 'utf8mb4');     // Кодировка для поддержки кириллицы и эмодзи

/**
 * Настройки безопасности
 * Используются для шифрования, хеширования и токенов сессий
 */
define('SECRET_KEY', 'VERY_COMPLEX_RANDOM_STRING_FOR_HASHING'); // Секретный ключ для хеширования (например, JWT или сессии)
define('SESSION_LIFETIME', 3600); // Время жизни сессии в секундах (1 час)

/**
 * Настройки путей
 * Используются для корректного подключения файлов (Controllers, Models)
 */
define('ROOT_DIR', dirname(__DIR__)); // Корневая директория приложения
define('CONTROLLER_PATH', ROOT_DIR . '/app/Controllers/');
define('MODEL_PATH', ROOT_DIR . '/app/Models/');
define('VIEW_PATH', ROOT_DIR . '/app/Views/');

/**
 * Включение режима отладки
 * В режиме 'true' отображаются подробные сообщения об ошибках. 
 * В продакшене должно быть 'false'.
 */
define('DEBUG_MODE', true);

// Установка конфигурации PHP на основе DEBUG_MODE
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Запуск сессии (требуется для аутентификации)
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

?>