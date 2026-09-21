<?php

// 型チェックを厳格（Strict Mode）にする」ための宣言
declare(strict_types=1);

// このURLに対応する医療機関キー
$hospitalKey = 'clinic_matsuda';

/*
* 非公開側の共通ログインページを呼び出す
*/
require_once dirname(__DIR__, 2) . '/script/Page/login.php';