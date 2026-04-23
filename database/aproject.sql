-- Database bootstrap script.
-- Creates the application schema, tables, indexes, and a small set of seed data for local testing.

CREATE DATABASE IF NOT EXISTS aproject CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aproject;

DROP TABLE IF EXISTS project_logs;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    uid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE projects (
    pid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    short_description TEXT NOT NULL,
    phase ENUM('design', 'development', 'testing', 'deployment', 'complete') NOT NULL,
    uid INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_projects_user
        FOREIGN KEY (uid) REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE project_logs (
    log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pid INT UNSIGNED NULL,
    uid INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_phase VARCHAR(30) DEFAULT NULL,
    new_phase VARCHAR(30) DEFAULT NULL,
    details VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_project
        FOREIGN KEY (pid) REFERENCES projects(pid)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_logs_user
        FOREIGN KEY (uid) REFERENCES users(uid)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_projects_title ON projects(title);
CREATE INDEX idx_projects_start_date ON projects(start_date);
CREATE INDEX idx_projects_uid ON projects(uid);
CREATE INDEX idx_logs_pid ON project_logs(pid);
CREATE INDEX idx_logs_uid ON project_logs(uid);
CREATE INDEX idx_logs_created_at ON project_logs(created_at);

-- Create these hashes fresh if needed in PHP:
-- echo password_hash('Admin1234', PASSWORD_DEFAULT);
-- echo password_hash('User1234', PASSWORD_DEFAULT);

INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$Jj7R4BIF6H3b9At0M8kQeOjk0PYVBfT7X7CT7ExgQ0lB0yKNGg8pC', 'admin@example.com', 'admin'),
('dido123', '$2y$10$8P2lG9D6wH4a4gr4Yz31Feq2tQ2WZ0ztN8B0mQ8ktzwHbi4h6nq0m', 'dido@example.com', 'user');

INSERT INTO projects (title, start_date, end_date, short_description, phase, uid) VALUES
('Website Redesign', '2026-01-10', '2026-05-15', 'A full redesign of the company website to improve usability and accessibility.', 'development', 2),
('Mobile App Testing', '2026-02-01', '2026-04-25', 'Testing the new customer mobile application before release.', 'testing', 1),
('Cloud Migration', '2026-03-01', '2026-09-30', 'Migrating internal systems to cloud infrastructure.', 'design', 2);

INSERT INTO project_logs (pid, uid, action, old_phase, new_phase, details) VALUES
(1, 2, 'created', NULL, 'development', 'Initial project seed'),
(2, 1, 'created', NULL, 'testing', 'Initial project seed'),
(3, 2, 'created', NULL, 'design', 'Initial project seed');