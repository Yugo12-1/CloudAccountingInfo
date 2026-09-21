<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Repository/AccountingRepository.php';
require_once $parentDir . '/Service/AccountingService.php';
require_once $parentDir . '/Controller/AddBillApiController.php';


// 必要なクラスを準備
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

$accountingService = new AccountingService(
    $logger,
    $authenticator, 
    $adminRepository, 
    $accountingRepository
);

$controller = new AddBillApiController(
    $logger,
    $accountingService
);


// HTTP リクエストボディの中身を文字列として取得する
$json = file_get_contents('php://input');

// テスト
// $accounting = [
//     'SID' => '0000000113',
//     'YMD' => '20260911',
//     'Hhmmss' => '112030',
//     "KID" => '000011',
//     'Department' => '精神科',
//     'HokenKind' => '社保',
//     'Nyugai' => '外来',
//     'HokenGaku' => 100000,
//     'JihiGaku' => 0,
//     'Gokei' => 100000,
//     'PayKind' => '現金'
// ];

// $accounting2 = [
//     'SID' => '0000000114',
//     'YMD' => '20260911',
//     'Hhmmss' => '110030',
//     "KID" => '000014',
//     'Department' => '眼科',
//     'HokenKind' => '国保',
//     'Nyugai' => '外来',
//     'HokenGaku' => 4000,
//     'JihiGaku' => 0,
//     'Gokei' => 4000,
//     'PayKind' => 'クレジット'
// ];


// $accountings = [
//     'HospitalID' => '0000000001',
//     'Accountings' => [$accounting, $accounting2]
// ];


// $accountings = [
//     'HospitalID' => '0000000001',
//     'Accountings' => []
// ];

// $json = json_encode($accountings);

// ここまでテスト


// コントローラーからステータスコードとjson形式のメッセージをもらう
[$statusCode, $message] = $controller->gfAddBill($json);

// レスポンスヘッダーにステータスコードを明記
http_response_code($statusCode);

// json形式のヘッダー記憶
header('Content-Type: application/json; charset=UTF-8');

// レスポンスをjson形式で返却
echo json_encode(
    $message,
    JSON_UNESCAPED_UNICODE
);