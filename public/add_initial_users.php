<?php
session_start();
require_once __DIR__ . '/../src/User.php';

$user = new User();

$user->createUser('teacher@fmi.bg', 'admin123', 'teacher');

$user->createUser('student1@fmi.bg', 'student123', 'student');
$user->createUser('student2@fmi.bg', 'student123', 'student');

echo "Потребителите са добавени успешно!";