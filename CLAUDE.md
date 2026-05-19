# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About this repo

This is a custom fork of Fork CMS (v5.13.0), updated in-house to run on Symfony 5 (`symfony-5` branch) since the upstream project no longer maintains it. The `master` branch tracks closer to upstream; `symfony-5` is the active development branch.

## Commands

### PHP / Backend

```bash
composer install -o                        # Install dependencies
composer test                              # Run all tests
composer test -- --testsuite=unit          # Unit tests only
composer test -- --testsuite=functional    # Functional tests only
composer test -- --testsuite=installer     # Installer tests only
composer test -- --exclude-group=installer # All except installer tests

# Run a single test file or method
vendor/bin/simple-phpunit path/to/SomeTest.php
vendor/bin/simple-phpunit path/to/SomeTest.php --filter testMethodName

bin/phpstan analyze src                    # Static analysis (config: phpstan.neon)
bin/phpcs --standard=psr2 src              # Check code style
bin/phpcbf --standard=psr2 src             # Auto-fix code style

php bin/console <command>                  # Symfony console
php bin/console forkcms:cache:clear        # Clear Fork-specific cache
```

### JS / Frontend assets

```bash
npm install          # Install JS dependencies
npm test             # Lint JS (StandardJS)
gulp build           # Compile SCSS, bundle JS, copy vendor assets to correct dirs
```

Run `gulp build` after any `npm install` or dependency changes to move assets to the right locations.

### Deployment

Capistrano is configured for deployments (`Capfile`, `app/config/capistrano/`). Stages are defined under `app/config/capistrano/stages/`.

## Architecture

### Top-level structure

- `src/` — all application code, split into four namespaces (see below)
- `app/` — Symfony kernel, YAML config (`config.yml`, `routing.yml`, `security.yml`, `parameters.yml`), and Capistrano config
- `bin/` — console entry point and CLI tools (phpstan, phpcs, simple-phpunit)
- `templates/` — global Twig templates
- `var/` — cache, logs, Docker config
- `css/`, `js/`, `fonts/` — compiled/vendored frontend assets (do not edit directly; edit SCSS sources then run `gulp build`)

### Source namespaces (`src/`)

| Namespace | Path | Purpose |
|-----------|------|---------|
| `Backend\` | `src/Backend/` | Admin area — actions, forms, module engines |
| `Frontend\` | `src/Frontend/` | Public site — actions, widgets, themes |
| `Common\` | `src/Common/` | Shared code — mailer, doctrine types, event listeners, base test cases |
| `Console\` | `src/Console/` | Symfony console commands |
| `ForkCMS\` | `src/ForkCMS/` | Core framework — installer bundle, core bundle, privacy, Google, Imagine integration |

### Module system

Both `Backend/` and `Frontend/` are composed of **modules**. Each module lives under `{Backend|Frontend}/Modules/ModuleName/` and follows this layout:

```
ModuleName/
├── Actions/        # Controllers (one class per URL action)
├── Ajax/           # Ajax endpoints
├── Config.php      # Module config/routing
├── Engine/         # Business logic (Model.php, etc.)
├── Form/           # Symfony form types
├── Installer/      # DB migrations and install logic
├── Js/             # Module-specific JavaScript
├── Layout/         # Twig templates and widgets
├── Tests/
│   ├── Actions/    # Functional tests (full request/response)
│   └── Engine/     # Unit tests (Model logic)
└── Widgets/        # Frontend widget controllers
```

`Actions/` correspond to test suites: `Tests/Actions/` = functional, `Tests/Engine/` = unit.

### Request lifecycle

`index.php` → detects environment (`FORK_ENV`) and whether installed → boots `AppKernel` → Symfony routing dispatches to a Fork action class in `Backend/` or `Frontend/`. If `parameters.yml` is missing, the app redirects to `/install`.

### Testing setup

- Test environment requires a database named `<your-db>_test` with the same credentials as the main DB
- `app/config/parameters.yml.test` holds test DB overrides
- Base test classes: `Common\WebTestCase`, `Backend\Core\Tests\BackendWebTestCase`
- Data fixtures for seeding: `{Backend|Frontend}/Modules/*/DataFixtures/`
- Three test suites: `installer`, `unit`, `functional` (see `phpunit.xml.dist`)

### Environment configuration

Environment is controlled by the `FORK_ENV` env var (`dev`, `prod`, `test`, `install`). Config files are layered: `config.yml` → `config_{env}.yml`. The `parameters.yml` file (gitignored) holds all runtime secrets; copy from `parameters.yml.dist` to bootstrap.

### Key dependencies

- **Symfony 5** — framework, DI, routing, forms, security, Twig
- **Doctrine ORM 2.7** — entities, migrations, custom types in `Common/Doctrine/`
- **SimpleBus** — command/event bus used throughout backend modules
- **Liip Imagine** — image resizing/manipulation
- **Spoon Library** — legacy Fork utility library (form helpers, cookies, etc.)
- **Bugsnag** — error tracking (`bugsnag.api_key` in `parameters.yml`)
- **Bootstrap 4** + jQuery — backend UI

### Code style

PSR-2 is enforced in CI. Run `bin/phpcbf --standard=psr2 src` to auto-fix before committing.

## Known issues / incomplete Symfony 5 migration

The Symfony 5 upgrade was done incrementally and some things were never fully finished:

- **Tests are broken** — the existing test suite has not been updated for the Symfony 5 structure. Do not treat failing tests as regressions; they were already failing. Don't spend time fixing the test runner unless that is the explicit goal.
- **Module installer errors in debug mode** — installing a module via the backend while `FORK_DEBUG=1` (or `FORK_ENV=dev`) throws an error. This is a known bug, not something introduced by recent changes.

## Privacy / Consent Mode v2

`ForkCMS\Privacy\ConsentDialog` manages visitor consent. Seven Google Consent Mode v2 levels are defined as constants on that class. `functionality_storage` is always granted and not configurable by the visitor. The other six can be toggled per-site in **Settings → Privacy**.

**Cookie names** use the `_granted` suffix — `privacy_consent_level_[level]_granted`. Old `_agreed` cookies (pre-5.13.1) are ignored.

**GTM integration** — when GTM is configured and the consent dialog is enabled, a `<script>` block with `gtag('consent', 'default', {...})` is injected into the `<head>` before the GTM snippet. Stored visitor choices are applied server-side via `gtag('consent', 'update', {...})` on the same request, so returning visitors never block tags they previously approved.

**Template variable** exposed to Twig is `privacyConsentDialogShow` (boolean, true = dialog should appear). Levels are passed as `privacyConsentDialogLevels` — an array of `{name, localeKey}` objects. Use `level.name` for `data-value` attributes and `level.localeKey` to construct translation keys (e.g. `'msg.PrivacyConsentLevel' ~ level.localeKey ~ 'Title'`). The localeKey converts `ad_storage` → `AdStorage`, `analytics_storage` → `AnalyticsStorage`, etc.
