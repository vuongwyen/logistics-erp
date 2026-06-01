@echo off
chcp 65001 > nul
title NT Logistics ERP — Khoi dong may chu LAN

echo.
echo  NT LOGISTICS ERP — Dang khoi dong may chu...
echo.

PowerShell -NoProfile -ExecutionPolicy Bypass -File "%~dp0start.ps1"

echo.
echo  May chu da dung.
pause
