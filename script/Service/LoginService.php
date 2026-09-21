<?php

$parentDir = dirname(__DIR__);
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';

/**
 * ログイン時のメインの処理を行うクラス
 * @author matsuda
 *
 */
class LoginService {
    
    private AdminRepository $pRepository;
    private AuthenticationManager $pAuthenticator;
    private string $pHospitalID;
    
    public function __construct(
        AdminRepository $repository,
        AuthenticationManager $authenticator,
        string $hospitalID) {
        
        $this->pRepository = $repository;
        $this->pAuthenticator = $authenticator;
        $this->pHospitalID = $hospitalID;
    }
    
    
    /**
     * ログイン処理ができたかどうかを判定する
     * @param string $loginID ユーザ入力のログインID
     * @param string $inputPassword ユーザ入力のログインパスワード
     * @return bool
     */
    public function gfLogin(string $loginID, string $inputPassword): bool {
        
        $userInfo = $this->pRepository->gfFindByLoginID(
            $this->pHospitalID,
            $loginID
        );
        
        // $userInfoが設定されているかどうかで情報が取得できたかどうか分かる
        if (is_null($userInfo)) {
            return false;
        }
        
        // ここでログインの判定を行う
        // AuthenticationManagerに認証
        if ($this->pAuthenticator->gfAuthenticate(
            $inputPassword, 
            $userInfo['LoginPassword'])) {
            
            // AuthenticationManagerでセッション作成して情報を保持
            $this->pAuthenticator->gfSaveSession(
                $this->pHospitalID,
                $userInfo['LoginID']
            );
            
            return true;
            
        } else {
            // 認証に失敗したときはfalse
            return false;
        }
    }
}