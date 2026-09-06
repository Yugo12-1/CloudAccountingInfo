<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Repository/AccountingRepository.php';


class AccountingService {
    
    private AuthenticationManager $pAuthenticator;
    private AdminRepository $pAdminRepository;
    private AccountingRepository $pAccountingRepository;
    
    public function __construct(
        AuthenticationManager $authenticator,
        AdminRepository $adminRepository,
        AccountingRepository $accountingRepository) {
        
        $this->pAuthenticator = $authenticator;
        $this->pAdminRepository = $adminRepository;
        $this->pAccountingRepository = $accountingRepository;
    }
    
    
    
    /**
     * ログイン済みかどうかをAuthenticatorに依頼する
     * @return bool
     */
    public function gfIsLogin() : bool {
        return $this->pAuthenticator->gfIsAuthenticated();   
    }
    
    
    public function  gfGetAccountingData(string $hospitalID, array $conditions) : ?array {
        
        // hospitalIDをもとに管理用のDBから個別DBへの接続情報を取得する
        $dbInfo = $this->pAdminRepository->gfGetDbConnectInfo($hospitalID);
        
        // 取得したDBの接続情報をもとに個別DBに接続
        if ($this->pAccountingRepository->gfCreatePDO($dbInfo)) {
            
            // 会計情報の一覧を取得する
            return $this->pAccountingRepository->gfSearch($conditions);
            
        } else {
            return null;
        }
    }
    

//     public function gfGetAllAccountingInfo(string $hospitalID) : ?array {
        
//         // hospitalIDをもとに管理用のDBから個別DBへの接続情報を取得する
//         $dbInfo = $this->pAdminRepository->gfGetDbConnectInfo($hospitalID);
        
//         // 取得したDBの接続情報をもと接続
//         if ($this->pAccountingRepository->gfCreatePDO($dbInfo)) {
            
//             // 会計情報の一覧を取得する
//             return $this->pAccountingRepository->gfGetAllAccountingInfo();
            
//         } else {
//             return null;
//         }
//     }
    
    
    
//     public function gfGetByDate(string $hospitalID, string $selectedDate) : ?array {
//         // hospitalIDをもとに管理用のDBから個別DBへの接続情報を取得する
//         $dbInfo = $this->pAdminRepository->gfGetDbConnectInfo($hospitalID);
        
//         // 取得したDBの接続情報をもと接続
//         if ($this->pAccountingRepository->gfCreatePDO($dbInfo)) {
            
//             // 会計情報の一覧を取得する
//             return $this->pAccountingRepository->gfGetByDate($selectedDate);
            
//         } else {
//             return null;
//         }
//     }
}