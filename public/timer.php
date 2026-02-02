<!DOCTYPE html>
<html lang="bg">
<head>
<meta charset="UTF-8">
<title>Таймер за презентация</title>

<style>
body {
    font-family: Arial, sans-serif;
    text-align: center;
    margin-top: 60px;
}

#timer {
    font-size: 64px;
    font-weight: bold;
    margin: 20px 0;
}

.warning {
    color: orange;
}

.danger {
    color: red;
}

button {
    font-size: 16px;
    padding: 10px 20px;
    margin: 5px;
}

.back-button {
    background: #2980b9;
    color: white;
    border: none;
    border-radius: 4px;
}

.back-button:hover {
    background: #1f6391;
}
</style>
</head>
<body>

<h1>⏱ Таймер за презентация (6 минути)</h1>

<div id="timer">06:00</div>

<button onclick="start()">Старт</button>
<button onclick="stop()">Стоп</button>
<button onclick="reset()">Рестарт</button>

<br><br>

<button class="back-button" onclick="window.location.href='calendar.php'">← Назад към календара</button>

<script>
let totalSeconds = 6 * 60;
let remaining = totalSeconds;
let interval = null;

function updateDisplay() {
    let m = String(Math.floor(remaining / 60)).padStart(2, '0');
    let s = String(remaining % 60).padStart(2, '0');
    const timerEl = document.getElementById('timer');

    timerEl.textContent = `${m}:${s}`;

    timerEl.classList.remove('warning', 'danger');

    if (remaining <= 60 && remaining > 0) {
        timerEl.classList.add('warning');
    }

    if (remaining === 0) {
        timerEl.classList.add('danger');
        timerEl.textContent = "Времето изтече!";
    }
}

function start() {
    if (interval) return;

    interval = setInterval(() => {
        if (remaining > 0) {
            remaining--;
            updateDisplay();
        } else {
            clearInterval(interval);
            interval = null;
        }
    }, 1000);
}

function stop() {
    clearInterval(interval);
    interval = null;
}

function reset() {
    stop();
    remaining = totalSeconds;
    updateDisplay();
}

updateDisplay();
</script>

</body>
</html>