<?php
/**
 * Страница "Расписание группы" для преподавателя
 * Путь: /pages/teacher/schedule.php
 */

require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Защита доступа: только для преподавателей
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../../dashboard.php?error=access_denied");
    exit;
}

$userId = $_SESSION['user_id'];
$groupId = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($groupId <= 0) {
    header("Location: groups.php?error=invalid_group");
    exit;
}

try {
    // 1. Получаем данные текущего преподавателя
    $stmt = $pdo->prepare("SELECT id, full_name FROM teachers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $teacher = $stmt->fetch();
    $teacherId = $teacher['id'];

    // 2. Получаем информацию о группе
    $groupStmt = $pdo->prepare("SELECT group_name FROM groups WHERE id = ?");
    $groupStmt->execute([$groupId]);
    $group = $groupStmt->fetch();

    if (!$group) {
        throw new Exception("Группа не найдена.");
    }

    // 3. Получаем полное расписание группы
    // Объединяем с дисциплинами, преподавателями и аудиториями
    $scheduleStmt = $pdo->prepare("
        SELECT 
            s.id, 
            s.day_of_week, 
            s.time_start, 
            s.time_end, 
            d.subject_name, 
            t.full_name as teacher_name, 
            t.id as teacher_record_id,
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

    // Группируем расписание по дням недели для удобного вывода
    $groupedSchedule = [
        'ПН' => [], 'ВТ' => [], 'СР' => [],
        'ЧТ' => [], 'ПТ' => [], 'СБ' => []
    ];

    foreach ($scheduleItems as $item) {
        $groupedSchedule[$item['day_of_week']][] = $item;
    }

    $daysFullNames = [
        'ПН' => 'Понедельник',
        'ВТ' => 'Вторник',
        'СР' => 'Среда',
        'ЧТ' => 'Четверг',
        'ПТ' => 'Пятница',
        'СБ' => 'Суббота'
    ];

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание <?php echo htmlspecialchars($group['group_name']); ?> | Витте.Портал</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

<div class="flex flex-col">
    <!-- Навигация -->
    <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
        <div class="flex items-center space-x-4">
            <a href="groups.php" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="flex items-center space-x-2 text-sm">
                <span class="text-slate-400">Преподаватель</span>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <a href="groups.php" class="text-slate-400 hover:text-blue-600">Мои группы</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                <span class="text-slate-900 font-semibold">График: <?php echo htmlspecialchars($group['group_name']); ?></span>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold"><?php echo htmlspecialchars($teacher['full_name']); ?></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Просмотр расписания</div>
            </div>
            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white border border-blue-700 shadow-lg shadow-blue-200">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>
    </nav>

    <main class="p-8 max-w-7xl mx-auto w-full">
        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-900">Учебный график группы</h1>
            <p class="text-slate-500 text-sm mt-1">Академическая группа: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($group['group_name']); ?></span></p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-xl mb-8">
                <p class="text-red-700 font-bold">Ошибка:</p>
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($groupedSchedule as $dayCode => $lessons): ?>
                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full">
                    <div class="px-8 py-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-black text-slate-900 uppercase tracking-widest text-xs">
                            <?php echo $daysFullNames[$dayCode]; ?>
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400"><?php echo count($lessons); ?> зан.</span>
                    </div>

                    <div class="p-6 flex-1 space-y-4">
                        <?php if (empty($lessons)): ?>
                            <div class="flex flex-col items-center justify-center py-10 opacity-30">
                                <i class="fa-solid fa-mug-hot text-2xl mb-2"></i>
                                <p class="text-[10px] font-bold uppercase tracking-tighter">Занятий нет</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($lessons as $lesson): ?>
                                <?php
                                $isMyLesson = ($lesson['teacher_record_id'] == $teacherId);
                                $cardClass = $isMyLesson ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-white border border-slate-100 text-slate-900';
                                $timeClass = $isMyLesson ? 'text-blue-100' : 'text-slate-400';
                                $teacherClass = $isMyLesson ? 'text-blue-50' : 'text-slate-500';
                                ?>
                                <div class="p-5 rounded-2xl transition-all <?php echo $cardClass; ?>">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="text-[11px] font-black uppercase tracking-widest <?php echo $timeClass; ?>">
                                            <?php echo date('H:i', strtotime($lesson['time_start'])); ?> — <?php echo date('H:i', strtotime($lesson['time_end'])); ?>
                                        </div>
                                        <div class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?php echo $isMyLesson ? 'bg-white/20' : 'bg-slate-100 text-slate-500'; ?>">
                                            Ауд. <?php echo htmlspecialchars($lesson['room_number']); ?>
                                        </div>
                                    </div>
                                    <h4 class="font-bold text-sm leading-tight mb-2">
                                        <?php echo htmlspecialchars($lesson['subject_name']); ?>
                                    </h4>
                                    <div class="text-[10px] font-medium flex items-center <?php echo $teacherClass; ?>">
                                        <i class="fa-solid fa-user-tie mr-1.5 opacity-70"></i>
                                        <?php echo $isMyLesson ? 'Вы ведете это занятие' : htmlspecialchars($lesson['teacher_name']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 flex items-center justify-between p-8 bg-white rounded-[2rem] border border-slate-200 shadow-sm">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-info-circle text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Информация для преподавателя</h4>
                    <p class="text-xs text-slate-500">Синим цветом выделены ваши занятия. Вы можете перейти в журнал для выставления оценок.</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="journal.php?group_id=<?php echo $groupId; ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl text-xs font-bold hover:bg-black transition-all">
                    Открыть журнал
                </a>
            </div>
        </div>
    </main>
</div>

<footer class="mt-20 py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Модуль "Расписание" • 2025</p>
</footer>
</body>
</html>