<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>会計分析</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="../js/analysis.js" defer></script>
    </head>
    <body>
        <h1>月別分析ダッシュボード</h1>
        <div class="month-selector">
            <button id="prevMonthButton"></button>
            <span id="selectedMonth"></span>
            <button id="nextMonthButton"></button>
        </div>
        <div style="width: 400px;">
            <canvas id="payKindChart"></canvas>
        </div>
        <div style="width: 400px;">
            <canvas id="hourlyCountsChart"></canvas>
        </div>
        <div style="width: 400px;">
            <canvas id="gokeiCountsChart"></canvas>
        </div>
    </body>
</html>