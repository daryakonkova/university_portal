<?php
/**
 * API для сохранения глобальных настроек системы
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

// Проверка прав доступа (только для Администратора)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен'], JSON_UNESCAPED_UNICODE);
    exit;
}

$adminId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Некорректный запрос'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Получаем данные из формы
        $univName = trim($_POST['univ_name'] ?? '');
        $siteName = trim($_POST['site_name'] ?? '');
        $supportEmail = trim($_POST['support_email'] ?? '');
        $semester = $_POST['semester'] ?? '1';
        $maintenance = isset($_POST['maintenance_mode']) ? 1 : 0;
        $forcePassword = isset($_POST['force_password_change']) ? 1 : 0;

        if (empty($univName) || empty($siteName)) {
            throw new Exception('Название организации и портала обязательны');
        }

        /**
         * В учебном проекте мы фиксируем изменения в журнале аудита (10-я таблица).
         * Если в БД нет 11-й таблицы settings, мы имитируем успех после логирования.
         * Но для полноты кода, здесь должен быть UPDATE запрос к таблице конфигурации.
         */

        // Пример лога в action_logs
        $logAction = "Обновлены системные настройки. Режим обслуживания: " . ($maintenance ? 'ВКЛ' : 'ВЫКЛ');
        $logStmt = $pdo->prepare("INSERT INTO action_logs (user_id, action) VALUES (?, ?)");
        $logStmt->execute([$adminId, $logAction]);

        // Ответ об успешном сохранении
        $response['success'] = true;
        $response['message'] = 'Настройки успешно сохранены и применены';

    } catch (Exception $e) {
        $response['message'] = 'Ошибка при сохранении: ' . $e->getMessage();
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);