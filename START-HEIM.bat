@echo off
title Heim POS - Server and Tunnel Launcher (SQLite + Cloudflare)
color 0A

echo ========================================================
echo        HEIM COFFEE SHOP POS - ALL-IN-ONE LAUNCHER
echo   (Standalone SQLite + Cloudflare Tunnel - No XAMPP Needed)
echo ========================================================
echo.

set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"
cd /d "%PROJECT_DIR%"

:: Clear conflicting scan dir environment variable for clean PHP extension loading
set "PHP_INI_SCAN_DIR="

:: Detect PHP executable
set "PHP_CMD="
if exist "C:\xampp\php\php.exe" (
    set "PHP_CMD=C:\xampp\php\php.exe"
) else (
    where php.exe >nul 2>nul
    if not errorlevel 1 (
        set "PHP_CMD=php"
    )
)

if "%PHP_CMD%"=="" (
    echo [ERROR] PHP executable was not found. Please ensure PHP is in PATH or installed at C:\xampp\php\php.exe.
    pause
    exit /b 1
)

echo [1/3] Preparing Standalone SQLite Database...
if not exist "database\database.sqlite" (
    echo       Creating database\database.sqlite file...
    type nul > "database\database.sqlite"
    echo       Migrating and seeding initial demo database...
    "%PHP_CMD%" artisan migrate:fresh --seed --force
)
echo       [OK] Standalone SQLite database ready (no MySQL or XAMPP service required!).

echo.
echo [2/3] Starting Laravel POS Server on 0.0.0.0:8000...
start "Heim - Backend Server (Do Not Close)" cmd /k "cd /d \"%PROJECT_DIR%\" && set PHP_INI_SCAN_DIR= && \"%PHP_CMD%\" artisan serve --host=0.0.0.0 --port=8000"

:: Wait for Laravel server to bind port 8000 (up to 15 seconds)
echo       Waiting for backend server to bind to port 8000...
for /l %%i in (1, 1, 15) do (
    netstat -ano | findstr ":8000 " | findstr "LISTENING" >nul
    if not errorlevel 1 goto :server_ready
    timeout /t 1 /nobreak >nul
)
:server_ready
echo       [OK] Laravel POS server is active on http://127.0.0.1:8000

echo.
echo [3/3] Starting Cloudflare Public Tunnel...
echo ========================================================
echo   ZERO-PORT-FORWARDING SECURE TUNNEL
echo   Your live public link will appear below in a moment.
echo   Look for the URL: https://*.trycloudflare.com
echo   Share that link to cashiers, tablets, phones, or managers!
echo ========================================================
echo.

set "CF_CMD="
if exist "C:\Program Files (x86)\cloudflared\cloudflared.exe" (
    set "CF_CMD=C:\Program Files (x86)\cloudflared\cloudflared.exe"
) else (
    where cloudflared.exe >nul 2>nul
    if not errorlevel 1 (
        set "CF_CMD=cloudflared"
    )
)

if "%CF_CMD%"=="" (
    echo [WARNING] cloudflared.exe not found in PATH or standard directory.
    echo Local POS is running at: http://127.0.0.1:8000
    echo To enable Cloudflare tunnel, download cloudflared from https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/
    pause
    exit /b 0
)

"%CF_CMD%" tunnel --protocol http2 --url http://127.0.0.1:8000

pause
