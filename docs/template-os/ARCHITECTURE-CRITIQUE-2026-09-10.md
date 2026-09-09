# Template OS — Architecture Critique (v0.1 TZ)

**Date:** 2026-09-10  
**Stance:** critical review — do not treat agreement as default  
**Source TZ:** philosophy + stack + ТЗ pasted by product owner  
**Canvas:** open `template-os-architecture-critique` beside chat  

Canonical idea sibling: [TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md](../nordicai/TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md)

---

## Verdict

**Product thesis is strong. Proposed MVP system is too heavy.**

`UNIQUE OUTSIDE / STANDARDIZED INSIDE / EDITABLE BY DESIGN / OWNED BY CLIENT` is the right formula for the market problem (“AI generated a template — now what?”).

The weak move is equating that formula with:

Next 16 + React 19 + Tailwind 4 + Prisma + SQLite + full mini-CMS + score system + Docker/VPS DoD + agent marketplace path — **as the first proof**.

Prove the **Template Contract** on a thinner core first.

---

## What holds

1. Template as product, not zip of HTML.
2. Editable but not breakable (Design Lock).
3. Manifest/content as source of truth; DOM is projection.
4. One Inspector engine ≠ one client visual language.
5. Validator + agent rules must be executable, not only prose.
6. Client ownership / export / self-host as ethics (with an honest buyer model).

---

## Five questions

### 1. Is Next.js required?

**No.** Next.js must not be the contract.

For most autonomous marketing templates it raises the floor (RSC complexity, Node ops, update surface) without buying the differentiator (manifest + lock + inspector).

**Recommendation:** define Template Contract independent of framework. MVP reference on **Vite + React** or **Astro islands** + tiny API (e.g. Hono) + JSON/SQLite. Add a Next adapter later only when a template needs real app/SSR features.

Risk if you lock to Next early: every “unique” template still feels like the same app shell; designers who are not Next-native bounce.

### 2. Manifest that won’t need a rewrite in a year?

The flat demo (`hero.title`) is fine for a slide, **dangerous as schema**.

It collapses under: section instances, repeaters, multi-page, i18n, variants, migrations.

**Durable shape:**

- Zod (or equivalent) as source of truth → emit JSON Schema for agents/tools.
- Split **capability map** (what is editable) from **value store** (content).
- Stable opaque `entityId` + optional human path/alias.
- Address tuple: `pageId` + `sectionInstanceId` + `entityId`.
- `cardinality`, constraints, defaults.
- **Migration table from day one** (even empty): renames/moves of IDs are inevitable.

Without that, year-1 rewrite is likely.

### 3. Template code vs client data?

**Highest structural risk in the TZ.**

“Physical clone of the whole project per client” for MVP demos is acceptable as a **shipping artifact**, fatal as the **development model** if you also want Template Update Engine later.

**Required boundary (even if folders are simple at first):**

| Layer | Owns |
|-------|------|
| Runtime | inspector, admin shell, validator, media API |
| Design package | sections, layout, tokens, motion, template manifest |
| Instance data | content values, uploads, SEO/site settings, nav |

Update = bump design package + run content migrations. Never “git merge beautiful new template into forked client app” as the strategy.

### 4. Strict validator without crushing design?

Global “no hardcoded values” will fight §1.4 (real visual diversity).

**Zone the rules:**

| Level | Examples |
|-------|----------|
| ERROR | orphan entities, unknown types, missing required IDs, admin leak, invalid content vs schema |
| WARNING | token coverage heuristics, soft a11y |
| INFO | suggestions |

Allowlisted escapes with reason registry for intentional one-offs.  
**Score** = health signal, not a vanity gate that pushes templates toward sameness.

### 5. Agent contract agents will actually follow?

Prose AGENTS.md alone is weak. Agents follow **scaffolds + failing CI**.

Minimum agent-ready kit:

1. One-screen AGENTS.md: can / cannot / source of truth / validate command.
2. Machine-readable `template.contract.json`.
3. Scaffold for new section/entity.
4. Golden reference template as oracle tests.
5. CI: `template:validate` must pass.
6. Few-shot examples of good changes.

38 sections of philosophy help humans; they do not by themselves make Cursor/Reasonix compliant.

---

## Other weak spots

1. **Buyer ambiguity** — agency/dev vs SMB end-client. Self-host story differs completely.
2. **MVP = mini-CMS** — Dashboard/Pages/Media/SEO/Nav/Settings before one locked template works is scope explosion.
3. **Universal Inspector myth** — start with ~5 types (`text`, `textarea`, `image`, `link`, `boolean`/`select`). Defer richtext/gallery/repeater.
4. **Self-host ≠ no lock-in** — Node/Docker/pnpm is still a technical lock for non-technical owners. Be honest or offer hosted operator path.
5. **Contradiction** — shared Tailwind/admin/tokens vs “templates must not look alike.” Design Lock is for **clients**; **authors** need composition freedom.
6. **§37 vs §2** — “foundation before pretty UI” conflicts with a Definition of Done that includes full template + Docker + VPS. Pick the spike.
7. **AI market insight is right; AI runtime must stay optional** — don’t let Stage 4 library/API distract Stage 1 contract proof.

---

## Disagreement with §34 breadth

Suggested **real** Stage 1:

Manifest → content store → Inspector (few types) → contract validator → one reference page → local run + zip export.

Defer: Prisma-or-die, full SEO/nav CMS, score system, Docker as DoD, CLI, marketplace, update engine, Agent HTTP API.

Keep **seams** so those land without rewriting Manifest/instance boundaries.

---

## Bottom line

Keep the philosophy.  
Shrink the first engine.  
Non-negotiable early: versioned Manifest + template↔instance boundary + executable agent gates.  
Do not start by boiling the ocean in Next.

---

*Review only — no implementation commitment.*
