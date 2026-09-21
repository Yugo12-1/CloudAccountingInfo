<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Context/HospitalContext.php';

class DBConnection {

    private HospitalContext $pHospitalContext;

    public function __construct(HospitalContext $hospitalContext) {
        $this->pHospitalContext = $hospitalContext;
    }

    public function gfConnect() : PDO {
        
        $hostName  = $this->pHospitalContext->gfGetDbHostName();
        $dbName    = $this->pHospitalContext->gfGetDbName();
        $userName  = $this->pHospitalContext->gfGetDbUserName();
        $password  = $this->pHospitalContext->gfGetDbPassword();
        $charset   = $this->pHospitalContext->gfGetDbCharset();

        // DB接続に接続するためのデータ・ソース・ネーム
        $dsn = 
            "mysql:host={$hostName};" . 
            "dbname={$dbName};" . 
            "charset={$charset}";

        
        // エラー発生時に例外発生、連想配列でリターン、動的プレースホルダを使用
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            return new PDO($dsn, $userName, $password, $options);

        } catch (PDOException $e) {
            
            //例外処理のラップ化
            throw new RuntimeException(
                'データベース接続に失敗しました',
                0,
                $e
            );
        }
    }
}