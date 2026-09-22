/* 
 * ダッシュボードの作成
 */

/*
 * 月別選択用のHTMLの要素を取得
 */
const prevMonthButton = document.getElementById("prevMonthButton");
const nextMonthButton = document.getElementById("nextMonthButton");
const selectedMonth = document.getElementById("selectedMonth");


let currentDate = new Date();

/**
 * 現在の月を表示する関数
 */
function displaySelectedMonth() {

    const year = currentDate.getFullYear();

    const month = currentDate.getMonth() + 1;

    selectedMonth.textContent = `${year}年${month}月`;
}

displaySelectedMonth();

// 戻るボタン押したとき
prevMonthButton.addEventListener('click', () => {

    currentDate.setMonth(
        currentDate.getMonth() - 1
    );

    displaySelectedMonth();

    // 変更後の月でAPIを再取得
    getAnalysisData();
});

nextMonthButton.addEventListener('click', () => {

    currentDate.setMonth(
        currentDate.getMonth() + 1
    );

    displaySelectedMonth();
    
    // 変更後の月でAPIを再取得
    getAnalysisData();
})

/* 
 * グラフの作成
 */
const ctxPayKind = document.getElementById("payKindChart");
const ctxHourlyCounts = document.getElementById("hourlyCountsChart");
const ctxGokeiCounts = document.getElementById("gokeiCountsChart");

let payKindChart = null;
let hourlyCountsChart = null;
let gokeiCountsChart = null;

async function getAnalysisData() {
    
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth() + 1;

    const response = await fetch(`./api/analysis_api.php?Year=${year}&Month=${month}`);

    console.log(year, month);

    const data = await response.json();

    // 決済手段別データだけを取り出す
    const payKindData = data.PayKind;

    // 時間帯別件数のデータを取り出す
    const hourlyCountsData = data.Hourly;

    // 金額別の件数データを取得
    const gokeiCountsData = data.Gokei;

    // Chart.js用の配列を作る
    const labelsPayKind = payKindData.map(item => item.PayKind);
    const countsPayKind = payKindData.map(item => item.count);

    const labelsHourlyCounts = hourlyCountsData.map(item => `${item.hour}時`);
    const countsHourlyCounts = hourlyCountsData.map(item => item.count);

    const labelsGokeiCounts = gokeiCountsData.map(item => item.GokeiRange);
    const countsGokeiCounts = gokeiCountsData.map(item => item.count);

    // 決済手段別のグラフを作成
    if (payKindChart !== null) {
        payKindChart.destroy();
    }

    payKindChart = new Chart(ctxPayKind, {
        type: 'doughnut',

        data: {
            labels: labelsPayKind,

            datasets: [{
                label: '件数',
                data: countsPayKind
            }]
        },

        options: {
            plugins: {
                title: {
                    display: true,
                    text: '決済手段割合'
                }
            }
        }
    });

    // 時間帯別件数のグラフを作成
    if (hourlyCountsChart !== null) {
        hourlyCountsChart.destroy();
    }

    hourlyCountsChart = new Chart(ctxHourlyCounts, {
        type: 'bar',

        data: {
            labels: labelsHourlyCounts,

            datasets: [{
                label: '件数',
                data: countsHourlyCounts
            }]
        },

        options: {
            plugins: {
                title: {
                    display: true,
                    text: '時間帯別件数'
                }
            }
        }
    });

    //金額別のグラフを作成
    if (gokeiCountsChart !== null) {
        gokeiCountsChart.destroy();
    }

    gokeiCountsChart = new Chart(ctxGokeiCounts, {
        type: 'bar',

        data: {
            labels: labelsGokeiCounts,

            datasets: [{
                label: '件数',
                data: countsGokeiCounts
            }]
        },

        options: {
            plugins: {
                title:{
                    display: true,
                    text: '金額別件数'
                }
            }
        }
    })
}

getAnalysisData();
