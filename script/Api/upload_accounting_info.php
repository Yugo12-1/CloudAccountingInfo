<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Tokyo');

header('Content-Type: application/json; charset=UTF-8');

/*
 * 医療機関別のAPIの入り口から呼び出されたことを確認
 */
if (!isset($hospitalKey) || !is_string($hospitalKey)) {
    http_response_code(500);

    echo json_encode(
        [
            'status' => 'failure',
            'message' => 'Hospital Key is not specified'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}


/*
 * scriptディレクトリ
 */
$scriptDir = dirname(__DIR__);

require_once $scriptDir . '/Support/HospitalResolver.php';
require_once $scriptDir . '/Support/LogManager.php';
require_once $scriptDir . '/DBManager/DBConnection.php';
require_once $scriptDir . '/Authentication/AuthenticationManager.php';
require_once $scriptDir . '/Repository/AccountingRepository.php';
require_once $scriptDir . '/Service/AccountingService.php';
require_once $scriptDir . '/Controller/AddBillApiController.php';


/*
 * hospital KeyからHospital Contextを生成
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

$accountingService = new AccountingService(
    $logger,
    $authenticator,
    $accountingRepository,
    $hospitalContext->gfGetHospitalID()
);

$apiController = new AddBillApiController(
    $logger,
    $accountingService,
    $hospitalContext->gfGetHospitalID()
);


/*
 * HTTP APIのリクエスト処理を開始
 */

// 入力ストリームから、クライアントから送信された生データを読み込む（json形式）
$json = file_get_contents('php://input');

if ($json === false) {
    http_response_code(500);

    echo json_encode(
        [
            'status' => 'failure',
            'message' => 'Failed to read request body'
        ]
    );

    exit();
}


/*
 * APIコントローラーに会計情報の登録を依頼する
 */
[$statusCode, $message] = $apiController->gfAddBill($json);

http_response_code($statusCode);

// レスポンスをjson形式で返却
echo json_encode(
    $message,
    JSON_UNESCAPED_UNICODE
);
