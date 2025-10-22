# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Testing
```bash
# Run all tests
composer test

# Run specific test suites
composer test-unit           # Entity tests
composer test-repo           # Repository tests
composer test-controller     # Controller tests
composer test-category       # Category entity tests
composer test-category-repo  # Category repository tests

# Test with coverage and verbose output
composer test-coverage
composer test-verbose
```

### Database Management (Test Environment)
```bash
composer db-create-test     # Create test database
composer db-update-test     # Update schema
composer db-drop-test       # Drop test database
composer cache-clear-test   # Clear test cache
```

### Symfony Console Commands
```bash
# Cache operations
php bin/console cache:clear --env=dev
php bin/console cache:clear --env=test

# Database operations
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:migrate

# User role management
php bin/console app:user:role   # Manage admin roles (grant/revoke)

# Development server
php bin/console server:run
```

## Project Architecture

This is a Symfony 3.4 memo management application with the following structure:

### Core Entities
- **Category** (`src/AppBundle/Entity/Category.php`): Categories for organizing memos
- **Memo** (`src/AppBundle/Entity/Memo.php`): Individual memo entries
- **User** (`src/AppBundle/Entity/User.php`): User management with role-based access control
- **MemoContainer** (`src/AppBundle/Entity/MemoContainer.php`): Container for memo aggregation

### Entity Relationships
```
Category (1) ←→ (n) Memo
User (1) ←→ (n) Memo
MemoContainer (1) ←→ (n) Memo
```

### Repository Pattern
Custom query logic is implemented in Repository classes:
- `CategoryRepository`: Advanced category queries with memo counting
- `MemoRepository`: Memo search and filtering
- `UserRepository`: User-specific queries
- `MemoContainerRepository`: Container aggregation logic

### Controllers & Routes
- `CategoryController`: CRUD operations for categories
- `MemoController`: Memo management with search/pagination
- `UserController`: User management
- `MemoContainerController`: Memo container functionality
- All routes use annotation-based routing (`@Route`)

### Forms & Validation
- `CategoryType`: Category form with CSRF protection
- `MemoType`: Memo creation/editing forms
- Form validation uses Symfony's validation component

### Template Structure
Templates are in `app/Resources/views/`:
- `base.html.twig`: Base layout
- `category/`: Category-related templates
- `memo/`: Memo management templates
- Uses Twig templating engine with Bootstrap styling

## Key Dependencies

### Core Symfony Bundles
- `symfony/symfony`: 3.4.* (Framework)
- `doctrine/orm`: Database ORM
- `knplabs/knp-paginator-bundle`: Pagination
- `twig/twig`: Template engine

### Testing
- `phpunit/phpunit`: ^7.0
- `symfony/phpunit-bridge`: Symfony PHPUnit integration

## Development Notes

### Known Issues
- The `Memo.php` entity may have incomplete `setCategory()` method implementation
- Some controller tests may be incomplete

### Testing Strategy
- Entity tests verify model behavior and relationships
- Repository tests ensure custom queries work correctly
- Form tests validate form processing and CSRF protection
- Controller tests check HTTP responses and routing

### Code Conventions
- PSR-4 autoloading with `AppBundle\` namespace
- Annotation-based routing and validation
- Repository pattern for complex queries
- Form types for all user input
- Twig templates with consistent naming

### Security Features
- CSRF protection on forms
- Symfony Security component integration
- Input validation on all forms
- Role-based access control (RBAC)

### User Role Management
The User entity supports role-based permissions:
- **ROLE_MEMBER**: Default role for all users
- **ROLE_ADMIN**: Administrator role with elevated privileges

**IMPORTANT SECURITY RULES:**
1. **NEVER** include the `roles` field in user registration/edit forms
2. Admin privileges can **ONLY** be granted via CLI command: `php bin/console app:user:role`
3. When creating UserType form, **DO NOT** add a `roles` field
4. Admin role is for future admin panel access and memo management permissions

**Managing Admin Users:**
```bash
# Interactive command to manage admin roles
php bin/console app:user:role

# Options:
# 1. Grant admin role to a user
# 2. Revoke admin role from a user
# 3. List all admin users
```

When making changes:
1. Always run tests after modifications: `composer test`
2. Update database schema if entities change: `composer db-update-test`
3. Follow existing naming conventions and patterns
4. Add tests for new functionality
5. Use the Repository pattern for complex database queries