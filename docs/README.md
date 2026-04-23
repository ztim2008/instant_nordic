# NordicBlocks Public Docs Index

This staged public repository keeps only product-facing documentation.

## Included

1. install docs in docs/install/;
2. release docs in docs/releases/;
3. reusable block family docs in docs/blocks/;
4. product roadmap in docs/roadmap/.

## Excluded by policy

The staged public repository intentionally excludes:

1. daily plans;
2. live incident worklogs;
3. server rollout notes;
4. private operational checklists;
5. SQL scratch files and temporary migration notes.

## Release duplication policy

Every release contour must duplicate both install and update outputs into this staged public repository together with VERSION, manifest, changelog and selected public docs.

Canonical sync entrypoint in the private product workspace:

```bash
bash scripts/nordicblocks-sync-public-staging.sh
```

## First public set

1. docs/install/INSTALLABLE-ZIP.md
2. docs/releases/UPDATE-RELEASE-WORKFLOW.md
3. docs/releases/RELEASE-LAYOUT.md
4. docs/releases/FIRST-PUBLIC-RELEASE-PLAYBOOK.md
5. docs/blocks/HERO-FAMILY-V1.md
6. docs/blocks/NEWS-FAMILY-V1.md
7. docs/blocks/CATALOG-FAMILY-V1.md
8. docs/blocks/BLOCK-SCAFFOLD-PIPELINE-STAGE3.md
9. docs/blocks/BLOCK-SCAFFOLD-VALIDATOR-SPEC-V1.md
10. docs/roadmap/NORDICBLOCKS-V2-ROADMAP.md