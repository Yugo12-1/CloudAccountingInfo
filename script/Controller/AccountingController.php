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
    
    
    /**
     * 検索条件に合致する会計情報の取得を行う
     * @param array $conditions
     * @return array
     */
    public function gfShowAccountingData(array $conditions) : array {
        $result = $this->pService->gfGetAccountingData($conditions);
        
        return $result;
    }
}