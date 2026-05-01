# 1.0.0 (2026-05-01)


### Features

* initial release ([15f9a6e](https://github.com/aliziodev/laravel-biteship/commit/15f9a6efa8baa0506fa1e9b8587683bcec778540))

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Rates API** - Check shipping rates with intelligent caching
- **Orders API** - Create, retrieve, and cancel orders
- **Tracking API** - Track shipments by order ID or waybill
- **Couriers API** - List available couriers
- **Locations API** - Manage saved locations (search, create, update, delete)
- **Webhook Handler** - Receive and dispatch Laravel events for Biteship webhooks
- **Label Generator** - Generate shipping labels from order data
- **Mock Mode** - Development mode with fake API responses for Rates and Orders
- **Default Configuration** - Support for `default_origin` and `default_shipper` in config
- **Shipper Fields** - Optional `shipper_*` fields for branding on labels
- **Exception Handling** - Structured exceptions for API errors (401, 429, 422, 500)
- **Type Safety** - Full type hints with DTOs and Enums
- **Testing** - Complete test coverage with Pest PHP (90+ tests)

### Security
- Webhook signature verification using `hash_equals()`
- API key validation and sandbox/production mode detection

---

## Semantic Versioning Guide

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** version (X.0.0) - Incompatible API changes
- **MINOR** version (0.X.0) - Added functionality (backwards compatible)
- **PATCH** version (0.0.X) - Bug fixes (backwards compatible)

### Version Tags

- `v1.0.0` - Initial stable release
- `v1.1.0` - New features added
- `v1.1.1` - Bug fixes

## Git Commit Conventions

This project uses [Conventional Commits](https://www.conventionalcommits.org/):

### Commit Message Format

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

### Types

| Type | Description |
|------|-------------|
| `feat` | New feature (minor version bump) |
| `fix` | Bug fix (patch version bump) |
| `docs` | Documentation changes |
| `style` | Code style changes (formatting, semicolons, etc) |
| `refactor` | Code refactoring |
| `perf` | Performance improvements |
| `test` | Adding or updating tests |
| `chore` | Build process, dependencies, etc |
| `ci` | CI/CD configuration changes |

### Scopes

Common scopes for this project:

- `config` - Configuration changes
- `rates` - Rates API related
- `orders` - Orders API related
- `tracking` - Tracking API related
- `webhook` - Webhook handling
- `mock` - Mock mode functionality
- `docs` - Documentation

### Examples

```
feat(orders): add shipper fields support for branding

fix(rates): resolve cache key collision issue

docs(readme): update configuration examples

feat(config): add default_origin and default_shipper configuration

test(mock): add comprehensive tests for mock mode

chore(deps): update laravel framework dependency
```

### Breaking Changes

For breaking changes, add `BREAKING CHANGE:` in the footer:

```
feat(api): change response format for orders

BREAKING CHANGE: OrderResponse now returns object instead of array
```

---

## Release Process

1. Update `CHANGELOG.md` with new version and changes
2. Update version in `composer.json`
3. Create git tag: `git tag -a v1.0.0 -m "Release version 1.0.0"`
4. Push tag: `git push origin v1.0.0`
5. GitHub Actions will auto-create release

[Unreleased]: https://github.com/aliziodev/laravel-biteship/compare/HEAD
