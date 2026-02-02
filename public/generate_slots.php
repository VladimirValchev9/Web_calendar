<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die('Нямате достъп');
}

include __DIR__ . '/header.php';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<title>Генератор на слотове</title>
<link rel="stylesheet" href="style.css">
<style>
.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input, .form-group select {
    width: 100%;
    max-width: 300px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

#output {
    background: #f4f4f4;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
    white-space: pre;
    font-family: monospace;
    max-height: 400px;
    overflow-y: auto;
}

.download-btn {
    margin-top: 10px;
}
</style>
</head>
<body>

<div class="container">
    <h1>🕐 Генератор на CSV файл за слотове</h1>
    
    <div class="card">
        <h2>Настройки</h2>
        
        <div class="form-group">
            <label>Начална дата:</label>
            <input type="date" id="startDate" value="2026-02-10">
        </div>
        
        <div class="form-group">
            <label>Крайна дата:</label>
            <input type="date" id="endDate" value="2026-02-12">
        </div>
        
        <div class="form-group">
            <label>Начален час:</label>
            <input type="time" id="startTime" value="09:00">
        </div>
        
        <div class="form-group">
            <label>Краен час:</label>
            <input type="time" id="endTime" value="10:00">
        </div>
        
        <div class="form-group">
            <label>Интервал (минути):</label>
            <select id="interval">
                <option value="5">5 минути</option>
                <option value="6" selected>6 минути</option>
                <option value="10">10 минути</option>
                <option value="15">15 минути</option>
                <option value="20">20 минути</option>
                <option value="30">30 минути</option>
            </select>
        </div>
        
        <button onclick="generateSlots()">Генерирай слотове</button>
    </div>
    
    <div class="card" id="resultCard" style="display:none;">
        <h2>Резултат</h2>
        <p>Генерирани са <strong id="count">0</strong> слота</p>
        <div id="output"></div>
        <button class="download-btn" onclick="downloadCSV()">⬇️ Изтегли CSV файл</button>
    </div>
    
    <br>
    <p style="text-align:center;">
        След като изтеглиш CSV файла, отиди на 
        <a href="import_slots.php"><strong>Импорт слотове</strong></a> 
        за да го качиш в системата.
    </p>
</div>

<script>
let csvContent = '';

function generateSlots() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const interval = parseInt(document.getElementById('interval').value);
    
    const slots = [];
    slots.push('date,time');
    
    const currentDate = new Date(startDate);
    while (currentDate <= endDate) {
        const dateStr = currentDate.toISOString().split('T')[0];
        
        const [startH, startM] = startTime.split(':').map(Number);
        const [endH, endM] = endTime.split(':').map(Number);
        
        let currentMinutes = startH * 60 + startM;
        const endMinutes = endH * 60 + endM;
        
        while (currentMinutes < endMinutes) {
            const hours = Math.floor(currentMinutes / 60);
            const minutes = currentMinutes % 60;
            const timeStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;
            
            slots.push(`${dateStr},${timeStr}`);
            currentMinutes += interval;
        }
        
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    csvContent = slots.join('\n');
    document.getElementById('output').textContent = csvContent;
    document.getElementById('count').textContent = slots.length - 1;
    document.getElementById('resultCard').style.display = 'block';
}

function downloadCSV() {
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'slots.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

</body>
</html>