<?php
/**
 * Страница "Моё расписание" для студента
 * Путь: /pages/student/schedule.php
 */

require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Access protection: students only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../dashboard.php?error=access_denied");
    exit;
}

$userId = $_SESSION['user_id'];
$error = null;
$student = null;
$groupedSchedule = ['ПН' => [], 'ВТ' => [], 'СР' => [], 'ЧТ' => [], 'ПТ' => [], 'СБ' => []];

try {
    $stmt = $pdo->prepare("
        SELECT s.id, s.full_name, s.group_id, g.group_name 
        FROM students s 
        JOIN groups g ON s.group_id = g.id 
        WHERE s.user_id = ?
    ");
    $stmt->execute([$userId]);
    $student = $stmt->fetch();

    if (!$student) {
        throw new Exception("Профиль студента не найден. Обратитесь в деканат.");
    }

    $groupId = $student['group_id'];

    $scheduleStmt = $pdo->prepare("
        SELECT 
            s.id, 
            s.day_of_week, 
            s.time_start, 
            s.time_end, 
            d.subject_name, 
            t.full_name as teacher_name, 
            c.room_number
        FROM schedule s
        JOIN disciplines d ON s.discipline_id = d.id
        JOIN teachers t ON s.teacher_id = t.id
        JOIN classrooms c ON s.classroom_id = c.id
        WHERE s.group_id = ?
        ORDER BY FIELD(s.day_of_week, 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'), s.time_start
    ");
    $scheduleStmt->execute([$groupId]);
    $scheduleItems = $scheduleStmt->fetchAll();

    foreach ($scheduleItems as $item) {
        $groupedSchedule[$item['day_of_week']][] = $item;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

$daysFullNames = [
    'ПН' => 'Понедельник', 'ВТ' => 'Вторник', 'СР' => 'Среда',
    'ЧТ' => 'Четверг', 'ПТ' => 'Пятница', 'СБ' => 'Суббота'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Моё расписание | Витте.Портал</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link.active { background-color: #1e40af; color: white; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<!-- Sidebar Navigation -->
<aside class="w-64 bg-slate-900 text-slate-300 flex flex-col h-screen sticky top-0 hidden md:flex">
    <div class="p-6 border-b border-slate-800 flex items-center space-x-3">
        <i class="fas fa-graduation-cap text-2xl text-blue-500"></i>
        <span class="font-bold text-lg text-white">Витте.Портал</span>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="../../dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all text-sm font-semibold">
            <i class="fas fa-chart-line w-5"></i>
            <span>Обзор</span>
        </a>
        <div class="pt-4 pb-2 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Студент</div>
        <a href="schedule.php" class="sidebar-link active flex items-center space-x-3 px-4 py-3 rounded-xl transition-all text-sm font-semibold">
            <i class="fas fa-calendar-alt w-5"></i>
            <span>Моё расписание</span>
        </a>
        <a href="grades.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all text-sm font-semibold">
            <i class="fas fa-star w-5"></i>
            <span>Успеваемость</span>
        </a>
    </nav>
    <div class="p-6 border-t border-slate-800">
        <a href="../../api/auth.php?action=logout" class="flex items-center space-x-3 text-sm font-bold text-red-400 hover:text-red-300 transition-colors">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>Выйти</span>
        </a>
    </div>
</aside>

<!-- Main Content -->
<div class="flex-1 flex flex-col">
    <!-- Header -->
    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2 text-sm">
                <span class="text-slate-400">Студент</span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-slate-900 font-semibold">Моё расписание</span>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($student['full_name'] ?? ''); ?></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Группа: <?php echo htmlspecialchars($student['group_name'] ?? ''); ?></div>
            </div>
            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 border border-emerald-200 shadow-lg shadow-emerald-50">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>
    </nav>

    <main class="p-8 max-w-7xl mx-auto w-full">
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-xl mb-8">
                <p class="text-red-700 font-bold">Ошибка:</p>
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Расписание занятий</h1>
                    <p class="text-slate-500 text-sm mt-1">Актуальный график для группы <span class="text-blue-600 font-bold"><?php echo htmlspecialchars($student['group_name']); ?></span></p>
                </div>
                <button onclick="window.print()" class="bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all flex items-center">
                    <i class="fas fa-print mr-2"></i> Печать
                </button>
            </div>

            <!-- Schedule Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($groupedSchedule as $dayCode => $lessons): ?>
                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300">
                        <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <h3 class="font-black text-slate-900 uppercase tracking-widest text-xs">
                                <?php echo $daysFullNames[$dayCode]; ?>
                            </h3>
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        </div>

                        <div class="p-6 flex-1 space-y-4">
                            <?php if (empty($lessons)): ?>
                                <div class="flex flex-col items-center justify-center py-12 opacity-20">
                                    <i class="fa-solid fa-calendar-xmark text-3xl mb-3"></i>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-center">Занятий не найдено</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($lessons as $lesson): ?>
                                    <div class="p-5 rounded-2xl border border-slate-50 bg-slate-50/50 hover:bg-white hover:border-blue-100 hover:shadow-lg hover:shadow-blue-50 transition-all duration-300 group">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="text-[11px] font-black uppercase tracking-widest text-blue-600">
                                                <?php echo date('H:i', strtotime($lesson['time_start'])); ?> — <?php echo date('H:i', strtotime($lesson['time_end'])); ?>
                                            </div>
                                            <div class="px-2 py-1 bg-white rounded-lg text-[9px] font-bold uppercase text-slate-500 border border-slate-100 shadow-sm">
                                                Каб. <?php echo htmlspecialchars($lesson['room_number']); ?>
                                            </div>
                                        </div>
                                        <h4 class="font-bold text-sm leading-tight text-slate-800 mb-2 group-hover:text-blue-700 transition-colors">
                                            <?php echo htmlspecialchars($lesson['subject_name']); ?>
                                        </h4>
                                        <div class="text-[10px] font-medium flex items-center text-slate-500">
                                            <i class="fa-solid fa-chalkboard-user mr-1.5 opacity-50"></i>
                                            <?php echo htmlspecialchars($lesson['teacher_name']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Info Banner -->
            <div class="mt-12 p-8 bg-blue-600 rounded-[2.5rem] text-white shadow-2xl shadow-blue-200 relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10 pointer-events-none">
                    <i class="fa-solid fa-clock text-[10rem] translate-x-10 -translate-y-10"></i>
                </div>
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center space-x-5">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">Уведомление системы</h4>
                            <p class="text-sm opacity-80 max-w-md leading-relaxed">В расписании возможны изменения. Пожалуйста, следите за обновлениями в личном кабинете или на информационных стендах кафедры.</p>
                        </div>
                    </div>
                    <a href="grades.php" class="bg-white text-blue-600 px-8 py-3 rounded-xl font-bold hover:bg-blue-50 transition-all shadow-lg text-sm">
                        Проверить успеваемость
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="mt-auto py-10 text-center">
        <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Коньюкова Дарья Дмитриевна • Модуль "Расписание студента" • 2025</p>
    </footer>
</div>

</body>
</html>