# Codex Notes (blocksy-child)

## Objective
Maintain SciuuuS Kids child theme behavior with minimal regressions on Woo product pages.

## Critical Files
- `functions.php`
- `inc/woocommerce/product-hooks.php`
- `assets/css/woocommerce-product.css`
- `assets/css/product-page-mobile.css`
- `inc/header-custom.php`
- `inc/footer-custom.php`

## Known Couplings
- Review UI and moderation flow are handled by plugin `sciuuusprodreviews`.
- Theme styling strongly relies on `.single-product` scoped selectors and Blocksy/Woo DOM structure.

## Change Rules
- Prefer non-invasive CSS and hook-priority adjustments over template overrides.
- Preserve existing hook registrations unless intentionally refactoring.
- Keep checkout/archive logic isolated from single-product changes.

## Sanity Checks
- `php -l` on all touched PHP files.
- Visual regression check: header, product summary, add-to-cart, reviews block, related products, footer.
