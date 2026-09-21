<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Service/LoginService.php';

/**
 * ログイン認証の画面遷移ようのクラス
 * @author matsuda
 *
 */
class LoginController {
    
    private LoginService $pService;
    
    public function __construct(LoginService $service) {
        $this->pService = $service;
    }
    
    
    /**
     * ログイン可能かどうかを判定して、リダイレクト先を決める
     * @param string $loginID
     * @param string $loginPassword
     */
    public function gfLogin(string $loginID, string $loginPassword) : void{
        
        $result = $this->pService->gfLogin($loginID, $loginPassword);
        
        if ($result) {
            // ログインに成功した場合は会計情報一覧へ
            header("Location: accounting.php");
            exit();
            
        } else {
            // ログインに失敗した場合はエラーメッセージをを保存
            $_SESSION['loginError'] = 'ログインIDまたはパスワードが正しくありません';
            header("Location: login.php");
            exit();
            
        }
    }
}