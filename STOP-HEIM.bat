@echo off
title Heim POS - Stop Server and Tunnel
color 0C

echo ========================================================
echo        HEIM COFFEE SHOP POS - SHUTDOWN
echo ========================================================
echo.
echo Stopping Laravel backend server and Cloudflare tunnel...

taskkill /F /IM php.exe /T 2>NUL
taskkill /F /IM cloudflared.exe /T 2>NUL

echo.
echo [OK] All Heim POS server processes have been stopped safely.
echo.
timeout /t 3 >nul
