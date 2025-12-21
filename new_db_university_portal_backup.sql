/*
SQLyog Trial v13.1.9 (64 bit)
MySQL - 10.4.32-MariaDB : Database - university_portal
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`university_portal` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;

USE `university_portal`;

/*Table structure for table `action_logs` */

DROP TABLE IF EXISTS `action_logs`;

CREATE TABLE `action_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `action_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `action_logs` */

insert  into `action_logs`(`id`,`user_id`,`action`,`action_time`) values 
(1,NULL,'Администратор вошел в систему','2025-12-21 12:16:45'),
(2,NULL,'Создана новая учебная группа ПИЭ-211','2025-12-21 12:16:45'),
(3,8,'Техническая поддержка обновила скрипты API','2025-12-21 12:16:45'),
(4,2,'Преподаватель Смирнов выставил оценки группе 1','2025-12-21 12:16:45'),
(5,3,'Преподаватель Иванова обновила расписание','2025-12-21 12:16:45'),
(6,NULL,'Удален тестовый пользователь ID 99','2025-12-21 12:16:45'),
(7,7,'Регистратор выгрузил отчет по успеваемости','2025-12-21 12:16:45'),
(8,NULL,'Изменены права доступа для роли Guest','2025-12-21 12:16:45'),
(9,2,'Добавлена новая дисциплина: Веб-разработка','2025-12-21 12:16:45'),
(10,NULL,'Резервное копирование базы данных завершено','2025-12-21 12:16:45'),
(11,11,'Успешный вход в систему','2025-12-21 13:06:06'),
(12,11,'Выход из системы','2025-12-21 13:07:44'),
(13,12,'Успешный вход в систему','2025-12-21 13:08:27'),
(14,12,'Выход из системы','2025-12-21 13:08:35'),
(15,13,'Успешный вход в систему','2025-12-21 13:09:00'),
(16,13,'Выход из системы','2025-12-21 13:09:08'),
(17,11,'Успешный вход в систему','2025-12-21 13:09:19'),
(18,11,'Удален пользователь: admin_test','2025-12-21 13:10:10'),
(19,11,'Обновлены системные настройки. Режим обслуживания: ВЫКЛ','2025-12-21 13:11:09'),
(20,11,'Выход из системы','2025-12-21 13:16:06'),
(21,12,'Успешный вход в систему','2025-12-21 13:16:17'),
(22,12,'Выход из системы','2025-12-21 13:26:42'),
(23,13,'Успешный вход в систему','2025-12-21 13:26:53'),
(24,13,'Выход из системы','2025-12-21 13:32:51'),
(25,13,'Успешный вход в систему','2025-12-21 13:33:02'),
(26,13,'Выход из системы','2025-12-21 13:34:08'),
(27,13,'Успешный вход в систему','2025-12-21 13:35:00'),
(28,13,'Выход из системы','2025-12-21 13:35:06'),
(29,12,'Успешный вход в систему','2025-12-21 13:35:14'),
(30,12,'Преподаватель изменил оценку студенту (ID: 1, Предмет ID: 2)','2025-12-21 13:38:01'),
(31,12,'Преподаватель изменил оценку студенту (ID: 2, Предмет ID: 2)','2025-12-21 13:38:08'),
(32,12,'Преподаватель изменил оценку студенту (ID: 1, Предмет ID: 2)','2025-12-21 13:38:12'),
(33,12,'Выход из системы','2025-12-21 13:38:21');

/*Table structure for table `classrooms` */

DROP TABLE IF EXISTS `classrooms`;

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(10) NOT NULL,
  `capacity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `classrooms` */

insert  into `classrooms`(`id`,`room_number`,`capacity`) values 
(1,'315',30),
(2,'402',25),
(3,'101',50),
(4,'205',20),
(5,'512',40),
(6,'Лекц-1',100),
(7,'303',15),
(8,'404',30),
(9,'210',25),
(10,'115',35);

/*Table structure for table `disciplines` */

DROP TABLE IF EXISTS `disciplines`;

CREATE TABLE `disciplines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_name` (`subject_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `disciplines` */

insert  into `disciplines`(`id`,`subject_name`) values 
(6,'Архитектура ЭВМ'),
(2,'Базы данных'),
(3,'Веб-разработка'),
(4,'Высшая математика'),
(7,'Дискретная математика'),
(8,'Иностранный язык'),
(1,'Информационные системы'),
(5,'Операционные системы'),
(10,'Правоведение'),
(9,'Экономическая теория');

/*Table structure for table `grades` */

DROP TABLE IF EXISTS `grades`;

CREATE TABLE `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `discipline_id` int(11) NOT NULL,
  `grade` varchar(20) NOT NULL,
  `date_given` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `discipline_id` (`discipline_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `grades` */

insert  into `grades`(`id`,`student_id`,`discipline_id`,`grade`,`date_given`) values 
(1,1,1,'Отлично','2025-12-01'),
(2,1,2,'Зачтено','2025-12-21'),
(3,2,1,'Удовлетворительно','2025-12-01'),
(4,3,4,'Отлично','2025-12-10'),
(5,4,9,'Зачтено','2025-12-15'),
(6,5,3,'Хорошо','2025-12-18'),
(7,6,5,'Отлично','2025-12-20'),
(8,7,6,'Удовлетворительно','2025-12-21'),
(9,8,10,'Отлично','2025-12-21'),
(10,9,8,'Зачтено','2025-12-21'),
(11,2,2,'Зачтено','2025-12-21');

/*Table structure for table `groups` */

DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `groups` */

insert  into `groups`(`id`,`group_name`) values 
(8,'ДИЗ-105'),
(3,'ИВТ-101'),
(4,'ИВТ-102'),
(10,'ЛИН-101'),
(7,'МЕН-201'),
(1,'ПИЭ-211'),
(2,'ПИЭ-212'),
(9,'ПСИ-302'),
(5,'ЭК-301'),
(6,'ЮР-401');

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `roles` */

insert  into `roles`(`id`,`role_name`) values 
(1,'Admin'),
(6,'Dean'),
(5,'Department Head'),
(10,'Guest'),
(8,'IT Support'),
(9,'Librarian'),
(7,'Methodologist'),
(4,'Registrar'),
(3,'Student'),
(2,'Teacher');

/*Table structure for table `schedule` */

DROP TABLE IF EXISTS `schedule`;

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `discipline_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `day_of_week` enum('ПН','ВТ','СР','ЧТ','ПТ','СБ') NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `discipline_id` (`discipline_id`),
  KEY `classroom_id` (`classroom_id`),
  CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`),
  CONSTRAINT `schedule_ibfk_4` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `schedule` */

insert  into `schedule`(`id`,`group_id`,`teacher_id`,`discipline_id`,`classroom_id`,`day_of_week`,`time_start`,`time_end`) values 
(1,1,1,1,1,'ПН','09:00:00','10:30:00'),
(2,1,2,2,1,'ПН','10:40:00','12:10:00'),
(3,2,2,4,3,'ВТ','09:00:00','10:30:00'),
(4,3,3,9,5,'СР','12:20:00','13:50:00'),
(5,1,4,3,2,'ЧТ','14:00:00','15:30:00'),
(6,4,5,5,4,'ПТ','09:00:00','10:30:00'),
(7,5,6,6,6,'СБ','10:40:00','12:10:00'),
(8,6,7,10,8,'ПН','14:00:00','15:30:00'),
(9,7,8,8,10,'ВТ','12:20:00','13:50:00'),
(10,8,9,7,7,'СР','15:40:00','17:10:00');

/*Table structure for table `students` */

DROP TABLE IF EXISTS `students`;

CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `students` */

insert  into `students`(`id`,`user_id`,`group_id`,`full_name`) values 
(1,13,1,'Коньюкова Дарья'),
(2,5,1,'Сидоров Виталий Александрович'),
(3,6,2,'Кузнецов Алексей Николаевич'),
(4,10,3,'Соколов Дмитрий Павлович'),
(5,4,3,'Козлов Максим Викторович'),
(6,5,4,'Новиков Андрей Сергеевич'),
(7,6,5,'Лебедев Егор Дмитриевич'),
(8,10,6,'Павлов Артем Григорьевич'),
(9,4,7,'Морозов Илья Владимирович'),
(10,5,8,'Степанов Олег Юрьевич');

/*Table structure for table `teachers` */

DROP TABLE IF EXISTS `teachers`;

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `teachers` */

insert  into `teachers`(`id`,`user_id`,`full_name`,`department`) values 
(1,2,'Смирнов Тимур Олегович','Кафедра ИТ'),
(2,12,'Иванова Прокофья Петровна','Кафедра Высшей математики'),
(3,9,'Морозова Марина Егоровна','Кафедра Экономики'),
(4,2,'Васильев Игорь Борисович','Кафедра Программной инженерии'),
(5,3,'Зайцев Геннадий Маркович','Кафедра Физики'),
(6,9,'Федоров Станислав Львович','Кафедра Философии'),
(7,2,'Михайлов Владимир Константинович','Кафедра Иностранных языков'),
(8,3,'Романов Эдуард Артурович','Кафедра Правоведения'),
(9,9,'Беляев Александр Михайлович','Кафедра Маркетинга'),
(10,2,'Тихонов Юрий Всеволодович','Кафедра Безопасности');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`username`,`password`,`role_id`,`created_at`) values 
(2,'smirnov_t','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',2,'2025-12-21 12:16:45'),
(3,'ivanova_prof','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',2,'2025-12-21 12:16:45'),
(4,'petrov_s','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,'2025-12-21 12:16:45'),
(5,'sidorov_v','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,'2025-12-21 12:16:45'),
(6,'kuznetsov_a','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,'2025-12-21 12:16:45'),
(7,'volkov_reg','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',4,'2025-12-21 12:16:45'),
(8,'popov_it','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',8,'2025-12-21 12:16:45'),
(9,'morozova_m','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',2,'2025-12-21 12:16:45'),
(10,'sokolov_d','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,'2025-12-21 12:16:45'),
(11,'admin_portal','$2y$10$w59DqLHVZ5vmMMTRRN2Pxeg42oiscHUOgv9SXbYg5fXV21H38CqQW',1,'2025-12-21 13:05:25'),
(12,'ivanov_it','$2y$10$C3aPgCsmvAfRK3QMHw7w0uGoDiNKysuA2K0MpHjTK2mEMzShRo01e',2,'2025-12-21 13:08:16'),
(13,'konkova_d','$2y$10$acMYizUq0ncobsEyydWp0ecQPnXUAQpJvLeo8NzYpJJZt0gC5O.Cu',3,'2025-12-21 13:08:51');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
