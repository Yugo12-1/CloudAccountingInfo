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
require_once $scriptDir . '/Repository/AccountingRepository.php';
require_once $scriptDir . '/Service/AnalysisService.php';
require_once $scriptDir . '/Controller/AnalysisController.php';


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

$accountingRepository = new AccountingRepository(
    $logger,
    $dbConnect,
    $hospitalContext->gfGetHospitalID()
);

$analysisService = new AnalysisService($accountingRepository);

$analysisController = new AnalysisController($analysisService);


/*
 *　GETメソッドでYearとMonthのデータをうけとる 
 */

$year = filter_input(
    INPUT_GET,
    'Year',
    FILTER_VALIDATE_INT
);

$month = filter_input(
    INPUT_GET,
    'Month',
    FILTER_VALIDATE_INT
);

if (
    $year === false ||
    $year === null ||
    $month === false ||
    $month < 1 ||
    $month > 12
) {
    http_response_code(400);

    echo json_encode(
        [
            'status' => 'failure',
            'message' => 'Year and Month are invalid data'
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit();
}



$data = $analysisController->gfGetDashboardData($year, $month);
echo json_encode($data, JSON_UNESCAPED_UNICODE);