@echo off
title Heim POS - Fresh Demo Database Setup (SQLite)
color 0B

echo ========================================================
echo        HEIM POS - FRESH DEMO DATABASE SETUP
echo     (Standalone SQLite - Bangkal & San Rafael Branches)
echo ========================================================
echo.
echo This script will reset the database for client deployment/demo:
echo   - Cleans all previous test orders and shift histories
echo   - Seeds Multi-Branch Structure (Bangkal Branch & San Rafael Branch)
echo   - Seeds Complete Menu with Dynamic Modifier BOM Ingredients
echo   - Prepares Order Channels (Dine-In, Takeout, Grab Delivery)
echo   - Sets up Manager/Owner Authorization & Cash-Only Refund Security
echo   - Sets up default demo accounts (Owner, Manager, Supervisor, Cashier)
echo.
echo WARNING: All existing transactions will be wiped clean!
echo.
set /p CONFIRM="Are you sure you want to proceed? (Y/N): "
if /i not "%CONFIRM%"=="Y" (
    echo.
    echo Operation cancelled by user.
    pause
    exit /b
)

echo.
set "PROJECT_DIR=%~dp0"
if "%PROJECT_DIR:~-1%"=="\" set "PROJECT_DIR=%PROJECT_DIR:~0,-1%"
cd /d "%PROJECT_DIR%"

set "PHP_INI_SCAN_DIR="

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
    echo [ERROR] PHP executable was not found.
    pause
    exit /b 1
)

:: Ensure database file exists
if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
)

echo [1/3] Running fresh migrations and seeding sample menu & branches...
"%PHP_CMD%" artisan migrate:fresh --seed --force

if %ERRORLEVEL% neq 0 (
    echo.
    echo [ERROR] Failed to run fresh database migrations.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo [2/3] Clearing compiled view and system caches...
"%PHP_CMD%" artisan view:clear
"%PHP_CMD%" artisan cache:clear
"%PHP_CMD%" artisan config:clear

echo.
echo ========================================================
echo   FRESH DEMO DATABASE DEPLOYED SUCCESSFULLY!
echo ========================================================
echo.
echo Ready for client demo / evaluation.
echo.
echo Default Demo Accounts (Password: 'password' for all):
echo   - Owner:      owner@coffee.com
echo   - Manager:    manager@coffee.com
echo   - Supervisor: supervisor@coffee.com
echo   - Cashier:    cashier@coffee.com (anna)
echo.
echo Fresh Data Summary:
echo   - Database:   Standalone SQLite (database\database.sqlite)
echo   - Branches:   Bangkal Branch (BNG-01) & San Rafael Branch (SRF-02)
echo   - Channels:   Dine-In, Takeout, Grab Delivery
echo   - Inventory:  Raw Ingredients with Dynamic BOM modifier deductions
echo   - Security:   Manager/Owner approval credentials for refunds & voids
echo.
pause
