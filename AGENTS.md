# Agent Metadata (Blocksy Child Theme)

## Scope
- Theme-only changes for `wp-content/themes/blocksy-child`.
- Do not edit WooCommerce core or parent `blocksy` theme files from this repo.
- Product reviews UI/logic may come from plugin `wp-content/plugins/sciuuusprodreviews`; avoid duplicating that logic in theme PHP.

## Key Theme Entry Points
- `functions.php`: enqueue stack, modular Woo hooks includes, archive wrappers.
- `inc/header-custom.php`: custom header output.
- `inc/footer-custom.php`: custom footer output.
- `inc/woocommerce/product-hooks.php`: single product hooks (summary extras, badges, details toggle).
- `assets/css/woocommerce-product.css`: desktop/tablet product layout.
- `assets/css/product-page-mobile.css`: mobile product layout.

## Current Product Page Behavior
- Header/footer are custom-rendered by child theme hooks.
- Product title/price layout is controlled by CSS grid in `woocommerce-product.css`.
- Product tabs are intentionally hidden; description/attributes/meta are shown in custom "Dettagli prodotto" toggle.
- Reviews can be injected inline by `sciuuusprodreviews`; theme currently has minimal review-specific styling.

## Guardrails
- Keep selectors scoped with `.single-product` for product-page CSS changes.
- Preserve Woo hooks priorities unless there is a documented reason to move them.
- Prefer CSS adjustments before template overrides.
- Keep i18n strings user-facing and translatable.

## Quick Validation
- `php -l functions.php`
- `php -l inc/header-custom.php`
- `php -l inc/footer-custom.php`
- `php -l inc/woocommerce/product-hooks.php`
- Open one product page and verify:
  - header/logo/nav renders,
  - title + price row alignment,
  - add-to-cart block + trust badges,
  - inline reviews section and form visibility,
  - related products cards.
