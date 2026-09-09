---
name: nordicai-agent
description: Rules for Nordic AI skin agent — DeepSeek patches must be tokens/scoped CSS only, never InstantCMS tpl/core rewrites. Live inspector for admins on frontend.
---

# Nordic AI Agent

Use when working on `nordicai` or AI design patches for InstantCMS Modern/Nordic.

**Handoff (read first):** `docs/nordicai/HYPOTHESIS-MVP-HANDOFF-2026-09-09.md`  
**Worklog:** `docs/nordicai/WORKLOG-2026-09-09.md`  
**Future idea:** `docs/nordicai/TEMPLATE-INSPECTOR-KIT-IDEA-2026-09-10.md` (controllable templates, not Instant)

## Live inspector (admin)
1. Login as admin on the site (frontend session).
2. Open any public page (e.g. `/users`).
3. Bottom-right button **Выбрать элемент**.
4. Click a DOM node → panel opens with selector.
5. Prompt → Generate → Preview → **Сохранить на сайт** (writes `site_overlay_css` options).
6. Overlay is injected via hook `before_print_head` for everyone.

## Hard rules
1. Never rewrite `templates/**/*.tpl.php` or InstantCMS core via AI output.
2. Allowed outputs only: CSS variables (`tokens`) and scoped CSS overlays.
3. Prefer existing tokens (`--nordic-*`, `--primary`) over new hardcoded hex.
4. Do not change Bootstrap layout classes (`.row`/`.col-*`/`.container`) display/position.
5. Store DeepSeek key only in component options (`/admin/nordicai/options`), never commit secrets.
6. Preserve Nordic brand accent (red family), not Bootstrap blue.
7. **Never** use `empty($this->cms_user->is_admin)` in frontend AJAX actions — InstantCMS magic `__get` makes `empty()` false-positive; use `cmsUser::getInstance()` and return JSON errors, not HTML 404.

## Known gap
Large wrapper selections lack inner DOM context → model often misses intent. Next: auto-narrow / compact DOM snapshot / role heuristics.

## Key files
- `hooks/before_print_head.php` — inject overlay + admin inspector
- `actions/generate.php`, `overlay_save.php`, `overlay_clear.php`
- `templates/modern/controllers/nordicai/js/inspector.js`
- `libs/PatchService.php`, `libs/DeepSeekClient.php`
- Package mirror: `packages/nordicai/`
