@echo off
cd /d "%~dp0"
title Internship System Launcher
echo ===================================================
echo   Starting Internship Management System
echo ===================================================
echo.

:: Add PHP and MySQL to PATH
set PATH=%PATH%;C:\xampp\php;C:\xampp\mysql\bin

echo 1. Checking for PHP...
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo    [ERROR] PHP not found in C:\xampp\php or system PATH.
    echo    Please install XAMPP or PHP and add it to your PATH.
    pause
    exit
)
echo    - PHP Found.

echo.
echo 2. Starting MySQL Database...
if exist "C:\xampp\mysql_start.bat" (
    start /min "" "C:\xampp\mysql_start.bat"
    echo    - MySQL start command executed.
) else (
    echo    [WARNING] Could not find C:\xampp\mysql_start.bat.
    echo    Please ensure MySQL is running manually.
)

echo.
echo 3. Waiting for Database to Initialize...
timeout /t 5 >nul

echo.
echo 3b. Updating Database Schema...
php "update_schema.php"


echo.
echo 4. Starting Web Server...
echo    - Server running at: http://localhost:8000
echo    - Document Root: %~dp0
echo    - Press Ctrl+C to stop.
echo.
echo ===================================================
echo    OPEN BROWSER TO: http://localhost:8000
echo ===================================================

start http://localhost:8000
php -S localhost:8000 -t "%~dp0"
