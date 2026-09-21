<?php

class ConfigReader {
    
    private array $pSettings;
    
    public function __construct(string $iniFilePath) {
        
        // ファイルが存在して、読み込み可能かの確認
        if (!is_file($iniFilePath) || !is_readable($iniFilePath)) {
            throw new RuntimeException(
                "指定されたINIファイルが存在しない、または読み込めません: {$iniFilePath}"
            );
        }

        $settings = parse_ini_file(
            $iniFilePath,
            true,
        );

        if ($settings === false) {
            throw new RuntimeException(
                "INIファイルの解析に失敗しました: {$iniFilePath}"
            );
        }

        $this->pSettings = $settings;
    }
    
    
    /**
     * セクションとキーを指定してINIファイルから設定値を取得する
     *
     * @param string $section
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function gfGetSettingsValue(
        string $section,
        string $key,
        string $default = ""
    ): mixed {
        // 設定値が存在しない場合は、指定されたデフォルト値を返す。
        if (!isset($this->pSettings[$section][$key])) {
            return $default;
        }

        return $this->pSettings[$section][$key];
    }
}