# Template OS — Decision Lock (post-critique)

**Status:** APPROVED DIRECTION — waiting on domain / project kickoff  
**Date:** 2026-09-10  
**Owner verdict:** keep concept · simplify architecture · start when domain is ready

This document **overrides** implementation defaults in [PHILOSOPHY-TZ-v0.1-2026-09-10.md](./PHILOSOPHY-TZ-v0.1-2026-09-10.md) where they conflict.  
Rationale: [ARCHITECTURE-CRITIQUE-2026-09-10.md](./ARCHITECTURE-CRITIQUE-2026-09-10.md).  
Earlier sketch: [../nordicai/TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md](../nordicai/TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md).

---

## Locked decisions

### 1. Concept — KEEP

Product remains:

> Autonomous **controllable** client templates: unique outside, standardized inside, editable by design, owned by the client — not a dump of AI HTML.

Formula stays:

`ONE ENGINE · MANY DESIGNS · STRICT CONTRACT`

### 2. Architecture — SIMPLIFY

Do **not** boil the ocean in v1.

**In for first cycle:**

- Manifest (durable schema)
- Content / instance data store
- Mini Inspector (few entity types)
- Validator (contract ERROR gate)
- One tiny reference template that completes the full loop
- Local run + exportable package

**Out until later (explicit defer):**

- Full mini-CMS (Nav/SEO suite/Dashboard scores as DoD)
- Marketplace / Template Library
- Template Update Engine (keep seams only)
- Agent HTTP API
- Template CLI productization
- Multi-tenant SaaS
- DnD / visual page builder
- AI as required runtime

### 3. Framework — DECOUPLE FROM CONTRACT

The **Template Contract** is framework-agnostic.

- Contract must not say “Template OS = Next.js”.
- First reference implementation may use a **light** stack (e.g. Vite + React or Astro + tiny API + SQLite/JSON).
- Next.js (or others) = optional **adapter**, not the identity of the system.

### 4. Manifest — DURABLE FROM DAY ONE

Not flat demo keys alone as the real schema.

Required from v0:

- Stable opaque `entityId` (+ optional human alias/path)
- Address: `pageId` + `sectionInstanceId` + `entityId`
- Type + constraints + cardinality
- Capability map **separate** from value store
- Schema version + **migration table** (may be empty, must exist)
- Zod (or equivalent) as source of truth → emit JSON Schema for agents/tools

Entity ID renames without migration = forbidden.

### 5. Template / Instance — SPLIT FROM DAY ONE

| Layer | Owns |
|-------|------|
| **Runtime** | inspector, admin shell, validator, media API |
| **Design (template)** | sections, layout, tokens, motion, manifest |
| **Instance (client)** | content values, uploads, site settings, SEO |

Zip export may bundle layers for delivery.  
**Development model** must never be “fork whole monorepo per client and hope git merge updates design.”

### 6. Validator — CENTRAL MECHANISM

- `validate` is mandatory before release / agent handoff.
- **ERROR** = contract breaks only (orphan entities, bad types, missing IDs, invalid content vs schema, admin leak).
- **WARNING/INFO** = heuristics (do not block unique design).
- Agents and humans cannot bypass ERROR gate.
- Score/marketplace metrics = later; not MVP identity.

### 7. First template — TINY, FULL CYCLE

One small reference site that proves:

```
Manifest → Content → Inspector → Validator → Frontend → Local run → Export
```

Prefer one page (or minimal pages), ~few sections, ~5 field types max  
(`text`, `textarea`, `image`, `link`, `boolean`|`select`).

Pretty “golden hour” scale designs come **after** the cycle is green.

---

## Kickoff gate

**Do not start greenfield coding in this InstantCMS repo as Template OS.**

When owner buys / assigns a **domain** (and preferably a dedicated repo/host):

1. Create dedicated Template OS repository (not InstantCMS).
2. Copy into `/docs`: **MVP-IMPLEMENTATION-PLAN-v0.1.md** (primary), this Decision Lock, Critique, Philosophy.
3. Hand agents the **Implementation Plan** — not the full Philosophy TZ.
4. Execute stages 0→6 in that plan.
5. Only after the thin loop is green: richer design language / more templates.

Until domain kickoff: this file + the Implementation Plan are the standing orders.

**Primary work doc:** [MVP-IMPLEMENTATION-PLAN-v0.1.md](./MVP-IMPLEMENTATION-PLAN-v0.1.md)

---

## Agent standing orders (until kickoff)

1. Do not implement Template OS inside `nordic-builder.store` InstantCMS tree.  
2. Do not expand nordicai into Template OS.  
3. If asked to “start Template OS” without domain/repo: point here and wait.  
4. When kickoff happens: read this file first, then Critique, then Philosophy TZ.

---

## One-line reminder

**Concept yes · System thinner · Contract > framework · Manifest durable · Instance split · Validator is law · First template tiny but complete · Start after domain.**
