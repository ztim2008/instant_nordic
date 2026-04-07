# Nordicbuilder Releases and Migrations

## Versioning (SemVer)

Format:

- MAJOR.MINOR.PATCH

Rules:

- MAJOR: breaking changes
- MINOR: new backward-compatible features
- PATCH: backward-compatible fixes

Source of truth:

- packages/nordicbuilder/VERSION

Current package metadata:

- packages/nordicbuilder/manifest.ru.ini uses major/minor/build (build maps to PATCH)
- packages/nordicbuilder/manifest.json stores full SemVer string

## Changelog

Files:

- CHANGELOG.md (project-level summary)
- packages/nordicbuilder/CHANGELOG.md (package-level details)

Release note format:

- [1.0.0]
- [1.1.0]

## ZIP Build

Build command:

```bash
bash scripts/build-nordicbuilder-package.sh
```

Artifacts:

- dist/nordicbuilder.zip
- dist/nordicbuilder-<version>.zip

Archive structure:

- components/nordicbuilder/
- install.php
- manifest.json

## Install and Updates

Core flow:

1. InstantCMS installer imports packages/nordicbuilder/install.sql
2. Component is registered via manifest install section
3. packages/nordicbuilder/install.php runs after install/update
4. migrations/*.sql are applied once and tracked in `{#}nordicbuilder_migrations`

Migration files:

- packages/nordicbuilder/migrations/001_init.sql
- packages/nordicbuilder/migrations/002_add_sections.sql

## Backward Compatibility

Approach:

- additive SQL changes (CREATE TABLE IF NOT EXISTS)
- migration tracking table prevents reapplying the same migration
- install.sql contains baseline schema for clean install
- migrations folder provides incremental updates for existing installs
