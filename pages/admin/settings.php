<?php
/**
 * Страница глобальных настроек системы (только для Администратора)
 */

require_once '../../includes/session.php';
require_once '../../includes/db_connect.php';

// Защита доступа: проверка прав администратора через общую систему сессий
requireAdmin();

$currentUserName = $_SESSION['username'] ?? 'Администратор';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки | Панель администратора</title>
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
        <!-- Панель навигации с хлебными крошками -->
        <nav class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center space-x-4">
                <a href="../../dashboard.php" class="text-blue-600 hover:bg-blue-50 p-2 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="text-slate-400">Панель управления</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-slate-900 font-semibold">Настройки системы</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold"><?php echo htmlspecialchars($currentUserName); ?></div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Администратор</div>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 border border-slate-200">
                    <i class="fa-solid fa-gears"></i>
                </div>
            </div>
        </nav>

        <main class="p-8 max-w-4xl mx-auto">
            <div class="mb-8 text-center md:text-left">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Конфигурация портала</h1>
                <p class="text-slate-500 text-sm mt-1">Управление глобальными параметрами, семестрами и безопасностью приложения.</p>
            </div>

            <form id="settingsForm" class="space-y-6">
                <!-- Раздел: Основная информация об организации -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-building-columns mr-3 text-blue-500"></i> Сведения об организации
                        </h3>
                    </div>
                    <div class="p-8 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Название университета</label>
                                <input type="text" name="univ_name" value="ЧОУ ВО «Московский университет им. С.Ю. Витте»" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none text-sm focus:ring-2 focus:ring-blue-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Название веб-ресурса</label>
                                <input type="text" name="site_name" value="Корпоративный портал ВУЗа" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none text-sm focus:ring-2 focus:ring-blue-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Email технической поддержки</label>
                            <input type="email" name="support_email" value="it-admin@witte.ru" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none text-sm focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Раздел: Учебные периоды -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-calendar-check mr-3 text-emerald-500"></i> Текущий период
                        </h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Активный учебный год</label>
                                <select name="academic_year" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 outline-none text-sm bg-white focus:ring-2 focus:ring-blue-500 transition-all">
                                    <option>2024/2025</option>
                                    <option selected>2025/2026</option>
                                    <option>2026/2027</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Семестр</label>
                                <div class="flex items-center space-x-6 mt-2">
                                    <label class="flex items-center space-x-2 cursor-pointer group">
                                        <input type="radio" name="semester" value="1" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Осенний (I)</span>
                                    </label>
                                    <label class="flex items-center space-x-2 cursor-pointer group">
                                        <input type="radio" name="semester" value="2" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Весенний (II)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Раздел: Безопасность и Статус обслуживания -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-user-shield mr-3 text-orange-500"></i> Безопасность и доступ
                        </h3>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="max-w-[80%]">
                                <div class="font-bold text-sm text-slate-900">Режим технического обслуживания</div>
                                <div class="text-xs text-slate-400 mt-1">При включении портал будет доступен только администраторам. Остальные пользователи увидят страницу-заглушку.</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <hr class="border-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="max-w-[80%]">
                                <div class="font-bold text-sm text-slate-900">Принудительная смена пароля</div>
                                <div class="text-xs text-slate-400 mt-1">Обязать новых пользователей сменить временный пароль при первом входе в систему.</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="force_password_change" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Кнопка сохранения -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 pt-4">
                    <p class="text-[10px] text-slate-400 italic">Все изменения автоматически фиксируются в журнале аудита системы.</p>
                    <button type="submit" id="save-settings-btn" class="w-full md:w-auto bg-slate-900 hover:bg-black text-white px-10 py-3 rounded-xl font-bold transition-all shadow-lg shadow-slate-200 flex items-center justify-center">
                        <span>Применить изменения</span>
                    </button>
                </div>
            </form>

            <div class="mt-12 text-center border-t border-slate-200 pt-8">
                <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал ВУЗа • Версия 2.5.0-Stable</p>
            </div>
        </main>
    </div>
</div>
<footer class="mt-auto py-10 text-center">
    <p class="text-[10px] text-slate-300 uppercase tracking-widest font-bold">Корпоративный портал • Конькова Дарья • 2025</p>
</footer>
<!-- Клиентская логика взаимодействия с API -->
<script>
    document.getElementById('settingsForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('save-settings-btn');
        const btnContent = btn.querySelector('span');
        const originalText = btnContent.innerText;

        // Визуальная индикация процесса загрузки
        btn.disabled = true;
        btnContent.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-2"></i> Сохранение данных...';

        try {
            const formData = new FormData(this);

            // Асинхронный запрос к серверному API настроек
            const response = await fetch('../../api/settings_api.php', {
                method: 'POST',
                body: formData
            });

            // Проверка HTTP статуса
            if (!response.ok) throw new Error('Ошибка сетевого соединения');

            const result = await response.json();

            if (result.success) {
                // Уведомление об успехе
                showNotification(result.message, 'success');
            } else {
                // Уведомление об ошибке бизнес-логики
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Критическая ошибка:', error);
            showNotification('Произошла непредвиденная ошибка при связи с сервером.', 'error');
        } finally {
            // Возврат кнопки в исходное состояние
            btn.disabled = false;
            btnContent.innerText = originalText;
        }
    });

    /**
     * Функция вывода кастомных уведомлений (вместо стандартных алертов)
     */
    function showNotification(message, type) {
        const colors = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-triangle-exclamation';

        const toast = document.createElement('div');
        toast.className = `fixed bottom-8 right-8 ${colors} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center space-x-3 z-50 transform transition-all duration-300 translate-y-20`;
        toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span class="text-sm font-bold">${message}</span>`;

        document.body.appendChild(toast);

        // Анимация появления
        setTimeout(() => toast.classList.remove('translate-y-20'), 10);

        // Автоматическое скрытие
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-4');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
</script>
</body>
</html>