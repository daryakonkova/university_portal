<?php
/**
 * API для работы с журналом аудита (логи системы)
 */

header('Content-Type: application/json; charset=utf-8');

// Подключаем конфигурацию, соединение с БД и управление сессиями
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

// Проверка прав администратора (безопасность)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Доступ запрещен. Требуются права администратора.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // 1. Получение списка всех событий аудита
        case 'list':
            /**
             * Выбираем данные из 10-й таблицы (action_logs).
             * Используем LEFT JOIN с таблицей users, чтобы получить логин пользователя.
             * Сортируем по времени (от новых к старым).
             */
            $stmt = $pdo->query("
                SELECT 
                    l.id, 
                    l.action, 
                    l.action_time, 
                    u.username 
                FROM action_logs l
                LEFT JOIN users u ON l.user_id = u.id
                ORDER BY l.action_time DESC
                LIMIT 500
            ");

            $logs = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $logs
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 2. Очистка журнала (опционально для админа)
        case 'clear':
            $stmt = $pdo->prepare("DELETE FROM action_logs WHERE action_time < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();

            // Фиксируем сам факт очистки старых логов
            $adminId = $_SESSION['user_id'];
            $logStmt = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, 'Очистка журнала аудита (удалены записи старше 30 дней)')");
            $logStmt->execute([$adminId]);

            echo json_encode([
                'success' => true,
                'message' => 'Старые записи успешно удалены'
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Некорректное действие API'
            ], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера при работе с логами: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}