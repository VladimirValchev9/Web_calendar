DROP DATABASE IF EXISTS web_calendar;
CREATE DATABASE web_calendar;
USE web_calendar;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','teacher') DEFAULT 'student',
    interests JSON DEFAULT '{}'
);

CREATE TABLE slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    time TIME NOT NULL,
    user_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE presentations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    faculty_number VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    category ENUM('frontend','backend','basics','technologies') NOT NULL,
    approved TINYINT(1) DEFAULT 0,
    interests JSON DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(student_id) REFERENCES users(id)
);

CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL UNIQUE,
    category ENUM('frontend','backend','basics','technologies') NOT NULL,
    interests JSON DEFAULT '{}',
    approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO topics (title, category, approved) VALUES
('CSS Basics', 'frontend', 1),
('CSS Object Model', 'frontend', 1),
('JavaScript Fundamentals', 'frontend', 1),
('REST API с PHP', 'backend', 1),
('MVC Architecture', 'backend', 1),
('Docker Fundamentals', 'technologies', 1),
('HTTP & Web Basics', 'basics', 1);

INSERT INTO slots (date, time) VALUES
('2026-01-30','09:00:00'),('2026-01-30','09:06:00'),('2026-01-30','09:12:00'),
('2026-01-30','09:18:00'),('2026-01-30','09:24:00'),('2026-01-30','09:30:00'),
('2026-01-30','09:36:00'),('2026-01-30','09:42:00'),('2026-01-30','09:48:00'),
('2026-01-30','09:54:00'),

('2026-01-31','09:00:00'),('2026-01-31','09:06:00'),('2026-01-31','09:12:00'),
('2026-01-31','09:18:00'),('2026-01-31','09:24:00'),('2026-01-31','09:30:00'),
('2026-01-31','09:36:00'),('2026-01-31','09:42:00'),('2026-01-31','09:48:00'),
('2026-01-31','09:54:00'),

('2026-02-01','09:00:00'),('2026-02-01','09:06:00'),('2026-02-01','09:12:00'),
('2026-02-01','09:18:00'),('2026-02-01','09:24:00'),('2026-02-01','09:30:00'),
('2026-02-01','09:36:00'),('2026-02-01','09:42:00'),('2026-02-01','09:48:00'),
('2026-02-01','09:54:00');

ALTER TABLE presentations
ADD COLUMN user_id INT NULL,
ADD COLUMN interests TEXT;

ALTER TABLE presentations
ADD CONSTRAINT fk_presentations_user
FOREIGN KEY (user_id) REFERENCES users(id)
ON DELETE SET NULL;