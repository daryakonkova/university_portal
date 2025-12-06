-- ==============================================================================
-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
-- ==============================================================================

-- Если база данных существует, удаляем ее, чтобы начать с чистого листа
DROP DATABASE IF EXISTS university_portal;
CREATE DATABASE university_portal;
USE university_portal; -- Используется для MySQL. Для PostgreSQL может потребоваться \c university_portal

-- ==============================================================================
-- 2. СОЗДАНИЕ ТАБЛИЦ
-- ==============================================================================

-- 2.1. Таблица Пользователей (общие данные для студентов и преподавателей)
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- В реальном приложении здесь должен быть хэш, а не plaintext
    full_name VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher') NOT NULL -- Роль пользователя
);

-- 2.2. Таблица Групп
CREATE TABLE Groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(50) UNIQUE NOT NULL
);

-- 2.3. Таблица Дисциплин (Предметов)
CREATE TABLE Subjects (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) UNIQUE NOT NULL
);

-- 2.4. Таблица Преподавателей
CREATE TABLE Teachers (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- 2.5. Таблица Студентов
CREATE TABLE Students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    group_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (group_id) REFERENCES Groups(group_id)
);

-- 2.6. Связь Преподаватель-Дисциплина (какой преподаватель что ведет)
CREATE TABLE TeacherSubjects (
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES Teachers(teacher_id),
    FOREIGN KEY (subject_id) REFERENCES Subjects(subject_id)
);

-- 2.7. Таблица Расписания
CREATE TABLE Schedule (
    lesson_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    group_id INT NOT NULL,
    day_of_week VARCHAR(20) NOT NULL, -- Например: 'Понедельник'
    time_slot VARCHAR(50) NOT NULL,    -- Например: '09:00 - 10:30'
    room VARCHAR(50),
    FOREIGN KEY (subject_id) REFERENCES Subjects(subject_id),
    FOREIGN KEY (teacher_id) REFERENCES Teachers(teacher_id),
    FOREIGN KEY (group_id) REFERENCES Groups(group_id)
);

-- 2.8. Таблица Оценок
CREATE TABLE Grades (
    grade_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    grade_value VARCHAR(50) NOT NULL, -- Например: 'Отлично (5)'
    ects INT NOT NULL,
    semester INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Students(student_id),
    FOREIGN KEY (subject_id) REFERENCES Subjects(subject_id)
);

-- 2.9. Таблица Финансов (для студентов)
CREATE TABLE Finance (
    finance_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT UNIQUE NOT NULL,
    contract_id VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    is_overdue BOOLEAN NOT NULL,
    FOREIGN KEY (student_id) REFERENCES Students(student_id)
);

-- 2.10. Таблица Новостей
CREATE TABLE News (
    news_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content_summary TEXT,
    publish_date DATE NOT NULL
);

-- 2.11. Таблица Сообщений
CREATE TABLE Messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender VARCHAR(100) NOT NULL, -- Отправитель (например, 'Деканат', 'Преподаватель')
    subject VARCHAR(255) NOT NULL,
    preview TEXT,
    message_date DATE NOT NULL
);


-- ==============================================================================
-- 3. ДОБАВЛЕНИЕ ДАННЫХ
-- ==============================================================================

-- 3.1. Данные Пользователей (Users)
INSERT INTO Users (email, password_hash, full_name, role) VALUES
-- Студенты ИБ-101
('student@witte.ru', '123', 'Иванов Студент', 'student'), -- ID 1
('a.ak@mail.ru', '123', 'Аксенов А.И.', 'student'),       -- ID 2
('b.bo@mail.ru', '123', 'Борисов Б.О.', 'student'),       -- ID 3
('v.vol@mail.ru', '123', 'Волкова В.Ю.', 'student'),      -- ID 4
('g.gri@mail.ru', '123', 'Григорьев Г.Н.', 'student'),    -- ID 5
('d.dmi@mail.ru', '123', 'Дмитриева Д.А.', 'student'),    -- ID 6
('e.ego@mail.ru', '123', 'Егоров Е.В.', 'student'),       -- ID 7
('j.zhu@mail.ru', '123', 'Жукова Ж.И.', 'student'),       -- ID 8
('z.zay@mail.ru', '123', 'Зайцев З.О.', 'student'),       -- ID 9
('i.ily@mail.ru', '123', 'Ильина И.К.', 'student'),       -- ID 10
('k.koz@mail.ru', '123', 'Козлов К.Н.', 'student'),       -- ID 11
('l.leb@mail.ru', '123', 'Лебедев Л.С.', 'student'),      -- ID 12
('m.mih@mail.ru', '123', 'Михайлов М.М.', 'student'),     -- ID 13
('n.nik@mail.ru', '123', 'Николаева Н.П.', 'student'),    -- ID 14
('o.orl@mail.ru', '123', 'Орлов О.Т.', 'student'),        -- ID 15
('p.pav@mail.ru', '123', 'Павлова П.Р.', 'student'),      -- ID 16
-- Студенты ЭК-202
('g.gri_ek@mail.ru', '123', 'Григорьев Г.Е.', 'student'), -- ID 17
('s.sid_ek@mail.ru', '123', 'Сидорова С.Л.', 'student'),  -- ID 18
-- Преподаватели
('teacher@witte.ru', '123', 'Петров Преподаватель', 'teacher'), -- ID 19
('kovaleva@witte.ru', '123', 'Ковалева Е.Р.', 'teacher'),       -- ID 20
('pronin@witte.ru', '123', 'Пронин В.О.', 'teacher'),           -- ID 21
('smirnov@witte.ru', '123', 'Смирнов И.А.', 'teacher'),         -- ID 22
('sidorova@witte.ru', '123', 'Сидорова А.П.', 'teacher'),       -- ID 23
('ignatov@witte.ru', '123', 'Игнатов И.И.', 'teacher');         -- ID 24

-- 3.2. Данные Групп (Groups)
INSERT INTO Groups (group_name) VALUES
('ИБ-101'), -- ID 1
('ЭК-202'); -- ID 2

-- 3.3. Данные Дисциплин (Subjects)
INSERT INTO Subjects (subject_name) VALUES
('Программирование'),  -- ID 1
('Базы данных'),       -- ID 2
('Философия'),         -- ID 3
('Теория вероятностей'), -- ID 4
('Английский язык'),   -- ID 5
('Физическая культура'), -- ID 6
('Экономика');         -- ID 7

-- 3.4. Данные Преподавателей (Teachers)
INSERT INTO Teachers (user_id) VALUES
(19), -- Петров Преподаватель (Программирование) - ID 1
(20), -- Ковалева Е.Р. (Базы данных) - ID 2
(21), -- Пронин В.О. (Философия) - ID 3
(22), -- Смирнов И.А. (Теория вероятностей) - ID 4
(23), -- Сидорова А.П. (Английский язык) - ID 5
(24); -- Игнатов И.И. (Физкультура) - ID 6

-- 3.5. Данные Студентов (Students)
-- Группа ИБ-101 (group_id = 1)
INSERT INTO Students (user_id, group_id) VALUES
(1, 1), (2, 1), (3, 1), (4, 1), (5, 1), (6, 1), (7, 1), (8, 1), (9, 1), (10, 1),
(11, 1), (12, 1), (13, 1), (14, 1), (15, 1), (16, 1),
-- Группа ЭК-202 (group_id = 2)
(17, 2), (18, 2);

-- 3.6. Связь Преподаватель-Дисциплина
INSERT INTO TeacherSubjects (teacher_id, subject_id) VALUES
(1, 1), -- Петров -> Программирование
(2, 2), -- Ковалева -> Базы данных
(3, 3), -- Пронин -> Философия
(4, 4), -- Смирнов -> Теория вероятностей
(5, 5), -- Сидорова -> Английский язык
(6, 6), -- Игнатов -> Физическая культура
(1, 7); -- Петров -> Экономика (добавим для примера)

-- 3.7. Данные Расписания (Schedule) для группы ИБ-101 (group_id = 1)
INSERT INTO Schedule (subject_id, teacher_id, group_id, day_of_week, time_slot, room) VALUES
-- Понедельник
(1, 1, 1, 'Понедельник', '09:00 - 10:30', 'А305'), -- Программирование (Лек)
(2, 2, 1, 'Понедельник', '10:40 - 12:10', 'В101'), -- Базы данных (Прак)
-- Вторник
(3, 3, 1, 'Вторник', '13:00 - 14:30', 'А207'),    -- Философия
(5, 5, 1, 'Вторник', '14:40 - 16:10', 'С10'),     -- Английский язык
-- Среда
(4, 4, 1, 'Среда', '09:00 - 10:30', 'А305'),     -- Теория вероятностей
-- Пятница
(6, 6, 1, 'Пятница', '10:40 - 12:10', 'Спорт.зал'); -- Физическая культура


-- 3.8. Данные Оценок (Grades) для основного студента (user_id 1, student_id 1)
INSERT INTO Grades (student_id, subject_id, grade_value, ects, semester) VALUES
(1, 1, 'Отлично (5)', 6, 1), -- Программирование
(1, 2, 'Хорошо (4)', 6, 1),  -- Базы данных
(1, 3, 'Зачет', 3, 1),       -- Философия
(1, 4, 'Удовл. (3)', 6, 1),  -- Теория вероятностей
(1, 5, 'Отлично (5)', 3, 1); -- Английский язык

-- 3.9. Данные Финансов (Finance) для основного студента (user_id 1, student_id 1)
INSERT INTO Finance (student_id, contract_id, total_amount, amount_paid, is_overdue) VALUES
(1, 'C10045', 150000.00, 125000.00, FALSE);

-- 3.10. Данные Новостей (News)
INSERT INTO News (title, content_summary, publish_date) VALUES
('Начало весеннего семестра', 'С 1 февраля начинаются занятия по расписанию для всех курсов.', '2024-02-01'),
('Конференция "Цифровое будущее"', 'Приглашаем студентов и преподавателей принять участие в ежегодной научной конференции.', '2024-02-15');

-- 3.11. Данные Сообщений (Messages)
INSERT INTO Messages (sender, subject, preview, message_date) VALUES
('Деканат', 'О задолженностях по оплате', 'Просим срочно погасить задолженность по контракту до 5 марта.', '2024-02-20'),
('Преподаватель (Петров И.И.)', 'Задание по Программированию', 'Новое практическое задание загружено в систему, дедлайн — следующая неделя.', '2024-02-18');

