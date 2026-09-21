<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/Support/LogManager.php';


class AuthenticationManager {
    
    private LogManager $pLogger;
    
    public function __construct(LogManager $logger) {
        $this->pLogger = $logger;
    }
    
    /**
     * ユーザ入力パスワードとDB保存のパスワードの照合をおこなう
     * @param string $loginPassword
     * @param string $dbPassword
     * @return bool
     */
    public function gfAuthenticate(string $loginPassword, string $dbPassword) : bool {
        
        return password_verify($loginPassword, $dbPassword);
    }
    
    
    
    /**
     * 医療機関IDとログインIDをセッションに保存
     * @param string $hospitalID
     * @param string $loginID
     */
    public function gfSaveSession(string $hospitalID, string $loginID) : void {
        
        // セッションIDを新しくしてセッション固定攻撃に備える
        session_regenerate_id(true);
        
        // 医療機関IDとLoginIDをsessionに保存する
        $_SESSION['hospitalID'] = $hospitalID;
        $_SESSION['loginID'] = $loginID;
    }
    
    
    
    /**
     * セッション変数の確認と保存された変数の確認
     * @return bool
     */
    public function gfIsAuthenticated() : bool {
        
        // セッションに変数がセットされているかどうかの確認
        return isset($_SESSION['hospitalID'], $_SESSION['loginID']);
    }
    
    
    /**
     * セッションの保持されている医療機関IDを取得
     * @return string|NULL
     */
    public function gfGetHospitalID(): ?string {
        
        return $_SESSION['hospitalID'] ?? null;
    }
    
    
    
    /**
     * セッションの情報を削除してログアウト処理を行う
     */
    public function gfClearSessionAndLogout() : void {
        
        // 1. セッションを開始
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // 2. セッション変数をクリア
        $_SESSION = [];
        
        // 3. セッションクッキーを削除
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 3600,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax'
                ]
            );
        }
        
        // 4. セッションを破棄
        session_destroy();
    }
}