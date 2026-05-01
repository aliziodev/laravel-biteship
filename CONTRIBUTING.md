# Contributing to Laravel Biteship

Terima kasih atas minat Anda untuk berkontribusi pada Laravel Biteship! Dokumen ini berisi panduan untuk berkontribusi pada project ini.

## Daftar Isi

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Git Commit Conventions](#git-commit-conventions)
- [Pull Request Process](#pull-request-process)
- [Semantic Versioning](#semantic-versioning)
- [Release Process](#release-process)

## Code of Conduct

Project ini mengikuti [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/version/2/1/code_of_conduct/). Dengan berpartisipasi, Anda diharapkan untuk menjaga lingkungan yang sopan dan inklusif.

## Getting Started

1. Fork repository ini
2. Clone fork Anda: `git clone https://github.com/YOUR_USERNAME/laravel-biteship.git`
3. Buat branch baru: `git checkout -b feature/nama-fitur`
4. Install dependencies: `composer install`
5. Jalankan tests: `vendor/bin/pest`

## Development Setup

```bash
# Clone repository
git clone https://github.com/aliziodev/laravel-biteship.git
cd laravel-biteship

# Install dependencies
composer install

# Run tests
vendor/bin/pest

# Check code style
vendor/bin/pint

# Fix code style
vendor/bin/pint --fix
```

## Git Commit Conventions

Kami menggunakan [Conventional Commits](https://www.conventionalcommits.org/) untuk memudahkan:
- Generate changelog otomatis
- Determining semantic version bumps
- Berkomunikasi antar developer

### Format Commit Message

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

### Tipe Commit

| Tipe | Deskripsi | Versi |
|------|-----------|-------|
| `feat` | Fitur baru | MINOR |
| `fix` | Bug fix | PATCH |
| `docs` | Perubahan dokumentasi | - |
| `style` | Perubahan formatting (spacing, semicolon, dll) | - |
| `refactor` | Refactor kode tanpa perubahan fungsional | - |
| `perf` | Performance improvement | PATCH |
| `test` | Menambah/mengupdate test | - |
| `chore` | Build process, dependencies, tools | - |
| `ci` | CI/CD configuration | - |
| `revert` | Revert commit sebelumnya | - |

### Scope

Scope adalah area kode yang terdampak:

| Scope | Deskripsi |
|-------|-----------|
| `config` | Konfigurasi package |
| `rates` | Rates API |
| `orders` | Orders API |
| `tracking` | Tracking API |
| `couriers` | Couriers API |
| `locations` | Locations API |
| `webhook` | Webhook handling |
| `mock` | Mock mode |
| `label` | Label generator |
| `docs` | Dokumentasi |
| `tests` | Test suite |

### Contoh Commit Messages

```
feat(orders): add support for shipper branding fields

Add shipper_contact_name, shipper_contact_phone, shipper_contact_email,
and shipper_organization fields to OrderRequest DTO for branding
on shipping labels.

fix(rates): resolve cache key collision for different payloads

Different request payloads were generating same cache key due to
array sorting issue. Now using normalized JSON for cache key.

docs(readme): update configuration examples for v1.1.0

Add examples for default_origin and default_shipper configuration
to README.md with clear usage instructions.

test(mock): add comprehensive tests for mock mode error simulation

Add tests for 401, 429, 422, and 500 error simulation in mock mode
to ensure proper exception handling.

refactor(config): reorganize config sections with clear comments

Restructure biteship.php config into 7 logical sections:
1. Koneksi & Autentikasi
2. Webhook
3. Origin Pengiriman
4. Cache Rates
5. Mock Mode
6. Label
7. Shipper Default

chore(deps): update illuminate/support to support Laravel 13

Update composer.json to allow Laravel 12 and 13, and test with
orchestra/testbench 10 and 11.
```

### Breaking Changes

Untuk breaking changes, tambahkan `BREAKING CHANGE:` di footer:

```
feat(api): change OrderResponse structure to object

OrderResponse sekarang mengembalikan object dengan typed properties
bukan array biasa. Ini memudahkan autocompletion dan type safety.

BREAKING CHANGE: OrderResponse::fromArray() sekarang mengembalikan
object bukan array. Update kode yang mengakses $response['id'] menjadi
$response->id.
```

## Pull Request Process

1. **Update CHANGELOG.md** - Tambahkan perubahan Anda di section `[Unreleased]`
2. **Jalankan tests** - Pastikan semua tests pass: `vendor/bin/pest`
3. **Check code style** - Jalankan `vendor/bin/pint --test`
4. **Update dokumentasi** - Jika ada perubahan fitur, update README.md
5. **Buat PR** - Dengan deskripsi jelas tentang perubahan

### PR Checklist

- [ ] Tests ditambahkan/updated untuk perubahan
- [ ] CHANGELOG.md diupdate
- [ ] Code style sesuai (pint tests pass)
- [ ] Dokumentasi diupdate (jika relevan)
- [ ] Commit messages mengikuti conventional commits
- [ ] Tidak ada breaking changes tanpa diskusi (kecuali major version)

## Semantic Versioning

Project ini mengikuti [Semantic Versioning](https://semver.org/lang/id/):

```
VERSION := MAJOR.MINOR.PATCH

MAJOR - Increment ketika ada breaking changes incompatible
MINOR - Increment ketika menambah fitur (backwards compatible)
PATCH - Increment ketika bug fix (backwards compatible)
```

### Kapan Naik Versi?

- **MAJOR** - Breaking changes yang require user mengubah kode mereka
- **MINOR** - Fitur baru, improvement, deprecation (tapi masih compatible)
- **PATCH** - Bug fixes, security patches

### Pre-release Tags

- `v1.0.0-alpha.1` - Alpha release untuk testing internal
- `v1.0.0-beta.1` - Beta release untuk testing publik
- `v1.0.0-rc.1` - Release candidate, hampir final

## Release Process (Fully Automated)

Project ini menggunakan **[semantic-release](https://semantic-release.gitbook.io/)** untuk otomatisasi versioning dan releasing berdasarkan [Conventional Commits](https://www.conventionalcommits.org/).

### 🚀 Cara Kerja

Setiap kali ada push ke `main` branch, GitHub Actions akan:

1. **Analyze commits** - Parse semua commit sejak release terakhir
2. **Determine version** - Tentukan versi baru (major/minor/patch)
3. **Generate changelog** - Update `CHANGELOG.md` otomatis
4. **Create tag** - Buat git tag (misal: `v1.2.3`)
5. **Create release** - Publish GitHub Release dengan release notes
6. **Update Packagist** - Trigger Packagist untuk update

### 📊 Versi Bump Rules

| Commit Type | Versi | Contoh |
|-------------|-------|--------|
| `feat:` | **MINOR** (0.1.0 → 0.2.0) | Fitur baru |
| `fix:` | **PATCH** (0.1.0 → 0.1.1) | Bug fix |
| `perf:` | **PATCH** | Performance improvement |
| `BREAKING CHANGE:` | **MAJOR** (0.1.0 → 1.0.0) | Breaking change |
| `docs:`, `style:`, `refactor:`, `test:`, `chore:` | **No release** | - |

### 📝 First Release (v1.0.0)

Jika repository belum punya tag sama sekali, semantic-release akan otomatis membuat **v1.0.0** saat ada commit dengan type `feat:` atau `fix:`.

```bash
# Contoh: Push pertama kali dengan feat commit
git commit -m "feat: initial release with core API integration"
git push origin main

# Otomatis release v1.0.0
```

### 🔁 Contoh Workflow

```bash
# 1. Developer membuat fitur baru
git checkout -b feature/add-tracking
git commit -m "feat(tracking): add real-time tracking support"
git push origin feature/add-tracking

# 2. Merge PR ke main
git checkout main
git merge feature/add-tracking
git push origin main

# 3. GitHub Actions auto-release (v1.1.0)
# - Analyze: feat commit found
# - Bump: 1.0.0 → 1.1.0 (minor)
# - Changelog: auto-generated
# - Tag: v1.1.0 created
# - Release: published
```

### ⚠️ Breaking Changes

Untuk breaking changes yang naik **MAJOR** version:

```bash
git commit -m "feat(api): change authentication method

BREAKING CHANGE: API key sekarang harus di-set via header
bukan query parameter. Update kode yang menggunakan
?api_key=xxx menjadi header Authorization."

# Otomatis release v2.0.0
```

### 🏷️ Pre-release (Alpha/Beta/RC)

Untuk pre-release, gunakan branch khusus:

```bash
# Buat branch beta
git checkout -b beta
git commit -m "feat: experimental feature"
git push origin beta

# Configure .releaserc.json untuk branch beta
# (menghasilkan v1.2.0-beta.1)
```

### 🔧 Skip Release

Jika perlu skip release untuk commit tertentu:

```bash
git commit -m "docs: update readme [skip ci]"
# atau
git commit -m "chore: update dependencies"
# (chore tidak trigger release)
```

### 📁 Konfigurasi Files

- **`.releaserc.json`** - Konfigurasi semantic-release
- **`.github/workflows/release.yml`** - GitHub Actions workflow
- **`CHANGELOG.md`** - Auto-generated dari commits

## Pertanyaan?

Jika ada pertanyaan, silakan:
- Buat [GitHub Issue](https://github.com/aliziodev/laravel-biteship/issues)
- Email: [aliziodev@gmail.com]

Terima kasih telah berkontribusi! 🚀
