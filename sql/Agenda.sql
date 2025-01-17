-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Jan 17, 2025 at 03:12 PM
-- Server version: 11.5.2-MariaDB-ubu2404
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Agenda`
--

-- --------------------------------------------------------

--
-- Table structure for table `agendas`
--

CREATE TABLE `agendas` (
  `id` int(11) NOT NULL,
  `name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci DEFAULT NULL,
  `default_color` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agendas`
--

INSERT INTO `agendas` (`id`, `name`, `description`, `default_color`) VALUES
(1, 'Jhon Doe', NULL, '#0a58ca'),
(2, 'Doe Jhon', NULL, '#0a58ca'),
(3, 'Showcase', '', '#0a58ca');

-- --------------------------------------------------------

--
-- Table structure for table `agenda_items`
--

CREATE TABLE `agenda_items` (
  `id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp NOT NULL,
  `name` varchar(500) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `color` varchar(500) DEFAULT NULL,
  `agenda_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `data` text DEFAULT NULL,
  `last_access` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(500) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(500) NOT NULL,
  `password` varchar(500) NOT NULL,
  `salt` varchar(20) NOT NULL,
  `profile_picture` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `salt`, `profile_picture`) VALUES
(1, 'Jhon', 'Doe', 'admin@admin.com', '$2y$12$5gc4q7VcxVJVNAi4Pb0pLOV0ruPSr613ipowI2zZdjKWRm3Sxet3i', 'fsp5IKa5g0emc0KVIt1X', 'placeholder.jpg'),
(2, 'Doe', 'Jhon', 'guest@guest.com', '$2y$12$bLuKxhzfEAGLIJ3wZ4s9/O0W7Xtj9yBis1sHT.qRBcHjW6B3lk3Nu', 'iD8iV6AlbBrVwqiMplTH', 'placeholder.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_agenda`
--

CREATE TABLE `user_agenda` (
  `user_id` int(11) NOT NULL,
  `agenda_id` int(11) NOT NULL,
  `personal_agenda` tinyint(4) NOT NULL DEFAULT 0,
  `role` varchar(10) NOT NULL,
  `accepted` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_agenda`
--

INSERT INTO `user_agenda` (`user_id`, `agenda_id`, `personal_agenda`, `role`, `accepted`) VALUES
(1, 1, 1, 'admin', 'accepted'),
(1, 3, 0, 'admin', 'accepted'),
(2, 2, 1, 'admin', 'accepted'),
(2, 3, 0, 'guest', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agenda_items`
--
ALTER TABLE `agenda_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agenda_id` (`agenda_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_agenda`
--
ALTER TABLE `user_agenda`
  ADD PRIMARY KEY (`user_id`,`agenda_id`),
  ADD KEY `agenda_id` (`agenda_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `agenda_items`
--
ALTER TABLE `agenda_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agenda_items`
--
ALTER TABLE `agenda_items`
  ADD CONSTRAINT `agenda_items_ibfk_1` FOREIGN KEY (`agenda_id`) REFERENCES `agendas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_agenda`
--
ALTER TABLE `user_agenda`
  ADD CONSTRAINT `user_agenda_ibfk_1` FOREIGN KEY (`agenda_id`) REFERENCES `agendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_agenda_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;