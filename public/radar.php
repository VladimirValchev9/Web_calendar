<?php
session_start();
require_once __DIR__ . '/../src/Presentation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$presentation = new Presentation();
$presentation->cleanupCancelled();
$approved = $presentation->getApprovedForRadar();

$categories = ['frontend', 'backend', 'basics', 'technologies'];
$category_counts = array_fill_keys($categories, 0);

foreach ($approved as $p) {
    if (in_array($p['category'], $categories)) {
        $category_counts[$p['category']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Радар на презентациите</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<h1>📊 Радар на всички одобрени презентации</h1>

<?php if (array_sum($category_counts) === 0): ?>
    <p>Все още няма одобрени презентации, за да се покаже радар.</p>
<?php else: ?>
    <canvas id="radarChart" width="400" height="400"></canvas>

    <script>
        const ctx = document.getElementById('radarChart').getContext('2d');
        const radarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    label: 'Одобрени презентации',
                    data: <?= json_encode(array_values($category_counts)) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                }]
            },
            options: {
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    </script>
<?php endif; ?>

</body>
</html>