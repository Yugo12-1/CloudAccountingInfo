
CREATE DATABASE cloud_accounting_matsuda_dev
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;


CREATE TABLE tbl_Administrator (
    AdministratorID VARCHAR(50) NOT NULL,
    LoginID VARCHAR(30) NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    AdministratorName VARCHAR(50) NULL,
    IsActive TINYINT(1) NOT NULL DEFAULT 1,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (AdministratorID),
    UNIQUE KEY UK_Administrator_LoginID (LoginID)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;



CREATE TABLE tbl_Accounting (
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
  


CREATE USER 'ca_matsuda_app'@'localhost'
IDENTIFIED BY 'matsuda';


