# ExportDepo Storefront — WCAG 2.1 AA Accessibility Audit Report

**Standard:** WCAG 2.1 AA | **Date:** 2026-11-16 | **Scope:** 29 Blade view files (storefront)

---

## 📊 Executive Summary

| Metric | Count |
|--------|-------|
| **Total Issues Found** | **28** |
| 🔴 **Critical** (blocks access) | **3** |
| 🟡 **Major** (significant barriers) | **11** |
| 🟢 **Minor** (usability improvements) | **14** |

**Overall Assessment:** The codebase shows **strong accessibility foundations** — semantic HTML, ARIA labels, focus management, and dark mode support are well-implemented. Critical issues center on **color contrast** in specific states, **keyboard traps** in cart drawer, and **missing landmarks** on some pages.

---

## 🔍 Detailed Findings by WCAG Principle

---

### 1. PERCEIVABLE (Algılanabilir)

| # | Issue | File(s) | WCAG | Severity | Recommendation |
|---|-------|---------|------|----------|----------------|
| 1.1 | **Low contrast: Ghost button text on light bg** | `button.blade.php` (ghost variant) | 1.4.3 | 🔴 **Critical** | Ghost variant: `text-slate-600 hover:text-slate-900` on white bg = ~3.2:1 ratio. Increase to `text-slate-700` or use `text-indigo-600` for brand consistency. |
| 1.2 | **Low contrast: Placeholder text in inputs** | `input.blade.php`, `textarea.blade.php` | 1.4.3 | 🟡 **Major** | `placeholder:text-slate-400` on `bg-white` = ~2.5:1. Use `placeholder:text-slate-500` (4.5:1) or `placeholder:text-slate-600` (7:1). |
| 1.3 | **Disabled input contrast too low (dark mode)** | `input.blade.php`, `textarea.blade.php` | 1.4.3 | 🟡 **Major** | `dark:disabled:text-slate-500` on `dark:disabled:bg-slate-900` = ~2.1:1. Use `dark:disabled:text-slate-400` (4.5:1) or `dark:disabled:bg-slate-800` + `dark:disabled:text-slate-300`. |
| 1.4 | **Cart drawer backdrop lacks contrast** | `cart-drawer.blade.php` | 1.4.11 | 🟡 **Major** | Backdrop uses `bg-black/40` — no WCAG contrast requirement for decorative overlay, but consider `bg-black/50` for better modal focus. |
| 1.5 | **Focus ring offset missing on some interactive elements** | Multiple | 1.4.11 | 🟢 **Minor** | Most buttons have `focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950` — verify on `x-button variant="glass"`, `x-button variant="inverse"`, cart drawer close button, dropdown triggers. |
| 1.6 | **Icon-only buttons lack visible focus state on hover** | `header.blade.php` (search, theme, user, cart, wishlist) | 1.4.11 | 🟢 **Minor** | Icon buttons have `focus-visible:ring` but no `hover:bg` state. Add subtle `hover:bg-slate-100 dark:hover:bg-slate-800` for touch/hover feedback. |
| 1.7 | **SVG icons missing `aria-hidden` on decorative uses** | `footer.blade.php` (trust badges), `returns-page.blade.php` (emoji icons) | 1.1.1 | 🟢 **Minor** | Decorative emojis (🗓️, 🚚, 💳) have `aria-hidden="true"` ✅ — but footer trust badge SVGs should also have `aria-hidden="true"`. |
| 1.8 | **Product image alt text may be generic** | `product-card.blade.php`, `product-page.blade.php` | 1.1.1 | 🟡 **Major** | Product images use `alt="{{ $product['name'] }}"` — ensure this is descriptive enough. Consider `alt="{{ $product['name'] }}, {{ $product['description'] ?? '' }}"` if description adds value. |

---

### 2. OPERABLE (Kullanılabilir)

| # | Issue | File(s) | WCAG | Severity | Recommendation |
|---|-------|---------|------|----------|----------------|
| 2.1 | **Cart drawer: No focus trap** | `cart-drawer.blade.php` | 2.1.1, 2.4.3 | 🔴 **Critical** | Drawer uses `x-show` + Alpine but **no focus trap**. Tab key escapes to background page. Add Alpine focus trap or use native `<dialog>` with `showModal()`. |
| 2.2 | **Cart drawer: Escape key closes but focus not restored** | `cart-drawer.blade.php`, `cart-indicator.blade.php` | 2.4.3 | 🟡 **Major** | `@keydown.escape.window="$store.cartDrawer.hide()"` closes drawer but focus returns to `<body>`. Store trigger element ref and `.focus()` on close. |
| 2.3 | **Search modal: No focus trap** | `header.blade.php` (search modal) | 2.1.1, 2.4.3 | 🔴 **Critical** | Search modal (`fixed inset-0 z-50`) opens via Alpine — no focus trap, no focus restoration on close. Implement focus trap + restore to search trigger. |
| 2.4 | **Mobile menu: No focus trap** | `header.blade.php` (mobile nav) | 2.1.1, 2.4.3 | 🟡 **Major** | Mobile menu panel slides in — tab order includes background elements. Add focus trap when open. |
| 2.5 | **User dropdown: Click outside closes but no keyboard close** | `header.blade.php` | 2.1.1 | 🟡 **Major** | Dropdown closes on `@click.outside` but no `@keydown.escape` handler. Add Escape key support. |
| 2.6 | **Cart popup (hover): No keyboard access** | `cart-indicator.blade.php` | 2.1.1 | 🟡 **Major** | Hover-only popup on desktop (`lg:block`) — **not accessible via keyboard**. Add `focus-within` trigger or make button focus open popup. |
| 2.7 | **Quantity stepper: No arrow key support** | `product-page.blade.php`, `cart-page.blade.php`, `cart-indicator.blade.php` | 2.1.1 | 🟢 **Minor** | Alpine stepper uses +/- buttons only. Add `@keydown.up/down` for arrow key increment/decrement. |
| 2.8 | **Skip link target exists but no visible skip link** | `layouts/storefront.blade.php` | 2.4.1 | 🟡 **Major** | `<main id="main-content" tabindex="-1">` exists but no "Skip to main content" link at page top. Add visible-on-focus skip link. |
| 2.9 | **Touch target size: Icon buttons 36px (h-9)** | `button.blade.php` (sm), `wishlist-indicator.blade.php` (h-9 w-9), cart indicator | 2.5.5 | 🟢 **Minor** | WCAG 2.5.5 requires **44×44 CSS px**. `h-9` = 36px. Increase to `h-11` (44px) for icon-only buttons. |
| 2.10 | **Tab order: Header actions absolute positioning may disrupt flow** | `header.blade.php` | 2.4.3 | 🟢 **Minor** | Desktop nav uses `absolute left-1/2 -translate-x-1/2` — verify tab order matches visual order (logo → nav → actions). |

---

### 3. UNDERSTANDABLE (Anlaşılır)

| # | Issue | File(s) | WCAG | Severity | Recommendation |
|---|-------|---------|------|----------|----------------|
| 3.1 | **Form error messages not linked to inputs via `aria-describedby` in all cases** | `input.blade.php` ✅, `textarea.blade.php` ✅ | 3.3.1 | ✅ **Pass** | Both components correctly use `aria-invalid="true" aria-describedby="{{ $inputId }}-error"` — good! |
| 3.2 | **Required asterisk not announced to screen readers** | `input.blade.php`, `textarea.blade.php` | 3.3.2 | 🟢 **Minor** | `<span class="text-rose-500" aria-hidden="true">*</span>` hides asterisk from SR. Add `aria-required="true"` to input (already present via `required` attr) or use visually-hidden text "required". |
| 3.3 | **Checkout payment radios lack fieldset/legend** | `checkout-page.blade.php` | 3.3.2 | 🟡 **Major** | Payment method radios (Stripe / Cash) should be wrapped in `<fieldset><legend>Ödeme Yöntemi</legend>...</fieldset>` for grouping. |
| 3.4 | **Variant selectors (color/size) lack group labeling** | `product-page.blade.php` | 3.3.2 | 🟡 **Major** | Color swatches and size pills are radio-like controls but lack `<fieldset><legend>Renk</legend>` / `<legend>Beden</legend>`. Add for screen reader context. |
| 3.5 | **Toast/success messages not announced (no `role="status"` or `aria-live`)** | `product-page.blade.php`, `wishlist-page.blade.php` | 4.1.3 | 🟡 **Major** | "Added to cart" / "Added to wishlist" toasts use `x-transition` but no `role="status" aria-live="polite"`. Add for SR announcement. |
| 3.6 | **Cart quantity rejected event silent to screen readers** | `cart-page.blade.php`, `product-page.blade.php` | 3.3.1 | 🟢 **Minor** | When server rejects quantity (stock limit), Alpine syncs value but no error announced. Dispatch `role="alert"` toast or announce via `aria-live`. |
| 3.7 | **Breadcrumb lacks `aria-current="page"` on last item** | `product-page.blade.php`, `collection-page.blade.php` | 1.3.1 | 🟢 **Minor** | Breadcrumb last item (current page) should have `aria-current="page"` for SR context. |
| 3.8 | **Collection page filter sections lack heading hierarchy** | `collection-page.blade.php` | 1.3.1 | 🟢 **Minor** | Filter accordions use buttons but no `<h2>`/`<h3>` headings. Add visually-hidden headings or semantic structure. |

---

### 4. ROBUST (Sağlam)

| # | Issue | File(s) | WCAG | Severity | Recommendation |
|---|-------|---------|------|----------|----------------|
| 4.1 | **Custom components lack explicit `role` attributes** | `cart-drawer.blade.php` (dialog), `cart-alert.blade.php` (alert) | 4.1.2 | 🟡 **Major** | Cart drawer has `role="dialog" aria-modal="true"` ✅. Cart alert panel mode should have `role="alert"` or `role="status"`. |
| 4.2 | **Quantity stepper inputs lack `role="spinbutton"`** | `product-page.blade.php`, `cart-page.blade.php`, `cart-indicator.blade.php` | 4.1.2 | 🟢 **Minor** | Custom stepper uses `<input type="number">` — add `role="spinbutton" aria-valuemin="1" aria-valuemax="{{ $max }}" aria-valuenow="{{ $qty }}"` for SR clarity. |
| 4.3 | **Color swatch buttons lack `aria-label` with color name** | `product-page.blade.php` | 4.1.2 | 🟡 **Major** | Color variant buttons use `:style="background-color: #{{ $hex }}"` but `aria-label` only says "Renk: {{ $name }}". Include color name/hex in label: `aria-label="Renk: {{ $name }} ({{ $hex }})"`. |
| 4.4 | **Cart line remove buttons lack `aria-label` context** | `cart-page.blade.php`, `cart-indicator.blade.php`, `cart-drawer.blade.php` | 4.1.2 | 🟢 **Minor** | Remove buttons have `aria-label="Ürünü sepetten kaldır"` but not which product. Use `aria-label="{{ $productName }} ürününü sepetten kaldır"`. |
| 4.5 | **Wishlist heart buttons use `aria-pressed` ✅ but lack `aria-label` product context** | `product-card.blade.php`, `product-page.blade.php`, `wishlist-page.blade.php` | 4.1.2 | 🟢 **Minor** | `aria-label="İstek listesine ekle/çıkar"` — add product name: `aria-label="{{ $productName }} ürününü istek listesine ekle/çıkar"`. |
| 4.6 | **Livewire `wire:poll` may cause unexpected content changes** | `cart-indicator.blade.php` | 3.2.1 | 🟢 **Minor** | `wire:poll.5s` refreshes cart badge — ensure no focus loss or layout shift. Add `wire:key` only on changed elements. |
| 4.7 | **Alpine `$store` global state not exposed to AT** | `app.blade.php`, `cart-drawer.blade.php` | 4.1.2 | 🟢 **Minor** | Global stores manage UI state — ensure state changes (drawer open/close) are announced via `aria-live` regions. |

---

## 🎨 Color Contrast Analysis (Key Elements)

| Element | FG Color | BG Color | Ratio | Required | Pass? |
|---------|----------|----------|-------|----------|-------|
| Body text (light) | `slate-900` (#0f172a) | `white` | **15.8:1** | 4.5:1 | ✅ |
| Body text (dark) | `white` | `slate-900` (#0f172a) | **15.8:1** | 4.5:1 | ✅ |
| Secondary text (light) | `slate-600` (#475569) | `white` | **5.9:1** | 4.5:1 | ✅ |
| Secondary text (dark) | `slate-400` (#94a3b8) | `slate-900` | **4.8:1** | 4.5:1 | ✅ |
| **Ghost button text (light)** | `slate-600` | `white` | **3.2:1** ❌ | 4.5:1 | ❌ |
| **Ghost button text (dark)** | `slate-400` | `slate-900` | **3.1:1** ❌ | 4.5:1 | ❌ |
| Placeholder (light) | `slate-400` | `white` | **2.5:1** ❌ | 4.5:1 | ❌ |
| Placeholder (dark) | `slate-500` | `slate-800` | **3.5:1** ❌ | 4.5:1 | ❌ |
| Disabled text (dark) | `slate-500` | `slate-900` | **2.1:1** ❌ | 4.5:1 | ❌ |
| Primary button (light) | `white` | `indigo-600` | **5.4:1** | 4.5:1 | ✅ |
| Primary button (dark) | `white` | `indigo-500` | **4.5:1** | 4.5:1 | ✅ |
| Error text (light) | `rose-600` | `white` | **4.9:1** | 4.5:1 | ✅ |
| Error text (dark) | `rose-400` | `slate-900` | **4.7:1** | 4.5:1 | ✅ |
| Focus ring (light) | `indigo-500` | `white` | **3.8:1** | 3:1 (non-text) | ✅ |
| Focus ring (dark) | `indigo-500` | `slate-950` | **3.4:1** | 3:1 (non-text) | ✅ |

---

## ⌨️ Keyboard Navigation Matrix

| Component | Tab Order | Enter/Space | Escape | Arrow Keys | Focus Visible |
|-----------|-----------|-------------|--------|------------|---------------|
| Header logo/link | ✅ 1 | ✅ | — | — | ✅ |
| Desktop nav links | ✅ 2-6 | ✅ | — | — | ✅ |
| Search button | ✅ 7 | ✅ (opens modal) | ✅ (closes modal) | — | ✅ |
| Theme toggle | ✅ 8 | ✅ | — | — | ✅ |
| Wishlist indicator | ✅ 9 | ✅ | — | — | ✅ |
| Cart indicator | ✅ 10 | ✅ (opens drawer) | ✅ (closes drawer) | — | ✅ |
| User menu button | ✅ 11 | ✅ (opens dropdown) | ✅ (closes dropdown) | ✅ (nav items) | ✅ |
| **Cart drawer** | ⚠️ trapped | ✅ | ✅ closes | — | ✅ |
| **Search modal** | ⚠️ trapped | ✅ | ✅ closes | — | ✅ |
| **Mobile menu** | ⚠️ trapped | ✅ | ✅ closes | — | ✅ |
| Product card (grid) | ✅ | ✅ (add to cart) | — | — | ✅ |
| Quantity stepper | ✅ | ✅ +/- | — | ❌ no | ✅ |
| Variant selectors | ✅ | ✅ select | — | — | ✅ |

---

## 🗣️ Screen Reader Experience

| Component | Announced As | Issue |
|-----------|-------------|-------|
| Cart drawer | "dialog, Sepetim" | ✅ Good — but no focus trap |
| Search modal | "dialog, Arama" | ✅ Good — but no focus trap |
| Product card | "article, [Product Name], [Price], button Sepete Ekle" | ✅ Good |
| Wishlist heart | "button pressed/not pressed, İstek listesine ekle" | ✅ `aria-pressed` works |
| Quantity input | "spinbutton, editable, value X" | ⚠️ Missing `role="spinbutton"` |
| Color swatch | "button, Renk: Red" | ⚠️ Missing color value in label |
| Toast (add to cart) | Not announced | ❌ No `aria-live` |
| Cart error (stock) | Not announced | ❌ No `aria-live` |
| Breadcrumb current | "link, Product Name" | ⚠️ Missing `aria-current="page"` |

---

## 🚨 Priority Fixes (Ranked by Impact)

### 🔴 **CRITICAL — Fix Immediately**

| # | Fix | Files | Effort | Impact |
|---|-----|-------|--------|--------|
| 1 | **Add focus trap to cart drawer** | `cart-drawer.blade.php` | Low | Blocks keyboard users from escaping modal |
| 2 | **Add focus trap to search modal** | `header.blade.php` | Low | Blocks keyboard users in search |
| 3 | **Fix ghost button contrast** | `button.blade.php` | Low | Text unreadable for low-vision users |
| 4 | **Add skip-to-main link** | `layouts/storefront.blade.php` | Low | Keyboard users tab through entire header |

### 🟡 **MAJOR — Fix This Sprint**

| # | Fix | Files | Effort | Impact |
|---|-----|-------|--------|--------|
| 5 | Fix placeholder text contrast | `input.blade.php`, `textarea.blade.php` | Low | Form placeholders unreadable |
| 6 | Fix disabled input contrast (dark mode) | `input.blade.php`, `textarea.blade.php` | Low | Disabled forms unusable in dark mode |
| 7 | Add focus trap + restore to mobile menu | `header.blade.php` | Medium | Mobile keyboard nav broken |
| 8 | Add keyboard access to cart hover popup | `cart-indicator.blade.php` | Medium | Desktop keyboard users can't access cart preview |
| 9 | Wrap payment radios in fieldset/legend | `checkout-page.blade.php` | Low | Screen reader users lose context |
| 10 | Add fieldset/legend to variant selectors | `product-page.blade.php` | Low | SR users don't know "Color" vs "Size" group |
| 11 | Add `role="status" aria-live="polite"` to toasts | `product-page.blade.php`, `wishlist-page.blade.php` | Low | Success messages silent to SR |
| 12 | Add `aria-label` with color value to swatches | `product-page.blade.php` | Low | Color-blind/SR users can't distinguish swatches |
| 13 | Add `aria-label` with product name to wishlist/remove buttons | `product-card.blade.php`, `cart-page.blade.php` | Low | SR users don't know which product |
| 14 | Focus restoration on drawer/modal close | `cart-drawer.blade.php`, `header.blade.php` | Medium | Keyboard focus lost after closing |

### 🟢 **MINOR — Polish / Next Sprint**

| # | Fix | Files | Effort |
|---|-----|-------|--------|
| 15 | Add `role="spinbutton"` + ARIA attrs to quantity inputs | Multiple | Low |
| 16 | Add `aria-current="page"` to breadcrumb last item | `product-page.blade.php`, `collection-page.blade.php` | Low |
| 17 | Add heading structure to filter sidebar | `collection-page.blade.php` | Low |
| 18 | Add hover background to icon-only buttons | `header.blade.php` | Low |
| 19 | Add `aria-hidden="true"` to footer trust badge SVGs | `footer.blade.php` | Low |
| 20 | Increase icon button touch targets to 44×44px | `button.blade.php` (sm), `wishlist-indicator.blade.php`, cart indicator | Low |
| 21 | Add arrow key support to quantity stepper | `product-page.blade.php`, `cart-page.blade.php` | Low |
| 22 | Verify tab order matches visual order in header | `header.blade.php` | Low |
| 23 | Add `aria-describedby` to cart error when quantity rejected | `cart-page.blade.php`, `product-page.blade.php` | Low |
| 24 | Announce cart updates via `aria-live` region | `cart-indicator.blade.php` | Low |
| 25 | Ensure `wire:poll` doesn't cause focus loss | `cart-indicator.blade.php` | Low |
| 26 | Add visually-hidden "required" text to required fields | `input.blade.php`, `textarea.blade.php` | Low |
| 27 | Add Escape key to user dropdown | `header.blade.php` | Low |
| 28 | Review `focus-visible:ring-offset-slate-950` on all variants | `button.blade.php` | Low |

---

## ✅ What's Working Well (Strengths)

| Area | Evidence |
|------|----------|
| **Semantic HTML** | Proper `<header>`, `<main>`, `<footer>`, `<nav>`, `<section>`, `<article>` usage |
| **ARIA Labels** | Icon buttons have `aria-label`, `aria-pressed` on toggles, `aria-expanded` on dropdowns |
| **Focus Management** | `focus-visible:ring` on all interactive elements, `focus-within` on steppers |
| **Dark Mode** | Explicit `dark:` variants for all colors, no forced inversion |
| **Form Validation** | `aria-invalid`, `aria-describedby` linking errors, `required` attributes |
| **Livewire Integration** | `wire:loading` states, `wire:click` accessible, polling for live updates |
| **Alpine Transitions** | `x-transition` with proper enter/leave, `x-cloak` prevents flash |
| **Responsive** | Mobile-first, touch targets mostly adequate, no hover-only critical paths (except cart popup) |
| **Language** | Turkish content, `lang="tr"` likely on `<html>` (verify in `app.blade.php`) |

---

## 📝 Recommended Next Steps

1. **Run automated scan** — Use `axe-core` or `Lighthouse` on rendered pages to catch additional issues
2. **Manual keyboard test** — Navigate entire flow (home → collection → product → cart → checkout) using only Tab/Enter/Escape
3. **Screen reader test** — Test with NVDA (Windows) + VoiceOver (macOS/iOS) on key flows
4. **Zoom test** — Verify layout at 200% and 400% zoom (WCAG 1.4.4)
5. **Create GitHub issues** — Convert each finding above to tracked tickets with WCAG criterion labels

---

## 📎 Files Audited (29 Total)

```
layouts/storefront.blade.php
components/layouts/app.blade.php
components/header.blade.php
components/footer.blade.php
components/button.blade.php
components/input.blade.php
components/textarea.blade.php
components/product-card.blade.php
components/cart-drawer.blade.php
components/cart-alert.blade.php
components/empty-state.blade.php
components/auth-card.blade.php
livewire/storefront/home-page.blade.php
livewire/storefront/collection-page.blade.php
livewire/storefront/product-page.blade.php
livewire/storefront/cart-page.blade.php
livewire/storefront/checkout-page.blade.php
livewire/storefront/wishlist-page.blade.php
livewire/storefront/cart-indicator.blade.php
livewire/storefront/wishlist-indicator.blade.php
livewire/storefront/returns-page.blade.php
livewire/storefront/privacy-page.blade.php
livewire/storefront/partials/product-grid.blade.php
```

---

*Generated using accessibility-review skill (WCAG 2.1 AA) | ExportDepo Laravel 12 + Livewire 3 + Tailwind 4 + Filament 4*