<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AccountingRepository.php';


class AccountingService {
    
    private LogManager $pLogger; 
    private AuthenticationManager $pAuthenticator;
    private AccountingRepository $pAccountingRepository;
    private string $pHospitalID;
    
    
    public function __construct(
        LogManager $logger,
        AuthenticationManager $authenticator,
        AccountingRepository $accountingRepository,
        string $hospitalID
    ) {
        
        $this->pLogger = $logger;
        $this->pAuthenticator = $authenticator;
        $this->pAccountingRepository = $accountingRepository;
        $this->pHospitalID = $hospitalID;
    }
    
    
    
    /**
     * ログイン済みかどうかをAuthenticatorに依頼する
     * @return bool
     */
    public function gfIsLogin() : bool {
        return $this->pAuthenticator->gfIsAuthenticated();   
    }
    
    
    
    /**
     * 検索条件に一致する会計情報を取得する
     * @param array $conditions
     * @return array
     */
    public function  gfGetAccountingData(array $conditions) : array {
        try {
            return $this->pAccountingRepository->gfSearch($conditions);

        } catch (Throwable $e) {
            $this->pLogger->gfHospitalError(
                $this->pHospitalID,
                '会計情報の取得に失敗しました'
            );

            throw $e;
        }
    }
    
    
    
    /**
     * 会計情報を追加または更新する
     * @param array $accountings
     * @return bool
     */
    public function gfAddAccountingData(array $accountings) : bool {
        
        try {
            
            $this->pLogger->gfHospitalInfo(
                $this->pHospitalID,
                '会計情報の登録を開始しました'
            );
            
            // 会計データをINSERT or UPDATEする
            $isSuccess = $this->pAccountingRepository->gfInsertOrUpdate($accountings);
            
            // 成功時のログはAPI側で出力されるからOK
            if (!$isSuccess) {
                $this->pLogger->gfHospitalError(
                    $this->pHospitalID,
                    '会計情報の登録に失敗しました'
                );
            }
            
            return $isSuccess;
            
        } catch (Throwable $e) {

            $this->pLogger->gfHospitalError(
                $this->pHospitalID,
                '会計情報の登録に失敗しました'
                );
            
            throw $e;
        }
    }
}