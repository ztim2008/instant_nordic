# Nordic AI (`nordicai`) — Hypothesis MVP Handoff

**Date:** 2026-09-09  
**Site:** https://nordic-builder.store  
**Branch:** `feature/nordicstyl-picker`  
**Status:** Live hypothesis confirmed working (tokens / scoped CSS overlay). No tpl/core rewrites.

This document is the canonical handoff for a **new agent**. Read before changing `nordicai`.

---

## What we built (one sentence)

Admin picks a DOM element on the public site → DeepSeek returns a JSON patch `{tokens, css, notes}` → preview locally → save as `site_overlay_css` in component options → hook injects overlay for everyone.

## Hard product rules (do not break)

1. AI may output **only** CSS variables (`tokens`) and **scoped CSS**. Never PHP / tpl / JS / InstantCMS core.
2. Prefer existing Nordic tokens (`--nordic-*`, `--primary`) over new hardcoded colors.
3. Keep brand accent in the **red** family (`--nordic-accent`), not Bootstrap blue.
4. Do not restyle Bootstrap layout utilities (`.row` / `.col-*` / `.container`) as display/position.
5. DeepSeek API key lives only in component options (`/admin/nordicai/options`). **Never commit secrets or paste keys into docs/git.**
6. Do not commit `system/config/config.php`.

## How to use (smoke)

1. Log in as **admin on the frontend** (same browser session as the site, not only `/admin`).
2. Open e.g. `/users`.
3. Bottom-right FAB **«Выбрать элемент»**.
4. Click a node → panel → prompt → **Сгенерировать** → Preview → **Сохранить на сайт**.
5. Guests see overlay, not inspector.
6. Admin UI: `/admin/nordicai/agent`, settings: `/admin/nordicai/options`.

HTML marker in page source: `<!-- nordicai-hook:ok admin=1 uid=… -->`.

## Architecture

| Piece | Role |
|---|---|
| Controller `nordicai` | Frontend + backend InstantCMS component |
| Hook `before_print_head` | Inject published overlay for all; inspector assets/config only for admin |
| `libs/DeepSeekClient.php` | OpenAI-compatible chat to DeepSeek |
| `libs/PatchService.php` | System prompt, parse/filter patch, `patchToCss` |
| `actions/generate.php` | Live AJAX generate (admin) |
| `actions/overlay_save.php` / `overlay_clear.php` | Persist / clear `site_overlay_css` in options |
| `templates/modern/controllers/nordicai/js/inspector.js` | FAB, pick mode, panel, fetch |
| Table `cms_nordicai_runs` | Run log (prompt / response / error) |
| Options YAML | `deepseek_*`, `strict_tokens_only`, `allow_scoped_css`, `site_overlay_css` |

Package snapshot: `packages/nordicai/`.  
Cursor skill: `.cursor/skills/nordicai-agent/SKILL.md`.

### Key URLs (frontend)

- `POST /nordicai/generate`
- `POST /nordicai/overlay_save`
- `POST /nordicai/overlay_clear`

Config injected as `window.NORDICAI_INSPECTOR` (absolute paths like `/nordicai/generate`).

## Critical bugfix (2026-09-09) — must remember

**Symptom:** Inspector visible, but Generate showed  
`SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON`.

**Cause:** Actions used `empty($this->cms_user->is_admin)`. Through InstantCMS `cmsAction` magic `__get` (by-ref), `empty()` on that chain is **false-positive true** even when `is_admin === "1"`. Action returned HTML 404 → `JSON.parse` died.

**Fix:** Check via `cmsUser::getInstance()` and `!$user->is_admin`. Return **JSON** errors (`forbidden`), never HTML 404 for these AJAX endpoints.

Affected files: `generate.php`, `overlay_save.php`, `overlay_clear.php`.

Verified live: admin generate → `ok:true` + DeepSeek patch; guest → `{ok:false,error:"forbidden"}`.

## Known limitation (next iteration, not blocking)

Selecting a **large wrapper** (section / container with many children) gives the model only a selector + thin computed context. It often cannot “see” inner structure and misses the intent.

**Possible next steps (not implemented):**

1. Auto-narrow: if node has many children, suggest a specific child (card / heading / CTA).
2. Compact DOM snapshot: 1–2 levels of tag/class/short text.
3. Role heuristics: `header` / `card` / `cta` for a richer prompt.

Point edits on concrete nodes already work well — that is enough for the hypothesis.

## Related same-day work (users catalog)

`/users` list redesigned as card grid (option 1):

- `templates/modern/controllers/users/list.tpl.php` + `styles.css` (+ scss partial)
- Mirrored styles/list under `templates/nordic/controllers/users/`

## Ops notes

- Controller registered in `cms_controllers` (`name=nordicai`, enabled).
- Event `before_print_head` → listener `nordicai` in `cms_events`.
- A broken `nordicstyl/before_print_head` DB event was disabled earlier (controller folder missing) so it would not corrupt the hook chain — re-check if nordicstyl is reinstalled.
- Cache bust for assets uses `production_time` in local config (local-only; do not commit config).

## Do / Don’t for the next agent

**Do**

- Extend context / auto-narrow for large selections.
- Tighten brand-lock in `PatchService` system prompt if blue accent drifts again.
- Keep package mirror in sync under `packages/nordicai/` when changing live files.

**Don’t**

- Let the model rewrite templates or core.
- Reintroduce `empty($this->cms_user->is_admin)` in frontend AJAX actions.
- Commit API keys, `config.php`, or `backups/`.

## Completion checklist (repo standard)

When finishing a pass, report:

1. What changed  
2. What was verified  
3. Remaining risks  
4. Rollback point (commit / checkpoint)

## Related idea (future)

- Template kits with baked-in mini inspector (not Instant): [TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md](./TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md)
