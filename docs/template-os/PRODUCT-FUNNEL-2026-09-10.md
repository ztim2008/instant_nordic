# Template OS — Product Funnel (Buy / Demo / Fill / Own)

**Date:** 2026-09-10  
**Status:** product intent — **Commerce Gateway** layer (Nordic store), not Template Package core  
**Depends on:** Template Contract working in the delivered ZIP

---

## Job to be done

Buyer gets a **hostable project**, not a Figma or a dead CodePen dump.

They can:

1. Try a template in **demo** (safe sandbox).  
2. Buy / receive an **admin code**.  
3. Open admin → answer a **short questionnaire**.  
4. Receive **AI-filled** copy (DeepSeek via main Nordic service API).  
5. Fine-tune text / photos / fonts / icons inside Design Lock.  
6. Configure SEO, favicon, metrics, webmaster, optional head/body snippets.  
7. Deploy to **own VPS**; optionally attach Cursor agents using the same Contract.

---

## Two layers (do not merge)

| Layer | Lives where | Responsibility |
|-------|-------------|----------------|
| **Template Package** | ZIP / client VPS | Contract, design, inspector, validator, instance data |
| **Commerce Gateway** | Main Nordic site (seller) | Catalog, demo sandbox, purchase/codes, questionnaire → DeepSeek fill, license |

After delivery, the site must run **without** permanent dependence on Nordic cloud.  
DeepSeek fill is a **bootstrap service**, not runtime lock-in.

---

## Flows

### A. Demo (catalog)

1. Visitor opens template preview on store.  
2. Enters **demo code** (or “Try demo”).  
3. Can edit allowed text / photos / fonts / icons.  
4. On exit / timeout → instance **resets to defaults**.  
5. No SEO/head injection / export in demo (or watermarked).

### B. Purchase → own instance

1. Buyer receives **admin code** (+ download or provisioned demo URL).  
2. Opens `/admin`, authenticates with code (or password derived from code).  
3. **Onboarding questionnaire** (minimal):  
   - company name  
   - activity / description  
   - advantages (bullets)  
   - contacts (phone, email, address, socials)  
4. Store calls Nordic **DeepSeek fill API** → writes into **Instance** entities only.  
5. Buyer lands on a site already filled, then edits manually.  
6. Admin settings: SEO, favicon, metrics IDs, webmaster verify, optional `head`/`body` snippets (sanitized).  
7. Export / deploy instructions for VPS; AGENTS.md for Cursor.

### C. Self-host forever

Instance + Template Package on buyer infrastructure.  
Optional later: reconnect agents; fill API only if they choose.

---

## Questionnaire → Contract mapping (example)

| Question | Writes to |
|----------|-----------|
| Company name | `site.brandName`, `hero.title` (or mapped entities) |
| Activity | `hero.subtitle` / `about.body` |
| Advantages | `features` block instances (N items) |
| Contacts | `site.phone`, `site.email`, `footer.*` |

Fill service must use **entity IDs from Contract**, never freeform HTML injection.

---

## Security / abuse (early requirements)

- Demo codes rate-limited; no persistent PII in demo.  
- Admin codes single-tenant; rotatable.  
- Head/body snippet allowlist / sanitizer (no arbitrary script free-for-all without warning).  
- DeepSeek key stays on **Nordic server**, never inside client ZIP.  
- Fill API authenticated; scoped to purchased template + instance.

---

## Build order vs core Template OS

| When | What |
|------|------|
| MVP loop (Implementation Plan) | Package: contract → instance → inspector → validate → export |
| After loop green | Commerce Gateway: demo reset + admin code + questionnaire UI |
| After Gateway | DeepSeek fill from Nordic API |
| Later | Catalog of many templates, billing, Cursor onboarding docs |

Do **not** block Stage 0–6 of Template Package on the storefront existing.

---

## One line

**Sell a controllable instance; demo proves the feel; AI only fills Contract fields; client owns the ZIP.**
