SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- 'admin' tablosunu oluşturma
CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 'admin' tablosuna veri ekleme
INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) 
VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '2025-01-11 20:23:34');

-- 'appointments' tablosunu oluşturma
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT 'confirmed',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `cancel_code` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
