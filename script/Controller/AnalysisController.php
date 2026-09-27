<?php

class AnalysisController {

    private AnalysisService $pAnalysisService;

    public function __construct(AnalysisService $service) {
        $this->pAnalysisService = $service;
    }


    /**
     * ログイン済みかどうかをサービスに依頼する
     * @return bool
     */
    public function gfIsLogin() : bool {
        return $this->pAnalysisService->gfIsLogin();
    }

    
    /**
     * ログインページへ強制的に移動
     */
    public function gfGoToLoginPage() : void {
        header("Location: login.php");
        exit();
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