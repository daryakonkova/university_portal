<?php
/**
 * Главная страница корпоративного портала
 * Содержит формы входа и регистрации.
 */

require_once 'includes/config.php';
require_once 'includes/session.php';

// Если пользователь уже авторизован, перенаправляем его в личный кабинет
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему | Корпоративный портал ВУЗа</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .auth-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-[440px]">
    <!-- Логотип и заголовок -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl shadow-xl shadow-blue-200 mb-4">
            <i class="fa-solid fa-graduation-cap text-3xl text-white"></i>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Университет Витте</h1>
        <p class="text-slate-500 text-sm mt-2 font-medium">Корпоративный образовательный портал</p>
    </div>

    <!-- Основная карточка -->
    <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200 border border-slate-100 overflow-hidden auth-card">

        <!-- Переключатель вкладок -->
        <div class="flex p-2 bg-slate-50 border-b border-slate-100">
            <button onclick="switchTab('login')" id="tab-login" class="flex-1 py-3 text-sm font-bold rounded-xl transition-all bg-white text-blue-600 shadow-sm">
                Вход
            </button>
            <button onclick="switchTab('register')" id="tab-register" class="flex-1 py-3 text-sm font-bold rounded-xl transition-all text-slate-400 hover:text-slate-600">
                Регистрация
            </button>
        </div>

        <div class="p-10">
            <!-- Форма входа -->
            <form id="loginForm" class="space-y-6">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Логин / Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="text" name="username" required placeholder="Введите логин"
                               class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Пароль</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all text-sm">
                    </div>
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold py-4 rounded-2xl shadow-lg shadow-slate-200 transition-all active:scale-[0.98]">
                    Войти в кабинет
                </button>
            </form>

            <!-- Форма регистрации (скрыта) -->
            <form id="registerForm" class="space-y-5 hidden">
                <input type="hidden" name="action" value="register">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Логин</label>
                    <input type="text" name="username" required placeholder="Новый логин"
                           class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Пароль</label>
                    <input type="password" name="password" required placeholder="Минимум 6 символов"
                           class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Ваша роль</label>
                    <select name="role_id" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all text-sm appearance-none cursor-pointer">
                        <option value="3">Студент</option>
                        <option value="2">Преподаватель</option>
                        <option value="1">Администратор</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-100 transition-all active:scale-[0.98]">
                    Создать аккаунт
                </button>
            </form>
        </div>
    </div>

    <!-- Подвал -->
    <div class="mt-8 text-center text-slate-400 text-[10px] uppercase tracking-[0.2em] font-bold">
        © 2025 Московский университет им. С.Ю. Витте
    </div>
</div>

<!-- Клиентская логика -->
<script>
    /**
     * Переключение между вкладками Вход / Регистрация
     */
    function switchTab(tab) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');

        if (tab === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            tabLogin.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-all bg-white text-blue-600 shadow-sm';
            tabRegister.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-all text-slate-400 hover:text-slate-600';
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            tabRegister.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-all bg-white text-blue-600 shadow-sm';
            tabLogin.className = 'flex-1 py-3 text-sm font-bold rounded-xl transition-all text-slate-400 hover:text-slate-600';
        }
    }

    /**
     * Универсальный обработчик отправки форм через AJAX
     */
    async function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button');
        const originalText = btn.innerText;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin"></i> Обработка...';

        try {
            const formData = new FormData(form);
            const response = await fetch('api/auth.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    alert(result.message);
                    switchTab('login'); // Переключаем на вход после регистрации
                }
            } else {
                alert('Ошибка: ' + result.message);
            }
        } catch (error) {
            console.error(error);
            alert('Системная ошибка. Проверьте соединение с сервером.');
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    }

    document.getElementById('loginForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('registerForm').addEventListener('submit', handleFormSubmit);
</script>
</body>
</html>