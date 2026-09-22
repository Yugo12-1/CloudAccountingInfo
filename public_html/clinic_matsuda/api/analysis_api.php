<?php

// 型チェックを厳格（Strict Mode）にする」ための宣言
declare(strict_types=1);

// このURLに対応する医療機関キー
$hospitalKey = 'clinic_matsuda';

require_once dirname(__DIR__, 3) . '/script/Api/analysis_api.php';