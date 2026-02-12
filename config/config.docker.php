<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $protocol . '://' . $host . $scriptPath;

return (object)[
    'DB_HOST' => getenv('DB_HOST') ?: 'db',
    'DB_NAME' => getenv('DB_NAME') ?: 'web_calendar',
    'DB_USER' => getenv('DB_USER') ?: 'root',
    'DB_PASS' => getenv('DB_PASS') ?: 'root_password',
    'BASE_URL' => $baseUrl
];