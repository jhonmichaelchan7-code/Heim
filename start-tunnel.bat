@echo off
title Heim POS - Cloudflare Tunnel (Zero Port-Forwarding)
color 0B

echo ========================================================
echo   HEIM POS - CLOUDFLARE SECURE TUNNEL (Port 8000)
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
    echo [ERROR] cloudflared.exe was not found.
    echo Please install Cloudflare Tunnel from:
    echo https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/
    pause
    exit /b 1
)

echo Starting tunnel on http://127.0.0.1:8000 ...
echo Look for your public URL below: https://xxxx.trycloudflare.com
echo.

"%CF_CMD%" tunnel --protocol http2 --url http://127.0.0.1:8000

pause
