@echo off
chcp 65001 > nul
title NT Logistics ERP -- Bo Cai Dat

echo.
echo  Dang khoi dong bo cai NT Logistics ERP...
echo.

PowerShell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install.ps1"

echo.
if %ERRORLEVEL% NEQ 0 (
    echo  [LOI] Qua trinh cai dat that bai. Xem thong tin loi o tren.
) else (
    echo  [XONG] May chu da dung. Chay lai install.bat de khoi dong lai.
)
echo.
pause
