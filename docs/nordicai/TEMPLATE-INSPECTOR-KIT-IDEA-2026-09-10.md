# Template Inspector Kit — Idea (Future Product Line)

**Status:** idea only — not in implementation  
**Date:** 2026-09-10  
**Related:** [nordicai hypothesis handoff](./HYPOTHESIS-MVP-HANDOFF-2026-09-09.md), NordicBlocks entity-first inspector

---

## One-liner

Ship **controllable site templates**: a finished design (landing / scroll story / portfolio) plus a **mini inspector** so the client edits text, styles, images, icons, and sections — without InstantCMS and without touching raw code.

## Why not Instant first

InstantCMS is a heavy host for this experiment:

- widgets, tpl, routing, permissions, cache;
- DOM on a live Instant page is not “our” contract.

The nordicai FAB proved the **UX gesture** (pick → panel → change).  
The **product bet** is elsewhere: **template kits with inspector baked in**.

## Product vision

1. We (or a designer) assemble a strong template — e.g. a scroll-driven “golden hour” beach landing.
2. The template ships with a small editor runtime (admin/client mode).
3. Client opens the site → mini inspector (DevTools-like pick) → edits allowed zones only.
4. Deliverable = zip / demo URL: “open and control”, not “hire a developer to change a headline”.

### What the client can edit (MVP scope)

| Type | Examples |
|------|----------|
| Text | headings, body, buttons, captions |
| Style / tokens | colors, fonts, radius, spacing via CSS variables |
| Images | hero / section media with aspect hints |
| Icons | from a fixed set or sprite map |
| Sections | show/hide, reorder, swap variant from **template catalog** |

### Explicit non-goals (for this idea)

- Not a freeform page builder (Webflow clone).
- Not InstantCMS core / tpl rewriting.
- Not “click any DOM node and invent CSS” as the main path (that stays a skin/AI side-tool).
- Not full CMS (users, ACL, blog) in v1 — content pack + tokens is enough.

## Core principle

**Manifest owns the page; DOM only points to entities.**

Same lesson as NordicBlocks:

> Contract interprets the DOM — the DOM does not drive the editor.

Every editable piece is marked up in the template, e.g.:

- `data-tik="heading"` / `data-tik-id="hero.title"`
- `data-tik="image"` / `data-tik-id="hero.media"`
- `data-tik="section"` / `data-tik-id="gallery"`

Inspector resolves pick → entity id → panel from manifest.  
Unknown nodes: ignore or offer read-only “what is this” — no blind CSS save.

## Suggested architecture (sketch)

```
template/
  index.html          # designed page
  styles.css          # uses CSS variables
  content.json        # texts, image URLs, section order
  tokens.json         # --brand-*, fonts, radii
  manifest.json       # entities, limits, section catalog
  inspector/          # mini runtime (JS/CSS), admin-only or ?edit=1
```

**Save path (v1):** rewrite `content.json` + `tokens.json` (static host, tiny PHP/Node, or local download).  
**Section insert:** only into declared **slots** between sections, from the template’s own section catalog — never arbitrary HTML injection into the middle of unknown markup.

## UX metaphor

Browser “Inspect” mode, but productized:

1. Hover outline on editable entities only (or dim non-editable).
2. Click → side mini panel: Content / Style / Media / Section.
3. Optional tree of sections for structure, not full DOM dump.
4. Preview live; Publish / Export when ready.

## Pilot candidate

Reference vibe (local designer source, not in this repo):

`golden-hour-scroll-driven-beach-sunset-codepenchallenge`

First pilot = one scroll landing with:

- 1 hero (title, sub, CTA, background image),
- 2–3 content sections,
- tokens for accent / sky / type,
- section show-hide + one optional “add from catalog” slot.

Success = non-developer changes copy and photo in &lt;10 minutes without breaking layout.

## Relation to existing Nordic work

| Piece | Role vs this idea |
|-------|-------------------|
| `nordicai` | UX prototype of pick + AI skin; stays Instant overlay |
| NordicBlocks inspector | Entity-first panels — conceptual cousin for **blocks**, not static templates |
| Template Inspector Kit | **Separate product line**: sell/deliver templates, not Instant modules |

Do not merge this into Instant “because we already have a FAB”. Keep Instant experiments optional; kit is the scalable story.

## Go / No-Go for a future MVP

**Go** if we can ship one template where:

1. all client edits go through manifest entities;
2. layout never requires editing HTML;
3. export is reproducible (content + tokens + same template build).

**No-Go** if the inspector becomes a general CSS/DOM editor or Instant-specific dependency.

## Next steps (when prioritized)

1. Freeze `manifest.json` schema v0 (entity types + limits).
2. Port one real landing (golden-hour or Nordic landing) to manifest markup.
3. Ship read/write inspector without AI.
4. Optional later: AI assist only for tokens/copy inside allowed entities (reuse nordicai rules).

---

*Document purpose: keep the idea visible for the next agent/human. No implementation commitment.*
