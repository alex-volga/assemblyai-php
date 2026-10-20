# Contributing to AssemblyAI PHP SDK

Thank you for your interest in contributing to the AssemblyAI PHP SDK!

We welcome contributions of all kinds: bug reports, bug fixes, documentation improvements, feature requests, and code reviews.

---

## Code of Conduct

Please be respectful, constructive, and considerate of others when participating in discussions, opening issues, or submitting pull requests.

---

## Development Workflow

### Prerequisites

- PHP 8.3 or higher
- [Composer](https://getcomposer.org/)

### Setting Up the Project

1. Fork and clone the repository:
   ```bash
   git clone https://github.com/alex-volga/assemblyai-php.git
   cd assemblyai-php
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run the test suite:
   ```bash
   composer test
   # or: ./vendor/bin/phpunit
   ```

4. Run static analysis (PHPStan):
   ```bash
   composer analyse
   # or: ./vendor/bin/phpstan analyse
   ```

5. Run all checks before committing:
   ```bash
   composer check
   ```

---

## Coding Standards

- **Strict types:** Every PHP file must declare strict types: `declare(strict_types=1);`.
- **PHP 8.3+ features:** Use modern idioms where appropriate (constructor promotion, `readonly` classes/properties, Backed Enums, match expressions).
- **PSR Standards:** Adhere to PSR-12 coding standard and PSR-7 / PSR-17 / PSR-18 standards for HTTP abstractions.
- **Testing:** Include unit or feature tests for any new functionality or bug fixes.
- **Documentation:** Keep PHPDoc and `README.md` updated if public APIs change.

---

## Submitting Pull Requests

1. Create a feature branch:
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. Commit your changes with clear, descriptive commit messages:
   ```bash
   git commit -m "feat: add support for real-time temporary token expiration option"
   ```

3. Push your branch to GitHub:
   ```bash
   git push origin feature/my-new-feature
   ```

4. Open a Pull Request targeting `main`. Fill in the PR template with details about your change.
