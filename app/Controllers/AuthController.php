// Файл: app/Controllers/AuthController.php

<?php
namespace App\Controllers;

require_once 'app/Database.php'; // Предполагаем наличие класса для работы с БД

class AuthController {
    private $db;

    public function __construct() {
        // Установление соединения с БД
        $this->db = new \App\Database(); 
    }

    /**
     * Обрабатывает запрос на авторизацию пользователя.
     * @param string $login Логин пользователя
     * @param string $password Пароль пользователя
     * @return array Результат авторизации: ID, роль и статус
     */
    public function login(string $login, string $password): array {
        // 1. Поиск пользователя по логину
        $query = "SELECT id, password_hash, role FROM users WHERE login = ?";
        $user = $this->db->fetchOne($query, [$login]);

        if (!$user) {
            return ['status' => 'error', 'message' => 'Неверный логин или пароль'];
        }

        // 2. Верификация пароля (предполагаем использование password_hash())
        if (!password_verify($password, $user['password_hash'])) {
            return ['status' => 'error', 'message' => 'Неверный логин или пароль'];
        }

        // 3. Успешная авторизация, запуск сессии и возврат данных
        // В реальном проекте здесь будет сгенерирован JWT-токен или установлена сессия
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        return [
            'status' => 'success',
            'user_id' => $user['id'],
            'role' => $user['role'],
            'message' => 'Авторизация прошла успешно'
        ];
    }
}
?>