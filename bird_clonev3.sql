-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 01:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bird_clone`
--

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE `blocked_users` (
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thought_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `thought_id`, `content`, `created_at`, `parent_id`, `parent_comment_id`, `deleted`) VALUES
(11, 2, 18, 'uhh', '2025-05-13 16:17:21', NULL, NULL, 0),
(12, 2, 18, 'a', '2025-05-13 16:17:25', NULL, NULL, 0),
(18, 1, 18, 'merhaba', '2025-05-15 19:18:55', NULL, 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `followed_id`, `created_at`) VALUES
(1, 2, 1, '2025-05-13 15:57:44'),
(4, 1, 2, '2025-05-15 21:16:59'),
(5, 3, 1, '2025-05-15 21:31:06'),
(7, 1, 3, '2025-05-15 22:16:04');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thought_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `thought_id`, `created_at`) VALUES
(4, 1, 18, '2025-05-13 15:32:14'),
(5, 2, 18, '2025-05-13 16:20:30'),
(6, 1, 21, '2025-05-15 19:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `image_path`, `created_at`, `seen`) VALUES
(1, 1, 2, 'ero', NULL, '2025-05-15 21:23:20', 1),
(2, 1, 2, 'mero', NULL, '2025-05-15 21:23:28', 1),
(3, 1, 2, 'zero', NULL, '2025-05-15 21:24:12', 1),
(4, 1, 2, 'a', NULL, '2025-05-15 21:24:35', 1),
(5, 1, 2, 'b', NULL, '2025-05-15 21:26:59', 1),
(6, 1, 2, 'c', NULL, '2025-05-15 21:27:39', 1),
(7, 1, 2, 'c', NULL, '2025-05-15 21:29:18', 1),
(8, 1, 2, 'd', NULL, '2025-05-15 21:30:17', 1),
(9, 1, 2, 'omgg', NULL, '2025-05-15 21:30:19', 1),
(10, 2, 1, 'hello!!', NULL, '2025-05-15 21:30:29', 1),
(11, 2, 1, '🤔🤔🤔', NULL, '2025-05-15 21:30:32', 1),
(12, 1, 3, 'hello!!', NULL, '2025-05-15 21:31:25', 1),
(13, 3, 1, 'hello :)', NULL, '2025-05-15 21:31:30', 1),
(14, 1, 3, 'ahaha!', NULL, '2025-05-15 21:36:32', 1),
(15, 1, 3, 'heyy', NULL, '2025-05-15 21:37:29', 1),
(16, 1, 3, 'yep!', NULL, '2025-05-15 21:40:10', 1),
(17, 3, 1, 'tmm', NULL, '2025-05-15 21:43:40', 1),
(18, 1, 3, 'oha', NULL, '2025-05-15 21:45:08', 1),
(19, 3, 1, 'ola', NULL, '2025-05-15 21:46:29', 1),
(20, 1, 3, 'slm', NULL, '2025-05-15 22:55:37', 1),
(21, 1, 3, 'a', NULL, '2025-05-15 22:55:40', 1),
(22, 3, 1, 'ola amigo', NULL, '2025-05-15 22:55:57', 1),
(23, 1, 3, 'ok', NULL, '2025-05-15 22:58:12', 1),
(24, 1, 3, 'nice', NULL, '2025-05-15 22:58:17', 1),
(25, 3, 1, 'wtf', NULL, '2025-05-15 22:58:29', 1),
(26, 1, 3, 'you ok', NULL, '2025-05-15 22:59:54', 1),
(27, 3, 1, 'no', NULL, '2025-05-15 23:00:19', 1),
(28, 3, 1, 'are  you sure?', NULL, '2025-05-15 23:00:25', 1),
(29, 1, 3, 'deneme', NULL, '2025-05-15 23:01:30', 1),
(30, 3, 1, 'o', NULL, '2025-05-15 23:01:34', 1),
(31, 3, 1, 'uuu', NULL, '2025-05-15 23:01:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reposts`
--

CREATE TABLE `reposts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thought_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thoughts`
--

CREATE TABLE `thoughts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(280) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `original_thought_id` int(11) DEFAULT NULL,
  `quote_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thoughts`
--

INSERT INTO `thoughts` (`id`, `user_id`, `content`, `created_at`, `original_thought_id`, `quote_id`) VALUES
(18, 1, 'ok!', '2025-05-13 15:28:51', NULL, NULL),
(21, 1, 'yok artık kaç tane', '2025-05-15 10:30:36', NULL, NULL),
(23, 1, 'degwegfwergwerge', '2025-05-15 19:17:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `thought_images`
--

CREATE TABLE `thought_images` (
  `id` int(11) NOT NULL,
  `thought_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `thought_images`
--

INSERT INTO `thought_images` (`id`, `thought_id`, `image_path`) VALUES
(7, 21, '6825c24c22098.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `bio`, `profile_pic`, `created_at`) VALUES
(1, 'eren', 'mehmet@eren.com', '$2y$10$lPqXBVHXhaNTE7ZgKvr.NeS4slv7gn78VIMSCLnmxlddE7vqMpCGa', 'selam!', 'profile_1_1747346199.png', '2025-05-13 14:26:14'),
(2, 'mehmet', 'mehmet@mehmet.com', '$2y$10$eTDVz./OsM7bQ4TjDbRt2.xtWX2WYxxbJF84r9BH2S70tY7tuVxvO', NULL, NULL, '2025-05-13 15:57:23'),
(3, 'berat', '', '$2y$10$6LHKWbdafbsOyQOv/cDH4OotUeKGJu12j/I7mQIF1Ln8I/.cPzaaK', NULL, NULL, '2025-05-15 21:31:00'),
(4, 'berat2', 'berat@berat.com', '$2y$10$xCMoWMpjRPZPHVrGlJlTT.ABEkChr/Yy74W235RfDhtYTH0YIPY.q', NULL, NULL, '2025-05-15 21:50:42'),
(5, 'admin', 'admin@admin.com', '$2y$10$rtpJIZJnZzBHO9aR6/eXaO4H4bhWvSsG4dqJI/n6bb5KSlM5fMDUm', NULL, NULL, '2025-05-15 23:28:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD PRIMARY KEY (`blocker_id`,`blocked_id`),
  ADD KEY `blocked_id` (`blocked_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `thought_id` (`thought_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `follower_id` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`thought_id`),
  ADD KEY `thought_id` (`thought_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `reposts`
--
ALTER TABLE `reposts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`thought_id`),
  ADD KEY `thought_id` (`thought_id`);

--
-- Indexes for table `thoughts`
--
ALTER TABLE `thoughts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `thought_images`
--
ALTER TABLE `thought_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thought_id` (`thought_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `reposts`
--
ALTER TABLE `reposts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `thoughts`
--
ALTER TABLE `thoughts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `thought_images`
--
ALTER TABLE `thought_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD CONSTRAINT `blocked_users_ibfk_1` FOREIGN KEY (`blocker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blocked_users_ibfk_2` FOREIGN KEY (`blocked_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`thought_id`) REFERENCES `thoughts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`thought_id`) REFERENCES `thoughts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reposts`
--
ALTER TABLE `reposts`
  ADD CONSTRAINT `reposts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reposts_ibfk_2` FOREIGN KEY (`thought_id`) REFERENCES `thoughts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `thoughts`
--
ALTER TABLE `thoughts`
  ADD CONSTRAINT `thoughts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `thought_images`
--
ALTER TABLE `thought_images`
  ADD CONSTRAINT `thought_images_ibfk_1` FOREIGN KEY (`thought_id`) REFERENCES `thoughts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
