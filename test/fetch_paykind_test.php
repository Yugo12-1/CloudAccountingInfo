<?php
header('Content-Type: text/html; charset=UTF-8');

$scriptDir = dirname(__DIR__) . '/script';

require_once $scriptDir . '/Support/HospitalResolver.php';
require_once $scriptDir . '/Support/LogManager.php';
require_once $scriptDir . '/Authentication/AuthenticationManager.php';
require_once $scriptDir . '/Repository/AccountingRepository.php';
require_once $scriptDir . '/Service/AccountingService.php';
require_once $scriptDir . '/Controller/AccountingController.php';

// 今回はURLからではなく、固定値で確認する
$hospitalKey = 'clinic_matsuda';

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

$accountingRepository = new AccountingRepository(
    $logger,
    $dbConnect,
    $hospitalContext->gfGetHospitalID()
);

/*
 * 決済手段別のクエリの結果
 */

$payKindData = $accountingRepository->gfFetchPayKindCounts();
var_dump($payKindData);

/*
 * 以下のようなデータが返ってくる
[
    [
        "PayKind" => "クレジットカード",
        "count"   => 3
    ],
    [
        "PayKind" => "現金",
        "count"   => 4,
    ]
]
*/

