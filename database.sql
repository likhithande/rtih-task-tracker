-- ============================================================
-- RTIH IT Internship Technical Assignment
-- Database Schema for Task Tracker Web Application
-- Database Name: rtih_task_tracker
-- ============================================================

-- Create database if it does not already exist
CREATE DATABASE IF NOT EXISTS `rtih_task_tracker`
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `rtih_task_tracker`;

-- ------------------------------------------------------------
-- Table: users
-- Stores registered user accounts with securely hashed passwords
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: tasks
-- Stores task records assigned to individual users
-- Foreign key ensures referential integrity to `users` table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tasks_user` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note:
-- Passwords must always be hashed with PHP password_hash() before inserting.
-- Users can register directly through the application's registration page.

