<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/Service/AccountingService.php';

class AccountingController {
    
    private AccountingService $pService;
    
    
    public function __construct(AccountingService $service) {
        $this->pService = $service;
    }
    
    
    /**
     * ログイン済みかどうかをサービスに依頼する
     * @return bool
     */
    public function gfIsLogin() : bool {
        return $this->pService->gfIsLogin();
    }
    
    
    /**
     * ログインページへ強制的に移動
     */
    public function gfGoToLoginPage() : void {
        header("Location: login.php");
        exit();
    }
    
    
    public function gfShowAccountingData(string $hospitalID, array $conditions) : array {
        $result = $this->pService->gfGetAccountingData($hospitalID, $conditions);
        
        return $result ?? [];
    }
    
    
//     /**
//      * 医療機関IDをもとにデータベースにある会計情報の全カラムの情報を取得する
//      * @param string $hospitalID
//      * @return array
//      */
//     public function gfShowAll(string $hospitalID) : array {
//         return $this->pService->gfGetAllAccountingInfo($hospitalID);
//     }
    
    
//     public function gfShowByDate(string $hospitalID, string $selectedDate) : array {
        
//         $result = $this->pService->gfGetByDate($hospitalID, $selectedDate);
        
//         // nullが返って来たら空の配列を返す
//         return $result ?? [];
//     }
}