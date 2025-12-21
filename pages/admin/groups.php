<?php
/**
 * Страница управления учебными группами (только для Администратора)
 * Путь: /pages/admin/groups.php
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
    <title>Группы | Панель администратора</title>
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
        <!-- Хлебные крошки и навигация -->
        <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center space-x-4">
                <a href="../../dashboard.php" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-slate-400">Панель управления</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-semibold">Учебные группы</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="loadGroups()" class="text-slate-400 hover:text-blue-600 p-2 transition-colors">
                    <i class="fa-solid fa-rotate"></i>
                </button>
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold"><?php echo htmlspecialchars($currentUserName); ?></div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Администратор</div>
                </div>
            </div>
        </nav>

        <main class="p-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Учебные группы</h1>
                    <p class="text-slate-500 text-sm mt-1">Список академических групп университета из базы данных.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" id="group-search" placeholder="Поиск группы..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64 transition-all">
                    </div>
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm font-bold flex items-center transition-all shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-plus mr-2"></i> Добавить
                    </button>
                </div>
            </div>

            <!-- Таблица данных -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest w-24">ID</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Название учебной группы</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-right">Действия</th>
                    </tr>
                    </thead>
                    <tbody id="groups-table-body" class="divide-y divide-slate-100">
                    <!-- Сюда JS вставит строки -->
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent mb-4"></div>
                            <div class="text-slate-400 text-sm italic">Загрузка данных из MySQL...</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Уведомление об ошибке -->
            <div id="error-alert" class="hidden mt-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                <div class="font-bold">Ошибка загрузки:</div>
                <div id="error-message"></div>
            </div>
        </main>
    </div>
</div>

<!-- Модальное окно (Add) -->
<div id="groupModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-900">Новая группа</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="addGroupForm" class="p-8 space-y-5">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Название группы</label>
                <input type="text" name="group_name" required placeholder="Например: ПИЭ-211" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all">
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 text-sm">
                Добавить в базу
            </button>
        </form>
    </div>
</div>
<footer class="mt-auto py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Конькова Дарья • 2025</p>
</footer>
<script>
    let allGroups = [];

    /**
     * Загрузка групп через API
     */
    async function loadGroups() {
        const tbody = document.getElementById('groups-table-body');
        const errorAlert = document.getElementById('error-alert');
        const errorMsg = document.getElementById('error-message');

        try {
            // Путь ../../api/ т.к. файл лежит в /pages/admin/
            const response = await fetch('../../api/groups_api.php?action=list');

            if (!response.ok) throw new Error(`HTTP статус: ${response.status}`);

            const result = await response.json();

            if (result.success) {
                allGroups = result.data;
                renderGroups(allGroups);
                errorAlert.classList.add('hidden');
            } else {
                throw new Error(result.message || 'Ошибка API');
            }
        } catch (e) {
            console.error('Ошибка при запросе:', e);
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-12 text-center text-red-400">Не удалось загрузить данные. Проверьте соединение с БД.</td></tr>';
            errorAlert.classList.remove('hidden');
            errorMsg.textContent = e.message;
        }
    }

    function renderGroups(groups) {
        const tbody = document.getElementById('groups-table-body');
        tbody.innerHTML = '';

        if (!groups || groups.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-12 text-center text-slate-400 italic">В базе данных пока нет ни одной группы.</td></tr>';
            return;
        }

        groups.forEach(group => {
            tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">#${group.id}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center font-bold text-[10px]">
                                    ${group.group_name.substring(0, 2)}
                                </div>
                                <span class="text-sm font-semibold text-slate-900">${group.group_name}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="deleteGroup(${group.id}, '${group.group_name}')" class="w-8 h-8 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </td>
                    </tr>
                `;
        });
    }

    // Поиск по названию
    document.getElementById('group-search').addEventListener('input', (e) => {
        const val = e.target.value.toLowerCase();
        const filtered = allGroups.filter(g => g.group_name.toLowerCase().includes(val));
        renderGroups(filtered);
    });

    async function deleteGroup(id, name) {
        if (!confirm(`Вы уверены, что хотите удалить группу "${name}"?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        try {
            const response = await fetch('../../api/groups_api.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) loadGroups();
            else alert(result.message);
        } catch (e) { alert('Ошибка при удалении'); }
    }

    document.getElementById('addGroupForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch('../../api/groups_api.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                closeModal();
                e.target.reset();
                loadGroups();
            } else alert(result.message);
        } catch (e) { alert('Ошибка при добавлении'); }
    });

    function openModal() { document.getElementById('groupModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('groupModal').classList.add('hidden'); }

    // Загружаем данные сразу при открытии страницы
    window.onload = loadGroups;
</script>
</body>
</html>