# Codex Notes (blocksy-child)

## Objective
Maintain SciuuuS Kids child theme behavior with minimal regressions on Woo product pages.

## Critical Files
- `functions.php`
- `inc/woocommerce/product-hooks.php`
- `inc/woocommerce/custom-shop-filters.php`
- `assets/css/shop-filters.css`
- `assets/css/woocommerce-product.css`
- `assets/css/product-page-mobile.css`
- `inc/header-custom.php`
- `inc/footer-custom.php`

## Known Couplings
- Review UI and moderation flow are handled by plugin `sciuuusprodreviews`.
- Theme styling strongly relies on `.single-product` scoped selectors and Blocksy/Woo DOM structure.
- Shop color-family filtering is theme-owned in archive sidebar and relies on:
  - Woo attribute `color-family` (taxonomy `pa_color-family`)
  - URL params `filter_color-family` and `query_type_color-family=or`
- Do not assume Woo filter widget/block markup exists on live.
- Family taxonomy includes `giallo` in addition to the original families.
- Multi-family product assignment is valid when product variants span multiple colors.
- Archive cards should surface color-family chips for filter transparency.

## Change Rules
- Prefer non-invasive CSS and hook-priority adjustments over template overrides.
- Preserve existing hook registrations unless intentionally refactoring.
- Keep checkout/archive logic isolated from single-product changes.
- For archive color filtering, preserve Woo-compatible query param naming:
  - correct: `filter_color-family`
  - incorrect: `filter_pa_color-family`

## Sanity Checks
- `php -l` on all touched PHP files.
- Visual regression check: header, product summary, add-to-cart, reviews block, related products, footer.
- Archive regression check: `/shop` unfiltered baseline + per-color filtered counts change when selecting swatches.
- Verify `giallo` appears in sidebar filter and behaves like other families.
