<?php
/**
 * Страница управления расписанием (только для Администратора)
 */

require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Защита доступа
requireAdmin();

$currentUserName = $_SESSION['username'] ?? 'Администратор';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание | Панель администратора</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

<div class="flex">
    <div class="flex-1">
        <!-- Навигация -->
        <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center space-x-4">
                <a href="../../dashboard.php" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-slate-400">Панель управления</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-semibold">Расписание занятий</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold"><?php echo htmlspecialchars($currentUserName); ?></div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Администратор</div>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 border border-slate-200">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>
        </nav>

        <main class="p-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Учебный график</h1>
                    <p class="text-slate-500 text-sm mt-1">Формирование и редактирование сетки занятий для всех групп.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <select id="group-filter" onchange="loadSchedule()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <option value="all">Все группы</option>
                        <!-- Группы подгрузятся через JS -->
                    </select>
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm font-bold flex items-center transition-all shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-plus mr-2"></i> Добавить запись
                    </button>
                </div>
            </div>

            <!-- Таблица расписания -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">День / Время</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Группа</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Дисциплина</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Преподаватель</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Аудитория</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-right">Управление</th>
                        </tr>
                        </thead>
                        <tbody id="schedule-table-body" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">Загрузка расписания...</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Модальное окно добавления -->
<div id="scheduleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-900">Новое занятие</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="addScheduleForm" class="p-8 grid grid-cols-2 gap-5">
            <input type="hidden" name="action" value="add">

            <div class="col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Группа</label>
                <select name="group_id" id="modal-group-select" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none text-sm bg-white"></select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">День недели</label>
                <select name="day_of_week" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none text-sm bg-white">
                    <option value="ПН">Понедельник</option>
                    <option value="ВТ">Вторник</option>
                    <option value="СР">Среда</option>
                    <option value="ЧТ">Четверг</option>
                    <option value="ПТ">Пятница</option>
                    <option value="СБ">Суббота</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Время начала</label>
                <input type="time" name="time_start" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none text-sm">
            </div>

            <div class="col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Дисциплина</label>
                <select name="discipline_id" id="modal-discipline-select" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none text-sm bg-white"></select>
            </div>

            <div class="col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Преподаватель</label>
                <select name="teacher_id" id="modal-teacher-select" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none text-sm bg-white"></select>
            </div>

            <div class="col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Аудитория</label>
                <select name="classroom_id" id="modal-classroom-select" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none text-sm bg-white"></select>
            </div>

            <button type="submit" class="col-span-2 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 text-sm mt-4">
                Сохранить в расписании
            </button>
        </form>
    </div>
</div>
<footer class="mt-auto py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Коньюкова Дарья Дмитриевна • 2025</p>
</footer>
<script>
    async function loadOptions() {
        try {
            const response = await fetch('../../api/schedule_api.php?action=get_options');
            const result = await response.json();
            if (result.success) {
                const populate = (id, data, textKey) => {
                    const select = document.getElementById(id);
                    if (!select) return;
                    select.innerHTML = data.map(item => `<option value="${item.id}">${item[textKey]}</option>`).join('');
                };

                populate('modal-group-select', result.groups, 'group_name');
                populate('modal-discipline-select', result.disciplines, 'subject_name');
                populate('modal-teacher-select', result.teachers, 'full_name');
                populate('modal-classroom-select', result.classrooms, 'room_number');

                // Фильтр в шапке
                const filter = document.getElementById('group-filter');
                filter.innerHTML = '<option value="all">Все группы</option>' +
                    result.groups.map(g => `<option value="${g.id}">${g.group_name}</option>`).join('');
            }
        } catch (e) { console.error(e); }
    }

    async function loadSchedule() {
        const groupId = document.getElementById('group-filter').value;
        try {
            const response = await fetch(`../../api/schedule_api.php?action=list&group_id=${groupId}`);
            const result = await response.json();
            if (result.success) {
                const tbody = document.getElementById('schedule-table-body');
                tbody.innerHTML = '';

                if (result.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Расписание на этот период отсутствует</td></tr>';
                    return;
                }

                result.data.forEach(item => {
                    tbody.innerHTML += `
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-900">${item.day_of_week}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">${item.time_start.substring(0,5)} - ${item.time_end ? item.time_end.substring(0,5) : '...'}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-blue-600">${item.group_name}</td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">${item.subject_name}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">${item.teacher_name}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-bold text-slate-600">Ауд. ${item.room_number}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="deleteSchedule(${item.id})" class="text-slate-300 hover:text-red-500 p-2"><i class="fa-solid fa-trash-can text-xs"></i></button>
                                </td>
                            </tr>
                        `;
                });
            }
        } catch (e) { console.error(e); }
    }

    async function deleteSchedule(id) {
        if (!confirm('Удалить эту запись из расписания?')) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch('../../api/schedule_api.php', { method: 'POST', body: formData });
        if ((await response.json()).success) loadSchedule();
    }

    document.getElementById('addScheduleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const response = await fetch('../../api/schedule_api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            closeModal();
            loadSchedule();
        } else alert(result.message);
    });

    function openModal() { document.getElementById('scheduleModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('scheduleModal').classList.add('hidden'); }

    window.onload = () => {
        loadOptions();
        loadSchedule();
    };
</script>
</body>
</html>