<?php
namespace App\Models;

// Подключение к базовому классу для работы с БД
require_once 'app/Database.php'; 

class UserModel {
    private $db;

    public function __construct() {
        // Инициализация объекта для работы с БД
        $this->db = new \App\Database(); 
    }

    /**
     * Ищет пользователя по логину и возвращает его данные для авторизации.
     * @param string $login Логин пользователя
     * @return array|null Данные пользователя (ID, хэш пароля, роль) или null, если не найден.
     */
    public function findByLogin(string $login): ?array {
        $query = "SELECT id, password_hash, role FROM users WHERE login = ?";
        
        // fetchOne — метод базового класса для получения одной строки
        return $this->db->fetchOne($query, [$login]);
    }

    /**
     * Получает полную информацию о пользователе по его ID.
     * @param int $userId ID пользователя
     * @return array|null Полные данные пользователя
     */
    public function getUserProfile(int $userId): ?array {
        $query = "SELECT id, login, fio, email, role FROM users WHERE id = ?";
        return $this->db->fetchOne($query, [$userId]);
    }
}
?>