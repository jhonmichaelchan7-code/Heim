@echo off
title Heim POS - Cloudflare Tunnel
echo ========================================================
echo   Starting Cloudflare Tunnel for Heim POS (Port 8000)
echo ========================================================
echo.
"C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --protocol http2 --url http://127.0.0.1:8000
pause
