<?php
/**
 * Страница "Мои группы" для преподавателя
 * Путь: /pages/teacher/groups.php
 */

require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Защита доступа: только для преподавателей
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../../dashboard.php?error=access_denied");
    exit;
}

$userId = $_SESSION['user_id'];
$currentUserName = $_SESSION['username'];
$error = null;
$teacher = null;
$groups = [];

try {
    // 1. Пытаемся найти профиль преподавателя
    $stmt = $pdo->prepare("SELECT id, full_name, department FROM teachers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $teacher = $stmt->fetch();

    if (!$teacher) {
        // Если профиль не найден, это не критическая ошибка PHP, а отсутствие данных
        $error = "Ваш профиль преподавателя еще не активирован администратором.";
    } else {
        $teacherId = $teacher['id'];

        /**
         * 2. Выбираем уникальные группы из расписания
         */
        $groupsQuery = $pdo->prepare("
            SELECT DISTINCT 
                g.id, 
                g.group_name,
                (SELECT COUNT(*) FROM students s WHERE s.group_id = g.id) as student_count
            FROM groups g
            JOIN schedule sch ON g.id = sch.group_id
            WHERE sch.teacher_id = ?
            ORDER BY g.group_name ASC
        ");
        $groupsQuery->execute([$teacherId]);
        $groups = $groupsQuery->fetchAll();
    }

} catch (Exception $e) {
    $error = "Системная ошибка: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои группы | Личный кабинет преподавателя</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

<div class="flex">
    <div class="flex-1">
        <!-- Верхняя навигация -->
        <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div class="flex items-center space-x-4">
                <a href="../../dashboard.php" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-slate-400">Преподаватель</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-semibold">Мои группы</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold"><?php echo htmlspecialchars($teacher['full_name'] ?? $currentUserName); ?></div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($teacher['department'] ?? 'Профиль не заполнен'); ?></div>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 border border-blue-200">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
        </nav>

        <main class="p-8 max-w-7xl mx-auto">
            <?php if ($error): ?>
                <!-- Блок обработки отсутствия данных в таблице teachers -->
                <div class="max-w-2xl mx-auto mt-12 bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 border border-slate-100 overflow-hidden">
                    <div class="bg-orange-500 p-8 text-center text-white">
                        <i class="fa-solid fa-user-gear text-5xl mb-4"></i>
                        <h2 class="text-2xl font-black">Доступ ограничен</h2>
                    </div>
                    <div class="p-10 text-center">
                        <p class="text-slate-600 mb-8"><?php echo htmlspecialchars($error); ?></p>
                        <div class="space-y-4">
                            <p class="text-xs text-slate-400 leading-relaxed italic">
                                Для корректной работы журнала и расписания необходимо, чтобы администратор привязал вашу учетную запись к справочнику сотрудников.
                            </p>
                            <div class="pt-6 flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="mailto:admin@witte.ru" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-black transition-all shadow-lg shadow-slate-200">
                                    Связаться с ИТ
                                </a>
                                <a href="../../dashboard.php" class="bg-slate-100 text-slate-600 px-8 py-3 rounded-xl font-bold hover:bg-slate-200 transition-all">
                                    На главную
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-10 text-center md:text-left">
                    <h1 class="text-3xl font-extrabold tracking-tight">Ваши учебные группы</h1>
                    <p class="text-slate-500 text-sm mt-1 italic">Список групп, в которых вы ведете лекции и практические занятия в текущем семестре.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (empty($groups)): ?>
                        <div class="col-span-full bg-white p-12 rounded-3xl border border-dashed border-slate-300 text-center">
                            <i class="fa-solid fa-users-slash text-4xl text-slate-200 mb-4"></i>
                            <p class="text-slate-400 font-medium">В расписании пока не назначено ни одной группы для вас.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groups as $group): ?>
                            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-blue-300 transition-all p-8 group relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>

                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-6">
                                        <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-black shadow-lg shadow-blue-100">
                                            <?php echo substr($group['group_name'], 0, 1); ?>
                                        </div>
                                        <span class="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase py-1 px-3 rounded-full border border-slate-100">
                                                ID: <?php echo $group['id']; ?>
                                            </span>
                                    </div>

                                    <h3 class="text-xl font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($group['group_name']); ?></h3>

                                    <div class="flex items-center text-slate-500 text-sm mb-8">
                                        <i class="fa-solid fa-user-graduate mr-2 opacity-50"></i>
                                        <span>Студентов: <span class="font-bold text-slate-700"><?php echo $group['student_count']; ?></span></span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="journal.php?group_id=<?php echo $group['id']; ?>" class="flex items-center justify-center space-x-2 bg-slate-900 text-white py-3 rounded-xl text-xs font-bold hover:bg-black transition-colors">
                                            <i class="fa-solid fa-book-open"></i>
                                            <span>Журнал</span>
                                        </a>
                                        <a href="schedule.php?group_id=<?php echo $group['id']; ?>" class="flex items-center justify-center space-x-2 bg-blue-50 text-blue-600 py-3 rounded-xl text-xs font-bold hover:bg-blue-100 transition-colors">
                                            <i class="fa-solid fa-calendar-day"></i>
                                            <span>График</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Статистика внизу -->
                <div class="mt-16 bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10">
                        <i class="fa-solid fa-briefcase text-[12rem] -translate-y-12 translate-x-12"></i>
                    </div>
                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
                        <div>
                            <div class="text-blue-400 text-xs font-bold uppercase tracking-widest mb-2">Активных групп</div>
                            <div class="text-4xl font-black"><?php echo count($groups); ?></div>
                        </div>
                        <div>
                            <div class="text-blue-400 text-xs font-bold uppercase tracking-widest mb-2">Статус сессии</div>
                            <div class="text-4xl font-black italic">Active</div>
                        </div>
                        <div class="flex items-center justify-center md:justify-end">
                            <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-2xl font-bold transition-all flex items-center space-x-3">
                                <i class="fa-solid fa-file-export"></i>
                                <span>Выгрузить отчет</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<footer class="mt-12 text-center pb-8">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Личный кабинет сотрудника • Система «Витте.Портал»</p>
</footer>
</body>
</html>