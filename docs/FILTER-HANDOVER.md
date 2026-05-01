# Filter Development Handover (pa_color-family)

## Current State
- Shop sidebar filter is theme-owned via `inc/woocommerce/custom-shop-filters.php`.
- Query contract is:
  - `filter_color-family`
  - `query_type_color-family=or`
- Family taxonomy: `pa_color-family` on attribute `color-family`.
- Family set currently expected:
  - `bianco`, `nero`, `verde`, `giallo`, `blu`, `rosa`, `marrone`, `multicolore`, `fantasia`
- Multi-family product assignment is valid.
- Product-card family chips on archive cards are rendered in `inc/woocommerce/shop-hooks.php` and styled in `assets/css/woocommerce-archive.css`.

## Live Ops Reality (important)
- Swatch rendering priority:
  1) `product_attribute_image` term-meta attachment
  2) fallback `assets/swatches/<slug>.png`
- If a swatch file update is not visible, term-meta attachment is usually overriding it.
- After bulk term/meta updates, Woo product-attributes lookup and transients may be stale.
  - Regenerate lookup table + clear transients, then clear page/CDN cache.

## Recent Fixes Applied in This Branch
- Added `giallo` support in migration/remediation/audit.
- Added one-off visibility fixer:
  - `inc/migrations/colour-family-make-visible.php`
- Added audit script:
  - `inc/migrations/colour-family-audit.php`
- Updated docs runbook:
  - `docs/MIGRATION-color-swatches.md`
- Updated local swatches:
  - `assets/swatches/giallo.png` (valid yellow)
  - `assets/swatches/blu.png` (diagonal dark-blue / sky-blue)

## Known Open Items for Next Thread
1. Decide canonical display label for `blu`:
   - Keep slug `blu` (do not change slug), but confirm whether label should be `Blu` or `Blu/Azzurro`.
2. Consider removing legacy block swatch injector path if no longer needed:
   - `inc/woocommerce/color-swatches.php`
3. Evaluate chip density/UX for products with many families:
   - optional cap + “+N” overflow behavior.
4. Optional data-quality pass:
   - Use audit output to normalize missing/weak assignments manually.

## Command Snippets (Live)
```bash
# Audit (read-only)
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-audit.php --path=/opt/bitnami/wordpress

# Visibility one-off write
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-make-visible.php --path=/opt/bitnami/wordpress

# Regenerate Woo lookup + clear transients (replace <admin_login>)
sudo /opt/bitnami/wp-cli/bin/wp wc tool run regenerate_product_attributes_lookup_table --user=<admin_login> --path=/opt/bitnami/wordpress
sudo /opt/bitnami/wp-cli/bin/wp wc tool run clear_transients --user=<admin_login> --path=/opt/bitnami/wordpress
```
