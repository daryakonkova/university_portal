<?php
/**
 * Страница "Журнал оценок" для преподавателя
 * Путь: /pages/teacher/journal.php
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
$disciplineId = isset($_GET['discipline_id']) ? intval($_GET['discipline_id']) : 0;

if ($groupId <= 0) {
    header("Location: groups.php?error=invalid_group");
    exit;
}

try {
    // 1. Получаем данные преподавателя
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

    // 3. Получаем список дисциплин, которые этот преподаватель ведет в этой группе
    $disciplinesStmt = $pdo->prepare("
        SELECT DISTINCT d.id, d.subject_name 
        FROM disciplines d
        JOIN schedule s ON d.id = s.discipline_id
        WHERE s.group_id = ? AND s.teacher_id = ?
    ");
    $disciplinesStmt->execute([$groupId, $teacherId]);
    $disciplines = $disciplinesStmt->fetchAll();

    // Если дисциплина не выбрана, берем первую из списка
    if ($disciplineId === 0 && !empty($disciplines)) {
        $disciplineId = $disciplines[0]['id'];
    }

    // 4. Получаем список студентов группы и их оценки по выбранной дисциплине
    $studentsStmt = $pdo->prepare("
        SELECT 
            s.id as student_id, 
            s.full_name, 
            g.grade, 
            g.id as grade_id,
            g.date_given
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id AND g.discipline_id = ?
        WHERE s.group_id = ?
        ORDER BY s.full_name ASC
    ");
    $studentsStmt->execute([$disciplineId, $groupId]);
    $students = $studentsStmt->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал группы <?php echo htmlspecialchars($group['group_name']); ?> | Витте.Портал</title>
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
                <span class="text-slate-900 font-semibold">Журнал: <?php echo htmlspecialchars($group['group_name']); ?></span>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-bold"><?php echo htmlspecialchars($teacher['full_name']); ?></div>
                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Ведомость успеваемости</div>
            </div>
            <div class="w-10 h-10 bg-slate-900 rounded-full flex items-center justify-center text-white border border-slate-700">
                <i class="fa-solid fa-book"></i>
            </div>
        </div>
    </nav>

    <main class="p-8 max-w-7xl mx-auto w-full">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900">Электронный журнал</h1>
                <p class="text-slate-500 text-sm mt-1">Группа: <span class="font-bold text-slate-700"><?php echo htmlspecialchars($group['group_name']); ?></span></p>
            </div>

            <div class="flex flex-col space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Выбор дисциплины</label>
                <select onchange="location.href='?group_id=<?php echo $groupId; ?>&discipline_id='+this.value"
                        class="bg-white border border-slate-200 px-4 py-3 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition-all min-w-[250px]">
                    <?php foreach ($disciplines as $disc): ?>
                        <option value="<?php echo $disc['id']; ?>" <?php echo ($disciplineId == $disc['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($disc['subject_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-xl mb-8">
                <p class="text-red-700 font-bold">Ошибка:</p>
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">№</th>
                    <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Студент</th>
                    <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Оценка</th>
                    <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest">Дата изменения</th>
                    <th class="px-8 py-5 text-[11px] font-black text-slate-400 uppercase tracking-widest text-right">Действие</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-slate-400 italic">Студенты в этой группе не найдены.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($students as $student): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-4 text-xs font-bold text-slate-300">
                                <?php echo str_pad($i++, 2, '0', STR_PAD_LEFT); ?>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                        <?php
                                        $names = explode(' ', $student['full_name']);
                                        echo mb_substr($names[0], 0, 1) . (isset($names[1]) ? mb_substr($names[1], 0, 1) : '');
                                        ?>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <select id="grade-<?php echo $student['student_id']; ?>"
                                        class="bg-white border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                    <option value="">Нет оценки</option>
                                    <option value="Отлично" <?php echo ($student['grade'] === 'Отлично') ? 'selected' : ''; ?>>Отлично</option>
                                    <option value="Хорошо" <?php echo ($student['grade'] === 'Хорошо') ? 'selected' : ''; ?>>Хорошо</option>
                                    <option value="Удовлетворительно" <?php echo ($student['grade'] === 'Удовлетворительно') ? 'selected' : ''; ?>>Удовлетворительно</option>
                                    <option value="Неудовлетворительно" <?php echo ($student['grade'] === 'Неудовлетворительно') ? 'selected' : ''; ?>>Неудовлетворительно</option>
                                    <option value="Зачтено" <?php echo ($student['grade'] === 'Зачтено') ? 'selected' : ''; ?>>Зачтено</option>
                                    <option value="Незачет" <?php echo ($student['grade'] === 'Незачет') ? 'selected' : ''; ?>>Незачет</option>
                                </select>
                            </td>
                            <td class="px-8 py-4 text-xs text-slate-400 font-medium">
                                <?php echo $student['date_given'] ? date('d.m.Y', strtotime($student['date_given'])) : '—'; ?>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <button onclick="saveGrade(<?php echo $student['student_id']; ?>)"
                                        class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                    Обновить
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-12 flex items-center justify-between">
            <div class="p-6 bg-blue-600 rounded-3xl text-white shadow-xl shadow-blue-200 flex-1 max-w-sm">
                <h4 class="font-bold text-sm mb-2">Автоматическое сохранение</h4>
                <p class="text-[10px] opacity-80 leading-relaxed">Все действия по изменению оценок логируются в системе аудита для обеспечения прозрачности учебного процесса.</p>
            </div>
            <button onclick="window.print()" class="flex items-center space-x-2 bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Экспорт ведомости</span>
            </button>
        </div>
    </main>
</div>

<script>
    /**
     * Сохранение оценки студента через API
     */
    async function saveGrade(studentId) {
        const grade = document.getElementById('grade-' + studentId).value;
        const disciplineId = <?php echo $disciplineId; ?>;

        // Визуальный фидбек
        const btn = event.target;
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = '...';

        try {
            const formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('discipline_id', disciplineId);
            formData.append('grade', grade);
            formData.append('action', 'save_grade');

            const response = await fetch('../../api/grades.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Можно обновить дату на клиенте или просто показать успех
                btn.classList.replace('bg-blue-50', 'bg-emerald-500');
                btn.classList.replace('text-blue-600', 'text-white');
                btn.innerText = 'OK';
                setTimeout(() => {
                    btn.classList.replace('bg-emerald-500', 'bg-blue-50');
                    btn.classList.replace('text-white', 'text-blue-600');
                    btn.innerText = originalText;
                    btn.disabled = false;
                }, 1500);
            } else {
                alert('Ошибка: ' + result.message);
                btn.disabled = false;
                btn.innerText = originalText;
            }
        } catch (error) {
            console.error(error);
            alert('Сетевая ошибка при сохранении');
            btn.disabled = false;
            btn.innerText = originalText;
        }
    }
</script>

<footer class="mt-20 py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Университетская информационная система  • Коньюкова Дарья Дмитриевна • 2025</p>
</footer>
</body>
</html>