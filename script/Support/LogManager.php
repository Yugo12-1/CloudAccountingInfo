<?php 

class LogManager {
    
    private string $pLogRootDir;
    
    /**
     * コンストラクタに'Logs'ディレクトリのパスを指定する
     * @param string $logRootDir
     */
    public function __construct(string $logRootDir) {
        $this->pLogRootDir = $logRootDir;
    }
    
    
    // system
    /**
     * systemログをINFOレベルで出力する
     * @param string $loginID
     * @param string $message
     */
    public function gfSystemInfo(string $loginID, string $message) : void {
        $this->pfWriteSystemLog('INFO', $loginID, $message);
    }
    
    
    /**
     * systemログをERRORレベルで出力する
     * @param string $loginID
     * @param string $message
     */
    public function gfSystemError(string $loginID, string $message) : void {
        $this->pfWriteSystemLog('ERROR', $loginID, $message);
    }
    
    
    
    /**
     * hospitalログをINFOレベルで出力する
     * @param string $hospitalID
     * @param string $message
     */
    public function gfHospitalInfo(string $hospitalID, string $message) : void {
        $this->pfWriteHospitalLog('INFO', $hospitalID, $message);
    }
    
    
    /**
     * hospitalログをERRORレベルで出力する
     * @param string $hospitalID
     * @param string $message
     */
    public function gfHospitalError(string $hospitalID, string $message) : void {
        $this->pfWriteHospitalLog('ERROR', $hospitalID, $message);
    }
    
    
    
    /**
     * 実行したSQL文だけを記録する
     * @param string $hospitalID
     * @param string $message
     */
    public function gfHospitalSql(string $hospitalID, string $message) : void {
        $this->pfWriteHospitalSql('SQL', $hospitalID, $message);
    }
    
    /**
     * systemログを書き込む
     * @param string $level
     * @param string $loginID
     * @param string $message
     */
    private function pfWriteSystemLog(
        string $level,
        string $loginID,
        string $message
    ) : void {
        
        // 現在の日付
        $today = date('Ymd');
        
        // systemログを保存するディレクトリ
        $logDir = $this->pLogRootDir . '/system/';
        
        // systemログのファイルパス
        $logFile = $logDir . "system_{$today}.log";
        
        // ディレクトリが存在しない時は作成する
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // 現在の日時
        $dateTime = date('Y-m-d H:i:s');
        
        // ログメッセージを作成
        $logMessage = sprintf(
            "[%s] [%s] [loginID:%s] %s%s",
            $dateTime,
            $level,
            $loginID,
            $message,
            PHP_EOL
        );
        
        // ログファイルの作成、追記
        file_put_contents(
            $logFile,
            $logMessage,
            FILE_APPEND | LOCK_EX
        );
    }
    
    
    /**
     * hospitalログを書き込む
     * @param string $level
     * @param string $hospitalID
     * @param string $message
     */
    private function pfWriteHospitalLog(
        string $level,
        string $hospitalID,
        string $message
    ) : void {
           
        // 今日の日付
        $today = date('Ymd');
        
        // hospitalログを保存するディレクトリ
        $logDir = $this->pLogRootDir . "/hospital/{$hospitalID}/";
        
        // hospitalログのファイルパス
        $logFile = $logDir . "app_{$today}.log";
        
        // ディレクトリがなければ作成します
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // 現在の時刻
        $dateTime = date('Y-m-d H:i:s');
        
        // ログメッセージの作成
        $logMessage = sprintf(
            "[%s] [%s] %s%s",
            $dateTime,
            $level,
            $message,
            PHP_EOL
        );
        
        // ログファイルの作成と追記
        file_put_contents(
            $logFile,
            $logMessage,
            FILE_APPEND | LOCK_EX
        );
    }
    
    
    private function pfWriteHospitalSql(
        string $level,
        string $hospitalID,
        string $message
    ) : void {
        
        // 今日の日付
        $today = date('Ymd');
        
        // hospitalログを保存するディレクトリ
        $logDir = $this->pLogRootDir . "/hospital/{$hospitalID}/";
        
        // hospitalのログのファイルパス
        $logFile = $logDir . "sql_{$today}.log";
        
        // ディレクトリがなければ作成します
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // 現在の時刻
        $dateTime = date('Y-m-d H:i:s');
        
        // ログメッセージの作成
        $logMessage = sprintf(
            "[%s] [%s] %s%s",
            $dateTime,
            $level,
            $message,
            PHP_EOL
            );
        
        // ログファイルの作成と追記
        file_put_contents(
            $logFile,
            $logMessage,
            FILE_APPEND | LOCK_EX
            );
    }
}