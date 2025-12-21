<?php
/**
 * Страница журнала аудита (только для Администратора)
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
    <title>Аудит системы | Панель администратора</title>
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
                    <span class="text-slate-900 font-semibold">Журнал аудита (Логи)</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="loadLogs()" class="text-slate-400 hover:text-blue-600 transition-colors p-2">
                    <i class="fa-solid fa-rotate"></i>
                </button>
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 border border-slate-200">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
        </nav>

        <main class="p-8 max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Безопасность и аудит</h1>
                    <p class="text-slate-500 text-sm mt-1">Просмотр всех значимых действий пользователей в системе.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <select id="log-filter" onchange="filterLogs()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <option value="all">Все события</option>
                        <option value="Login">Входы в систему</option>
                        <option value="Создан">Создание записей</option>
                        <option value="Удален">Удаление записей</option>
                    </select>
                </div>
            </div>

            <!-- Таблица логов -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Время</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Пользователь</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Действие</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-right">Статус</th>
                        </tr>
                        </thead>
                        <tbody id="logs-table-body" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">Загрузка данных журнала...</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</div>
<footer class="mt-auto py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Конькова Дарья • 2025</p>
</footer>
<script>
    let allLogs = [];

    async function loadLogs() {
        try {
            const response = await fetch('../../api/audit_api.php?action=list');
            const result = await response.json();
            if (result.success) {
                allLogs = result.data;
                renderLogs(allLogs);
            }
        } catch (e) {
            console.error('Ошибка загрузки логов:', e);
        }
    }

    function renderLogs(logs) {
        const tbody = document.getElementById('logs-table-body');
        tbody.innerHTML = '';

        if (logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">Событий не найдено</td></tr>';
            return;
        }

        logs.forEach(log => {
            const actionClass = getActionClass(log.action);
            const row = `
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 text-xs font-mono text-slate-500">
                            ${new Date(log.action_time).toLocaleString('ru-RU')}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] text-slate-500 uppercase">
                                    ${log.username ? log.username.substring(0, 1) : '?'}
                                </span>
                                <span class="text-sm font-semibold text-slate-700">${log.username || 'Система'}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            ${log.action}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-block w-2 h-2 rounded-full ${actionClass} mr-2"></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Записано</span>
                        </td>
                    </tr>
                `;
            tbody.innerHTML += row;
        });
    }

    function getActionClass(action) {
        if (action.includes('Удален')) return 'bg-red-500';
        if (action.includes('Создан') || action.includes('Добавлена')) return 'bg-emerald-500';
        if (action.includes('вход')) return 'bg-blue-500';
        return 'bg-slate-300';
    }

    function filterLogs() {
        const val = document.getElementById('log-filter').value;
        if (val === 'all') {
            renderLogs(allLogs);
        } else {
            const filtered = allLogs.filter(l => l.action.toLowerCase().includes(val.toLowerCase()));
            renderLogs(filtered);
        }
    }

    window.onload = loadLogs;
</script>
</body>
</html>