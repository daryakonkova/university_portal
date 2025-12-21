<?php
/**
 * Главная страница личного кабинета (Dashboard)
 * Распределяет контент в зависимости от роли пользователя.
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/db_connect.php';

// Проверка авторизации
requireLogin();

$userId = getCurrentUserId();
$username = $_SESSION['username'];
$role = getCurrentUserRole();

// Инициализируем переменные для статистики (для Админа и других ролей)
$stats = [
    'users' => 0,
    'groups' => 0,
    'schedules' => 0,
    'grades' => 0
];

try {
    if ($role === 'Admin') {
        // Загрузка статистики для админ-панели
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['groups'] = $pdo->query("SELECT COUNT(*) FROM groups")->fetchColumn();
        $stats['logs'] = $pdo->query("SELECT COUNT(*) FROM action_logs")->fetchColumn();
    } elseif ($role === 'Student') {
        // Загрузка данных для студента
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM grades WHERE student_id = (SELECT id FROM students WHERE user_id = ?)");
        $stmt->execute([$userId]);
        $stats['grades'] = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    // Ошибка БД не должна ломать страницу
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления | <?php echo htmlspecialchars($username); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .nav-active { background: #eff6ff; color: #1d4ed8; border-right: 4px solid #1d4ed8; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<!-- Боковая навигация -->
<aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0 hidden md:flex flex-col">
    <div class="p-6 border-b border-slate-100">
        <div class="flex items-center space-x-3 text-blue-600">
            <i class="fa-solid fa-graduation-cap text-2xl"></i>
            <span class="font-bold text-lg text-slate-900 tracking-tight">Витте.Портал</span>
        </div>
    </div>

    <nav class="flex-1 py-6 space-y-1">
        <a href="dashboard.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-semibold nav-active">
            <i class="fa-solid fa-chart-pie w-5"></i>
            <span>Обзор</span>
        </a>

        <?php if ($role === 'Admin'): ?>
            <div class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Управление</div>
            <a href="pages/admin/users.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-users w-5"></i>
                <span>Пользователи</span>
            </a>
            <a href="pages/admin/groups.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-layer-group w-5"></i>
                <span>Группы</span>
            </a>
            <a href="pages/admin/schedule.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-calendar-days w-5"></i>
                <span>Расписание</span>
            </a>
            <a href="pages/admin/audit.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-clock-rotate-left w-5"></i>
                <span>Аудит</span>
            </a>
            <a href="pages/admin/settings.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-sliders w-5"></i>
                <span>Настройки</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'Student'): ?>
            <a href="pages/student/schedule.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-calendar-week w-5"></i>
                <span>Моё расписание</span>
            </a>
            <a href="pages/student/grades.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-star w-5"></i>
                <span>Успеваемость</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'Teacher'): ?>
            <a href="pages/teacher/groups.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-chalkboard-user w-5"></i>
                <span>Мои группы</span>
            </a>
            <a href="pages/teacher/journal.php" class="flex items-center space-x-3 px-6 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-book w-5"></i>
                <span>Журнал</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="p-6 border-t border-slate-100">
        <button onclick="handleLogout()" class="flex items-center space-x-3 text-sm font-bold text-red-500 hover:text-red-700 transition-colors">
            <i class="fa-solid fa-right-from-bracket w-5"></i>
            <span>Выйти из системы</span>
        </button>
    </div>
</aside>

<!-- Основная область -->
<main class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <!-- Верхняя панель -->
    <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 flex-shrink-0">
        <h2 class="font-bold text-slate-800">Обзор системы</h2>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <div class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars($username); ?></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold"><?php echo $role; ?></div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 border border-blue-200">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
    </header>

    <!-- Контент -->
    <div class="flex-1 overflow-y-auto p-8">
        <div class="max-w-6xl mx-auto">

            <!-- Приветствие -->
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Добро пожаловать, <?php echo htmlspecialchars($username); ?>!</h1>
                <p class="text-slate-500 mt-1">Сегодня <?php echo date('d.m.Y'); ?>. Проверьте актуальные обновления портала.</p>
            </div>

            <!-- Карточки статистики -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <?php if ($role === 'Admin'): ?>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center mb-4"><i class="fa-solid fa-users"></i></div>
                        <div class="text-2xl font-bold text-slate-900"><?php echo $stats['users']; ?></div>
                        <div class="text-xs font-bold text-slate-400 uppercase">Пользователей</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="text-2xl font-bold text-slate-900"><?php echo $stats['groups']; ?></div>
                        <div class="text-xs font-bold text-slate-400 uppercase">Групп в системе</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center mb-4"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div class="text-2xl font-bold text-slate-900"><?php echo $stats['logs']; ?></div>
                        <div class="text-xs font-bold text-slate-400 uppercase">Событий аудита</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center mb-4"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="text-2xl font-bold text-slate-900 text-orange-600">Active</div>
                        <div class="text-xs font-bold text-slate-400 uppercase">Защита включена</div>
                    </div>
                <?php elseif ($role === 'Student'): ?>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 col-span-2">
                        <div class="text-xs font-bold text-slate-400 uppercase mb-4">Ваша успеваемость</div>
                        <div class="flex items-center space-x-6">
                            <div class="text-4xl font-black text-blue-600"><?php echo $stats['grades']; ?></div>
                            <div class="text-sm text-slate-500">Оценок получено за текущий семестр. Проверьте зачетную книжку для деталей.</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Быстрые действия / Объявления -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm">
                        <h3 class="font-bold text-lg text-slate-900 mb-6 flex items-center">
                            <i class="fa-solid fa-bullhorn mr-3 text-blue-500"></i> Важные объявления
                        </h3>
                        <div class="space-y-6">
                            <div class="flex items-start space-x-4 pb-6 border-b border-slate-50">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 flex-shrink-0 flex items-center justify-center text-slate-500">
                                    <i class="fa-solid fa-circle-info"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">Обновление расписания на весенний семестр</h4>
                                    <p class="text-xs text-slate-500 mt-1">Ознакомьтесь с изменениями в кабинетах для потоковых лекций. Все данные уже внесены в систему.</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-xl bg-orange-50 flex-shrink-0 flex items-center justify-center text-orange-500">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">Технические работы 25.12</h4>
                                    <p class="text-xs text-slate-500 mt-1">С 22:00 до 00:00 портал будет недоступен в связи с обновлением модуля отчетности.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-slate-900 rounded-2xl p-8 text-white shadow-xl shadow-slate-200">
                        <h3 class="font-bold mb-4">Помощь и поддержка</h3>
                        <p class="text-slate-400 text-sm mb-6">Возникли проблемы с доступом или ошибки в данных? Напишите нам.</p>
                        <a href="mailto:support@witte.ru" class="block w-full text-center bg-blue-600 hover:bg-blue-700 py-3 rounded-xl text-sm font-bold transition-colors">Связаться с IT</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <footer class="mt-auto py-10 text-center">
        <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Конькова Дарья • 2025</p>
    </footer>
</main>

<script>
    /**
     * Функция выхода из системы
     */
    async function handleLogout() {
        if (!confirm('Вы действительно хотите выйти из системы?')) return;

        try {
            const formData = new FormData();
            formData.append('action', 'logout');

            const response = await fetch('api/auth.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                window.location.href = 'index.php';
            }
        } catch (error) {
            console.error('Ошибка при выходе:', error);
            alert('Произошла ошибка при выходе. Попробуйте еще раз.');
        }
    }
</script>
</body>
</html>