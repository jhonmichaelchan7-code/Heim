@echo off
title Heim POS - Fresh Demo Database Setup
color 0B

echo ========================================================
echo        HEIM POS - FRESH DEMO DATABASE SETUP
echo ========================================================
echo.
echo This script will reset the database for client deployment/demo:
echo   - Cleans all previous test orders and shift histories
echo   - Pre-seeds sample menu, drinks, recipes, and ingredients
echo   - Seeds default Branch and default POS terminal settings
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
echo [1/3] Running fresh migrations and seeding sample menu...
cd /d "%~dp0"
if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" artisan migrate:fresh --seed
) else (
    php artisan migrate:fresh --seed
)

if %ERRORLEVEL% neq 0 (
    echo.
    echo [ERROR] Failed to run fresh database migrations.
    echo Please make sure MySQL is running in XAMPP!
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo [2/3] Clearing compiled view and system caches...
if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" artisan view:clear
    "C:\xampp\php\php.exe" artisan cache:clear
    "C:\xampp\php\php.exe" artisan config:clear
) else (
    php artisan view:clear
    php artisan cache:clear
    php artisan config:clear
)

echo.
echo ========================================================
echo   FRESH DEMO DATABASE DEPLOYED SUCCESSFULLY!
echo ========================================================
echo.
echo Ready for client demo / bug testing.
echo.
echo Default Demo Accounts (Password: 'password' for all):
echo   - Owner:      owner@coffee.com
echo   - Manager:    manager@coffee.com
echo   - Supervisor: supervisor@coffee.com
echo   - Cashier:    cashier@coffee.com (anna)
echo.
echo Fresh Data Summary:
echo   - Orders:      0 (Fresh clean slate)
echo   - Shifts:      0 (Start your demo shift on POS)
echo   - Menu Items:  12 drinks with recipes and ingredients
echo   - Categories:  5 categories (Hot, Iced, Frappe, etc.)
echo.
pause
