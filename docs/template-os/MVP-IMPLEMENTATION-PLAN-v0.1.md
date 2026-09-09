# Template OS — MVP Implementation Plan v0.1

**Status:** PRIMARY WORK DOC for agents (Cursor / Reasonix)  
**Date:** 2026-09-10  
**Audience:** implementers tomorrow morning — not a vision essay

| Doc | Role |
|-----|------|
| [PHILOSOPHY-TZ-v0.1](./PHILOSOPHY-TZ-v0.1-2026-09-10.md) | North Star — what we want long-term |
| [ARCHITECTURE-CRITIQUE](./ARCHITECTURE-CRITIQUE-2026-09-10.md) | What was dangerous in the first stack dump |
| [DECISION-LOCK](./DECISION-LOCK-2026-09-10.md) | Approved constraints |
| **This file** | **What we actually build next** |

Do **not** start from PHILOSOPHY-TZ as the task list. Start here.  
Open Decision Lock if a choice conflicts with vision.

---

## Goal of this MVP

Prove one thin vertical loop:

```
Durable Manifest → Instance content → Inspector → Validator → Site render → Local run → Zip export
```

One tiny reference template. No marketplace, no Next lock-in, no full CMS.

---

## Non-negotiables (from Decision Lock)

1. Framework ≠ contract (light stack OK; Next is optional later).  
2. Manifest durable from day one (IDs, pages/sections, migrations stub).  
3. Template / Instance split from day one.  
4. Validator is the release gate (ERROR = block).  
5. First template tiny but full-cycle.  
6. New **dedicated repo** — not InstantCMS / nordic-builder.store.

---

## Stack for MVP (default — change only with reason)

| Piece | Choice |
|-------|--------|
| App | Vite + React + TypeScript |
| API | tiny Hono (or equivalent) on Node |
| Data | SQLite **or** JSON files for instance content (pick one in Stage 0 and stick) |
| Admin UI | simple forms (shadcn OK **admin only**) |
| Public site | template’s own CSS/tokens — not shadcn look |
| Validate | Zod + `pnpm template:validate` |
| Package | pnpm |

If domain/repo is not ready at start of day: create repo skeleton locally, deploy when domain exists.

---

## Stages (do in order)

### Stage 0 — Repo & seams (≤ half day)

**Done when:**

- [ ] Dedicated repo exists (`template-os` or agreed name).  
- [ ] `/docs` contains Decision Lock + this plan + links to Philosophy/Critique.  
- [ ] Folders exist: `runtime/`, `templates/reference/`, `instances/demo/`, `docs/`.  
- [ ] `AGENTS.md` one screen: can / cannot / source of truth / `pnpm template:validate`.  
- [ ] README: `pnpm install` → `pnpm dev` (may be stub).

**Do not:** design the beach landing, add Prisma “for the future”, copy InstantCMS code.

---

### Stage 1 — Manifest schema v0

**Done when:**

- [ ] Zod schemas for: TemplateManifest, Page, SectionInstance, EntityDefinition.  
- [ ] Entity has: stable `id`, `type`, `label`, constraints, cardinality.  
- [ ] Address model: `pageId` + `sectionInstanceId` + `entityId`.  
- [ ] Capability map ≠ value store (two files/modules).  
- [ ] `migrations` array exists (can be `[]`).  
- [ ] Emit/export JSON Schema for agents.  
- [ ] Fixture: reference manifest with ≤ ~15 entities, ≤ 3 sections, 1 page.

**Entity types allowed in MVP:** `text` | `textarea` | `image` | `link` | `boolean` | `select`  
(No richtext / gallery / repeater yet.)

---

### Stage 2 — Instance content store

**Done when:**

- [ ] Demo instance holds only: content values, media paths, site settings stub.  
- [ ] No client content hardcoded in JSX as source of truth.  
- [ ] Load API/helpers: getEntity(page, section, id), setEntity(...).  
- [ ] Upload image to instance `uploads/` with basic validation (type/size).  
- [ ] Design package has zero knowledge of which client owns the data.

---

### Stage 3 — Validator (central gate)

**Done when:**

- [ ] `pnpm template:validate` runs in CI locally.  
- [ ] **ERROR** on: missing manifest, duplicate IDs, unknown types, orphan content keys, content failing constraints, editable DOM without entity binding (if detectable).  
- [ ] **WARNING** optional heuristics (do not block).  
- [ ] Broken fixture fails; good reference fixture passes.  
- [ ] Documented: agents must not bypass ERROR.

---

### Stage 4 — Mini Inspector + Admin shell

**Done when:**

- [ ] `/admin` (or `?edit=1` gate) lists entities from manifest.  
- [ ] Type → control mapping for the 5–6 MVP types.  
- [ ] Save writes to **instance** store only.  
- [ ] Preview/site reflects changes after save/reload.  
- [ ] No arbitrary CSS editor. No DOM free-edit.

Admin may look generic. Public site must not.

---

### Stage 5 — Reference template (tiny, real)

**Done when:**

- [ ] One page, distinct simple composition (not “another purple SaaS hero”).  
- [ ] 2–3 sections wired to manifest entities.  
- [ ] Tokens file for colors/type used by the template.  
- [ ] Public route renders from instance content.  
- [ ] Full loop green: edit text + image in admin → visible on site → validate passes.

**Out of scope for this stage:** scroll-driven golden-hour complexity, many pages, motion systems, section marketplace.

---

### Stage 6 — Run / export package

**Done when:**

- [ ] `pnpm dev` runs site + admin.  
- [ ] `pnpm build` production build succeeds.  
- [ ] `pnpm template:export` (or script) produces zip: runtime + design + instance demo + docs + short DEPLOY.md.  
- [ ] Second machine / clean folder can install and run from export instructions (smoke by human or checklist).

Docker/VPS nice-to-have **after** zip+local works — not a blocker for calling MVP loop “proven”.

---

### Stage 7 — Agent handoff hardening (same day if time, else next)

**Done when:**

- [ ] AGENTS.md matches reality.  
- [ ] One scaffold note or script: “add entity” / “add section” checklist.  
- [ ] Example of a bad change that validator catches (in docs or test).  
- [ ] IMPLEMENTATION-PLAN checklist above updated with what shipped / what slipped.

---

## Explicitly not tomorrow

- Next.js migration  
- Prisma “because production”  
- SEO/Nav full CMS modules  
- Template score / marketplace  
- Update engine across versions  
- nordicai merge  
- Work inside InstantCMS repo as the product

---

## Definition of Done (MVP loop)

MVP is done when **all** are true:

1. Fresh clone/export runs locally.  
2. Admin can change allowed text + image.  
3. Site shows those changes.  
4. Design cannot be broken via admin (no free CSS/DOM).  
5. `template:validate` passes on reference; fails on intentional broken fixture.  
6. Manifest/instance boundary is visible in the folder layout.  
7. Another agent can read **this plan + AGENTS.md** and continue without the philosophy tome.

---

## Morning start command (for the agent)

1. Read **this file** end-to-end.  
2. Skim Decision Lock (5 min).  
3. Create/open dedicated repo.  
4. Execute **Stage 0 → 1**; stop and report if Manifest choices need a human call.  
5. Continue Stages 2–6 without expanding scope.  
6. End of day: update checkboxes + short WORKLOG in `/docs`.

---

## One line

**Tomorrow we build the thin contract loop — not the North Star platform.**
