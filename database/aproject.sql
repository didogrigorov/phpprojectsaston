CREATE DATABASE IF NOT EXISTS aproject CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aproject;

DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    uid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
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

CREATE INDEX idx_projects_title ON projects(title);
CREATE INDEX idx_projects_start_date ON projects(start_date);
CREATE INDEX idx_projects_uid ON projects(uid);

-- Passwords below are hashed versions of:
-- alice123 / bob123
INSERT INTO users (username, password, email) VALUES
('alice', '$2y$10$7wL6W0Q7G4nO4Al2A6bqf.sYz6A0C4mYt/9F9bK6WnQ7u4h1OL7Gm', 'alice@example.com'),
('bob',   '$2y$10$u2vKzwD2h7oB8x0wV2M1KuM7HXn7WwR1w8w0nM8Ylz6zPjF6gLtN2', 'bob@example.com');

INSERT INTO projects (title, start_date, end_date, short_description, phase, uid) VALUES
('Website Redesign', '2026-01-10', '2026-05-15', 'A full redesign of the company website to improve usability and accessibility.', 'development', 1),
('Mobile App Testing', '2026-02-01', '2026-04-25', 'Testing the new customer mobile application before release.', 'testing', 2),
('Cloud Migration', '2026-03-01', '2026-09-30', 'Migrating internal systems to cloud infrastructure.', 'design', 1);