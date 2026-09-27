-- 決済手段別の件数を取得
SELECT
    PayKind,
    COUNT(*) AS count
FROM tbl_accounting
WHERE PayKind IS NOT NULL
    AND YMD >= :startDate
    AND YMD < :endDate
GROUP BY PayKind
ORDER BY PayKind;


-- 時間帯別の件数を取得
SELECT
    HOUR(Hhmmss) as hour,
    COUNT(*) as count
FROM tbl_accounting
WHERE YMD >= :startDate
    AND YMD < :endDate
GROUP BY HOUR(Hhmmss)
ORDER BY hour;


-- 金額別の件数を取得
-- 金額ごとの分類結果をCTEとして作成
WITH Categorized AS (
    SELECT
        Gokei,
        -- SortOrderという数値列を作る
        CASE
            WHEN Gokei <= 1000 THEN 1
            WHEN Gokei <= 3000 THEN 2
            WHEN Gokei <= 5000 THEN 3
            WHEN Gokei <= 10000 THEN 4
            ELSE 5
        END AS SortOrder,
        -- GokeiRangeという文字列を作る
        CASE
            WHEN Gokei <= 1000 THEN '～1,000円'
            WHEN Gokei <= 3000 THEN '1,001円～3,000円'
            WHEN Gokei <= 5000 THEN '3,001円～5,000円'
            WHEN Gokei <= 10000 THEN '5,001円～10,000円'
            ELSE '10,001円～'
        END AS GokeiRange
    FROM tbl_accounting
    WHERE Gokei IS NOT NULL
        AND YMD >= :startDate
        AND YMD < :endDate
)
-- あとは"Categorized"に対してSQLを実行する
SELECT
    SortOrder,
    GokeiRange,
    COUNT(*) As count
FROM Categorized
GROUP BY SortOrder, GokeiRange
ORDER BY SortOrder;