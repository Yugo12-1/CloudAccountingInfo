<?php

class ConfigReader {
    
    private array $pSettings;
    
    public function __construct(string $iniFilePath) {
        
        // ファイルが存在してかつ読み書きが可能であるときのみ設定情報を読み込む
        if (is_file($iniFilePath) && is_readable($iniFilePath)) {
            $this->pSettings = parse_ini_file($iniFilePath, true);
            
        } else {
            $msg = "指定されたINIファイルのパスが存在しない、もしくは読み込むことができません: {$iniFilePath}";
            throw new Exception($msg);
        }
    }
    
    
    /**
     * セクションとキーを指定してINIファイルから情報を取得する
     * @param string $section
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function gfGetSettingsValue(
        string $section, 
        string $key,
        string $default = "") : mixed {
        
        // 変数が取得できないときはデフォルトのnullを返すます。
        if (!isset($this->pSettings[$section][$key])) {
            return $default;    
        }
        
        return $this->pSettings[$section][$key];
    }
}