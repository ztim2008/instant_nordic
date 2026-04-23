# NordicBlocks Release Layout

## Official dist contract

The public product repository should use the following structured release layout:

```text
dist/
└── nordicblocks/
    ├── start/
    │   ├── nordicblocks.zip
    │   └── nordicblocks-<version>.zip
    └── updates/
        └── <version>/
            ├── nordicblocks-update.zip
            └── nordicblocks-update-<version>.zip
```

## Meaning of each path

### Fresh install

1. dist/nordicblocks/start/nordicblocks-<version>.zip = primary install artifact;
2. dist/nordicblocks/start/nordicblocks.zip = convenience alias for latest build.

### Update release

1. dist/nordicblocks/updates/<version>/nordicblocks-update-<version>.zip = primary update artifact;
2. dist/nordicblocks/updates/<version>/nordicblocks-update.zip = convenience alias inside version folder.

## Compatibility aliases

Flat root-level aliases may still exist:

1. dist/nordicblocks.zip
2. dist/nordicblocks-update.zip

But they should be treated as compatibility shortcuts only. Documentation and release notes should point users to the structured paths under dist/nordicblocks/.

## Release note convention

Each release note should always publish:

1. install artifact path;
2. update artifact path;
3. summary of included changes;
4. validation performed;
5. residual risks;
6. rollback point.

## Build script contract

Public repo build tooling should preserve the current behavior:

1. install archive is built from manifest.ru.ini, install.php, install.sql and package/;
2. update archive is built from the same product root with update naming;
3. docs and working notes must not leak into runtime install/update payloads unless explicitly intended.