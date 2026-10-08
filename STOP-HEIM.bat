@echo off
title Heim POS - Stop Server and Tunnel
color 0C

echo ========================================================
echo        HEIM COFFEE SHOP POS - SHUTDOWN
echo ========================================================
echo.
echo Stopping Laravel backend server, Cloudflare tunnel, and freeing port 8000...

:: Kill any process listening on port 8000
for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":8000 " ^| findstr "LISTENING"') do (
    echo Killing process listening on port 8000 (PID: %%a)...
    taskkill /F /PID %%a >nul 2>nul
)

:: Terminate php and cloudflared processes
taskkill /F /IM php.exe /T 2>NUL
taskkill /F /IM cloudflared.exe /T 2>NUL

echo.
echo [OK] Port 8000 freed and all Heim POS server processes stopped safely.
echo.
timeout /t 3 >nul
