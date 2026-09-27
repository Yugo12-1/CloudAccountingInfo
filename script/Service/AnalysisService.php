<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AccountingRepository.php';


class AnalysisService {
    
    private LogManager $pLogger; 
    private AuthenticationManager $pAuthenticator;
    private AccountingRepository $pAccountingRepository;
    private string $pHospitalID;

    
    public function __construct(
        LogManager $logger,
        AuthenticationManager $authenticator,
        AccountingRepository $repository,
        string $hospitalID
    ) {
        $this->pLogger = $logger;
        $this->pAuthenticator = $authenticator;
        $this->pAccountingRepository = $repository;
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
     * ダッシュボード全体のデータを取得
     *
     * @param integer $year
     * @param integer $month
     * @return array
     */
    public function gfGetDashboardData(
        int $year,
        int $month
    ) : array {

        $startDate = sprintf(
            '%04d-%02d-01',
            $year,
            $month
        );

        $startDateObj = new DateTimeImmutable($startDate);

        $endDate = $startDateObj
            ->modify('+1 month')
            ->format('Y-m-d');
    
        return [
            'PayKind' => $this->gfGetPayKindCounts($startDate, $endDate),
            'Hourly'  => $this->gfGetHourlyCounts($startDate, $endDate),
            'Gokei'   => $this->gfGetGokeiCounts($startDate, $endDate)
        ];
    }

    /**
     * 決済手段別の集計
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function gfGetPayKindCounts(
        string $startDate,
        string $endDate
    ) : array {
        
        return $this->pAccountingRepository->gfFetchPayKindCounts($startDate, $endDate);
    }



    /**
     * 時間帯別（1h）の集計
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function gfGetHourlyCounts(
        string $startDate,
        string $endDate
    ) : array {

        return $this->pAccountingRepository->gfFetchHourlyCounts($startDate, $endDate);
    }

    
    /**
     * 金額別の件数を集計
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function gfGetGokeiCounts(
        string $startDate,
        string $endDate
    ) : array {

        return $this->pAccountingRepository->gfFetchGokeiCounts($startDate, $endDate);
    }
}