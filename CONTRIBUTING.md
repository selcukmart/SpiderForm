# Contributing to FormGenerator

Thank you for your interest in contributing to FormGenerator! This document provides guidelines and instructions for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Documentation](#documentation)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Enhancements](#suggesting-enhancements)

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and help them get started
- Focus on constructive feedback
- Respect differing viewpoints and experiences

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/FormGenerator.git`
3. Add upstream remote: `git remote add upstream https://github.com/selcukmart/FormGenerator.git`
4. Create a feature branch: `git checkout -b feature/your-feature-name`

## Development Setup

### Requirements

- PHP 8.1 or higher
- Composer
- PHPUnit 10+

### Installation

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/html
```

## Coding Standards

### PHP Standards

- **PSR-12**: All code MUST follow PSR-12 coding standards
- **Strict Types**: Always use `declare(strict_types=1);`
- **Type Hints**: Use type hints for all parameters and return types
- **PHP 8.1+ Features**: Utilize enums, readonly properties, match expressions

### Code Style

```php
<?php

declare(strict_types=1);

namespace FormGenerator\V2\Example;

use FormGenerator\V2\Contracts\InputType;

class ExampleClass
{
    private readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function exampleMethod(string $param): string
    {
        return match ($param) {
            'option1' => 'Result 1',
            'option2' => 'Result 2',
            default => 'Default result',
        };
    }
}
```

### Naming Conventions

- **Classes**: PascalCase (e.g., `FormBuilder`, `InputBuilder`)
- **Methods**: camelCase (e.g., `addText()`, `setTheme()`)
- **Variables**: camelCase (e.g., `$formName`, `$inputType`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `MODE_CASCADE`, `MAX_ROWS`)
- **Files**: Match class name (e.g., `FormBuilder.php`)

### Documentation

- **PHPDoc**: All classes, methods, and properties MUST have PHPDoc comments
- **Inline Comments**: Use sparingly, only when code is not self-explanatory
- **README**: Update relevant documentation when adding features

Example PHPDoc:

```php
/**
 * Add a text input to the form
 *
 * @param string $name Field name
 * @param string|null $label Optional label text
 * @return InputBuilder Returns InputBuilder for method chaining
 */
public function addText(string $name, ?string $label = null): InputBuilder
{
    return $this->createInput($name, InputType::TEXT, $label);
}
```

## Testing

### Writing Tests

- **Test Coverage**: Aim for 80%+ code coverage
- **Unit Tests**: Place in `tests/Unit/`
- **Integration Tests**: Place in `tests/Integration/`
- **Feature Tests**: Place in `tests/Feature/`

### Test Structure

```php
<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Unit\Example;

use FormGenerator\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class ExampleTest extends TestCase
{
    #[Test]
    public function it_does_something(): void
    {
        // Arrange
        $expected = 'result';

        // Act
        $actual = someFunction();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    #[DataProvider('exampleDataProvider')]
    public function it_handles_various_inputs(string $input, string $expected): void
    {
        $actual = processInput($input);
        $this->assertEquals($expected, $actual);
    }

    public static function exampleDataProvider(): array
    {
        return [
            ['input1', 'expected1'],
            ['input2', 'expected2'],
        ];
    }
}
```

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Unit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/html

# Run specific test file
vendor/bin/phpunit tests/Unit/Builder/FormBuilderTest.php
```

## Pull Request Process

### Before Submitting

1. **Tests**: Ensure all tests pass
2. **Coverage**: Maintain or improve code coverage
3. **Standards**: Run code style checkers
4. **Documentation**: Update relevant docs
5. **Commits**: Use clear, descriptive commit messages

### Commit Message Format

```
type: Brief description (max 72 characters)

Detailed explanation of what changed and why.

- Bullet points for multiple changes
- Reference issues with #issue-number

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: [Your Name] <your.email@example.com>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] All tests pass
- [ ] Added new tests
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-reviewed my code
- [ ] Commented complex code
- [ ] Updated documentation
- [ ] No new warnings
```

## Documentation

### README Updates

When adding features, update:

- `README.md`: Main project overview
- `README_V2.md`: Detailed V2 documentation
- `UPGRADE.md`: Migration guides (if breaking changes)

### Code Examples

Provide working examples in the `Examples/` directory:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use FormGenerator\V2\Builder\FormBuilder;

// Clear, commented example code
$form = FormBuilder::create('example-form')
    ->addText('field', 'Label')
    ->add()
    ->build();
```

## Reporting Bugs

### Bug Report Template

```markdown
**Describe the Bug**
Clear description of the issue

**To Reproduce**
Steps to reproduce:
1. Create form with '...'
2. Add input '...'
3. Call build()
4. See error

**Expected Behavior**
What you expected to happen

**Actual Behavior**
What actually happened

**Environment**
- PHP Version: 8.1.0
- FormGenerator Version: 2.0.0
- OS: Ubuntu 22.04

**Code Sample**
```php
// Minimal code to reproduce the issue
```

**Error Messages**
```
Full error message or stack trace
```
```

## Suggesting Enhancements

### Enhancement Template

```markdown
**Feature Description**
Clear description of the proposed feature

**Use Case**
Why is this feature needed?

**Proposed Solution**
How should it work?

**Alternative Solutions**
Other approaches considered

**Additional Context**
Screenshots, mockups, or examples
```

## Architecture Guidelines

### Adding New Features

1. **Contracts First**: Define interfaces in `Contracts/`
2. **Implementation**: Implement in appropriate namespace
3. **Tests**: Write comprehensive tests
4. **Documentation**: Update relevant docs
5. **Examples**: Add working examples

### File Organization

```
src/V2/
├── Builder/          # Form and input builders
├── Contracts/        # Interfaces and enums
├── DataProvider/     # Data providers
├── Integration/      # Framework integrations
├── Renderer/         # Template renderers
├── Security/         # Security features
├── Theme/            # Themes and templates
└── Validation/       # Validation logic
```

### Design Patterns Used

- **Builder Pattern**: FormBuilder, InputBuilder
- **Strategy Pattern**: Validators, Themes, Renderers
- **Chain of Responsibility**: Method chaining
- **Factory Pattern**: Input creation

## Questions?

Feel free to:
- Open an issue for questions
- Join discussions
- Reach out to maintainers

Thank you for contributing! 🎉
