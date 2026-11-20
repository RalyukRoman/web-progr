-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Час створення: Жов 28 2026 р., 21:32
-- Версія сервера: 10.4.32-MariaDB
-- Версія PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База даних: `lab11`
--

-- --------------------------------------------------------

--
-- Структура таблиці `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` varchar(16) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп даних таблиці `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `content`, `views`) VALUES
('12b3c4d5e6f7a8b', 'Програмування для початківців', 'Головне в цій справі - регулярна практика та розбір чужого коду.', 45),
('16b3c4d4e6f7a8b', 'Новий запис', 'Я створив новий запис', 2),
('8f3b2a1c0d4e5f6a', 'Мій перший запуск PHP', 'Сьогодні я успішно налаштував локальний сервер та запустив скрипти.', 12),
('a1b2c3d4e5f67890', 'Основи баз даних та SQL', 'Розглядаємо роботу індексів, первинних ключів та параметризацію запитів.', 120),
('c9d8e7f6a5b4c3d2', 'Клуб <Код> & \"лапки\" у блозі', 'Тестуємо екранування символів <, >, & та різних видів лапок \'тест\' у тексті.', 0),
('fedcba9876543210', 'Безпека вебзастосунків', 'Завжди фільтруйте введення користувача та екрануйте виведення перед друком в HTML.', 350);

-- --------------------------------------------------------

--
-- Структура таблиці `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_code` varchar(20) NOT NULL,
  `product_name` varchar(80) NOT NULL,
  `product_desc` varchar(500) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп даних таблиці `products`
--

INSERT INTO `products` (`id`, `product_code`, `product_name`, `product_desc`, `price`, `created_at`) VALUES
(1, 'PD1001', 'Зошит', 'Зошит для навчальних нотаток', 25.50, '2026-10-28 19:32:02'),
(2, 'PD1002', 'Папка', 'Папка для матеріалів курсу', 40.00, '2026-10-28 19:32:02'),
(3, 'PD1003', 'Набір ручок', 'Навчальний набір, 3 кольори', 60.25, '2026-10-28 19:32:02');

--
-- Індекси збережених таблиць
--

--
-- Індекси таблиці `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_code` (`product_code`);

--
-- AUTO_INCREMENT для збережених таблиць
--

--
-- AUTO_INCREMENT для таблиці `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
