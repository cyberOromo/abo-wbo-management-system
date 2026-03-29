@echo off
REM ============================================================================
REM STAGING DATABASE IMPORT SCRIPT (Windows)
REM Imports complete schema and data to staging.j-abo-wbo.org
REM ============================================================================

echo ============================================================================
echo ABO-WBO STAGING DATABASE IMPORT
echo ============================================================================
echo.

REM Note: Update these credentials before running
set DB_HOST=localhost
set DB_NAME=jabowbo_abo_staging
set DB_USER=jabowbo_abo_user
set DB_PASS=your_password_here

echo WARNING: This will DROP ALL TABLES in %DB_NAME%
echo.
set /p CONFIRM="Are you sure you want to continue? (yes/no): "
if /i not "%CONFIRM%"=="yes" (
    echo Import cancelled.
    exit /b 1
)
echo.

REM Step 1: Drop all existing tables
echo Step 1: Dropping all existing tables...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < drop_all_tables.sql
if errorlevel 1 (
    echo [ERROR] Failed to drop tables
    exit /b 1
)
echo [OK] All tables dropped successfully
echo.

REM Step 2: Import schema
echo Step 2: Importing schema ^(38 tables + 3 views^)...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < schema.sql
if errorlevel 1 (
    echo [ERROR] Failed to import schema
    exit /b 1
)
echo [OK] Schema imported successfully
echo.

REM Step 3: Import data
echo Step 3: Importing organizational data...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < comprehensive_data_insertion.sql
if errorlevel 1 (
    echo [ERROR] Failed to import data
    exit /b 1
)
echo [OK] Data imported successfully
echo.

REM Step 4: Verification
echo Step 4: Verifying import...
echo.

REM Count records
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% -e "SELECT 'Godinas:' as Item, COUNT(*) as Count FROM godinas UNION ALL SELECT 'Gamtas:', COUNT(*) FROM gamtas UNION ALL SELECT 'Gurmus:', COUNT(*) FROM gurmus UNION ALL SELECT 'Positions:', COUNT(*) FROM positions UNION ALL SELECT 'Individual Responsibilities:', COUNT(*) FROM individual_responsibilities UNION ALL SELECT 'Shared Responsibilities:', COUNT(*) FROM shared_responsibilities UNION ALL SELECT 'General Responsibilities:', COUNT(*) FROM responsibilities UNION ALL SELECT 'Users:', COUNT(*) FROM users;"

echo.
echo ============================================================================
echo IMPORT COMPLETE!
echo ============================================================================
echo.
echo Staging Site: https://staging.j-abo-wbo.org
echo Login: admin@abo-wbo.org
echo Password: admin123
echo.
echo Next steps:
echo 1. Test login functionality
echo 2. Verify user_type based dashboard routing
echo 3. Test member registration at Gurmu level
echo 4. Test executive position assignment
echo.
pause
