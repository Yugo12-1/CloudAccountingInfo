<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/DBManager/DBConnection.php';


class AdminRepository {
    
    private LogManager $pLogger;
    private DBConnection $pDbConnect;
    private PDO $pPdo;
    
    
    public function __construct(LogManager $logger, DBConnection $dbConnect) {
        
        $this->pLogger = $logger;
        $this->pDbConnect = $dbConnect;
        $this->pPdo = $this->pDbConnect->gfConnect();
    }
    
    
    /**
     * 
     * @param string  $hospitalID
     * @param string $loginID
     * @return array|NULL
     * ・医療機関ID -> ['HospitalID']
     * ・ログインID -> ['LoginID']
     * ・DB上のハッシュパスワード -> ['LoginPassword']
     * ・取得できないときは、null 
     */
    public function gfFindByLoginID(string $hospitalID, string $loginID) : ?array {
        
        // SQL文の組立
        $strSQL = <<<SQL
            SELECT
                HospitalID,
                LoginID,
                LoginPassword
            FROM tbl_administrator
            WHERE HospitalID = ?
              AND LoginID = ?
              AND IsActive = 1
        SQL;
        
        $stmt = $this->pPdo->prepare($strSQL);
        
        $stmt->execute([
            $hospitalID,
            $loginID
        ]);
        
        // 1件だけデータをゲットする
        $userInfo = $stmt->fetch();
        
        if (!$userInfo) {
            return null;
        }
        
        return $userInfo;
    }
}