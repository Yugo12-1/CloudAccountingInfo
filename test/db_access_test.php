<?php
header('Content-Type: text/html; charset=UTF-8');

$parentDir = dirname(__DIR__);

require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Support/HospitalResolver.php';

try {
    $configDir = $parentDir . '/Config/Hospitals/';

    $resolver = new HospitalResolver($configDir);
    $hospitalContext = $resolver->gfResolveKeyToContext('clinic_matsuda');

    $dbConnection = new DBConnection($hospitalContext);

    $pdo = $dbConnection->gfConnect();

    $stmt = $pdo->query('SELECT 1 AS Result');
    $result = $stmt->fetch();

    echo 'DB接続成功';
} catch (Throwable $e) {
    http_response_code(500);
    
    echo htmlspecialchars(
    (string)$result['Result'],
    ENT_QUOTES,
    'UTF-8'
);
}