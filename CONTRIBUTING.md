# Contributing

Thanks for contributing to `atldays/laravel-geo`.

This document explains the basic expectations for contributions, workflow, commit messages, and quality checks.

## Before You Start

Before opening an issue or pull request:

- check whether the behavior already exists in the package
- check whether there is already an open issue or pull request for the same topic
- make sure the change is useful beyond a single private use case

This package is especially open to contributions around:

- new geo drivers
- improvements to existing drivers
- new country definition providers
- improvements to existing country definition providers
- update flows for local database providers
- normalization of geo data into the shared typed contracts
- documentation and live coverage for supported providers

The main long-term direction is broader provider support, both for geo drivers and country definition providers, so contributions in those areas are especially welcome.

## Development Workflow

This repository follows a Git Flow style workflow.

In practice, that means:

- `master` is kept stable
- `develop` is the main integration branch
- feature work should be done in focused branches
- pull requests should target the appropriate shared branch instead of mixing unrelated changes directly into long-lived branches

Please keep changes small, reviewable, and focused on a single concern whenever possible.

## Commit Messages

All commits must follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.

Format:

```text
<type>(optional-scope): <description>
```

Examples:

```text
feat(driver): add support for a new geo provider
fix(maxmind): handle missing metadata during update
docs(readme): document request geo macro
test(ip-api): add live coverage for public IP lookups
refactor(manager): simplify fallback resolution
```

Common commit types:

- `build`
- `chore`
- `ci`
- `docs`
- `feat`
- `fix`
- `perf`
- `refactor`
- `revert`
- `style`
- `test`

## Running Tests And Formatting

The recommended way to work with the project is through Docker.

### Format

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD:/app" -w /app composer:2 sh -lc 'composer format:test'
```

To apply formatting:

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD:/app" -w /app composer:2 sh -lc 'composer format'
```

### Tests

Run the standard test suite:

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD:/app" -w /app composer:2 sh -lc 'composer test'
```

Run live provider checks:

```bash
docker run --rm -u $(id -u):$(id -g) -v "$PWD:/app" -w /app composer:2 sh -lc 'composer test:live'
```

### Local Development

If you already have a compatible local PHP environment, you can run tools locally instead of Docker.

Examples:

```bash
composer format:test
composer format
composer test
composer test:live
```

Docker is still preferred because it matches the project environment more closely.

## What To Include In A Contribution

For most changes, please include:

- focused code changes
- tests for the new behavior or bug fix
- updated documentation when public behavior changes

If you add or change a driver, please also consider:

- configuration shape
- fallback behavior
- normalization into the shared geo contracts
- update behavior, if the provider requires local resources
- live coverage when the provider can be tested reliably

New drivers should include standard tests for normalization and behavior.

When a provider can be tested reliably against a real external source, a live test should be added as well.

Contributions affecting drivers are expected to keep both the regular CI workflow and the live workflow green.

If you add or change a country definition provider, please also consider:

- configuration shape under `geo.definitions`
- normalization into `CountryDefinitionContract`
- optional dependency behavior, if the provider depends on an external package
- tests for successful resolution and failure cases
- README examples and setup notes when public behavior changes

If public behavior changes, examples in `README.md` should stay in sync.

## Pull Request Notes

Try to keep pull requests focused and easy to review.

A good pull request usually includes:

- what changed
- why it changed
- how it was tested
- any compatibility notes, if needed

Please avoid mixing driver additions, unrelated refactors, and documentation cleanup in the same pull request unless they are tightly connected.

## Compatibility Expectations

This package aims to stay compatible with the Laravel and PHP versions declared in `composer.json`.

When contributing:

- avoid changes that silently break supported Laravel versions
- prefer backward-compatible improvements when possible
- update tests when framework-specific behavior changes
- keep DTO and contract behavior consistent across drivers
- keep DTO and contract behavior consistent across country definition providers

## Quality Checklist

Before opening or updating a pull request, make sure:

- formatting passes
- tests pass
- commit messages follow Conventional Commits
- public API changes are documented
- driver behavior stays normalized through the shared contracts
- country definition providers stay normalized through `CountryDefinitionContract`
