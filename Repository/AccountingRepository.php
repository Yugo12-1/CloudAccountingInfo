<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/DBManager/DBConnection.php';

class AccountingRepository {
    
    private DBConnection $pDbConnection;
    private ?PDO $pPdo = null;
    
    
    public function __construct(DBConnection $dbConnection) {
        
        $this->pDbConnection = $dbConnection;
    }
    
    
    
    /**
     * 個別DBに接続するためのPDOを作成する
     * @param array $dbInfo
     * @return bool
     */
    public function gfCreatePDO(array $dbInfo) : bool {
        $this->pPdo = $this->pDbConnection->gfConnect('hospital', $dbInfo);
        
        if (is_null($this->pPdo)) {
            // 例外を投げる、またはエラーログを出して処理をとめる
            throw new RuntimeException('データベースの接続に失敗しました。');
        }
        
        return true;
    }
    
    
    /**
     * 検索条件をもとにSQL文を構築して実行する
     * @param array $conditions ["PayKind" => "現金"]
     * @return array
     */
    public function gfSearch(array $conditions) : array {
        
        // PDOのnullチェック
        if (is_null($this->pPdo)) {
            throw new RuntimeException(
                'データベースに接続されていません'
            );
        }

        // 会計情報の一覧の取得をベースにする
        $strSQL = <<<SQL
            SELECT
                *
            FROM tbl_accounting
        SQL;
        
        /*
            値が''の条件を省いた連想配列の作成 ("PayKind" => ''　などを削る)
            fn($val) => $val !== ''　は無名関数で引数が''以外ならtrueをリターン
        */
        $filteredConditions = 
            array_filter(
                $conditions, 
                fn($val) => $val !== ''
                );
        /*
            以下のように値が入っている連想配列を取り出す
            [
                "YMD" => "2026-09-01",
                "PayKind" => "クレジット"
            ]
        */

        // keyだけを配列でゲット
        $keys = array_keys($filteredConditions);
        
        $whereClauses = [];
        foreach ($keys as $key) {
            // name = : name のような条件を作って連想配列にためる
            $whereClauses[] = "{$key} = :{$key}";
        }
        
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
        
        // where句の条件が入っているときはパラメータのバインドをする
        foreach ($filteredConditions as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        
        $stmt->execute();
        
        $accountings = $stmt->fetchAll();
        
        return $accountings;
    }
}