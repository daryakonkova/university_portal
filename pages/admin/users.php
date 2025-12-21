<?php
/**
 * Страница управления пользователями (только для Администратора)
 */

// Переходим на два уровня вверх для доступа к системным файлам
require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Защита доступа: только для администраторов
requireAdmin();

$currentUserName = $_SESSION['username'] ?? 'Администратор';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи | Панель администратора</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .modal-active { overflow: hidden; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">

<!-- Sidebar (Mini) / Mobile Toggle placeholder -->
<div class="flex">
    <!-- Основной контент -->
    <div class="flex-1">
        <!-- Верхняя панель навигации -->
        <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center space-x-4">
                <a href="../../dashboard.php" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-slate-400">Панель управления</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-semibold">Пользователи</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold"><?php echo htmlspecialchars($currentUserName); ?></div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Администратор системы</div>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 border border-slate-200">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
        </nav>

        <main class="p-8 max-w-7xl mx-auto">
            <!-- Заголовок и действия -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight">Учетные записи</h1>
                    <p class="text-slate-500 text-sm mt-1">Всего зарегистрировано в системе: <span id="user-count" class="font-bold text-slate-700">...</span></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" id="user-search" placeholder="Поиск по логину..." class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64 transition-all">
                    </div>
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm font-bold flex items-center transition-all shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-user-plus mr-2"></i> Создать
                    </button>
                </div>
            </div>

            <!-- Таблица -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Пользователь</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Роль</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Регистрация</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-right">Управление</th>
                        </tr>
                        </thead>
                        <tbody id="users-table-body" class="divide-y divide-slate-100">
                        <!-- Скелетон загрузки -->
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
                                <div class="mt-4 text-slate-400 text-sm">Получение данных с сервера...</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200 flex justify-between items-center">
                    <div class="text-xs text-slate-400">Показаны активные пользователи портала</div>
                    <div class="flex space-x-2">
                        <button class="p-2 text-slate-400 hover:text-slate-600 disabled:opacity-50" disabled><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="p-2 text-slate-400 hover:text-slate-600 disabled:opacity-50" disabled><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Модальное окно создания (Add User) -->
<div id="userModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform transition-all">
        <div class="bg-slate-50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Новый аккаунт</h3>
                <p class="text-xs text-slate-400">Регистрация в системе портала</p>
            </div>
            <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-200 text-slate-400 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="addUserForm" class="p-8 space-y-5">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Логин (System Username)</label>
                <div class="relative">
                    <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="text" name="username" required placeholder="ivanov_ii" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Пароль</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Системная роль</label>
                <div class="relative">
                    <i class="fa-solid fa-id-badge absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                    <select name="role_id" id="role-select" required class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none bg-white transition-all text-sm appearance-none">
                        <!-- Подгружается из API -->
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-[10px]"></i>
                </div>
            </div>
            <div class="pt-4 flex space-x-3">
                <button type="button" onclick="closeModal()" class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors text-sm">Отмена</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-100 text-sm">Создать</button>
            </div>
        </form>
    </div>
</div>
<footer class="mt-auto py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Коньюкова Дарья Дмитриевна • 2025</p>
</footer>
<script>
    let allUsers = [];

    // Загрузка списка пользователей через API
    async function loadUsers() {
        try {
            const response = await fetch('../../api/users_api.php?action=list');
            const result = await response.json();

            if (result.success) {
                allUsers = result.data;
                renderUsers(allUsers);
                document.getElementById('user-count').textContent = allUsers.length;
            } else {
                console.error(result.message);
            }
        } catch (error) {
            console.error('Ошибка сети:', error);
        }
    }

    function renderUsers(users) {
        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Пользователи не найдены</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = `
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-6 py-4 text-xs font-mono text-slate-400">#${user.id}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors">
                                    <i class="fa-solid fa-user text-[10px]"></i>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">${user.username}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider ${getRoleClass(user.role_name)}">
                                ${user.role_name}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            ${new Date(user.created_at).toLocaleDateString('ru-RU')}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="deleteUser(${user.id}, '${user.username}')" class="w-8 h-8 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </td>
                    </tr>
                `;
            tbody.innerHTML += row;
        });
    }

    async function loadRoles() {
        try {
            const response = await fetch('../../api/users_api.php?action=get_roles');
            const result = await response.json();
            if (result.success) {
                const select = document.getElementById('role-select');
                select.innerHTML = result.data.map(r => `<option value="${r.id}">${r.role_name}</option>`).join('');
            }
        } catch (e) { console.error(e); }
    }

    function getRoleClass(role) {
        switch(role) {
            case 'Admin': return 'bg-slate-900 text-white';
            case 'Teacher': return 'bg-blue-50 text-blue-600';
            case 'Student': return 'bg-emerald-50 text-emerald-600';
            default: return 'bg-gray-100 text-gray-500';
        }
    }

    // Поиск по таблице
    document.getElementById('user-search').addEventListener('input', (e) => {
        const val = e.target.value.toLowerCase();
        const filtered = allUsers.filter(u => u.username.toLowerCase().includes(val));
        renderUsers(filtered);
    });

    async function deleteUser(id, name) {
        if (!confirm(`Вы действительно хотите удалить пользователя "${name}"? Это действие необратимо и будет записано в лог аудита.`)) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        try {
            const response = await fetch('../../api/users_api.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                loadUsers();
            } else {
                alert(result.message);
            }
        } catch (e) { alert('Ошибка при удалении'); }
    }

    document.getElementById('addUserForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch('../../api/users_api.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                closeModal();
                e.target.reset();
                loadUsers();
            } else {
                alert(result.message);
            }
        } catch (e) { alert('Ошибка при сохранении'); }
    });

    function openModal() {
        document.getElementById('userModal').classList.remove('hidden');
        document.body.classList.add('modal-active');
    }
    function closeModal() {
        document.getElementById('userModal').classList.add('hidden');
        document.body.classList.remove('modal-active');
    }

    window.onload = () => {
        loadUsers();
        loadRoles();
    };
</script>
</body>
</html>