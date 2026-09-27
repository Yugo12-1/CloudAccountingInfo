-- 会計情報の登録
INSERT INTO tbl_accounting (
    SID,
    YMD,
    Hhmmss,
    KID,
    Department,
    HokenKind,
    Nyugai,
    HokenGaku,
    JihiGaku,
    Gokei,
    PayKind
) VALUES (
    :SID,
    :YMD,
    :Hhmmss,
    :KID,
    :Department,
    :HokenKind,
    :Nyugai,
    :HokenGaku,
    :JihiGaku,
    :Gokei,
    :PayKind
);

-- 会計情報の更新
UPDATE tbl_accounting
SET
    YMD = :YMD,
    Hhmmss = :Hhmmss,
    KID = :KID,
    Department = :Department,
    HokenKind = :HokenKind,
    Nyugai = :Nyugai,
    HokenGaku = :HokenGaku,
    JihiGaku = :JihiGaku,
    Gokei = :Gokei,
    PayKind = :PayKind
WHERE SID = :SID;