<?php

// ここでセッションを開始する
session_start();

$parentDir = dirname(__DIR__);

require_once $parentDir . '/Support/ConfigReader.php';
require_once $parentDir . '/Support/LogManager.php';
require_once $parentDir . '/DBManager/DBConnection.php';
require_once $parentDir . '/Authentication/AuthenticationManager.php';
require_once $parentDir . '/Repository/AdminRepository.php';
require_once $parentDir . '/Service/LoginService.php';
require_once $parentDir . '/Controller/LoginController.php';

// 必要なクラスを作成する
$iniFilePath = $parentDir . '/SettingFiles/settings.ini';
$logRootDir = $parentDir . '/Logs';

$logger = new LogManager($logRootDir);
$configReader = new ConfigReader($iniFilePath);
$dbConnect = new DBConnection($configReader);
$authenticator = new AuthenticationManager($logger);

$repository = new AdminRepository(
    $logger,
    $dbConnect
);

$service = new LoginService(
    $repository, 
    $authenticator
);
$controller = new LoginController($service);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    
    $loginID = $_POST['loginID'] ?? '';
    $loginPassword = $_POST['loginPassword'] ?? '';
    
    $controller->gfLogin($loginID, $loginPassword);
}

?>

<!DOCTYPE html>
<html>
 <head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width" />
  <title>login</title>
  <link rel="stylesheet" href="../css/login.css">
 </head>
 <body>
     <header class="site-header">
      <div class="header-content">
       <img src="../img/medifo_logo1.png" alt="製品ロゴ">
      </div>
     </header>
  <main class="login-area">
   <div class="login-content">
          <div class="login-header">
                 <h1>クラウド会計情報システム</h1>
                </div>
    <?php if (!empty($_SESSION['loginError'])): ?>
     <p class="login-error">
      <?php echo htmlspecialchars($_SESSION['loginError']); ?>
     </p>
     <?php unset($_SESSION['loginError']); ?>
    <?php endif; ?>
          <div class="login-container">
              <form action="" method="post">
               <div class="form-fields">
                   <div class="form-item" >
                    <label for="loginID">ログインID</label>
                    <input type="text" name="loginID" id="loginID" required />
                   </div>
                   <div class="form-item" >
                    <label for="loginPassword">パスワード</label>
                    <input type="password" name="loginPassword" id="loginPassword" required />
                   </div>
               </div>
               <div class="form-action" >
                <input type="submit" value="ログイン" />
               </div>
              </form>  
          </div>
      </div>
  </main>
     <footer class="site-footer">
      <small>© 2026 intequa.Co.,Ltd. All rights reserved.</small>
     </footer>
 </body>
</html>