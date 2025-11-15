#!/bin/bash

# Hybrid Registration Test Suite Runner
# This script runs comprehensive tests for the ABO-WBO Hybrid Registration System

echo "=========================================="
echo "  ABO-WBO Hybrid Registration Test Suite"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to run tests and check results
run_test_suite() {
    local suite_name=$1
    local suite_command=$2
    
    print_status $BLUE "Running $suite_name..."
    echo "Command: $suite_command"
    echo ""
    
    if eval $suite_command; then
        print_status $GREEN "✓ $suite_name PASSED"
        return 0
    else
        print_status $RED "✗ $suite_name FAILED"
        return 1
    fi
}

# Check if PHPUnit is available
if ! command -v vendor/bin/phpunit &> /dev/null; then
    print_status $RED "PHPUnit not found. Please install dependencies with 'composer install'"
    exit 1
fi

# Create necessary directories
mkdir -p storage/logs
mkdir -p storage/cache/phpunit
mkdir -p storage/cache/phpunit-coverage

# Initialize test counters
total_tests=0
passed_tests=0
failed_tests=0

echo "Setting up test environment..."
echo ""

# Test 1: Unit Tests for Services
print_status $YELLOW "=== UNIT TESTS: Services ==="
if run_test_suite "Service Unit Tests" "vendor/bin/phpunit tests/Unit/Services --testdox"; then
    ((passed_tests++))
else
    ((failed_tests++))
fi
((total_tests++))
echo ""

# Test 2: Unit Tests for Controllers
print_status $YELLOW "=== UNIT TESTS: Controllers ==="
if run_test_suite "Controller Unit Tests" "vendor/bin/phpunit tests/Unit/Controllers --testdox"; then
    ((passed_tests++))
else
    ((failed_tests++))
fi
((total_tests++))
echo ""

# Test 3: Integration Tests
print_status $YELLOW "=== INTEGRATION TESTS ==="
if run_test_suite "Integration Tests" "vendor/bin/phpunit tests/Integration/HybridRegistration --testdox"; then
    ((passed_tests++))
else
    ((failed_tests++))
fi
((total_tests++))
echo ""

# Test 4: Complete Test Suite with Coverage
print_status $YELLOW "=== FULL SUITE WITH COVERAGE ==="
if run_test_suite "Full Test Suite with Coverage" "vendor/bin/phpunit --testsuite=HybridRegistration --coverage-html=storage/logs/coverage-report --coverage-text"; then
    ((passed_tests++))
else
    ((failed_tests++))
fi
((total_tests++))
echo ""

# Test 5: Performance Tests (if available)
if [ -f "tests/Performance/HybridRegistrationPerformanceTest.php" ]; then
    print_status $YELLOW "=== PERFORMANCE TESTS ==="
    if run_test_suite "Performance Tests" "vendor/bin/phpunit tests/Performance --group=performance"; then
        ((passed_tests++))
    else
        ((failed_tests++))
    fi
    ((total_tests++))
    echo ""
fi

# Summary Report
print_status $BLUE "=========================================="
print_status $BLUE "           TEST SUITE SUMMARY"
print_status $BLUE "=========================================="
echo ""
echo "Total Test Suites: $total_tests"
print_status $GREEN "Passed: $passed_tests"
print_status $RED "Failed: $failed_tests"
echo ""

if [ $failed_tests -eq 0 ]; then
    print_status $GREEN "🎉 ALL TESTS PASSED! 🎉"
    print_status $GREEN "The Hybrid Registration System is working correctly."
    exit_code=0
else
    print_status $RED "❌ SOME TESTS FAILED"
    print_status $YELLOW "Please check the test output above for details."
    exit_code=1
fi

echo ""
print_status $BLUE "Test Reports Generated:"
echo "- HTML Coverage Report: storage/logs/coverage-report/index.html"
echo "- Text Coverage Report: storage/logs/coverage.txt"
echo "- JUnit Report: storage/logs/junit.xml"
echo "- TestDox HTML Report: storage/logs/testdox.html"
echo "- TestDox Text Report: storage/logs/testdox.txt"
echo ""

print_status $BLUE "Test Logs Location:"
echo "- PHPUnit Cache: storage/cache/phpunit/"
echo "- Coverage Cache: storage/cache/phpunit-coverage/"
echo "- All Reports: storage/logs/"
echo ""

if [ $failed_tests -gt 0 ]; then
    print_status $YELLOW "Troubleshooting Tips:"
    echo "1. Check database connection and test data setup"
    echo "2. Ensure all required services are properly configured"
    echo "3. Verify test environment variables in phpunit.xml"
    echo "4. Check for any missing dependencies or extensions"
    echo "5. Review individual test output for specific error details"
    echo ""
fi

print_status $BLUE "=========================================="

exit $exit_code