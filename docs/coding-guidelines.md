# Coding Guidelines

## General

- Use `final` classes by default
- Keep classes small and focused (Single Responsibility)
- Follow PSR-4 autoloading conventions

## PHP Features

Use **readonly properties** with constructor promotion for immutable class properties (project targets PHP 8.2).

## Preferences

When working on this codebase, prefer:

- Extracting logic into testable classes with interfaces over inline implementation
- Constructor injection over service locator patterns
- Explicit code over clever abstractions
- Integration with existing patterns over introducing new ones
- Inline expressions over unnecessary local variables (if a variable is only used once and doesn't improve readability, inline it)

## Comments & Self-Documenting Code

Avoid comments in favor of self-documenting code:

- **Type hints**: Use typed parameters, typed properties and return types instead of docblock descriptions
- **Method names**: Use descriptive, self-explaining method and function names
- **Variable names**: Choose clear, meaningful variable names that convey purpose
- **Class names**: Use descriptive class names that explain their responsibility
- **File names**: Mirror class names for easy discoverability

Comments are acceptable when explaining **why** something is done (not **what**), such as workarounds for external limitations or non-obvious business rules.

## Naming

### Interfaces

- Descriptive name without `Interface` suffix (e.g., `DatabaseConnectionChecker`)
- Implementations: Prefix with a descriptive detail (e.g., `DBALDatabaseConnectionChecker`)

### Exceptions

- Descriptive name without `Exception` suffix
- Name should describe what went wrong (e.g., `IntegrationNotFound`)

## Dependency Injection

- Service providers wire dependencies
- Use **constructor injection**, not service locators
- Extract testable logic into separate classes with **interfaces**

## Testing

- Use `@test` annotation style for test methods
- Mock objects use intersection types: `Connection&MockObject`
- Prefer separate test methods over data providers for clarity
- Test file location mirrors source: `app/Foo/Bar.php` → `tests/Foo/BarTest.php`
- When you change or add frontend behavior, add or update a Playwright E2E test under `e2e/` covering it

## Restrictions

- **Never** modify files in `vendor/`
- **Never** commit `.env` or credentials files
- **Avoid** adding dependencies without team discussion
- **Always** run `make ci` before considering work complete

## AI Restrictions

- **Never** create commits - only humans commit code
- **Plan** larger changes but implement step by step, waiting for human review between steps

## Workflow

- **Small commits**: One logical change per commit
  - A logical change can span multiple files (e.g., source code + tests, or a refactor touching several related files)
  - All changes in the commit should belong together and serve a single purpose
  - The goal is to make commits easy to review and understand
- **Small pull requests**: Easier to review, faster to merge
- **Feature flags**: Deploy often, enable features when ready

## Security

### Never Commit

- `.env` files or environment-specific configurations
- API keys, tokens, or credentials
- Private keys or certificates
- Database connection strings with passwords
- Any hardcoded secrets

### Configuration

- Secrets belong in environment variables, not in code
- Use `.env.example` for documenting required env vars (without actual values)
- Configuration files with secrets should be in `.gitignore`

### Secure Coding

- No hardcoded credentials or secrets in code
- No sensitive data in log statements
- Validate input on user-provided data
- Use parameterized queries / the Eloquent query builder (avoid raw SQL string concatenation)
- No sensitive data exposure in error messages
