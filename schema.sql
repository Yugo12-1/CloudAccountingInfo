-- データベースの作成
CREATE DATABASE IF NOT EXISTS cloud_accounting_matsuda_dev
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- データベースの使用 
USE cloud_accounting_matsuda_dev;

-- tbl_Administratorの作成・構造
CREATE TABLE IF NOT EXISTS tbl_Administrator (
    HospitalID VARCHAR(50) NOT NULL,
    LoginID VARCHAR(30) NOT NULL,
    LoginPassword VARCHAR(255) NOT NULL,
    AdministratorName VARCHAR(50) NULL,
    IsActive TINYINT(1) NOT NULL DEFAULT 1,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (HospitalID),
    UNIQUE KEY UK_Administrator_LoginID (LoginID)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- tbl_Accountingの作成・構造
CREATE TABLE IF NOT EXISTS tbl_Accounting (
    SID VARCHAR(50) NOT NULL,
    YMD DATE NOT NULL,
    Hhmmss TIME NOT NULL,
    KID VARCHAR(20) NOT NULL,
    Department VARCHAR(20) NULL,
    Nyugai VARCHAR(5) NULL,
    HokenKind VARCHAR(20) NULL,
    HokenGaku INT NOT NULL DEFAULT 0,
    JihiGaku INT NOT NULL DEFAULT 0,
    Gokei INT NOT NULL DEFAULT 0,
    PayKind VARCHAR(20) NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (SID),
    KEY IX_Accounting_YMD_Hhmmss (YMD, Hhmmss),
    KEY IX_Accounting_YMD_PayKind (YMD, PayKind)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- MySQL 8.0以降対応のユーザー作成構文
CREATE USER IF NOT EXISTS 'ca_matsuda_app'@'localhost'
IDENTIFIED WITH mysql_native_password BY 'matsuda';

-- データベースへの権限付与（必要に応じて実行）
GRANT ALL PRIVILEGES ON cloud_accounting_matsuda_dev.* TO 'ca_matsuda_app'@'localhost';
FLUSH PRIVILEGES;