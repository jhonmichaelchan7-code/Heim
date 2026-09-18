@echo off
title Heim POS — Server & Tunnel Launcher
color 0A

echo ========================================================
echo        HEIM COFFEE SHOP POS - SERVER LAUNCHER
echo ========================================================
echo.

set "PROJECT_DIR=C:\Users\user\Documents\PROJECT"
cd /d "%PROJECT_DIR%"

echo [1/3] Checking MySQL Database...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo       [OK] MySQL service is running.
) else (
    echo       [!] MySQL is not detected. Please ensure MySQL is started in XAMPP.
)

echo.
echo [2/3] Starting Laravel Backend Server on port 8000...
start "Heim - Backend Server (Do Not Close)" cmd /k "cd /d \"%PROJECT_DIR%\" && C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000"

:: Wait 3 seconds for Laravel server to bind port
timeout /t 3 /nobreak >nul

echo.
echo [3/3] Starting Cloudflare Public Tunnel...
echo ========================================================
echo   Your live public link will appear below in a moment.
echo   Look for: https://xxxx.trycloudflare.com
echo   Share that link to your cashiers, tablets, and phones!
echo ========================================================
echo.

"C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --url http://127.0.0.1:8000

pause
