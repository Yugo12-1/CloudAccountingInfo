<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/DBManager/DBConnection.php';

class AccountingRepository {
    
    private LogManager $pLogger;
    private DBConnection $pDbConnection;
    private PDO $pPdo;
    private string $pHospitalID;
    
    
    public function __construct(
        LogManager $logger,
        DBConnection $dbConnection,
        string $hospitalID
    ) {
        
        $this->pLogger = $logger;
        $this->pDbConnection = $dbConnection;
        $this->pHospitalID = $hospitalID;

        // 接続先の情報は既に"HospitalContext"で決まっている (New Version)
        $this->pPdo = $this->pDbConnection->gfConnect();
    }
    
    
    /**
     * 会計情報をDBに登録をする処理
     * @param array $accountings
     * @return bool
     */
    public function gfInsertOrUpdate(array $accountings) : bool {
        
        try {
            // トランザクション開始
            $this->pPdo->beginTransaction();
            
            // SIDの存在チェック
            $checkSql = <<<SQL
                SELECT COUNT(*)
                FROM tbl_accounting
                WHERE SID = :SID
            SQL;
            
            $checkStmt = $this->pPdo->prepare($checkSql);
            
            // INSERT
            $insertSql = <<<SQL
                INSERT INTO tbl_accounting (
                    SID,
                    YMD,
                    Hhmmss,
                    KID,
                    Department,
                    HokenKind,
                    Nyugai,
                    HokenGaku,
                    JihiGaku,
                    Gokei,
                    PayKind
                ) VALUES (
                    :SID,
                    :YMD,
                    :Hhmmss,
                    :KID,
                    :Department,
                    :HokenKind,
                    :Nyugai,
                    :HokenGaku,
                    :JihiGaku,
                    :Gokei,
                    :PayKind
                )               
            SQL;
            
            $insertStmt = $this->pPdo->prepare($insertSql);
            
            
            // UPDATE
            $updateSql = <<<SQL
                UPDATE tbl_accounting
                SET
                    YMD = :YMD,
                    Hhmmss = :Hhmmss,
                    KID = :KID,
                    Department = :Department,
                    HokenKind = :HokenKind,
                    Nyugai = :Nyugai,
                    HokenGaku = :HokenGaku,
                    JihiGaku = :JihiGaku,
                    Gokei = :Gokei,
                    PayKind = :PayKind
                WHERE SID = :SID
            SQL;
            
            $updateStmt = $this->pPdo->prepare($updateSql);
            
            foreach ($accountings as $accounting) {
                
                // SIDが存在するかどうかの確認
                $checkStmt->execute([
                    ':SID' => $accounting['SID']
                ]);
                
                $isExist = $checkStmt->fetchColumn() > 0;
                if ($isExist) {
                    // 存在するので -> UPDATE
                    $updateStmt->execute($accounting);
                    
                    $this->pLogger->gfHospitalSql(
                        $this->pHospitalID, 
                        'UPDATE tbl_accounting | params: ' . json_encode($accounting, JSON_UNESCAPED_UNICODE)
                    );
                    
                } else {
                    // 存在しないので -> INSERT
                    $insertStmt->execute($accounting);
                    
                    $this->pLogger->gfHospitalSql(
                        $this->pHospitalID,
                        'INSERT tbl_accounting | params: ' . json_encode($accounting, JSON_UNESCAPED_UNICODE)
                    );
                }
            }
            
            // 全部成功したので確定
            $this->pPdo->commit();
            
            return true;
            
        } catch (Throwable $e) {
            
            // 途中でエラーが発生したら全部取り消す
            if ($this->pPdo->inTransaction()) {
                $this->pPdo->rollBack();
            }
            
            throw $e;
        }
    }
    
    
    /**
     * 検索条件をもとにSQL文を構築して実行する
     * @param array $conditions ["PayKind" => "現金"]
     * @return array
     */
    public function gfSearch(array $conditions) : array {
    
        // 会計情報の一覧の取得をベースにする
        $strSQL = <<<SQL
            SELECT
                *
            FROM tbl_accounting
        SQL;
    
    
        // 条件を組み立てる処理は別のメソッドに任せる
        [$whereClauses, $params] = $this->pfBuildWhereClauses($conditions);
    
        // where句条件があるときに WHERE句を追加したい
        if (!empty($whereClauses)) {
            // implodeは(区切り文字、配列)で配列の要素をつなぎます。
            $strSQL .= " WHERE " . implode(' AND ', $whereClauses);
        }
    
        // 最後にORDER BY句を追加する
        $sqlOrder = ' ORDER BY YMD DESC, Hhmmss DESC';
        $strSQL .= $sqlOrder;
    
        // プリアドステートメント
        $stmt = $this->pPdo->prepare($strSQL);
        
        //パラメータをまとめてバインド
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
    
        $accountings = $stmt->fetchAll();
        return $accountings;
    }
    
    
    /**
     * where句の配列、パラメータの配列を検索条件から作成
     * @param array $conditions
     * @return array
     */
    private function pfBuildWhereClauses(array $conditions) : array {
        
        /*
         * 値が''の条件を省いた連想配列の作成 ("PayKind" => '' などを削る)
         * fn($val) => $val !== '' は無名関数で引数が''以外ならtrueをリターン
         */
        $filteredConditons = array_filter(
            $conditions,
            fn($val) => $val !== '' && !is_null($val)
        );
        
        /*
         * 以下のように値が入っている連想配列を取り出す
         * [
         *   "YMD" => "2026-09-01",
         *   "PayKind" => "クレジット",
         *   "StartDate" => "2026-09-01",
         *   "EndDate" => "2026-09-30"
         * ]
         */
        
        $whereClauses = [];
        $params = [];
        
        // 検索を許可するカラム（キー）のリストを定義しておく
        $allowdKeys = [
            'PayKind',
            'StartDate', 
            'EndDate',
            'SelectedYear',
            'Nyugai'
        ];
        
        // 値が入った条件を格納していく
        foreach ($filteredConditons as $key => $val) {
            
            // 許可されていないキーは無視する
            if (!in_array($key, $allowdKeys, true)) {
                continue;
            }
            
            // 年別検索の場合(例: '2026' -> '2026%')
            if ($key === 'SelectedYear') {
                $whereClauses[] = "YMD LIKE :SelectedYear";
                $params[':SelectedYear'] = $val . '%';
                continue;
            }
            
            // 期間指定の開始日の場合
            if ($key === 'StartDate') {
                $whereClauses[] = "YMD >= :StartDate";
                $params[':StartDate'] = $val;
                continue;
            }
            
            // 期間指定の終了日の場合
            if ($key === 'EndDate') {
                $whereClauses[] = "YMD <= :EndDate";
                $params[':EndDate'] = $val;
                continue;
            }
            
            // 上記以外は完全一致の条件
            $whereClauses[] = "{$key} = :{$key}";
            $params[":{$key}"] = $val;
        }
        
        return [$whereClauses, $params];
    }
}