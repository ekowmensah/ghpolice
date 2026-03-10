# GHPIMS Testing Suite

## Overview

This directory contains the testing suite for the Ghana Police Information Management System (GHPIMS).

## Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Create Test Database

```sql
CREATE DATABASE ghpims_test;
```

Import the schema:
```bash
mysql -u root -p ghpims_test < ghpims.sql
```

### 3. Configure Test Environment

Update `phpunit.xml` with your test database credentials if needed.

## Running Tests

### Run All Tests
```bash
composer test
```

Or:
```bash
vendor/bin/phpunit
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Integration
vendor/bin/phpunit --testsuite Feature
```

### Run Specific Test File
```bash
vendor/bin/phpunit tests/Unit/Services/CaseServiceTest.php
```

### Run Specific Test Method
```bash
vendor/bin/phpunit --filter testGetCaseFullDetailsReturnsCompleteData
```

### Run with Coverage
```bash
vendor/bin/phpunit --coverage-html tests/coverage/html
```

Then open `tests/coverage/html/index.html` in your browser.

## Test Structure

```
tests/
├── bootstrap.php              # Test bootstrap file
├── TestCase.php              # Base test case class
├── Helpers/
│   └── TestHelper.php        # Test helper functions
├── Factories/
│   └── ModelFactory.php      # Test data factories
├── Unit/
│   ├── Services/             # Service layer tests
│   │   ├── CaseServiceTest.php
│   │   ├── PersonServiceTest.php
│   │   └── OfficerServiceTest.php
│   └── Models/               # Model tests (to be added)
├── Integration/              # Integration tests (to be added)
└── Feature/                  # Feature tests (to be added)
```

## Writing Tests

### Unit Test Example

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CaseService;
use Tests\Factories\ModelFactory;

class CaseServiceTest extends TestCase
{
    private CaseService $caseService;
    private ModelFactory $factory;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->caseService = new CaseService();
        $this->factory = new ModelFactory($this->db);
    }
    
    public function testGetCaseFullDetails(): void
    {
        // Arrange
        $caseId = $this->factory->createCase();
        
        // Act
        $result = $this->caseService->getCaseFullDetails($caseId);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($caseId, $result['id']);
    }
}
```

## Test Helpers

### ModelFactory

Creates test data in the database:

```php
$factory = new ModelFactory($this->db);

// Create test user
$userId = $factory->createUser();

// Create test person
$personId = $factory->createPerson([
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

// Create test case
$caseId = $factory->createCase([
    'case_priority' => 'High'
]);
```

### TestHelper

Provides utility functions:

```php
use Tests\Helpers\TestHelper;

// Generate random data
$email = TestHelper::randomEmail();
$phone = TestHelper::randomPhone();
$ghanaCard = TestHelper::randomGhanaCard();
$caseNumber = TestHelper::randomCaseNumber();

// Create test data arrays
$userData = TestHelper::createUserData(['username' => 'testuser']);
$personData = TestHelper::createPersonData();
$caseData = TestHelper::createCaseData();
```

## Test Isolation

All tests run in database transactions that are automatically rolled back after each test. This ensures:
- Tests don't affect each other
- Database remains clean
- Tests can run in any order

## Current Test Coverage

### Services Tested:
- ✅ CaseService (8 tests)
- ✅ PersonService (8 tests)
- ✅ OfficerService (7 tests)

### Total Tests: 23 tests

### Target Coverage: 80%+

## Next Steps

1. Add more service tests:
   - EvidenceService
   - OperationService
   - ReportingService
   - AssetService

2. Add model tests:
   - CaseModel
   - Person
   - Officer
   - Evidence

3. Add integration tests:
   - Case registration workflow
   - Evidence chain of custody
   - Officer assignment workflow

4. Add feature tests:
   - User login
   - Case creation
   - Person search
   - Report generation

## Continuous Integration

Tests should be run automatically on:
- Every commit (pre-commit hook)
- Every pull request
- Before deployment

## Troubleshooting

### Tests Failing Due to Database

Ensure test database exists and schema is up to date:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ghpims_test"
mysql -u root -p ghpims_test < ghpims.sql
```

### Permission Issues

Ensure test database user has proper permissions:
```sql
GRANT ALL PRIVILEGES ON ghpims_test.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Autoload Issues

Regenerate autoload files:
```bash
composer dump-autoload
```

## Best Practices

1. **Follow AAA Pattern**: Arrange, Act, Assert
2. **One assertion per test** (when possible)
3. **Use descriptive test names**: `testMethodName_Scenario_ExpectedResult`
4. **Keep tests independent**: Don't rely on test execution order
5. **Use factories**: Don't create test data manually
6. **Clean up**: Transactions handle this automatically
7. **Test edge cases**: Null values, empty arrays, invalid inputs
8. **Mock external dependencies**: Don't make real API calls

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Testing Best Practices](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
- [Test-Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)
