<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/Context/HospitalContext.php';


class HospitalResolver {
    
    private string $pConfigDirectory;

    // 各医療機関のINIファイルが格納されているディレクトリを指定
    public function __construct(string $configDirectory) {
        
        // 末尾に区切り文字が付いていた場合は取り除く
        $this->pConfigDirectory = rtrim(
            $configDirectory,
            "/\\"
        );
    }


    /**
     * 医療機関キーから医療機関情報を解決する
     *
     * @param string $hospitalKey
     * @return HospitalContext
     */
    public function gfResolveKeyToContext(string $hospitalKey) : HospitalContext {
        // iniファイルのパス
        $iniFilePath = $this->pfGetIniFilePath($hospitalKey);

        // ConfigReaderインスタンスの生成
        $configReader = new ConfigReader($iniFilePath);

        // Contextの生成
        return $this->pfCreateContext($hospitalKey, $configReader);
    }

    
    /**
     * 医療機関keyからINIファイルのパスを作成
     *
     * @param string $hospitalKey
     * @return string
     */
    private function pfGetIniFilePath(string $hospitalKey) : string {

        if (!preg_match('/\A[a-z0-9_]{1,50}\z/',$hospitalKey)) {
            throw new RuntimeException(
                '医療機関キーが不正です'
            );
        }

        return
            $this->pConfigDirectory .
            DIRECTORY_SEPARATOR .        // ディレクトリの区切り文字(windows or linux)
            $hospitalKey .
            '.ini';
    }


    /**
     * 必須項目の""空白チェック
     *
     * @param ConfigReader $configReader
     * @param string $section
     * @param string $key
     * @return string
     */
    private function pfEmptyChecker(
        ConfigReader $configReader,
        string $section,
        string $key
    ) : string {
        
        $value = $configReader->gfGetSettingsValue(
            $section,
            $key,
            ''
        );

        if (trim($value) === '') {
            throw new RuntimeException(
                "必須設定が不足しています: {$section}.{$key}"
            );
        }

        return $value;
    }


    /**
     * 該当の医療機関のINIファイルの情報を保持する"Context"インスタンスの生成
     *
     * @param string $hospitalKey
     * @param ConfigReader $configReader
     * @return HospitalContext
     */
    private function pfCreateContext(
        string $hospitalKey, 
        ConfigReader $configReader
        ) : HospitalContext {
        
        $hospitalContext = new HospitalContext(
            $hospitalKey,

            $this->pfEmptyChecker(
                $configReader,
                'hospital',
                'hospitalId'
            ),

            $this->pfEmptyChecker(
                $configReader,
                'hospital',
                'displayName'
            ),

            // グループIDは現状必須ではない
            $configReader->gfGetSettingsValue(
                'hospital',
                'groupId',
                ''
            ),

            $this->pfEmptyChecker(
                $configReader,
                'database',
                'hostName'
            ),

            $this->pfEmptyChecker(
                $configReader,
                'database',
                'dbName'
            ),

            $this->pfEmptyChecker(
                $configReader,
                'database',
                'userName'
            ),

            $this->pfEmptyChecker(
                $configReader,
                'database',
                'password'
            ),

            // DBの文字コード設定は必須ではない
            $configReader->gfGetSettingsValue(
                'database',
                'charset',
                'utf8mb4'
            )
        );

        return $hospitalContext;
    }
}