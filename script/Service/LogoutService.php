<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';

class LogoutService {
    
    private LogManager $pLogger;
    private AuthenticationManager $pAuthenticator;
    
    public function __construct(
        LogManager $logger, 
        AuthenticationManager $authenticator
    ) {
       
        $this->pLogger = $logger;
        $this->pAuthenticator = $authenticator;
    }
    
    
    
    /**
     * ログアウト処理を行う
     * @param string $loginID
     */
    public function gfLogout(string $loginID) : void {
        
        try {
            // authenticatorにログアウト処理依頼
            $this->pAuthenticator->gfClearSessionAndLogout();
            
            $this->pLogger->gfSystemInfo(
                $loginID,
                'ログアウトしました'
            );
            
        } catch (Throwable $e) {
            
            $this->pLogger->gfSystemError(
                $loginID, 
                'ログアウト処理に失敗しました'
            );
            
            throw $e;
        }
    }
}