# Template OS — Project Philosophy + Tech Stack + TZ (source v0.1)

**Status:** product owner draft captured for agents  
**Date:** 2026-09-10  
**Critical companion:** [ARCHITECTURE-CRITIQUE-2026-09-10.md](./ARCHITECTURE-CRITIQUE-2026-09-10.md)

> This file preserves the original vision text. For architectural pushback and MVP cut recommendations, read the critique first.

---

## 0. Idea

Not another catalog of empty HTML/Next templates.

**Goal:** designer/dev builds a unique template once; client receives an autonomous site with a mini-admin — not a dead archive.

**Formula:** `ONE ENGINE · MANY DESIGNS · STRICT CONTRACT`

Template = design + content model + manifest + inspector + validator + docs + agent rules + deploy strategy.

Client gets: ready site, allowed content/SEO/images/settings edits, local or VPS deploy, no SaaS vendor lock-in.  
Client does **not** get arbitrary DOM editing that can destroy architecture/design.

## 1. Philosophy (summary)

1. Template is a product, not a mockup.  
2. Client owns the site (exportable, self-hosted, agent-understandable).  
3. Freedom limited by good design — “Editable, but not breakable.”  
4. Visual diversity required; engineering contract shared.  
5. Content separated from presentation — Manifest + Content Model are source of truth.  
6. Design Lock — content/tokens/variants yes; DOM/grid/arbitrary CSS/architecture no (without developer level).  
7. AI/agents only inside contract; validate → build → test; no bypassing validator.

## 2. MVP goal

Prove: beautiful site + controllability + autonomy + strict contract + agent-ready architecture.

Reference template + mini-admin + manifest + inspector + validator + tokens + content model + SEO + agent docs + local + VPS + export.

**Out of MVP:** DnD builder, multi-tenant SaaS, marketplace, collab editing, complex roles, AI site generation, universal CMS monster.

## 3–4. Stack & architecture (as proposed in v0.1)

Proposed: Next.js 16+, React 19, TS strict, App Router, Tailwind 4, tokens, shadcn **admin only**, SQLite+Prisma, Zod, Vitest/Playwright, pnpm, Docker/nginx, GitHub Actions.

Layout sketch: `app/(site|admin|api)`, `sections/`, `content/`, `template/`, `design/`, `validator/`, `docs/`, `prisma/`.

> Critique challenges Next-as-default and full mini-CMS breadth for first proof. See companion doc.

## 5–9. Domain / Manifest / Inspector / Admin

Entities (conceptual): Template, Page, Section, Entity, Content, Media, Navigation, SEO, DesignTokens, SiteSettings, AdminUser.

Manifest maps stable IDs → type/label/constraints. Inspector is type-driven UI over manifest, template-agnostic.

Admin sections proposed: Dashboard, Pages, Content, Media, SEO, Navigation, Site Settings, Template, Diagnostics.

## 10–13. Tokens, validator, score

Tokens = engineering system, not visual sameness.  
Validator: manifest/content/sections/design/SEO/a11y/build/agent docs.  
Levels: ERROR / WARNING / INFO. Optional Template Score later.

## 14–15. Agent-first

Required `AGENTS.md`, forbidden ID renames without migration, no editable content outside manifest, no validator bypass. Workflow for new sections is contract-driven.

## 16–22. Versioning, instance, local-first, self-host, security, backup, export

Semver for templates; instance = client data; local `pnpm dev`; Docker preferred for VPS; basic security for uploads/auth; `pnpm backup` / `pnpm template:export`.

## 23–26. Docs, new-template process, anti-patterns, admin≠client design

Full docs set listed in original TZ. Anti-patterns: color-only clones, content in JSX only, per-template admins, SaaS dependence, AI required at runtime.

## 27–33. Future

`template.contract.json`, CLI, library/market, update engine, Design Lock levels, visual inspector (entity not DOM), Agent API over registered entities only.

## 34–35. MVP stages & DoD

Staged delivery from reference+manifest+inspector+sqlite → agents/tests/docker/export → CLI/instances/versioning → library/updates.

DoD checklist: download, local run, site, admin, text/image/SEO/settings edits, design not breakable via admin, manifest, validator, TS/build/playwright, Docker/VPS, agent docs, handoffable.

## 36. One paragraph

Not a library of empty templates — a standard for autonomous controllable sites: unique outside, predictable inside; content separated; Manifest describes editables; Inspector manages; Validator enforces; agents stay inside rules; client owns and can self-host; new templates reuse contract with new visual language.

`UNIQUE OUTSIDE · STANDARDIZED INSIDE · EDITABLE BY DESIGN · VALIDATED BY CODE · UNDERSTOOD BY AGENTS · OWNED BY CLIENT`

## 37. First agent task (from TZ)

Do not build everything. Study doc → propose file architecture, Prisma schema, Manifest schema, Content Model, Validator rules → find contradictions → simplify MVP → write IMPLEMENTATION-PLAN.md. Foundation before pretty frontend: Manifest → Content → Inspector → Validator → Frontend.

## 38. Final principle

First template is **reference implementation** of the Template Contract — proof that the system works. After that, the job is not “make sites” but “make sites that conform to the system.”
