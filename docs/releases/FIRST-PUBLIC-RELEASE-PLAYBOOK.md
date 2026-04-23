# NordicBlocks First Public Release Playbook

## Goal

Publish the first clean public NordicBlocks repository without leaking private project structure, while preserving a reproducible release workflow.

## Pre-publish sequence

1. freeze the release scope in the private working project;
2. build both install and update artifacts in the private product source;
3. run the public staging sync so source, docs and dist artifacts are duplicated into this staged repository together;
4. review docs so only product-facing material remains;
5. verify VERSION, manifest.ru.ini and CHANGELOG.md are aligned;
6. run build and archive validation from this public repo layout when needed;
7. review git diff to confirm no server-specific files slipped in;
8. create the first public repository on GitHub and push the staged tree.

## Mandatory duplication rule

For NordicBlocks, every release contour must duplicate both artifact types into the staged public repository:

1. fresh install archive;
2. update archive.

This duplication should happen immediately after the private release build, not later as a manual cleanup step.

The canonical sync entrypoint in the private product workspace is:

```bash
bash ../scripts/nordicblocks-sync-public-staging.sh
```

The canonical private workspace release contour is:

```bash
bash scripts/nordicblocks-release-public-contour.sh 0.2.0
```

## Minimum validation sequence

```bash
bash scripts/nordicblocks-package-preflight.sh
bash scripts/build-nordicblocks-package.sh
bash scripts/build-nordicblocks-update-package.sh
unzip -l dist/nordicblocks/start/nordicblocks-$(cat VERSION).zip
unzip -l dist/nordicblocks/updates/$(cat VERSION)/nordicblocks-update-$(cat VERSION).zip
```

## Initial tag policy

1. use annotated SemVer tags only;
2. first public tag should match the product version exactly, for example v0.2.0;
3. create the tag only after staged artifacts and changelog are final;
4. do not create floating tags like latest or public-beta in git;
5. use GitHub Release entries for human-facing release notes, not extra git tag variants.

## GitHub automation policy

1. the public repository should publish releases through .github/workflows/release.yml;
2. workflow_dispatch may be used for manual release builds;
3. pushing an annotated SemVer tag vX.Y.Z should also be a valid release trigger;
4. release notes should be generated from CHANGELOG.md first, with artifact paths appended automatically.

## Initial branch policy

1. main = release-ready public branch;
2. feature work can happen in short-lived branches;
3. do not push raw server snapshots into main;
4. public repo history should represent product evolution, not production maintenance noise.

## First GitHub release payload

Attach these artifacts to the first public release:

1. dist/nordicblocks/start/nordicblocks-<version>.zip
2. dist/nordicblocks/updates/<version>/nordicblocks-update-<version>.zip

## Release note skeleton

Each public release note should include:

1. what changed in runtime/admin/editor;
2. install artifact path;
3. update artifact path;
4. validation performed;
5. known limitations;
6. rollback reference in the private project.