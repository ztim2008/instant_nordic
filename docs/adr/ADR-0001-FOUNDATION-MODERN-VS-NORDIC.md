# ADR-0001: Foundation strategy for Modern vs Nordic

- Status: Accepted
- Date: 2026-04-07
- Owners: Nordic Builder team

## Context

InstantCMS template `modern` is the most common production base and receives regular updates.

At the same time, Nordic Builder must provide:

1. Stable visual identity and design system ownership.
2. Predictable runtime behavior for builder pages.
3. Upgrade safety for existing projects.
4. A simple flow for non-technical users (without manual widget-grid engineering).

Directly building product identity on top of `modern` internals creates long-term coupling to upstream markup/CSS changes and increases regression risk on every InstantCMS update.

## Decision

We adopt a **separate Nordic runtime template** as the product foundation.

1. `nordic` is the target frontend template and owns shell, slots, and visible design language.
2. `modern` is used as reference and migration path, not as final product shell.
3. Runtime follows Variant B:
- builder controls only `content_body` via one SSR widget;
- header/footer/sidebars remain template-owned;
- no takeover/hybrid runtime modes.
4. Design system is a first-class token layer (global defaults -> page overrides), consumed by canvas, preview, and live runtime through one contract.
5. During transition, controlled compatibility with `modern` is allowed, but planned for reduction in phased migration.

## Consequences

### Positive

1. Clear ownership boundaries: template shell vs builder content.
2. Lower upgrade risk from `modern` internal changes.
3. Better parity: canvas = preview = live runtime.
4. Cleaner UX: users work with page intents/presets instead of low-level widget layout engineering.
5. Independent release discipline for Nordic template and builder component.

### Trade-offs / Costs

1. Need to maintain a dedicated `nordic` template lifecycle.
2. Temporary dual-mode complexity during migration.
3. Requires explicit compatibility matrix against InstantCMS versions.

### Non-goals

1. No permanent product mode based on direct `modern` takeover.
2. No global runtime magic that suppresses system zones outside `content_body`.

## Rollback

Rollback target is not "remove Nordic", but "return to safe migration mode".

### Rollback triggers

1. Critical regressions after InstantCMS core update.
2. Runtime parity breaks (preview/live mismatch) on production-critical routes.
3. Severe compatibility issue in template shell or adapter bindings.

### Rollback actions

1. Switch site template to known stable preset/mode (migration-safe profile).
2. Disable new phase-specific features behind config flags.
3. Keep Variant B ownership rule (builder only in `content_body`).
4. Restore previous package release using versioned artifact and DB backup policy.

### Rollback constraints

1. No destructive DB rollback without backup validation.
2. No ad-hoc hotfixes in `system/config/config.php` unless explicitly approved.

## Migration Phases 0-3

### Phase 0: Stabilize migration-safe baseline

Goal: freeze safe behavior before further decoupling.

1. Keep controlled `modern` compatibility where required.
2. Enforce Variant B runtime ownership contract.
3. Lock release discipline (SemVer, changelog, package smoke).

DoD:

1. DB/install/update smoke passes.
2. Preview/live parity for current supported page modes.

### Phase 1: Nordic shell ownership

Goal: Nordic becomes the primary shell owner.

1. `nordic` owns header/footer/layout slots contract.
2. Builder injects SSR only in `content_body`.
3. Remove remaining takeover/hybrid runtime branches.

DoD:

1. Main user routes run via Nordic shell without regressions.
2. No runtime dependence on `modern` zone suppression logic.

### Phase 2: Design system authority

Goal: token contract is the single source of visual truth.

1. Global token layer finalized (colors, type, spacing, containers, components).
2. Page-level overrides merge predictably over globals.
3. Canvas/preview/live consume same token-to-CSS pipeline.

DoD:

1. Visual parity tests pass across canvas/preview/live.
2. Non-technical flow works through presets, not low-level layout tuning.

### Phase 3: Controlled decoupling from Modern internals

Goal: minimize infrastructure coupling to `modern`.

1. Audit and remove residual hard dependencies on `modern` internals where feasible.
2. Maintain explicit compatibility adapter layer for unavoidable integration points.
3. Introduce and maintain InstantCMS x Nordic compatibility matrix.

DoD:

1. Upgrade test plan passes on target InstantCMS versions.
2. Nordic release notes include compatibility status and known limits.

## Enforcement

1. Any architectural proposal conflicting with this ADR must include a superseding ADR.
2. New runtime/theme changes must reference this ADR in worklog entries.
