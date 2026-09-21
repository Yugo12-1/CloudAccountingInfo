@echo off
chcp 65001 > nul

curl -X POST "http://localhost/CloudAccountingInfo/public_html/clinic_matsuda/api/upload_accounting_info.php" ^
    -H "Content-Type: application/json; charset=UTF-8" ^
    -d @data.json

echo.
pause