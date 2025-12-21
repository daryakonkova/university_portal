<?php
/**
 * Файл управления сессиями и правами доступа
 * Обеспечивает безопасность страниц и проверку ролей пользователей.
 */

// Запускаем сессию, если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Проверяет, авторизован ли пользователь.
 * Если нет — перенаправляет на страницу входа.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /index.php?error=auth_required");
        exit;
    }
}

/**
 * Проверяет наличие конкретной роли у пользователя.
 * @param string|array $requiredRole Название роли или массив ролей (например, 'Admin' или ['Admin', 'Teacher'])
 * @return bool
 */
function hasRole($requiredRole) {
    if (!isset($_SESSION['role'])) {
        return false;
    }

    if (is_array($requiredRole)) {
        return in_array($_SESSION['role'], $requiredRole);
    }

    return $_SESSION['role'] === $requiredRole;
}

/**
 * Ограничивает доступ к странице только для администраторов.
 * Если у пользователя нет прав — перенаправляет на главную или страницу ошибки.
 */
function requireAdmin() {
    requireLogin();
    if (!hasRole('Admin')) {
        header("Location: /dashboard.php?error=access_denied");
        exit;
    }
}

/**
 * Ограничивает доступ к странице только для преподавателей.
 */
function requireTeacher() {
    requireLogin();
    if (!hasRole('Teacher')) {
        header("Location: /dashboard.php?error=access_denied");
        exit;
    }
}

/**
 * Возвращает ID текущего пользователя.
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Возвращает роль текущего пользователя.
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}