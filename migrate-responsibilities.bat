@echo off
echo Running Shared Responsibilities Database Migration...
echo =====================================================

REM Try to find MySQL executable
set MYSQL_PATH=""
if exist "C:\xampp\mysql\bin\mysql.exe" set MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"
if exist "C:\wamp\bin\mysql\mysql8.0.31\bin\mysql.exe" set MYSQL_PATH="C:\wamp\bin\mysql\mysql8.0.31\bin\mysql.exe"
if exist "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"

if %MYSQL_PATH%=="" (
    echo Error: MySQL executable not found. Please ensure XAMPP/WAMP is installed.
    echo Trying alternative paths...
    where mysql >nul 2>&1
    if errorlevel 1 (
        echo MySQL not found in PATH either. Please:
        echo 1. Start XAMPP Control Panel
        echo 2. Start Apache and MySQL services
        echo 3. Use phpMyAdmin to run the SQL manually
        pause
        exit /b 1
    ) else (
        set MYSQL_PATH=mysql
    )
)

echo Using MySQL at: %MYSQL_PATH%
echo.

REM Execute the migration
%MYSQL_PATH% -u root -p abo_wbo_db < "database\migrations\responsibilities_schema.sql"

if errorlevel 1 (
    echo.
    echo Migration failed. Please check:
    echo 1. MySQL service is running
    echo 2. Database 'abo_wbo_db' exists
    echo 3. MySQL root password is correct
    echo.
    echo You can also copy the SQL from database\migrations\responsibilities_schema.sql
    echo and run it manually in phpMyAdmin
    pause
    exit /b 1
)

echo.
echo ✅ Migration completed successfully!
echo.
echo Next steps:
echo 1. Access the system at: http://localhost/ABO-WBO Management System/public/
echo 2. Navigate to Responsibilities section
echo 3. Initialize the 5 Core Areas if needed
echo.
pause