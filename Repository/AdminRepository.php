<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/DBManager/DBConnection.php';


class AdminRepository {
    
    private DBConnection $pDbConnect;
    private ?PDO $pPdo;
    
    
    public function __construct(DBConnection $dbConnect) {
        
        $this->pDbConnect = $dbConnect;
        $this->pPdo = $this->pDbConnect->gfConnect('admin');
        
        if (is_null($this->pPdo)) {
            // 例外を投げる、またはエラーログを出して処理をとめる
            throw new RuntimeException('データベースの接続に失敗しました。');
        }
    }
    
    
    /**
     * 
     * @param string $loginID
     * @return array|NULL
     * ・医療機関ID -> ['HospitalID']
     * ・ログインID -> ['LoginID']
     * ・DB上のハッシュパスワード -> ['LoginPassword']
     * ・取得できないときは、null 
     */
    public function gfFindbyLoginID(string $loginID) : ?array {
        
        // SQL文の組立
        $strSQL = <<<SQL
            SELECT
                HospitalID,
                LoginID,
                LoginPassword
            FROM tbl_administrator
            WHERE LoginID = ?
        SQL;
        
        $stmt = $this->pPdo->prepare($strSQL);
        
        $stmt->execute([
            $loginID
        ]);
        
        // 1件だけデータをゲットする
        $userInfo = $stmt->fetch();
        
        if (!$userInfo) {
            return null;
        }
        
        return $userInfo;
    }
    
    
    
    public function gfGetDbConnectInfo(string $hospitalID) : ?array {
        
        // SQLの組立
        $strSQL = <<<SQL
            SELECT
                DBName,
                DBUser,
                DBPassword
            FROM tbl_administrator
            WHERE HospitalID = ?
        SQL;
            
        $stmt = $this->pPdo->prepare($strSQL);
        
        $stmt->execute([
            $hospitalID
        ]);
        
        // 1件のデータを取得
        $dbInfo = $stmt->fetch();
        
        if (!$dbInfo) {
            return null;
        }
        
        return $dbInfo;
    }
}