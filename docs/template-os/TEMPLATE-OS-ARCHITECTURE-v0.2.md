# Template OS — Architecture v0.2 (Ten Contracts)

**Date:** 2026-09-10  
**Status:** architecture spine — implement against [MVP-IMPLEMENTATION-PLAN-v0.1.md](./MVP-IMPLEMENTATION-PLAN-v0.1.md)  
**Inputs:** Decision Lock · Critique · Market Analysis · Product Funnel  

Vision stays in Philosophy TZ. **This file defines the laws.**

---

## System shape

```
                 TEMPLATE CONTRACT
                         │
             ┌───────────┼───────────┐
             │           │           │
           Astro       React       Next…   ← adapters, not identity
             │           │           │
             └───────────┼───────────┘
                         │
                      INSTANCE
                         │
              ┌──────────┼──────────┐
              │          │          │
          INSPECTOR   VALIDATOR   AGENT
```

**Law:** `TEMPLATE = what the site is allowed to be` · `INSTANCE = what this client’s site contains`.

Recommended MVP runtime (from market verdict): **Astro + React island (Inspector) + Zod → JSON Schema + JSON instance store.**  
Not: Next/Prisma/Puck/Grapes as core.

Package layout (conceptual):

```
template-package/
  contract/          # machine contracts (+ generated JSON Schema)
  sections/          # one folder per section type
  design/            # tokens + design rules
  inspector/         # admin UI (generic)
  validator/
  docs/

instance/
  content/
  media/
  settings/
  instance.json
```

Commerce Gateway (demo / codes / DeepSeek fill) lives on the **seller site**, not inside every ZIP. See [PRODUCT-FUNNEL-2026-09-10.md](./PRODUCT-FUNNEL-2026-09-10.md).

---

## 01. Template Contract

**Defines:** identity of a design package.

Must include:

- `name`, `version` (semver)
- runtime hint (adapter id), not hard dependency
- list of page types + allowed section types
- capabilities flags (e.g. `content`, `tokens`, `section_reorder`, `section_add`)
- pointer to design rules + agent rules docs

Machine file example: `template.contract.json` (generated/synced from Zod).

**Cannot:** embed client-specific content or media.

---

## 02. Instance Contract

**Defines:** one deployed client site’s data boundary.

Contains only:

- content values keyed by stable entity addresses
- media files / refs
- site settings (SEO, favicon, metrics, optional sanitized head/body)
- `instance.json` metadata (template name/version bound, locale)

**Cannot:** redefine section schemas or bypass capabilities.

Update path later: migrate Instance when Template version changes — never `git merge` template into forked app as strategy.

---

## 03. Entity Contract

**Defines:** one editable field.

Required:

- stable opaque `id` (renames only via Migration Contract)
- `type` (`text` | `textarea` | `image` | `link` | `boolean` | `select` in MVP)
- `label`, constraints (`maxLength`, `required`, `aspectRatio`, options…)
- optional default

Address:

`pageId` + `sectionInstanceId` + optional `blockInstanceId` + `entityId`

Flat `hero.title` may exist as **alias**, not as sole identity.

---

## 04. Section Contract

**Defines:** reusable section **type** (Shopify-shaped).

Per section folder:

- component (adapter-specific)
- `schema` (Zod → JSON Schema): settings + allowed blocks + max blocks + presets + `allowedPages` / `maxInstances`
- preview asset + short README + tests when feasible

Section **instance** on a page: `{ type, id, settings, blocks[] }`.

Capabilities live here (what client/agent may do), not only “here is data.”

---

## 05. Content Contract

**Defines:** how values are stored and read.

- Source of truth = Instance content files/DB matching schemas  
- No editable marketing copy as hardcoded JSX source of truth  
- Repeatable items = **blocks**, not ad-hoc arrays without schema  
- Fill APIs (questionnaire / DeepSeek) write **only** to registered entities

---

## 06. Design Contract

**Defines:** Design Lock for authors vs clients.

- Tokens: colors, type, spacing, radius, motion keys used by the template  
- Client Level 1: content (+ maybe limited token picks if capability allows)  
- Client must not get arbitrary CSS / DOM / grid editors in MVP  
- Authors may use one-off values; Validator WARN (not ERROR) unless contract forbids  

Design Lock Levels (future product): Content → Theme → Structure → Developer — MVP ships Content (+ settings).

---

## 07. Inspector Contract

**Defines:** generic UI over schemas.

- Inputs: entity type + constraints + current value  
- Outputs: patches to Instance only  
- Does not know business meaning of “Dental Hero” — only field types  
- Click-to-edit (future): DOM → entity reference → Inspector; never free DOM edit  
- **Not** Puck/Grapes runtime

Admin chrome may use generic UI kit; public site uses template design system only.

---

## 08. Agent Contract

**Defines:** what AI/Cursor may change.

Machine + short `AGENTS.md`:

- CAN / CANNOT derived from Template + Section capabilities  
- Must preserve entity IDs  
- Must run Validator after material changes  
- Must not invent editable fields outside schemas  
- Must not add dependencies / layout rewrites unless Developer capability  

Same contract powers: human docs, CLI checks, future `template agent check`.

---

## 09. Validator Contract

**Defines:** the release gate (central mechanism).

Levels:

| Level | Scope | Severity |
|-------|--------|----------|
| 1 Data | content ↔ schema | ERROR |
| 2 Architecture | no instance data in template; no orphan entities; no unbound editables | ERROR |
| 3 Design | token heuristics, a11y soft rules | WARNING/INFO |

CLI: `pnpm template:validate` — ERROR ⇒ fail CI / fail agent apply.

Inspired by Shopify Theme Check; owned by us.

---

## 10. Migration Contract

**Defines:** safe Template version evolution.

- `migrations[]`: rename entity, move path, add field with default, drop field policy  
- Bound to `from` → `to` template semver  
- Instance update applies migrations then re-validates  
- Empty migrations file allowed in v0 — **file must exist**

Without this, year-1 updates destroy client work.

---

## Reference template strategy (order)

1. **Spike template (tiny)** — 1 page, 2–3 sections — proves all ten contracts end-to-end (Implementation Plan).  
2. **Commercial reference #1** — real landing with ~7–10 sections — stress-tests Section/Block/Inspector/Agent **after** spike is green.

Do not start with #2 only; architecture that only works on toys is useless, but architecture unproven on a toy never reaches a commercial page.

---

## Commerce Gateway (outside these ten, but dependent)

Storefront features (demo reset, admin codes, questionnaire, DeepSeek fill, catalog) consume Contracts 01–05 + 07–09.  
They are **not** part of the ZIP identity. Spec: Product Funnel doc.

---

## Anti-patterns (architecture level)

- Template OS = Next app  
- Manifest = one giant unversioned JSON blob with only flat keys  
- Puck/Grapes as core  
- Client content inside `sections/`  
- Validator as optional “nice score”  
- AI fill writing HTML instead of entities  
- Demo and production sharing one mutable instance without reset  

---

## Next implementation step

Agents: execute [MVP-IMPLEMENTATION-PLAN-v0.1.md](./MVP-IMPLEMENTATION-PLAN-v0.1.md) Stages 0–6, interpreting Manifest as **Section → Block → Entity** per contracts 03–05.

Humans: domain + dedicated repo → paste this doc set into `/docs`.
