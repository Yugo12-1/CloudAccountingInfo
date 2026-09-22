<?php

class AnalysisController {

    private AnalysisService $pAnalysisService;

    public function __construct(AnalysisService $service) {
        $this->pAnalysisService = $service;
    }



    /**
     * ダッシュボード全体の情報を取得
     *
     * @return array
     */
    public function gfGetDashboardData(
        int $year,
        int $month
    ) : array {
        return $this->pAnalysisService->gfGetDashboardData($year, $month);
    }
}