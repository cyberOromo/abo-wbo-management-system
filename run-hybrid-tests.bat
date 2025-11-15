@echo off
setlocal EnableDelayedExpansion

:: Hybrid Registration Test Suite Runner (Windows)
:: This script runs comprehensive tests for the ABO-WBO Hybrid Registration System

echo ==========================================
echo   ABO-WBO Hybrid Registration Test Suite
echo ==========================================
echo.

:: Initialize counters
set total_tests=0
set passed_tests=0
set failed_tests=0

:: Check if PHPUnit is available
if not exist "vendor\bin\phpunit.bat" (
    echo [ERROR] PHPUnit not found. Please install dependencies with 'composer install'
    exit /b 1
)

:: Create necessary directories
if not exist "storage\logs" mkdir "storage\logs"
if not exist "storage\cache\phpunit" mkdir "storage\cache\phpunit"
if not exist "storage\cache\phpunit-coverage" mkdir "storage\cache\phpunit-coverage"

echo Setting up test environment...
echo.

:: Test 1: Unit Tests for Services
echo === UNIT TESTS: Services ===
echo Running Service Unit Tests...
echo Command: vendor\bin\phpunit tests\Unit\Services --testdox
echo.

vendor\bin\phpunit tests\Unit\Services --testdox
if !errorlevel! == 0 (
    echo [PASS] Service Unit Tests PASSED
    set /a passed_tests+=1
) else (
    echo [FAIL] Service Unit Tests FAILED
    set /a failed_tests+=1
)
set /a total_tests+=1
echo.

:: Test 2: Unit Tests for Controllers
echo === UNIT TESTS: Controllers ===
echo Running Controller Unit Tests...
echo Command: vendor\bin\phpunit tests\Unit\Controllers --testdox
echo.

vendor\bin\phpunit tests\Unit\Controllers --testdox
if !errorlevel! == 0 (
    echo [PASS] Controller Unit Tests PASSED
    set /a passed_tests+=1
) else (
    echo [FAIL] Controller Unit Tests FAILED
    set /a failed_tests+=1
)
set /a total_tests+=1
echo.

:: Test 3: Integration Tests
echo === INTEGRATION TESTS ===
echo Running Integration Tests...
echo Command: vendor\bin\phpunit tests\Integration\HybridRegistration --testdox
echo.

vendor\bin\phpunit tests\Integration\HybridRegistration --testdox
if !errorlevel! == 0 (
    echo [PASS] Integration Tests PASSED
    set /a passed_tests+=1
) else (
    echo [FAIL] Integration Tests FAILED
    set /a failed_tests+=1
)
set /a total_tests+=1
echo.

:: Test 4: Complete Test Suite with Coverage
echo === FULL SUITE WITH COVERAGE ===
echo Running Full Test Suite with Coverage...
echo Command: vendor\bin\phpunit --testsuite=HybridRegistration --coverage-html=storage\logs\coverage-report --coverage-text
echo.

vendor\bin\phpunit --testsuite=HybridRegistration --coverage-html=storage\logs\coverage-report --coverage-text
if !errorlevel! == 0 (
    echo [PASS] Full Test Suite with Coverage PASSED
    set /a passed_tests+=1
) else (
    echo [FAIL] Full Test Suite with Coverage FAILED
    set /a failed_tests+=1
)
set /a total_tests+=1
echo.

:: Test 5: Performance Tests (if available)
if exist "tests\Performance\HybridRegistrationPerformanceTest.php" (
    echo === PERFORMANCE TESTS ===
    echo Running Performance Tests...
    echo Command: vendor\bin\phpunit tests\Performance --group=performance
    echo.
    
    vendor\bin\phpunit tests\Performance --group=performance
    if !errorlevel! == 0 (
        echo [PASS] Performance Tests PASSED
        set /a passed_tests+=1
    ) else (
        echo [FAIL] Performance Tests FAILED
        set /a failed_tests+=1
    )
    set /a total_tests+=1
    echo.
)

:: Summary Report
echo ==========================================
echo            TEST SUITE SUMMARY
echo ==========================================
echo.
echo Total Test Suites: !total_tests!
echo Passed: !passed_tests!
echo Failed: !failed_tests!
echo.

if !failed_tests! == 0 (
    echo [SUCCESS] ALL TESTS PASSED! 🎉
    echo The Hybrid Registration System is working correctly.
    set exit_code=0
) else (
    echo [ERROR] SOME TESTS FAILED ❌
    echo Please check the test output above for details.
    set exit_code=1
)

echo.
echo Test Reports Generated:
echo - HTML Coverage Report: storage\logs\coverage-report\index.html
echo - Text Coverage Report: storage\logs\coverage.txt
echo - JUnit Report: storage\logs\junit.xml
echo - TestDox HTML Report: storage\logs\testdox.html
echo - TestDox Text Report: storage\logs\testdox.txt
echo.

echo Test Logs Location:
echo - PHPUnit Cache: storage\cache\phpunit\
echo - Coverage Cache: storage\cache\phpunit-coverage\
echo - All Reports: storage\logs\
echo.

if !failed_tests! gtr 0 (
    echo Troubleshooting Tips:
    echo 1. Check database connection and test data setup
    echo 2. Ensure all required services are properly configured
    echo 3. Verify test environment variables in phpunit.xml
    echo 4. Check for any missing dependencies or extensions
    echo 5. Review individual test output for specific error details
    echo.
)

echo ==========================================

pause
exit /b !exit_code!