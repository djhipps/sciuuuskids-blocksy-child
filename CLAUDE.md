# Claude Code Notes (blocksy-child)

## Work Area
- Primary scope: this theme directory only.
- Keep changes compatible with WooCommerce + Blocksy parent.

## Where To Change What
- Header markup/hooks: `inc/header-custom.php`
- Footer markup/hooks: `inc/footer-custom.php`
- Product PHP hooks: `inc/woocommerce/product-hooks.php`
- Product desktop styles: `assets/css/woocommerce-product.css`
- Product mobile styles: `assets/css/product-page-mobile.css`
- Enqueues and module loading: `functions.php`

## Important Context
- Reviews are not fully theme-native: plugin `sciuuusprodreviews` can render inline review summary/form/list.
- Theme currently hides default Woo product tabs and uses custom details accordion.
- Avoid adding duplicate review form logic in theme if plugin already owns submission flow.

## Validation
- Lint changed PHP files with `php -l`.
- Manually check one product page in desktop + mobile widths.
