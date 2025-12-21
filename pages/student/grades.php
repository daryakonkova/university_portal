<?php
/**
 * Страница "Успеваемость" (Электронная зачетка) для студента
 * Путь: /pages/student/grades.php
 */

require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Защита доступа: только для студентов
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../dashboard.php?error=access_denied");
    exit;
}

$userId = $_SESSION['user_id'];
$error = null;
$student = null;
$grades = [];

try {
    // 1. Получаем профиль студента и информацию о группе
    $stmt = $pdo->prepare("
        SELECT s.id, s.full_name, s.group_id, g.group_name 
        FROM students s 
        JOIN groups g ON s.group_id = g.id 
        WHERE s.user_id = ?
    ");
    $stmt->execute([$userId]);
    $student = $stmt->fetch();

    if (!$student) {
        throw new Exception("Профиль студента не найден.");
    }

    $studentId = $student['id'];

    // 2. Получаем список оценок из ведомости (grades + disciplines)
    $gradesStmt = $pdo->prepare("
        SELECT 
            g.id, 
            d.subject_name, 
            g.grade, 
            g.date_given
        FROM grades g
        JOIN disciplines d ON g.discipline_id = d.id
        WHERE g.student_id = ?
        ORDER BY g.date_given DESC
    ");
    $gradesStmt->execute([$studentId]);
    $grades = $gradesStmt->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
}

/**
 * Вспомогательная функция для определения цвета оценки
 */
function getGradeColor($grade) {
    switch ($grade) {
        case 'Отлично': return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        case 'Хорошо': return 'text-blue-600 bg-blue-50 border-blue-100';
        case 'Удовлетворительно': return 'text-orange-600 bg-orange-50 border-orange-100';
        case 'Зачтено': return 'text-purple-600 bg-purple-50 border-purple-100';
        default: return 'text-slate-600 bg-slate-50 border-slate-100';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Успеваемость | Витте.Портал</title>
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
        <a href="schedule.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all text-sm font-semibold">
            <i class="fas fa-calendar-alt w-5"></i>
            <span>Моё расписание</span>
        </a>
        <a href="grades.php" class="sidebar-link active flex items-center space-x-3 px-4 py-3 rounded-xl transition-all text-sm font-semibold">
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
                <span class="text-slate-900 font-semibold">Электронная зачетка</span>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($student['full_name'] ?? ''); ?></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Группа: <?php echo htmlspecialchars($student['group_name'] ?? ''); ?></div>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 border border-blue-200 shadow-lg shadow-blue-50">
                <i class="fa-solid fa-star"></i>
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

            <!-- Welcome & Stats -->
            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Ваша успеваемость</h1>
                    <p class="text-slate-500 text-sm mt-1">Результаты текущего семестра для группы <span class="text-blue-600 font-bold"><?php echo htmlspecialchars($student['group_name']); ?></span></p>
                </div>

                <div class="flex space-x-4">
                    <div class="bg-white px-6 py-4 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-4">
                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Всего оценок</div>
                            <div class="text-lg font-black text-slate-900"><?php echo count($grades); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grades List -->
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden mb-12">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Дисциплина</th>
                        <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Оценка</th>
                        <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Дата выставления</th>
                        <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-right">Статус</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                    <?php if (empty($grades)): ?>
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-file-invoice text-5xl mb-4"></i>
                                    <p class="text-sm font-bold uppercase tracking-widest">Оценок пока нет</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grades as $grade): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            <i class="fas fa-book text-[10px]"></i>
                                        </div>
                                        <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($grade['subject_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                            <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border <?php echo getGradeColor($grade['grade']); ?>">
                                                <?php echo htmlspecialchars($grade['grade']); ?>
                                            </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center text-slate-400 text-xs font-medium">
                                        <i class="far fa-calendar-alt mr-2 opacity-50"></i>
                                        <?php echo date('d.m.Y', strtotime($grade['date_given'])); ?>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <?php if (in_array($grade['grade'], ['Отлично', 'Хорошо', 'Удовлетворительно', 'Зачтено'])): ?>
                                        <span class="text-emerald-500 text-xs font-bold flex items-center justify-end">
                                                    <i class="fas fa-check-circle mr-1.5"></i> Сдано
                                                </span>
                                    <?php else: ?>
                                        <span class="text-rose-500 text-xs font-bold flex items-center justify-end">
                                                    <i class="fas fa-exclamation-circle mr-1.5"></i> Пересдача
                                                </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Info Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-slate-900 rounded-[2rem] p-8 text-white shadow-xl shadow-slate-200">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-900/50">
                            <i class="fas fa-info-circle text-xl"></i>
                        </div>
                        <h4 class="font-bold text-lg leading-tight">Важное примечание</h4>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Данные об успеваемости вносятся преподавателями в течение 3-х рабочих дней после проведения аттестации. В случае обнаружения неточностей, пожалуйста, обратитесь в деканат или к ведущему преподавателю.
                    </p>
                </div>

                <div class="bg-white rounded-[2rem] p-8 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 mb-2">Формирование выписки</h4>
                        <p class="text-xs text-slate-500 leading-relaxed mb-6">Вы можете скачать официальную выписку из электронной ведомости для предоставления по месту требования.</p>
                    </div>
                    <button onclick="window.print()" class="w-full bg-slate-50 hover:bg-slate-100 text-slate-900 font-bold py-3 rounded-xl transition-all flex items-center justify-center space-x-2 border border-slate-100">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        <span>Скачать ведомость (PDF)</span>
                    </button>
                </div>
            </div>

        <?php endif; ?>
    </main>

    <footer class="mt-auto py-10 text-center">
        <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Модуль "Успеваемость" • Коньюкова Дарья Дмитриевна • 2025</p>
    </footer>
</div>

</body>
</html>