<?php 

// セッション開始
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Repository/AccountingRepository.php';
require_once $parentDir . '/Service/AccountingService.php';
require_once $parentDir . '/Controller/AccountingController.php';

// 必要なクラスを作成する
$iniFilePath = $parentDir . '/SettingFiles/settings.ini';
$configReader = new ConfigReader($iniFilePath);
$dbConnect = new DBConnection($configReader);
$authenticator = new AuthenticationManager();
$adminRepository = new AdminRepository($dbConnect);
$accountingRepository = new AccountingRepository($dbConnect);
$service = new AccountingService($authenticator, $adminRepository, $accountingRepository);
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

// 日付選択
$selectedDate = $_GET['selectedDate'] ?? '';
$isValidDate = false;

// 決済種別
$selectedPayKind = $_GET['PayKind'] ?? '';

// 下記以外の決済手段が入力された場合
$allowedPayKind = [
    '',
    '現金',
    'クレジット',
    '電子マネー',
    'QRコード'
];
if (!in_array($selectedPayKind, $allowedPayKind, true)) {
    $selectedPayKind = '';
}

// 入力値のバリデーションチェック
if (!empty($selectedDate)) {
    
    // 形式を指定してパース
    $d = DateTime::createFromFormat('Y-m-d', $selectedDate);
    
    // オブジェクトが生成されたこと、形式があっていることを確認してフラグを書き換える
    if ($d && $d->format('Y-m-d') === $selectedDate) {
        $isValidDate = true;
    }
}

if (!$isValidDate) {
    $selectedDate = '';
}

// 検索条件の格納 (DBのカラム名に合わせないとだめだよね）
$conditions['YMD'] = $selectedDate;
$conditions['PayKind'] = $selectedPayKind;

// 条件に応じて表示させる内容を変化させる
$accountings = $controller->gfShowAccountingData($hospitalID, $conditions);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width" />
        <title>会計情報一覧</title>
    </head>
    <body>
        <h1>会計情報一覧を表示する画面</h1>
        <br><br>
        <form method="GET">
            <!-- 日付での絞り込み -->
            <label for="selectedDate">日付: </label>
            <input
                type="date"
                id="selectedDate"
                name="selectedDate"
                value="<?php echo htmlspecialchars($selectedDate); ?>"
            >
            <!-- 決済種別での絞り込み -->
            <label for="PayKind">決済種別: </label>
            <select id="PayKind" name="PayKind">
                <option value="">すべて</option>
                <option value="現金"
                    <?php echo ($selectedPayKind === '現金') ? 'selected' : ''; ?>>
                    現金
                </option>
                <option value="クレジット"
                    <?php echo ($selectedPayKind === 'クレジット') ? 'selected' : ''; ?>>
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
            <button type="submit">検索</button>
            <!-- $_SERVER['PHP_SELF'] は 今実行しているPHPファイルのパスを取得 -->
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">全件取得</a><br><br>
        </form>
        <table border="1">
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
                <?php endif; ?>
            </tbody>
        </table>
        <br><br>
        <form method="POST" action="logout.php">
            <button type="submit">ログアウトする</button>
        </form>
    </body>
</html>