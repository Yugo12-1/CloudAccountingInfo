<?php

// 型チェックを厳格（Strict Mode）にする」ための宣言
declare(strict_types=1);

date_default_timezone_set('Asia/Tokyo');

/*
 * 医療機関別のlogin.phpから呼び出されたことを確認する
 */

if (!isset($hospitalKey) || !is_string($hospitalKey)) {
    throw new RuntimeException(
        '医療機関キーが指定されていません'
    );
}

/*
 * セッション開始
 */
session_start();

/*
 * キャッシュの保持禁止する
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/*
 * scriptディレクトリの絶対パスと必要なクラスの読み込み
 */
$scriptDir = dirname(__DIR__);

require_once $scriptDir . '/Support/HospitalResolver.php';
require_once $scriptDir . '/Support/LogManager.php';
require_once $scriptDir . '/Authentication/AuthenticationManager.php';
require_once $scriptDir . '/Repository/AccountingRepository.php';
require_once $scriptDir . '/Service/AccountingService.php';
require_once $scriptDir . '/Controller/AccountingController.php';

/*
 * hospitalKeyからHospitalContextを生成
 */
$configDirectory = $scriptDir . '/Config/Hospitals';

$hospitalResolver = new HospitalResolver($configDirectory);

$hospitalContext = $hospitalResolver->gfResolveKeyToContext($hospitalKey);

/*
 * 必要なクラスの作成
 */
$logRootDir = $scriptDir . '/Logs';

$logger = new LogManager($logRootDir);

$dbConnect = new DBConnection($hospitalContext);

$authenticator = new AuthenticationManager($logger);

$accountingRepository = new AccountingRepository(
    $logger,
    $dbConnect,
    $hospitalContext->gfGetHospitalID()
);

$service = new AccountingService(
    $logger,
    $authenticator,
    $accountingRepository,
    $hospitalContext->gfGetHospitalID()
);

$controller = new AccountingController($service);

/*
 * ログイン済みかどうかをコントローラーに依頼
 */
$isLogin = $controller->gfIsLogin();

if (!$isLogin) {
    $controller->gfGoToLoginPage();
}

/*
 * ログインした医療機関と現在のURLが異なる場合
 */
if ($authenticator->gfGetHospitalID()
    !== $hospitalContext->gfGetHospitalID()
) {
    header('Location: login.php');
    exit();
}


/*
 * 検索のための条件を準備する
 */

// 選択条件の連想配列で渡す
$conditions = [];

/*
 * ここから検索条件の各項目の設定
 */

// 日別・月別の検索モードを変化される
$mode = $_GET['mode'] ?? 'day';
$targetDate = $_GET['date'] ?? date('Y-m-d');

$isValidTargetDate = false;

// ターゲット日のバリデーションチェック
if (!empty($targetDate)) {

    $d = DateTime::createFromFormat('Y-m-d', $targetDate);

    if ($d && $d->format('Y-m-d') === $targetDate) {
        $isValidTargetDate = true;
    }
}

// 不正な値なら今日日付に強制変更
if (!$isValidTargetDate) {
    $targetDate = date('Y-m-d');
}


// 基準日をずらすことで前日・前月、翌日・次月のデータを表示す
$nav = $_GET['nav'] ?? '';

if ($nav === 'prev') {
    // 前日もしくは前月に$targetDateをずらせばいい？
    if ($mode === 'day') {
        $targetDate = date(
            'Y-m-d',
            strtotime($targetDate . ' -1 day')
        );

    } elseif ($mode === 'month') {
        $targetDate = date(
            'Y-m-01',
            strtotime($targetDate)
        );

        $targetDate = date(
            'Y-m-d',
            strtotime($targetDate . ' -1 month')
        );
    }

} elseif ($nav === 'next') {
    if ($mode === 'day') {
        $targetDate = date(
            'Y-m-d',
            strtotime($targetDate . ' +1 day')
        );

    } elseif ($mode === 'month') {
        $targetDate = date(
            'Y-m-01',
            strtotime($targetDate)
        );

        $targetDate = date(
            'Y-m-d',
            strtotime($targetDate . ' +1 month')
        );
    }
}


/*
 * モードに応じた、開始日と終了日を作る
 */
if ($mode === 'day') {

    $startDate = $targetDate;
    $endDate = $targetDate;

} elseif ($mode === 'month') {

    $startDate = date(
        'Y-m-01',
        strtotime($targetDate)
    );

    $endDate = date(
        'Y-m-t',
        strtotime($targetDate)
    );

} else {
    // 不正なmodeを直接入力されたケースは日別に戻す
    $mode = 'day';
    
    $startDate = $targetDate;
    $endDate = $targetDate;
}


// 表示用
if ($mode === 'day') {
    $displayDate = date(
        'Y/m/d',
        strtotime($targetDate)
    );
} else {
    $displayDate = date(
        'Y年m月',
        strtotime($targetDate)
    );
}

// 決済種別
$selectedPayKind = $_GET['payKind'] ?? '';

// 入外区分
$selectedNyugai = $_GET['nyugai'] ?? '';


// 検索条件の格納 (レポジトリのキーに合わせる)
$conditions = [
    'StartDate' => $startDate,
    'EndDate'   => $endDate,
    'PayKind'   => $selectedPayKind,
    'Nyugai'    => $selectedNyugai
];

/*
 * 検索に合致するデータを取得（連想配列(1件データ)の配列）
 */
$accountings = $controller->gfShowAccountingData($conditions);

$accountingCount = count($accountings);

// 合計額の変数を定義しておく
$totalGokei = 0;
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width" />
        <title>会計情報一覧</title>
        <link rel="stylesheet" href="../css/accounting.css">
    </head>
    <body>
        <header class="site-header">
            <div class="header-content">
                <img src="../img/medifo_logo1.png" alt="製品ロゴ">
            </div>
        </header>
        <main class="accounting-area">
            <div class="accounting-content">
                <div class="accounting-header">
                    <h1>会計情報一覧ページ</h1>
                    <p class="hospital-name">
                        <?php echo htmlspecialchars(
                            $hospitalContext->gfGetDisplayName(),
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        ); ?>
                    </p>
                </div>
                <div class="accounting-container">
                    <div class="search-panel">
                        <div class="search-panel-title">
                            <h2>検索条件</h2>
                        </div>
                        <form method="GET" class="search-form">
                            <button type="submit" name="nav" value="prev">
                                <
                            </button>
                            <div class="current-date">
                                <span class="calendar-icon">📅</span>
                                <span>
                                    <?php echo htmlspecialchars(
                                        $displayDate,
                                        ENT_QUOTES | ENT_SUBSTITUTE,
                                        'UTF-8'
                                    ); ?>
                                </span>
                            </div>
                            <button type="submit" name="nav" value="next">
                                >
                            </button>
                            <input 
                                type="hidden"
                                name="date"
                                value="<?php echo htmlspecialchars(
                                    $targetDate,
                                    ENT_QUOTES | ENT_SUBSTITUTE,
                                    'UTF-8'
                                ); ?>"
                            >
                            <!-- 決済種別での絞り込み -->
                            <div class="search-item">
                                <span>表示単位</span>
                                <label>
                                    <input
                                        type="radio"
                                        name="mode"
                                        value="day"
                                        <?php echo ($mode === 'day') ? 'checked' : '' ?>
                                    >
                                    日別
                                </label>
                                <label>
                                    <input
                                        type="radio"
                                        name="mode"
                                        value="month"
                                        <?php echo ($mode === 'month') ? 'checked' : ''?>
                                    >
                                    月別
                                </label>
                            </div>
                            <div class="search-item">
                                <label for="payKind">決済種別</label>
                                <select id="payKind" name="payKind">
                                    <option value="">すべて</option>
                                    <option value="現金"
                                    <?php echo ($selectedPayKind === '現金') ? 'selected' : ''; ?>>
                                    現金
                                    </option>
                                    <option value="クレジットカード"
                                    <?php echo ($selectedPayKind === 'クレジットカード') ? 'selected' : ''; ?>>
                                    クレジットカード
                                    </option>
                                    <option value="電子マネー"
                                    <?php echo ($selectedPayKind === '電子マネー') ? 'selected' : ''; ?>>
                                    電子マネー
                                    </option>
                                    <option value="QRコード"
                                    <?php echo ($selectedPayKind === 'QRコード') ? 'selected' : ''; ?>>
                                    QRコード
                                    </option>
                                </select>
                            </div>
                            <!-- 入外区分での絞り込み -->
                            <div class="search-item">
                                <label for="nyugai">入外</label>
                                <select id="nyugai" name="nyugai">
                                    <option value="">すべて</option>
                                    <option value="外来"
                                    <?php echo ($selectedNyugai === '外来') ? 'selected' : ''; ?>>
                                    外来
                                    </option>
                                    <option value="入院"
                                    <?php echo ($selectedNyugai === '入院') ? 'selected' : ''; ?>>
                                    入院
                                    </option>
                                </select>
                            </div>
                            <div class="search-buttons">
                                <button type="submit">検 索</button>
                                <!-- $_SERVER['PHP_SELF'] は 今実行しているPHPファイルのパスを取得 -->
                                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">条件をクリア</a>
                            </div>
                        </form>
                        <div class="result-count">
                            件数: <?php echo htmlspecialchars(number_format($accountingCount)); ?>件
                        </div>
                    </div>
                        <table class="accounting-table">
                            <thead>
                                <tr>
                                    <th>日付</th>
                                    <th>時間</th>
                                    <th>患者ID</th>
                                    <th>受診科</th>
                                    <th>保険種別</th>
                                    <th>入外</th>
                                    <th>保険診療額</th>
                                    <th>保険外診療額</th>
                                    <th>支払額</th>
                                    <th>決済方法</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accountings)): ?>
                                    <tr>
                                    <td colspan="10">該当する会計情報がありません。</td>
                                    </tr>
                                <?php else:?>
                                    <?php foreach ($accountings as $accounting): ?>
                                    <?php 
                                        // ループ内部の変数で合計額を持って置く
                                        $totalGokei += (int)$accounting['Gokei'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($accounting['YMD']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['Hhmmss']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['KID']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['Department']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['HokenKind']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['Nyugai']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($accounting['HokenGaku'])); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($accounting['JihiGaku'])); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($accounting['Gokei'])); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['PayKind']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <!-- 合計を表示する行を追加 -->
                                    <tr class="total-amount">
                                        <td colspan="8" class="item">合計</td>
                                        <td colspan="1" class="number"><?php echo htmlspecialchars(number_format($totalGokei))?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <form method="POST" action="logout.php" class="logout-form">
                            <button type="submit">ログアウト</button>
                        </form>
                </div><!-- accounting-container -->
            </div><!-- accounting-content -->
        </main>
    </body>
</html>