# GitHub Copilot Instructions (blocksy-child)

This repository customizes a WooCommerce storefront on a Blocksy child theme.

## Scope and Priorities
- Keep edits inside `wp-content/themes/blocksy-child`.
- Preserve existing WooCommerce hook architecture in `inc/woocommerce/*.php`.
- Prefer extending current CSS patterns over introducing new template overrides.

## Product Page Guidance
- Single product styles live in:
  - `assets/css/woocommerce-product.css`
  - `assets/css/product-page-mobile.css`
- Product hooks live in:
  - `inc/woocommerce/product-hooks.php`
- Reviews may be rendered by plugin `sciuuusprodreviews`; do not duplicate review submission logic in theme files.

## Coding Expectations
- Use WordPress escaping/sanitization helpers (`esc_html`, `esc_attr`, `wp_kses_post`).
- Keep user-facing strings translatable.
- Keep selectors scoped (prefer `.single-product` for product page changes).

## Quick Verification
- Run `php -l` on touched PHP files.
- Check a product page for:
  - title/price layout,
  - add-to-cart and trust badges,
  - inline reviews visibility,
  - related products cards,
  - footer rendering.
