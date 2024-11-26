-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Nov 26, 2024 at 02:13 AM
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
-- Database: `youth_ministry_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `name` varchar(101) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','youth') DEFAULT 'youth',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `name`, `email`, `phone`, `password`, `profile_picture`, `role`, `created_at`) VALUES
(1, 'Kimeu', 'Admin', 'Kimeu Admin', 'admin@example.com', '1234567890', '$2y$10$Xo0L7ZDCrK2tfJydXHJWWe7t1TPHrlbQbi5iuf5nZKQl4rN/6oTEe', 'masila.jpg', 'admin', '2024-10-13 03:00:40'),
(3, 'kimeu', 'dan', 'Kimeu dan', 'admin@mail.com', '0788888999', '$2y$10$EzbYd7DuAgdVmSEP8sF7AeQwSElqcfz5ubQfN0FfvWEe2eKhZvXAO', 'retreat.jpg', 'admin', '2024-10-13 03:14:13'),
(7, 'Juma', 'Kioko', 'Juma Kioko', 'juma@mail.com', '0788888777', '$2y$10$4o4jgJ4xesDVJGFwP0DNyOw0xitgJfv/L0O/uEKhvhrumcHzcLrn2', 'assets/images/profiles/672591d713d10.jpg', 'youth', '2024-11-02 02:43:35'),
(9, 'kilonzo', 'Masila', 'kilo Masila', 'kilo@mail.com', '0788999000', '$2y$10$h/1e70RfhL59RHrAYZVlPOZV9yppqBL0CW90ErLi.j8HgdpqfvLxy', 'assets/images/profiles/672cc294f1c64.jpg', 'youth', '2024-11-02 04:06:57'),
(10, 'Daniel', 'Kithokoi', 'Daniel Kithokoi', 'dan@mail.com', '0789989734', '$2y$10$9RYjD5TW6xnat4RpOYf4uOnB49MqWjenn9mzPQEp4inZyAiJaLRju', 'assets/images/profiles/67264fb9641fe.jpg', 'youth', '2024-11-02 16:13:45'),
(11, 'John', 'Doe', 'John Doe', 'john@mail.com', '0788999887', '$2y$10$rY6V2p.3zNN6NqfXTvlY9.QGFAEzgeLqEL48KPqiWATduFS70sZi2', 'assets/images/profiles/672659e300eca.jpg', 'youth', '2024-11-02 16:57:07'),
(13, 'Kennedy', 'Juma', 'Kennedy Juma', 'ken@mail.com', '0897786767', '$2y$10$b3V6IGhTCDjzOHvdhdrrqeZdsN.DSze.gRfgZipuXuUb5Ii/tNa.O', 'assets/images/profiles/672b848dc2d33.png', 'youth', '2024-11-06 15:00:29'),
(14, 'Kimeu', 'Ludwin', 'Kimeu Ludwin', 'ludwin@mail.com', '0123456778', '$2y$10$Sqml0aI/eoMAUeVbQx7pTeeu024cQk1g3mAIZSP.E9JXVzNK17e4S', 'assets/images/profiles/672cc6eacce24.png', 'youth', '2024-11-07 13:53:25'),
(15, 'Jacob', 'Mutuse', 'Jacob Mutuse', 'mutuse@mail.com', '0123456799', '$2y$10$bHxzdba0fpsI8cLSbse9G.2GFaPgBWA/SPJ1lyrTJW2n9TSW.HNma', 'assets/images/profiles/672dec2609a45.png', 'youth', '2024-11-08 10:44:50'),
(16, 'Noah', 'Lyles', 'Noah Lyles', 'noah@mail.com', '0190899787', '$2y$10$mba92tFLgOOywL.a0MVjDOViK1WNlJh9tkJox5cgB2HNepJ7.FYE.', '../assets/images/profiles/672dee4419904.png', 'youth', '2024-11-08 10:56:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
