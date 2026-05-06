# Filter Development Handover (pa_color-family)

## Size Filter Rollout (Temporary Bridge + Migration)
### Implementation Status (as of 2026-05-02)
- Implemented in theme code:
  - `filter_size` + `query_type_size=or` URL contract support
  - Size filter UI section in `inc/woocommerce/custom-shop-filters.php`
  - Temporary bridge query logic:
    - matches `pa_size` terms when present
    - matches legacy variation meta keys `attribute_size` / `attribute_taglia`
    - maps matches to parent product IDs for archive filtering
  - Migration script created: `inc/migrations/size-taxonomy-migration.php`
  - Audit script created: `inc/migrations/size-taxonomy-audit.php`
- Implemented verification in local theme workspace:
  - PHP lint checks pass for modified files
- Not executed yet (live operations step):
  - running migration/audit scripts on production/staging data
  - Woo lookup regeneration after each migration batch
  - transient cleanup only if storefront/filter output is stale
- Exit criteria to remove bridge path:
  - audit shows 100% intended `pa_size` coverage and no remaining legacy-key dependency

- Frontend query contract:
  - `filter_size`
  - `query_type_size=or`
- Bridge behavior is theme-owned in `inc/woocommerce/custom-shop-filters.php`:
  - reads variation legacy keys `attribute_size` and `attribute_taglia`
  - maps selected slugs to parent products
  - also matches `pa_size` terms when available
- Migration script:
  - `inc/migrations/size-taxonomy-migration.php`
- Audit script:
  - `inc/migrations/size-taxonomy-audit.php`
- Bridge stays enabled until audit coverage is 100% on `pa_size`.
- After each migration batch:
  - run full lookup regeneration
  - clear transients only if filter/catalog output remains stale
- Required cross-team reminder:
  - mirror this rollout checklist in `sciuuusadmin` Product Attributes admin page banner/checklist so operators run migration + audit + lookup regeneration in order.

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

# Size migration + audit
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-migration.php --path=/opt/bitnami/wordpress
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-audit.php --path=/opt/bitnami/wordpress
```
