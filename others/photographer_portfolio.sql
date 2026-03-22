-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Мар 19 2026 г., 14:11
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

CREATE DATABASE IF NOT EXISTS photographer_portfolio;
USE photographer_portfolio;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `photographer_portfolio`
--

-- --------------------------------------------------------

--
-- Структура таблицы `album`
--

CREATE TABLE `album` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `album`
--

INSERT INTO `album` (`id`, `title`, `description`, `created_at`, `created_by`) VALUES
(2, 'Семейный портрет', 'Теплые семейные моменты', '2026-03-19 15:31:02', 1),
(3, 'Портретная съемка', 'Индивидуальные портреты', '2026-03-19 15:31:02', 1),
(4, 'Детская съемка', 'Яркие и веселые дети', '2026-03-19 15:31:02', 1),
(5, 'Love Story', 'Романтические истории любви', '2026-03-19 15:31:02', 1),
(6, 'Студийный портрет', 'Профессиональные студийные фото', '2026-03-19 15:31:02', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `album_photo`
--

CREATE TABLE `album_photo` (
  `album_id` int(11) NOT NULL,
  `photo_id` int(11) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `album_photo`
--

INSERT INTO `album_photo` (`album_id`, `photo_id`, `sort_order`, `added_at`) VALUES
(2, 2, 0, '2026-03-19 15:31:02'),
(3, 3, 0, '2026-03-19 15:31:02'),
(4, 4, 0, '2026-03-19 15:31:02'),
(5, 5, 0, '2026-03-19 15:31:02'),
(6, 6, 0, '2026-03-19 15:31:02');

-- --------------------------------------------------------

--
-- Структура таблицы `contact_request`
--

CREATE TABLE `contact_request` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_processed` tinyint(1) DEFAULT 0,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `photo`
--

CREATE TABLE `photo` (
  `id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `photo`
--

INSERT INTO `photo` (`id`, `file_path`, `title`, `uploaded_at`, `uploaded_by`) VALUES
(1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQau-wfWLn0wfxaMpChW3S-izGypYg9z-r5VA&s', 'Свадьба', '2026-03-19 15:31:02', 1),
(2, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4q1c5NFCaS9XdEuHqHhUiB8OijcO44UQhSg&s', 'Семья на природе', '2026-03-19 15:31:02', 1),
(3, 'https://fullmedia.ru/500/uploaded/20092b46/5cbb7131/d56f32ed/7b2f622b.jpg', 'Портрет девушки', '2026-03-19 15:31:02', 1),
(4, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMI-rR6lJHJlUw8Z422aOU68lXzynYmTfByg&s', 'Детская съемка', '2026-03-19 15:31:02', 1),
(5, 'https://1gai.ru/uploads/posts/2025-12/1766479405_prompty-dlja-chatgpt-dlja-professionalnyh-foto-1gai.jpg', 'Love Story', '2026-03-19 15:31:02', 1),
(6, 'https://dailystudio.ru/wp-content/uploads/2019/01/434.jpg', 'Студийный портрет', '2026-03-19 15:31:02', 1),
(7, 'uploads/albums/1773923784_af0aa3635e3cca6290260e64be438c17.jpg', '', '2026-03-19 15:36:24', 1),
(8, 'uploads/albums/1773923789_c65692cf6fde1122db1886d4bdc4cf9e.jpg', '', '2026-03-19 15:36:29', 1),
(9, 'uploads/albums/1773923792_0c5fd217d5d0e4ad8a675aa50e1de793.jpg', '', '2026-03-19 15:36:32', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `nickname` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0,
  `moderated_by` int(11) DEFAULT NULL,
  `moderated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `review`
--

INSERT INTO `review` (`id`, `nickname`, `content`, `created_at`, `is_approved`, `moderated_by`, `moderated_at`) VALUES
(1, 'Анна Петрова', 'Иванна — настоящий профессионал! С ней очень легко и комфортно работать. Фотографии получились просто волшебные!', '2026-03-19 15:31:02', 1, NULL, NULL),
(2, 'Сергей и Мария', 'Спасибо Иванне за потрясающие свадебные фото! Она поймала все самые важные моменты дня. Очень рекомендую!', '2026-03-19 15:31:02', 1, NULL, NULL),
(3, 'Ольга Смирнова', 'Заказывали семейную фотосессию. Дети обычно не любят фотографироваться, но Иванна нашла к ним подход. Результат превзошел ожидания!', '2026-03-19 15:31:02', 1, NULL, NULL),
(4, 'cat', 'i love cats fr', '2026-03-19 15:38:26', 1, 1, '2026-03-19 16:02:14');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `username`, `password_hash`, `email`) VALUES
(1, 'admin', '$2y$10$/C5MNlGNohEyzobjKrdj/O3/eYN7PoMjjq9Qpz9CqfwJtfNhic2s.', 'admin@example.com'),
(2, 'helper', '$2y$10$u3c7I43gtTRGhkdDk.N8JesF4imwzEaETOAiV7jm3hDhOi.jNjhIK', 'helper@example.com');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Индексы таблицы `album_photo`
--
ALTER TABLE `album_photo`
  ADD PRIMARY KEY (`album_id`,`photo_id`),
  ADD KEY `photo_id` (`photo_id`);

--
-- Индексы таблицы `contact_request`
--
ALTER TABLE `contact_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Индексы таблицы `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Индексы таблицы `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moderated_by` (`moderated_by`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `album`
--
ALTER TABLE `album`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `contact_request`
--
ALTER TABLE `contact_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `photo`
--
ALTER TABLE `photo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `album`
--
ALTER TABLE `album`
  ADD CONSTRAINT `album_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `album_photo`
--
ALTER TABLE `album_photo`
  ADD CONSTRAINT `album_photo_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `album_photo_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photo` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `contact_request`
--
ALTER TABLE `contact_request`
  ADD CONSTRAINT `contact_request_ibfk_1` FOREIGN KEY (`processed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `photo`
--
ALTER TABLE `photo`
  ADD CONSTRAINT `photo_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`moderated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
