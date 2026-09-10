# Template OS — Market & Analog Analysis

**Date:** 2026-09-10  
**Status:** research captured — informs Architecture v0.2  
**Does not replace:** [MVP-IMPLEMENTATION-PLAN-v0.1.md](./MVP-IMPLEMENTATION-PLAN-v0.1.md)

---

## What we compare against

Not “another CMS” and not “another page builder.”

Object: **Theme Engine / Application Contract** — client receives a **working instance**, not a dead HTML dump.

Required properties of that instance: editable · extensible · portable · validatable · agent-readable · visually controlled · independent of our servers after delivery.

---

## Primary analog: Shopify Themes

**Why #1:** longest-running answer to “let users change the site without giving them chaos or the whole codebase.”

Takeaways to adopt:

| Concept | Use in Template OS |
|---------|-------------------|
| Section definition + schema + instance settings | Section Contract |
| Blocks inside sections | Block Contract / repeatable items |
| JSON template = ordered section instances | Page composition in Instance |
| Schema capabilities (max blocks, allowed templates, presets) | Capability boundaries in Contract |
| Theme Check CLI | Validator inspiration |

Model to prefer over flat entities:

```
Page
 └── Section Instance
       ├── Section Type + Settings
       └── Block Instances
             ├── Block Type + Settings
```

**Do not adopt:** Shopify SaaS lock, Liquid-as-identity, merchant-only editor as the whole product.

---

## WordPress `block.json`

Take: per-component canonical metadata (name, version, attributes, supports, assets).  
Folder pattern: `sections/hero/{component, schema, preview, test, README}`.

Agent DX: contract readable **without** reading all React.

**Do not adopt:** full WP ecosystem as architecture base.

---

## TinaCMS

Take: content-modeling as code; schema versions with repo; visual editing DX; multi-frontend friendly.

Difference: Tina often lets editors **compose** freely. We are stricter:

```
designer defines template
  → client edits allowed content
  → AI extends only inside contract
```

---

## Keystatic

Take: **local-first** filesystem content; GitHub mode optional. Aligns with ownership / ZIP → VPS.

Prefer early: `content/` JSON on disk over mandatory cloud DB.

---

## Puck — useful, dangerous

Take: Component / Field / Config / Data / Render as Inspector ideas.

**Do not make Puck the runtime core** — path of least resistance is DnD page builder → Webflow clone → Design Lock dies.

---

## GrapesJS — where not to go

Powerful component/traits/storage model. Philosophy opposite:

- GrapesJS: user builds the page  
- Template OS: user safely changes an already designed product  

Steal traits ideas only; never the canvas-first identity.

---

## Builder.io / Storyblok / Sanity

Take: iframe/SDK registration, click-to-edit, schema-driven fields.

**Do not depend** on their cloud. Our order is **Website first → Inspector second**, not builder-first.

---

## Astro + Zod + JSON Schema

Strong foundation triad:

```
Zod (DX) → TypeScript + runtime validate + Inspector meta
         → JSON Schema (agents, CLI, docs, external tools)
```

Do **not** invent a proprietary schema language.

---

## Borrow / refuse matrix

| Source | Borrow | Refuse |
|--------|--------|--------|
| Shopify | sections/blocks/settings/capabilities/check | SaaS identity |
| WordPress | block.json packaging | CMS gravity |
| Tina | schema-as-code DX | free composition default |
| Keystatic | local-first files | — |
| Puck | field/config mental model | core runtime |
| GrapesJS | traits ideas | page-builder core |
| Storyblok/Sanity | click-to-edit UX | cloud dependency |
| Astro/Zod/JSON Schema | contract spine | — |

---

## Strategic one-liner (from research)

Not “mini-admin for HTML templates.”  
Not “template constructor.”

**Template OS = contract system for creating, delivering, and evolving autonomous sites.**

```
DESIGN → TEMPLATE CONTRACT → INSTANCE → INSPECTOR → VALIDATOR → AI AGENT → EVOLUTION
```

Same Contract explains the system to: human, Inspector, Validator, CLI, agent.

---

## Implication for MVP (reconcile with Decision Lock)

Research **confirms** Decision Lock: runtime-agnostic, Zod+JSON Schema, Template≠Instance, no Puck/Grapes core, no Next/Prisma as identity.

Research **upgrades** Manifest: use **Section → Block → Settings** (Shopify-shaped), not only flat `hero.title`.

Research temptation to resist in week 1: 7–10 section commercial landing **before** thin loop is green. Order remains: tiny full-cycle → then commercial reference #1.
