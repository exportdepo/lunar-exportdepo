# ExportDepo Design System

Comprehensive documentation of the UI/UX design system used across all storefront views. This serves as the single source of truth for design tokens, component patterns, and implementation guidelines.

---

## 1. Design Tokens

### 1.1 Color Palette

#### Brand Colors
| Token | Light | Dark | Usage |
|-------|-------|------|-------|
| `indigo-500` | `#6366f1` | `#818cf8` | Primary brand, primary buttons, focus rings, active states |
| `indigo-600` | `#4f46e5` | `#6366f1` | Primary button hover, badge backgrounds, text accents |
| `indigo-700` | `#4338ca` | `#4f46e5` | Primary button active, text links |
| `purple-600` | `#9333ea` | `#a855f7` | Gradient endpoints (hero) |
| `pink-500` | `#ec4899` | `#f472b6` | Gradient endpoints (hero) |

#### Semantic Colors
| Token | Light | Dark | Usage |
|-------|-------|------|-------|
| `emerald-500` | `#10b981` | `#34d399` | Success states, in-stock badges, success alerts |
| `emerald-600` | `#059669` | `#10b981` | Success button hover |
| `rose-500` | `#f43f5e` | `#fb7185` | Danger states, out-of-stock, remove actions, error alerts |
| `rose-600` | `#e11d48` | `#f43f5e` | Danger button hover, wishlist active |
| `amber-500` | `#f59e0b` | `#fbbf24` | Low stock warnings, rating stars |
| `amber-400` | `#fbbf24` | `#fcd34d` | Rating stars (filled) |

#### Neutral Scale (Slate)
| Token | Light | Dark | Usage |
|-------|-------|------|-------|
| `slate-50` | `#f8fafc` | — | Subtle backgrounds |
| `slate-100` | `#f1f5f9` | — | Card backgrounds, input backgrounds |
| `slate-200` | `#e2e8f0` | — | Borders, dividers |
| `slate-300` | `#cbd5e1` | — | Input borders, placeholder text |
| `slate-400` | `#94a3b8` | `#94a3b8` | Disabled text, placeholder |
| `slate-500` | `#64748b` | `#64748b` | Secondary text, icons |
| `slate-600` | `#475569` | `#475569` | Body text (light), hover states |
| `slate-700` | `#334155` | `#334155` | Headings (light), label text |
| `slate-800` | `#1e293b` | `#1e293b` | Card borders (dark), headings (dark) |
| `slate-900` | `#0f172a` | `#0f172a` | Primary text (dark), footer background |
| `slate-950` | `#020617` | — | Focus ring offset (dark) |

#### Special Backgrounds
| Pattern | Classes | Usage |
|---------|---------|-------|
| Hero gradient | `bg-linear-to-br from-indigo-600 via-purple-600 to-pink-500` | Homepage hero |
| Brand badge | `bg-linear-to-br from-indigo-500 to-purple-600` | Footer logo, auth card icons |
| Card hover overlay | `bg-linear-to-t from-slate-950/80 via-slate-900/20 to-transparent` | Collection cards |
| Glass/backdrop | `backdrop-blur-sm bg-white/10 border-white/20` | Hero stat cards, glass buttons |

### 1.2 Typography

#### Font Family
```css
font-family: 'Inter', system-ui, sans-serif;
```
Loaded via `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap')` in `app.blade.php`.

#### Type Scale
| Element | Class | Size | Weight | Line Height | Usage |
|---------|-------|------|--------|-------------|-------|
| Hero H1 | `text-4xl sm:text-5xl lg:text-6xl` | 36-60px | `font-extrabold` | `tracking-tight` | Homepage hero |
| Page H1 | `text-3xl font-extrabold tracking-tight sm:text-4xl` | 30-36px | `font-extrabold` | `tracking-tight` | Page titles (cart, checkout, collection) |
| Section H2 | `text-2xl font-bold` | 24px | `font-bold` | — | Section headers (featured, categories) |
| Card H3 | `text-sm font-semibold` / `text-base font-semibold` | 14-16px | `font-semibold` | — | Product names, cart items |
| Body | `text-sm` / `text-base` | 14-16px | `font-normal` | `leading-7` | Descriptions, paragraphs |
| Small | `text-xs` | 12px | `font-medium`/`font-normal` | — | Labels, badges, prices |
| Price XL | `text-3xl font-extrabold tracking-tight` | 30px | `font-extrabold` | — | Product page price |
| Price Total | `text-2xl font-extrabold` / `text-base font-bold` | 18-24px | `font-extrabold`/`font-bold` | — | Order summary totals |

#### Text Color Patterns
```blade
<!-- Light mode primary -->
text-slate-900
<!-- Dark mode primary -->
dark:text-white

<!-- Light mode secondary -->
text-slate-600 / text-slate-500 / text-slate-400
<!-- Dark mode secondary -->
dark:text-slate-300 / dark:text-slate-400 / dark:text-slate-500

<!-- Brand accent -->
text-indigo-600 dark:text-indigo-400
```

### 1.3 Spacing System

Base unit: **4px** (Tailwind default). Common patterns:

| Pattern | Classes | Usage |
|---------|---------|-------|
| Page section vertical | `py-10 sm:py-12 lg:py-14` / `pt-16` | Section padding |
| Container horizontal | `px-4 sm:px-6 lg:px-8` | Page container padding |
| Card internal | `p-4` / `p-5` / `p-6` / `sm:p-8` | Card content padding |
| Element gaps | `gap-2` / `gap-3` / `gap-4` / `gap-6` / `gap-8` | Flex/grid gaps |
| Inline gaps | `gap-1.5` / `gap-2` / `gap-2.5` / `gap-3` | Button groups, badge clusters |
| Section margins | `mb-6` / `mb-8` / `mt-6` / `mt-8` | Between sections |

### 1.4 Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `rounded` | 4px | Small elements (badges, icon buttons) |
| `rounded-lg` | 8px | Buttons, inputs, dropdowns, popups |
| `rounded-xl` | 12px | Product images, cart line images |
| `rounded-2xl` | 16px | **Primary card radius** (product cards, cart lines, alerts, empty states, modals) |
| `rounded-3xl` | 24px | Product page main image |
| `rounded-full` | 9999px | Pills (tags, quantity steppers), avatar/icon circles |

### 1.5 Shadows

| Token | Usage |
|-------|-------|
| `shadow-sm` | Default card elevation (product cards, cart lines, footer trust badges) |
| `shadow-md` | Hover states, primary buttons |
| `shadow-xl` | Collection card hover, cart drawer panel |
| `shadow-2xl` | Cart drawer container |
| `shadow-lg` | Stock badges, wishlist remove button |

### 1.6 Transitions & Animation

```css
/* Standard transitions */
transition-all duration-150        /* Buttons, interactive elements */
transition-colors duration-150     /* Color-only changes */
transition-transform duration-500  /* Image zoom on hover (collection cards) */
transition-all duration-300        /* Cart drawer slide, popup fade */

/* Alpine.js transitions */
x-transition.opacity.duration.300ms
x-transition:enter="ease-out duration-300"
x-transition:enter-start="translate-x-full"
x-transition:enter-end="translate-x-0"
x-transition:leave="ease-in duration-200"
x-transition:leave-start="translate-x-0"
x-transition:leave-end="translate-x-full"
```

Custom keyframes (in `app.css`):
```css
@keyframes fade-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes stagger-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.anim-fade-up { animation: fade-up 0.6s ease-out forwards; }
.stagger-in > * { animation: stagger-in 0.5s ease-out forwards; }
.stagger-in > *:nth-child(1) { animation-delay: 0ms; }
.stagger-in > *:nth-child(2) { animation-delay: 100ms; }
/* ... up to nth-child(5) */
```

### 1.7 Z-Index Scale

| Layer | Value | Usage |
|-------|-------|-------|
| Base content | 0-10 | Normal flow |
| Sticky header | 40 | `z-40` on header |
| Dropdowns/Popups | 50 | Cart popup, search modal |
| Drawer/Modal | 50 | Cart drawer, modals |
| Backdrop | 50 | Cart drawer backdrop |
| Toasts/Tooltips | 50+ | Notifications |

---

## 2. Component Library

### 2.1 Atomic Components

#### Button (`components/button.blade.php`)
**Variants:** `primary`, `secondary`, `outline`, `ghost`, `inverse`, `glass`, `danger`
**Sizes:** `sm` (h-9), `md` (h-11), `lg` (h-12)
**States:** `disabled`, `loading` (spinner), `block` (full width)
**Renders as:** `<button>` or `<a>` (if `href` provided)

```blade
<x-button variant="primary" size="md">Sepete Ekle</x-button>
<x-button variant="outline" size="sm" href="/sepet">Sepeti Gör</x-button>
<x-button variant="glass" size="lg" href="#kategoriler">Kategorileri Gör</x-button>
<x-button variant="inverse" size="lg" href="#one-cikanlar">Alışverişe Başla</x-button>
```

**Key Classes:**
- Base: `inline-flex select-none items-center justify-center gap-2 whitespace-nowrap rounded-lg font-semibold transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950 disabled:pointer-events-none disabled:opacity-50`

#### Input (`components/input.blade.php`)
**Props:** `name`, `label`, `type`, `value`, `placeholder`, `hint`, `error`, `required`, `disabled`
**Auto-resolves:** `old($name)` for classic forms, `$errors` bag for validation

```blade
<x-input name="email" type="email" label="E-posta" placeholder="ornek@site.com" required />
<x-input name="firstName" label="Ad" wire:model="firstName" />
```

**States:** Default, focus (indigo ring), error (rose border/ring), disabled (slate-50/900 bg)

#### Textarea (`components/textarea.blade.php`)
Similar to input, multi-line.

#### Product Card (`components/product-card.blade.php`)
**Props:** `product` (array), `addToCart` (bool), `showWishlist` (bool), `inWishlist` (bool)
**Features:**
- Image with fallback (📦)
- Wishlist heart button (icon-only, `aria-pressed`)
- Add to cart button (icon-only, disabled when out of stock)
- Rating stars + count
- Tag badges (max 3, lowercase)
- Stock status badge (sold out / low stock)
- `h-full` flex column for equal height in grids

```blade
<x-product-card :product="$product" :show-wishlist="true" :in-wishlist="in_array($product['id'], $wishlistIds)" />
```

#### Cart Alert (`components/cart-alert.blade.php`)
**Two modes:**
1. **Legacy error banner:** `<x-cart-alert :message="$cartError" />` → rose inline alert
2. **Panel mode:** `<x-cart-alert type="success" icon="✓" title="...">content<x-slot:footer>...</x-slot:footer></x-cart-alert>` → centered card

**Types:** `error` (rose), `success` (emerald), `info` (indigo)

#### Empty State (`components/empty-state.blade.php`)
**Props:** `title`, `description`, `icon` (default 📦), `slot` for CTA
**Style:** Dashed border, centered, rounded-2xl

```blade
<x-empty-state icon="🔍" title="Aramanızla eşleşen ürün bulunamadı" description="Farklı anahtar kelimelerle tekrar deneyin.">
    <x-button href="{{ url('/') }}" size="sm">Tüm ürünlere dön</x-button>
</x-empty-state>
```

#### Auth Card (`components/auth-card.blade.php`)
**Props:** `icon` (giris/kayit/sifre), `title`, `description`, `slot` (form), `footer` (slot)
**Style:** Centered icon badge (indigo-purple gradient), card with rounded-2xl, max-w-md

#### Cart Drawer (`components/cart-drawer.blade.php`)
**Props:** `title`, `slot` (scrollable content), `footer` (slot, sticky bottom)
**Alpine-driven:** `$store.cartDrawer.open`, transitions (slide from right, fade backdrop)
**No backdrop-blur** (would break fixed positioning)

---

### 2.2 Composite Components

#### Header (`components/header.blade.php`)
**Structure:**
- Sticky (`sticky top-0 z-40`), white/slate-900 bg, border-b
- Desktop: Logo | Navigation (absolute center) | Actions (search, theme, wishlist, cart, user)
- Mobile: Hamburger menu (Alpine `mobileOpen`)
- Search modal (Alpine, centered, `fixed inset-0 z-50`)
- User dropdown (Alpine, same pattern for auth/guest)
- **Cart integration:** `livewire:storefront.cart-indicator` (badge + popup + drawer)

**Key Decision:** No `backdrop-blur` on cart drawer backdrop — would position fixed drawer relative to header instead of viewport.

#### Footer (`components/footer.blade.php`)
**Layers (top to bottom):**
1. Trust badges (4 icons in grid) — slate-800 bg, indigo-400 icons
2. Newsletter band — `livewire:storefront.newsletter-subscription`
3. Link grid (5 cols lg): Brand | Categories | Corporate | Account (auth-aware) | Order Tracking (Alpine form → `/siparis/{ref}`)
4. Copyright + payment badges (dynamic: shows Stripe only if configured, else cash-on-delivery only)

**Theme:** Always dark (slate-900), no `dark:` variants needed.

#### Cart Indicator (`livewire/storefront/cart-indicator.blade.php`)
**Three UIs in one:**
1. **Button + Badge:** Cart icon, count badge (indigo-600), total price (xl+)
2. **Hover Popup (desktop lg+):** Alpine hover intent (200ms delay), shows lines, totals, CTAs
3. **Drawer (mobile/click):** `<x-cart-drawer>` with same content

**Polling:** `wire:poll.5s` for live updates.

---

### 2.3 Page-Level Patterns

#### Layout Wrapper (`layouts/storefront.blade.php`)
```blade
<x-layouts.app :title="$title" :description="$description" :ogImage="$ogImage">
    <x-header />
    <main id="main-content" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot }}
    </main>
    <x-footer />
</x-layouts.app>
```
- `flex-1` for sticky footer
- `focus:outline-none` on main for skip-link accessibility

#### Product Grid Partial (`livewire/storefront/partials/product-grid.blade.php`)
**Props:** `gridProducts`, `wishlistIds`, `gridClasses`
**Default grid:** `grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4`
**Dense grid (home/wishlist):** `... 2xl:grid-cols-5`
**PLP grid (with sidebar):** `grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4`

#### Collection Page (PLP) — `livewire/storefront/collection-page.blade.php`
**Layout:** `lg:grid lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-8`
- Left: Sticky filters (`lg:sticky lg:top-24`) — accordion sections (search, categories, variant attributes)
- Right: Product grid + active filter chips + results count
- Mobile: Filter toggle button → Alpine panel slide-down
- Empty state handling

#### Product Page (PDP) — `livewire/storefront/product-page.blade.php`
**Layout:** `lg:grid lg:grid-cols-2 lg:items-start`
- Left: Gallery (Alpine `activeIndex`, main image aspect-square rounded-3xl, thumbnail strip)
- Right: Sticky panel with:
  - Breadcrumb, collection link
  - H1 + tag badges (indigo-50/100)
  - Rating stars + review link
  - Price (text-3xl) + "KDV dahil"
  - Description (line-clamp-3)
  - Feature list (check icons, emerald-600)
  - Trust badges (3-col grid: shipping, returns, secure)
  - Variant selectors (color = round swatches with hex, size = pills)
  - Stock indicator (green dot + count)
  - Quantity stepper (Alpine) + Wishlist heart (icon-only) + Add to cart (x-button lg)
  - Success toast on add
- Tabs below: Description / Reviews (with form) / Returns

#### Cart Page — `livewire/storefront/cart-page.blade.php`
**Layout:** `lg:grid lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-8`
- Left: Lines list (card per line: image, name, options, price, Alpine quantity stepper, remove button)
- Right: Sticky order summary (subtotal, VAT, shipping, total, CTA to checkout)
- Empty state with CTA

#### Checkout Page — `livewire/storefront/checkout-page.blade.php`
**Layout:** `lg:grid lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-8`
- Left: Address form (x-input fields in grid), Payment method radios (Stripe / Cash), Place order button
- Right: Sticky order summary (same as cart)
- Success state: Full-width centered card with order reference, payment method, CTAs

#### Home Page — `livewire/storefront/home-page.blade.php`
**Sections (conditional on search):**
1. **Hero:** Full-screen gradient, centered content, glass stat cards, dual CTAs (inverse + glass)
2. **Featured Products:** Section header + product grid (8 products, 5-col max)
3. **Categories:** Image cards (aspect-4/3, gradient overlay, hover scale)
4. **All Products:** Remaining products in grid
5. **Search Results Mode:** Results-first layout when `?q=` present

#### Wishlist Page — `livewire/storefront/wishlist-page.blade.php`
- Auth banner for guests
- Grid of wishlist items (custom card: image, name, rating, tags, price/stock, remove heart, add-to-cart button)
- "Added to cart" toast outside grid (prevents height jumping)

---

## 3. Responsive Breakpoints

| Breakpoint | Min Width | Usage |
|------------|-----------|-------|
| `sm` | 640px | 2-col grids, horizontal flex, larger padding |
| `md` | 768px | — |
| `lg` | 1024px | **Primary desktop**: sticky sidebars, 2-col layouts, hover popups |
| `xl` | 1280px | 4-col product grids |
| `2xl` | 1536px | 5-col product grids |

**Key Responsive Patterns:**
- Mobile-first: `grid-cols-1` → `sm:grid-cols-2` → `lg:grid-cols-3` → `xl:grid-cols-4` → `2xl:grid-cols-5`
- Sidebar layouts: `lg:grid lg:grid-cols-[18rem_minmax(0,1fr)]` (filters) or `[minmax(0,1fr)_20rem]` (summary)
- Hidden on mobile: `hidden lg:block` (hover popup, desktop nav)
- Mobile-only: `lg:hidden` (hamburger, filter toggle)

---

## 4. Accessibility Patterns

### 4.1 ARIA & Semantic HTML
- `role="dialog" aria-modal="true" aria-labelledby` on cart drawer
- `role="alert"` on error banners, `role="status"` on success toasts
- `aria-label` / `aria-labelledby` on icon-only buttons
- `aria-pressed` on toggle buttons (wishlist heart, variant selectors)
- `aria-selected` on tabs (product page, gallery thumbnails)
- `aria-expanded` / `aria-haspopup` / `aria-controls` on dropdown triggers
- `aria-hidden="true"` on decorative icons/SVGs
- `<nav aria-label="...">` for breadcrumbs
- `<section aria-label="...">` for labeled regions

### 4.2 Focus Management
- `focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950` on all interactive elements
- `focus-within:ring-2 focus-within:ring-indigo-500/30` on quantity steppers
- Skip link target: `<main id="main-content" tabindex="-1">`

### 4.3 Keyboard Navigation
- Escape key closes modals/drawers/dropdowns (`@keydown.escape.window`)
- Click outside closes (`@click.outside`)
- Tab trapping in cart drawer (native `<dialog>`-like behavior via Alpine)

---

## 5. Dark Mode Strategy

**Class-based:** `<html class="scroll-smooth">` + `dark:` variants throughout
**No OS preference detection** — user toggles via header theme button (Alpine store)
**Color approach:** Explicit `dark:` variants for every color, not automatic inversion

**Patterns:**
```blade
<!-- Text -->
text-slate-900 dark:text-white
text-slate-600 dark:text-slate-400

<!-- Backgrounds -->
bg-white dark:bg-slate-900
bg-slate-50 dark:bg-slate-800
bg-slate-100 dark:bg-slate-800

<!-- Borders -->
border-slate-200 dark:border-slate-700
border-slate-300 dark:border-slate-600

<!-- Inputs -->
dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500

<!-- Focus rings -->
focus-visible:ring-offset-slate-950
```

**Exceptions:** Footer always dark (slate-900), hero gradient works both modes.

---

## 6. Alpine.js Integration Patterns

### 6.1 Global Stores (`window.$store`)
```javascript
// In app.blade.php
window.$store = {
    cartDrawer: { open: false, toggle(), show(), hide() },
    // ... other stores
}
```

### 6.2 Component-Level State
```blade
<!-- Quantity stepper (reused in PDP, Cart, Cart Popup, Cart Drawer) -->
<div x-data="{ qty: {{ $line['quantity'] }} }"
     x-on:cart-quantity-rejected.window="if ($event.detail.lineId === {{ $line['id'] }}) qty = $event.detail.quantity">
    <button @click="qty = Math.max(1, qty - 1); $wire.updateQuantity({{ $line['id'] }}, qty)">−</button>
    <input type="number" x-model.number="qty" @change="$wire.updateQuantity({{ $line['id'] }}, $event.target.value)" />
    <button @click="qty = Math.min({{ $max }}, qty + 1); $wire.updateQuantity({{ $line['id'] }}, qty)">+</button>
</div>
```

### 6.3 Transitions
- `x-show` + `x-transition.opacity.duration.300ms` (fade)
- `x-transition:enter/leave` with `translate-x` (slide drawer)
- `x-cloak` to prevent flash

### 6.4 Event Dispatching (Livewire → Alpine)
```php
// In Livewire component
$this->dispatch('cart-quantity-rejected', lineId: $id, quantity: $serverQty);
```
```blade
<!-- In Alpine component -->
x-on:cart-quantity-rejected.window="if ($event.detail.lineId === {{ $line['id'] }}) qty = $event.detail.quantity"
```

---

## 7. Livewire Integration Patterns

### 7.1 Wire Directives
| Directive | Usage |
|-----------|-------|
| `wire:click` | Actions (addToCart, removeLine, toggleWishlist) |
| `wire:model.lazy` | Debounced inputs (search, filters) |
| `wire:model.live` | Real-time selects (variant dropdown) |
| `wire:submit` | Form submissions (checkout, review) |
| `wire:loading.attr="disabled"` | Disable during request |
| `wire:loading.remove` / `wire:loading` | Loading text swap |
| `wire:key` | Force re-render (toasts, dynamic lists) |
| `wire:poll.5s` | Live cart indicator updates |

### 7.2 Validation
```blade
@error('field')
    <p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
@enderror
```
Or `#[Validate]` attribute in component class.

### 7.3 Component Communication
- Events: `$this->dispatch('event-name', data)` → Alpine `x-on:event-name.window`
- Cart updates: `cart-updated` event triggers `wire:poll` refresh

---

## 8. Form Patterns

### 8.1 Input Fields
- `<x-input>` component for all text/email/tel/number fields
- Labels: `mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300`
- Required asterisk: `<span class="text-rose-500" aria-hidden="true">*</span>`
- Error state: rose border/ring, error message with icon
- Hint text: `mt-1.5 text-xs text-slate-500 dark:text-slate-400`

### 8.2 Select Fields
```blade
<select wire:model="reviewRating" class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-400">
```

### 8.3 Radio Buttons (Payment Methods)
```blade
<input type="radio" wire:model="paymentMethod" value="stripe" class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" />
<label class="ml-3 block text-sm font-medium text-slate-700 dark:text-slate-300">Kart ile öde (Stripe)</label>
```

### 8.4 Quantity Stepper (Reusable Pattern)
Alpine-driven, syncs with Livewire via `@change` and `x-on:cart-quantity-rejected`.

---

## 9. E-Commerce Specific Patterns

### 9.1 Price Display
```blade
{{ number_format($price / 100, 2, ',', '.') }} ₺
<!-- Minor units (kuruş) stored in DB, formatted for display -->
```

### 9.2 Stock Badges
| Condition | Badge |
|-----------|-------|
| `stock <= 0` | `bg-rose-600` "Tükendi" |
| `stock <= 5` | `bg-amber-500` "Son X ürün" |
| `stock > 5` | Green dot "Stokta: X adet" |
| `stock === null` | "Stok bilgisi yok" |

### 9.3 Rating Stars
```blade
@for ($i = 1; $i <= 5; $i++)
    <svg class="h-4 w-4 {{ $i <= (int) round($rating) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" ...>
@endfor
```

### 9.4 Tag Badges
```blade
<span class="rounded-full border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300">{{ $tag }}</span>
```
- Wishlist page: smaller `text-[10px] lowercase tracking-wide` with shadow

### 9.5 Variant Selectors
- **Color:** Round buttons (`h-10 w-10 rounded-full`), hex background via `data-hex` + `:style`, selected = `border-indigo-600 ring-2 ring-indigo-500/40`
- **Size/Text:** Pills (`min-w-12 rounded-lg px-4 py-2`), selected = `bg-indigo-50 border-indigo-600 text-indigo-700`

---

## 10. Implementation Guidelines

### 10.1 Adding New Pages
1. Create Livewire component in `app/Livewire/Storefront/`
2. Create Blade view in `resources/views/livewire/storefront/`
3. Extend `layouts.storefront`
4. Use existing components (`x-header`, `x-footer`, `x-product-card`, `x-button`, `x-input`, etc.)
5. Follow responsive grid patterns
6. Add route in `routes/web.php`

### 10.2 Creating New Components
1. Create in `resources/views/components/`
2. Use `@props` for typed props
3. Use `$attributes->merge(['class' => ...])` for attribute forwarding
4. Support both `<button>` and `<a>` via `href` prop (see button.blade.php)
5. Include dark mode variants for all colors
6. Add focus-visible styles

### 10.3 Modifying Design Tokens
- Colors: Update in component files (Tailwind v4 uses CSS variables, no config file)
- Spacing/Radius/Shadows: Tailwind defaults, override inline if needed
- Custom animations: Add to `resources/css/app.css`

### 10.4 Testing Visual Changes
1. Run `npm run build` after CSS changes
2. Check `public/build/manifest.json` for asset paths
3. Test both light/dark modes
4. Test mobile (≤640px), tablet (640-1024px), desktop (≥1024px)
5. Verify Alpine transitions work (drawer, popups, modals)

---

## 11. File Structure Reference

```
resources/views/
├── layouts/
│   └── storefront.blade.php          # Main layout wrapper
├── components/
│   ├── layouts/
│   │   └── app.blade.php             # HTML skeleton, meta, fonts, dark mode init
│   ├── header.blade.php              # Sticky nav, search modal, user menu, cart indicator
│   ├── footer.blade.php              # Trust badges, newsletter, link grid, order tracking
│   ├── button.blade.php              # Atomic button (7 variants, 3 sizes)
│   ├── input.blade.php               # Atomic form input
│   ├── textarea.blade.php            # Atomic textarea
│   ├── product-card.blade.php        # Atomic product card
│   ├── cart-drawer.blade.php         # Slide-out cart panel (Alpine)
│   ├── cart-alert.blade.php          # Error banner / success panel
│   ├── empty-state.blade.php         # Empty state with CTA
│   └── auth-card.blade.php           # Auth page wrapper
├── livewire/storefront/
│   ├── home-page.blade.php           # Hero, featured, categories, all products
│   ├── collection-page.blade.php     # PLP with sticky filters
│   ├── product-page.blade.php        # PDP with gallery, variants, tabs
│   ├── cart-page.blade.php           # Cart lines + sticky summary
│   ├── checkout-page.blade.php       # Address form + payment + summary
│   ├── wishlist-page.blade.php       # Wishlist grid with add-to-cart
│   ├── cart-indicator.blade.php      # Header badge + popup + drawer
│   ├── wishlist-indicator.blade.php  # Header wishlist count
│   ├── returns-page.blade.php        # Static returns info
│   ├── privacy-page.blade.php        # Static privacy policy
│   └── partials/
│       └── product-grid.blade.php    # Shared product grid
```

---

## 12. Known Issues / Technical Debt

1. **Cart Drawer Positioning:** `backdrop-blur` intentionally avoided — would break `fixed` positioning context
2. **Quantity Stepper Duplication:** Same Alpine pattern repeated in PDP, Cart Page, Cart Popup, Cart Drawer, Wishlist — could be extracted to component
3. **Rating Stars Duplication:** Star SVG loop repeated in product-card, product-page, wishlist-page, cart-alert (success) — could be a Blade component
4. **Tag Badge Variants:** Two styles (product-page: indigo pill; wishlist: small lowercase with shadow) — consolidate
5. **Focus Ring Offset Dark Mode:** Uses `dark:focus-visible:ring-offset-slate-950` — verify on all interactive elements
6. **Hero Gradient Performance:** Large blurred gradients on hero — consider `will-change` or reduce complexity

---

*Last updated: 2026-11-16 | Based on analysis of 29 Blade view files across storefront*