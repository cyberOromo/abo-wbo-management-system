# Hybrid Registration System Testing Guide

## Overview

This guide covers the comprehensive testing suite for the ABO-WBO Hybrid Registration System. The testing framework ensures all components work correctly individually and as an integrated system.

## Test Structure

### Test Organization
```
tests/
├── Unit/
│   ├── Services/
│   │   ├── HybridRegistrationServiceTest.php
│   │   ├── InternalEmailGeneratorTest.php
│   │   └── ApprovalWorkflowServiceTest.php
│   └── Controllers/
│       └── HybridRegistrationControllerTest.php
├── Integration/
│   └── HybridRegistration/
│       └── HybridRegistrationWorkflowTest.php
├── Feature/
│   └── (Future feature tests)
└── Support/
    └── (Test utilities and fixtures)
```

## Test Coverage

### Unit Tests

#### 1. HybridRegistrationServiceTest
**Purpose**: Tests the core registration service functionality
**Coverage**:
- ✅ Email verification code generation and validation
- ✅ Registration workflow management
- ✅ User account creation
- ✅ Status tracking and reporting
- ✅ Error handling and edge cases
- ✅ Database operations and data integrity

**Key Test Methods**:
- `testGenerateVerificationCode()` - Validates 6-digit code generation
- `testInitiateEmailVerification()` - Tests email verification initiation
- `testVerifyEmailWithValidCode()` - Tests successful email verification
- `testSubmitRegistrationForm()` - Tests form submission and validation
- `testCreateUserAccount()` - Tests user account creation process
- `testGetRegistrationStatistics()` - Tests reporting functionality

#### 2. InternalEmailGeneratorTest
**Purpose**: Tests internal email address generation system
**Coverage**:
- ✅ Email format generation (position.hierarchy.firstname.lastname@abo-wbo.org)
- ✅ Hierarchy path resolution
- ✅ Email collision detection and resolution
- ✅ Name sanitization and special character handling
- ✅ Database integration and storage
- ✅ Validation and error handling

**Key Test Methods**:
- `testGenerateBasicEmailFormat()` - Tests standard email format
- `testGenerateEmailWithHierarchyPath()` - Tests nested hierarchy handling
- `testHandleEmailCollision()` - Tests collision resolution with numbering
- `testGenerateEmailWithSpecialCharacters()` - Tests name sanitization
- `testStoreEmailInDatabase()` - Tests database integration

#### 3. ApprovalWorkflowServiceTest
**Purpose**: Tests the hierarchical approval workflow system
**Coverage**:
- ✅ Workflow initiation for different hierarchy levels
- ✅ Approval and rejection processing
- ✅ Escalation handling after timeouts
- ✅ Authorization and permission checking
- ✅ Notification integration
- ✅ Workflow statistics and reporting

**Key Test Methods**:
- `testInitiateWorkflowForGurmuLevel()` - Tests Gurmu-level workflow
- `testProcessApproval()` - Tests approval processing
- `testProcessRejection()` - Tests rejection handling
- `testApprovalEscalation()` - Tests timeout escalation
- `testGetPendingApprovalsForUser()` - Tests user-specific approvals

#### 4. HybridRegistrationControllerTest
**Purpose**: Tests the main controller orchestrating the registration process
**Coverage**:
- ✅ HTTP request handling and validation
- ✅ Service integration and coordination
- ✅ JSON API responses
- ✅ Admin dashboard functionality
- ✅ Security measures (CSRF, XSS, SQL injection prevention)
- ✅ Error handling and user feedback

**Key Test Methods**:
- `testInitiateEmailVerification()` - Tests email verification endpoint
- `testCompleteRegistration()` - Tests full registration flow
- `testAdminDashboard()` - Tests admin interface
- `testProcessApproval()` - Tests approval processing endpoint
- `testEmailVerificationInputValidation()` - Tests input validation

### Integration Tests

#### HybridRegistrationWorkflowTest
**Purpose**: Tests the complete end-to-end registration workflow
**Coverage**:
- ✅ Complete registration flow from email verification to user account creation
- ✅ Multi-phase workflow coordination
- ✅ Database consistency across all operations
- ✅ Service integration and data flow
- ✅ Performance testing with multiple registrations
- ✅ Concurrent registration handling
- ✅ Error recovery and rollback scenarios

**Key Test Methods**:
- `testCompleteHybridRegistrationWorkflow()` - Full end-to-end test
- `testEmailVerificationCodeExpiration()` - Tests timeout handling
- `testApprovalWorkflowEscalation()` - Tests escalation process
- `testRegistrationRejectionWorkflow()` - Tests rejection flow
- `testEmailCollisionHandling()` - Tests collision resolution
- `testPerformanceWithMultipleRegistrations()` - Performance testing

## Running Tests

### Prerequisites
1. **PHP 8.2+** with required extensions
2. **Composer** dependencies installed
3. **PHPUnit 10.4+** 
4. **Database** connection configured for testing
5. **Test environment** variables set in `phpunit.xml`

### Quick Start

#### Windows
```cmd
# Run all hybrid registration tests
run-hybrid-tests.bat

# Run specific test suites
vendor\bin\phpunit tests\Unit\Services --testdox
vendor\bin\phpunit tests\Integration --testdox
```

#### Linux/macOS
```bash
# Make script executable
chmod +x run-hybrid-tests.sh

# Run all hybrid registration tests
./run-hybrid-tests.sh

# Run specific test suites
vendor/bin/phpunit tests/Unit/Services --testdox
vendor/bin/phpunit tests/Integration --testdox
```

### Individual Test Execution

```bash
# Run specific test class
vendor/bin/phpunit tests/Unit/Services/HybridRegistrationServiceTest.php

# Run specific test method
vendor/bin/phpunit --filter testCompleteHybridRegistrationWorkflow

# Run with coverage
vendor/bin/phpunit --coverage-html storage/logs/coverage-report

# Run only integration tests
vendor/bin/phpunit --testsuite Integration
```

## Test Configuration

### PHPUnit Configuration (`phpunit.xml`)
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_DRIVER" value="sync"/>
    <env name="MAIL_DRIVER" value="array"/>
</php>
```

### Test Database Setup
The integration tests use a separate test database to avoid affecting production data. The test environment automatically:
- Creates necessary test tables
- Inserts test data for hierarchies and positions  
- Creates test approver users
- Cleans up data after each test

## Test Data Management

### Test Fixtures
- **Test Email**: `integration.test@example.com`
- **Test Hierarchies**: Gurmu (ID: 1), Gamta (ID: 2)
- **Test Positions**: Member (ID: 1), Pastor (ID: 2)
- **Test Approver**: User ID 999 with admin privileges

### Data Cleanup
All tests implement proper cleanup in `tearDown()` methods:
```php
private function cleanupTestData(): void
{
    $this->database->query("DELETE FROM approval_workflows WHERE registration_id IN (...)");
    $this->database->query("DELETE FROM internal_emails WHERE email LIKE '%test%'");
    $this->database->query("DELETE FROM pending_registrations WHERE email = ?", [$this->testEmail]);
}
```

## Performance Testing

### Benchmarks
- **Email Verification**: < 0.1 seconds per request
- **Registration Submission**: < 0.5 seconds per request
- **Email Generation**: < 0.2 seconds per request
- **Workflow Processing**: < 0.3 seconds per request
- **Bulk Operations**: < 1.0 second per registration average

### Load Testing
The integration tests include performance validation:
```php
public function testPerformanceWithMultipleRegistrations()
{
    $registrationCount = 10;
    $startTime = microtime(true);
    
    // Process multiple registrations
    for ($i = 1; $i <= $registrationCount; $i++) {
        // ... registration logic
    }
    
    $avgTime = (microtime(true) - $startTime) / $registrationCount;
    $this->assertLessThan(1.0, $avgTime); // < 1 second average
}
```

## Security Testing

### Coverage Areas
- **Input Validation**: All user inputs validated and sanitized
- **SQL Injection Prevention**: Parameterized queries throughout
- **XSS Prevention**: Output escaping and validation
- **CSRF Protection**: Token validation on state-changing operations
- **Authentication**: Admin function access control
- **Authorization**: Role-based approval permissions

### Security Test Examples
```php
public function testSqlInjectionPrevention()
{
    $maliciousInput = "test@example.com'; DROP TABLE users; --";
    $result = $this->service->initiateEmailVerification($maliciousInput);
    $this->assertFalse($result['success']);
}

public function testXssPreventionInOutput()
{
    $xssPayload = '<script>alert("xss")</script>';
    $result = $this->controller->processInput(['name' => $xssPayload]);
    $this->assertStringNotContainsString('<script>', $result);
}
```

## Coverage Reports

### Generated Reports
- **HTML Coverage Report**: `storage/logs/coverage-report/index.html`
- **Text Coverage Report**: `storage/logs/coverage.txt`
- **JUnit XML Report**: `storage/logs/junit.xml`
- **TestDox Reports**: `storage/logs/testdox.html` and `storage/logs/testdox.txt`

### Coverage Targets
- **Services**: > 95% line coverage
- **Controllers**: > 90% line coverage
- **Integration**: > 85% workflow coverage
- **Overall System**: > 90% coverage

## Troubleshooting

### Common Issues

#### 1. Database Connection Errors
```bash
# Check database configuration
php -r "print_r(PDO::getAvailableDrivers());"

# Verify test database setup
vendor/bin/phpunit --debug tests/Integration/HybridRegistration/HybridRegistrationWorkflowTest.php::testCompleteHybridRegistrationWorkflow
```

#### 2. Missing Dependencies
```bash
# Install missing extensions
composer install --dev

# Check PHP extensions
php -m | grep -E "(pdo|sqlite|mysqli)"
```

#### 3. Permission Issues
```bash
# Fix storage permissions
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

#### 4. Memory or Time Limits
```php
// In phpunit.xml
<ini name="memory_limit" value="512M"/>
<ini name="max_execution_time" value="120"/>
```

### Debug Mode
```bash
# Run with debug output
vendor/bin/phpunit --debug --verbose

# Run with stack traces
vendor/bin/phpunit --debug --verbose --stop-on-failure
```

## Continuous Integration

### GitHub Actions Integration
```yaml
# .github/workflows/hybrid-registration-tests.yml
name: Hybrid Registration Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: ./run-hybrid-tests.sh
```

## Best Practices

### Writing Tests
1. **Follow AAA Pattern**: Arrange, Act, Assert
2. **Use Descriptive Names**: `testEmailVerificationWithValidCode()`
3. **Test Edge Cases**: Invalid inputs, boundary conditions
4. **Mock External Dependencies**: Database, email services
5. **Clean Up Resources**: Proper `tearDown()` implementation
6. **Isolated Tests**: Each test should be independent

### Test Maintenance
1. **Regular Updates**: Keep tests current with code changes
2. **Performance Monitoring**: Track test execution times
3. **Coverage Monitoring**: Maintain high coverage levels
4. **Documentation**: Keep test documentation updated
5. **Refactoring**: Improve test code quality regularly

## Conclusion

This comprehensive testing suite ensures the Hybrid Registration System is:
- ✅ **Functionally Correct**: All features work as designed
- ✅ **Performant**: Meets performance requirements
- ✅ **Secure**: Protected against common vulnerabilities  
- ✅ **Reliable**: Handles errors gracefully
- ✅ **Maintainable**: Easy to modify and extend
- ✅ **Well-Documented**: Clear test coverage and documentation

The testing framework provides confidence in the system's reliability and facilitates safe development and deployment of new features.