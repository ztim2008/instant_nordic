# NordicBlocks

NordicBlocks is an installable block library for InstantCMS with SSR runtime, widget placement and a visual editor workflow.

## What This Repository Contains

This repository is the public product repository for NordicBlocks.

It contains:

1. install and update package sources;
2. runtime payload for the component;
3. release scripts and version sync tooling;
4. minimal public documentation for install and releases;
5. current release artifacts in the structured dist layout.

It does not contain:

1. a full InstantCMS site snapshot;
2. production configs;
3. uploads, cache, backups or private environment data;
4. project-specific server customizations unrelated to the reusable product.

## Repository Layout

```text
.
├── docs/
├── dist/
├── package/
├── scripts/
├── VERSION
├── manifest.ru.ini
├── install.php
├── install.sql
├── CHANGELOG.md
└── README.md
```

## Install and Update Artifacts

Structured release paths:

1. install: dist/nordicblocks/start/nordicblocks-<version>.zip
2. update: dist/nordicblocks/updates/<version>/nordicblocks-update-<version>.zip

Only the current release should be kept in the public repository working tree. Historical artifacts should live in GitHub Releases, not as accumulated files in dist/.

## Release Workflow

The standard release flow is:

1. create checkpoint;
2. sync version;
3. update changelog;
4. run preflight and smoke for changed scope;
5. build install and update archives;
6. sync the release into the staged public repository;
7. verify staged artifacts;
8. publish release notes with rollback point.

Quick commands:

```bash
bash scripts/nordicblocks-version-sync.sh 0.2.0
bash scripts/build-nordicblocks-package.sh
bash scripts/build-nordicblocks-update-package.sh 0.2.0
bash ../scripts/nordicblocks-sync-public-staging.sh
```

Private workspace one-command release contour:

```bash
bash scripts/nordicblocks-release-public-contour.sh 0.2.0
```

For this product, duplicating releases into the public staging repository is not optional. Install and update artifacts should be mirrored there together with VERSION, manifest, changelog and selected public docs in the same release contour.

When the public GitHub repository is live, releases should be built there as well via .github/workflows/release.yml so install/update artifacts and release notes are emitted from the product repo itself.

## Development Policy

NordicBlocks is developed as a reusable product.

That means:

1. product-safe reusable logic belongs here;
2. site-specific integration logic should stay in a separate private project;
3. releases should be built from this repository, not from a production server snapshot.

## Current Focus Areas

1. design block editor and runtime parity;
2. managed block catalog and scaffold pipeline;
3. stable install/update release flow;
4. reusable production delivery for InstantCMS sites.

## Status

Current version is defined in VERSION and mirrored into manifest.ru.ini, release artifacts and changelog.

## License and Distribution

Add the final license/distribution policy here before the first public launch.

If the project will be source-available but not fully open-source, document that explicitly.