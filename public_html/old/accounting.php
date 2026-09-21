<?php 

// セッション開始
session_start();

// キャッシュの保持禁止する
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Repository/AccountingRepository.php';
require_once $parentDir . '/Service/AccountingService.php';
require_once $parentDir . '/Controller/AccountingController.php';

// 必要なクラスを作成する
$iniFilePath = $parentDir . '/SettingFiles/settings.ini';
$logRootDir = $parentDir . '/Logs';

$logger = new LogManager($logRootDir);
$configReader = new ConfigReader($iniFilePath);
$dbConnect = new DBConnection($configReader);
$authenticator = new AuthenticationManager($logger);
$adminRepository = new AdminRepository(
    $logger,
    $dbConnect
);

$accountingRepository = new AccountingRepository(
    $logger,
    $dbConnect
);

$service = new AccountingService(
    $logger,
    $authenticator, 
    $adminRepository, 
    $accountingRepository
);
$controller = new AccountingController($service);


// ログイン済みかどうかをコントローラーに依頼
$isLogin = $controller->gfIsLogin();

if (!$isLogin) {
    $controller->gfGoToLoginPage();
}

// セッションにある医療機関IDを取得してコントローラーに処理を依頼する
$hospitalID = $_SESSION['hospitalID'];


// 選択条件の連想配列で渡す
$conditions = [];

// 初回アクセス時、条件のリセットを今日日付のデータにする
$startDate = $_GET['startDate'] ?? date('Y-m-d');
$endDate =$_GET['endDate'] ?? date('Y-m-d');

$isValidStartDate = false;
$isValidEndDate = false;

// 開始日のバリデーションチェック
if (!empty($startDate)) {
    $d = DateTime::createFromFormat('Y-m-d', $startDate);
    if ($d && $d->format('Y-m-d') === $startDate) {
        $isValidStartDate = true;
    }
}

if (!$isValidStartDate) {
    $startDate = '';
}


// 終了日のバリデーションチェック
if (!empty($endDate)) {
    $d = DateTime::createFromFormat('Y-m-d', $endDate);
    if ($d && $d->format('Y-m-d') === $endDate) {
        $isValidEndDate = true;
    }
}

if (!$isValidEndDate) {
    $endDate = '';
}


// 年別のバリデーションチェック
$selectedYear = $_GET['selectedYear'] ?? '';

if (!preg_match('/^\d{4}$/', $selectedYear)) {
    $selectedYear = '';
}

// 決済種別
$selectedPayKind = $_GET['payKind'] ?? '';

// 入外区分
$selectedNyugai = $_GET['nyugai'] ?? '';

// 検索条件の格納 (レポジトリのキーに合わせる)
$conditions = [
    'StartDate' => $startDate,
    'EndDate' => $endDate,
    'SelectedYear' => $selectedYear,
    'PayKind' => $selectedPayKind,
    'Nyugai' => $selectedNyugai
];

// 条件に応じて表示させる内容を変化させる
$accountings = $controller->gfShowAccountingData($hospitalID, $conditions);

// 件数表示用
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
    </head>
    <body>
        <header class="site-header">
            <div class="header-content">
            <img src="../img/medifo_logo1.png" alt="製品ロゴ">
            </div>
        </header>
        <h1>会計情報一覧を表示する画面</h1>
        <br><br>
        <main class="accounting-area">
            <div class="accounting-content">
                <div class="accounting-header">
                    <h1>会計情報一覧ページ</h1>
                </div>
                <div class="accounting-container">
                    <div class="search-panel">
                        <div class="search-panel-title">
                            <h2>検索条件</h2>
                        </div>
                        <form method="GET" class="search-form">
                            <!-- 年での絞り込み -->
                            <div class="search-item">
                                <label for="selectedYear">年</label>
                                <select id="selectedYear" name="selectedYear">
                                <option value="">すべて</option>
                                <?php 
                                    $currentYear = (int)date('Y');
                                    // 過去3年分のデータを選択条件に表示する
                                    for ($y = $currentYear; $y >= $currentYear - 3; $y--) {
                                        $selected = ($selectedYear === (string)$y) ? 'selected' : '';
                                        echo "<option value=\"{$y}\" {$selected}>{$y}年</option>";
                                    }
                                ?>
                                </select>
                            </div>
                            <!-- 開始日、終了日での絞り込み -->
                            <div class="search-item search-date">
                                <label for="startDate">期間</label>
                                <div class="date-inputs">
                                    <input
                                    type="date"
                                    id="startDate"
                                    name="startDate"
                                    value="<?php echo htmlspecialchars($startDate); ?>"
                                    >
                                    <span>～</span>
                                    <input
                                    type="date"
                                    id="endDate"
                                    name="endDate"
                                    value="<?php echo htmlspecialchars($endDate); ?>"
                                    >
                                </div>
                            </div>
                            <!-- 決済種別での絞り込み -->
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
                                        <td><?php echo htmlspecialchars($accounting['HokenGaku']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['JihiGaku']); ?></td>
                                        <td><?php echo htmlspecialchars($accounting['Gokei']); ?></td>
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
                        <br><br>
                        <form method="POST" action="logout.php">
                            <button type="submit">ログアウトする</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>