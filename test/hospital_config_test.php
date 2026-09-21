<?php

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/Support/HospitalResolver.php';
require_once $parentDir . '/Context/HospitalContext.php';

// 今回はURLからではなく、固定値で確認する
$hospitalKey = 'clinic_matsuda';

$configDirectory = $parentDir . '/Config/Hospitals';

$resolver = new HospitalResolver($configDirectory);

$hospitalContext = $resolver->gfResolveKeyToContext($hospitalKey);

echo '医療機関ID: ' . htmlspecialchars($hospitalContext->gfGetHospitalID(), ENT_QUOTES, 'UTF-8');
echo '<br>';

echo '医療機関名: ' . htmlspecialchars($hospitalContext->gfGetDbHostName(), ENT_QUOTES, 'UTF-8');
echo '<br>';

echo 'データベース名: ' . htmlspecialchars($hospitalContext->gfGetDbName(), ENT_QUOTES, 'UTF-8');
echo '<br>';

echo 'データベースユーザー名: ' . htmlspecialchars($hospitalContext->gfGetDbUserName(), ENT_QUOTES, 'UTF-8');
echo '<br>';

echo 'データベースのパスワード: ' . htmlspecialchars($hospitalContext->gfGetDbPassword(), ENT_QUOTES, 'UTF-8');
