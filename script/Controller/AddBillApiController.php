<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/Service/AccountingService.php';


class AddBillApiController {
    
    private LogManager $pLogger;
    private AccountingService $pAccountingService;
    private string $pHospitalID;
    
    public function __construct(
        LogManager $logger,
        AccountingService $service,
        string $hospitalID
    ) {
        
        $this->pLogger = $logger;
        $this->pAccountingService = $service;
        $this->pHospitalID = $hospitalID;
    }
    
    
    /**
     * 院内側からのリクエストJsonを元に会計情報の登録を行う（Jsonの形式を確認して、登録結果はサービスが判定）
     * @param string $json
     * @return array
     */
    public function gfAddBill(string $json) : array {
        
        // Json -> PHP array
        $data = json_decode($json, true);
        
        // Jsonデコード処理でエラーが出ていないかどうかの確認
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                400,
                [
                    'status' => 'failure',
                    'message' => 'Invalid JSON'
                ]
            ];
        }
        
        // Jsonとしてはデコードできたけど、$dataがnull or "test"など配列ではないものを排除
        if (!is_array($data)) {
            return [
                400,
                [
                    'status' => 'failure',
                    'message' => 'Invalid Request Format'
                ]
            ];
        }
       
        
        // こちらが想定しているJsonの形式かどうかを確認
        $requestHospitalID = $data['HospitalID'] ?? null;
        $accountings = $data['Accountings'] ?? null;
        
        /*
         * HospitalIDの形式を確認
         */
        if (!is_string($requestHospitalID) || $requestHospitalID === '') {
            return [
                400,
                [
                    'status' => 'failure',
                    'message' => 'Invalid Hospital ID'
                ]
            ];
        }

        /*
         * URLの医療機関とリクエストのHospitalIDが一致するかどうかの確認
         */
        if ($requestHospitalID !== $this->pHospitalID) {
            return [
                403,
                [
                    'status' => 'failure',
                    'message' => 'Hospital ID mismatch'
                ]
            ];
        }

        /*
        * Accountingsが配列か確認
        *
        * この確認を先に行うことで、
        * pfCheckAccountings()へのnull渡しを防ぐ。
        */
        if (!is_array($accountings) || $accountings === []) {
            return [
                400,
                [
                    'status' => 'failure',
                    'message' => 'Invalid Accountings'
                ]
            ];
        }

        // hospitalIDが確定したのでここからAPIのログを残す？
        $this->pLogger->gfHospitalInfo(
            $this->pHospitalID,
            '会計情報の登録APIリクエストを受信'
        );
        
        
        // JSONの各項目の形式が正しいかどうかを判定する
        $isValid = $this->pfCheckAccountings($accountings);
        if (!$isValid) {
            
            $this->pLogger->gfHospitalError(
                $this->pHospitalID,
                'Jsonリクエストのパラメータが不適切です'
            );
            
            return [
                400,
                [
                    'status' => 'failure',
                    'message' => 'Invalid Parameter'
                ]
            ];
        }
        

        /*
         * YMDをDB保存用のYYYY-MM-DD形式に統一する
         */
        foreach ($accountings as $index => $accounting) {
            $ymd = $accounting['YMD'];

            if(!is_string($ymd)) {
                return [
                    400,
                    [
                        'status' => 'failure',
                        'message' => 'Invalid YMD'
                    ]
                ];
            }

            // 前後の空白を除去
            $ymd = trim($ymd);

            // 20260921 -> 2026-09-21
            if (preg_match('/^\d{8}$/', $ymd)) {

                $normalizedYmd = 
                    substr($ymd, 0, 4) . '-' .
                    substr($ymd, 4, 2) . '-' .
                    substr($ymd, 6, 2);
                
            // 2026/09/21 -> 2026-09-21
            } elseif (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $ymd)) {

                $normalizedYmd = str_replace('/', '-', $ymd);
            
            // 2026-09-21
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
                $normalizedYmd = $ymd;
            } else {
                return [
                    400,
                    [
                        'status' => 'failure',
                        'message' => 'Invalid YMD'
                    ]
                ];
            }

            // 変換結果を元の配列に戻す
            $accountings[$index]['YMD'] = $normalizedYmd;
        }
        
        /*
         * データを登録できたかどうかを確認（ここでサービスクラスに依頼）
         */
        $isSuccess = $this->pAccountingService->gfAddAccountingData($accountings);
        
        if ($isSuccess) {
            
            $this->pLogger->gfHospitalInfo(
                $this->pHospitalID,
                'APIでの会計情報の登録が完了しました'
            );
            
            return [
                200,
                [
                    'status' => 'success',
                    'message' => 'Insert Complete!'
                ]
            ];
            
        } else {
            
            $this->pLogger->gfHospitalError(
                $this->pHospitalID,
                'APIでの会計情報の登録に失敗しました'
            );
            
            return [
                500,
                [
                    'status' => 'failure',
                    'message' => 'Insert Failed'
                ]
            ];
        }
    }
    
    
    
    /**
     * 1件の会計データが適切に値が入っているいるかどうかを確認
     * @param array $accounting
     * @return bool
     */
    private function pfCheckOneAccounting(array $accounting) : bool {
        
        // 個別DBに必要なカラム
        $columns = [
            "SID",
            "YMD",
            "Hhmmss",
            "KID",
            "Department",
            "HokenKind",
            "Nyugai",
            "HokenGaku",
            "JihiGaku",
            "Gokei",
            "PayKind"
        ];
        
        
        foreach ($columns as $column) {
            
            // キーが存在するか？
            if (!array_key_exists($column, $accounting)) {
                return false;
            }
            
            // キーはあるが、値が入っているかどうか
            if (is_null($accounting[$column])) {
                return false;
            }
        }
        
        return true;
    }
    
    
    /**
     * 複数の会計データの中身が適切な形式かどうかを確認
     * @param array $accountings 連想配列の配列
     * @return bool
     */
    private function pfCheckAccountings(array $accountings) : bool {
        
        // 複数の会計データの中身が適切な形式かどうかを確認
        foreach ($accountings as $accounting) {
            
            // 配列が入っているかどうかのチェック
            if (!is_array($accounting)) {
                return false;
            }
            
            $isValid = $this->pfCheckOneAccounting($accounting);
            
            if (!$isValid) {
                return false;
            }
        }
        
        return true;
    }
}