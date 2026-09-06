<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';


class DBConnection {
    
    private ConfigReader $pConfigReader;
    
    public function __construct(ConfigReader $configReader) {
        $this->pConfigReader = $configReader;
    }
    
    
    public function gfConnect(string $kind, array $dbInfo = []) : ?PDO{
        
        if ($kind === "admin") {
            $hostName = $this->pConfigReader->gfGetSettingsValue('adminDb', 'hostName');
            $dbName = $this->pConfigReader->gfGetSettingsValue('adminDb', 'dbName');
            $userName = $this->pConfigReader->gfGetSettingsValue('adminDb', 'userName');
            $password = $this->pConfigReader->gfGetSettingsValue('adminDb', 'password');
            
        } else {
            $hostName = $this->pConfigReader->gfGetSettingsValue('hospitalDb', 'hostName');
            $dbName = $dbInfo['DBName'];
            $userName = $dbInfo['DBUser'];
            $password = $dbInfo['DBPassword'];
        }
        
        $dsn = "mysql:host={$hostName};dbname={$dbName};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
            
        ];
        
        try {
            // PDOの作成
            $pdo = new PDO($dsn, $userName, $password, $options);
            
            return $pdo;
        }
        catch (PDOException $e) {
            throw new RuntimeException(
                'データベースの接続に失敗しました: ' . $e->getMessage(),
                0,
                $e
                );
        }
    }
}