# Filter Development Handover (pa_color-family)

## Runtime Safeguards (as of 2026-05-08)
- Implemented in theme code: `inc/woocommerce/custom-shop-filters.php`
- Guardrail objective:
  - treat filter state as bounded input, not open crawlable URL space.

### Server-Side Request Guard (Early)
- Hook:
  - `template_redirect` at priority `1` (`sciuuus_normalize_filter_query_request`)
- Behavior:
  - validates filter query args before expensive Woo archive query work.
  - rejects invalid requests with HTTP `400 Bad Request`.

### Color Filter Policy (`filter_color-family`)
- Allowed values whitelist:
  - `bianco`, `nero`, `blu`, `rosa`, `giallo`, `verde`, `marrone`, `fantasia`, `multicolore`
- Rules:
  - remove duplicates
  - normalize/sort for stable URL
  - max selected colors: `2`
  - max raw param length: `100`
  - unknown values: `400`
  - over-limit values: `400`

### Size Filter Policy (`filter_size`)
- Allowed values:
  - numeric-only sizes `20..44`
- Rules:
  - remove duplicates
  - normalize/sort for stable URL
  - max selected sizes: `8`
  - max raw param length: `100`
  - non-numeric values: `400`
  - out-of-range values: `400`
  - over-limit values: `400`

### Canonicalization and SEO
- Filtered archive requests are canonicalized to one normalized URL shape.
- Filtered states output:
  - `<meta name="robots" content="noindex,follow">`
  - canonical link to base archive URL (`get_pagenum_link(1)`).
- Base archive/tag pages remain indexable.

### Runtime Cost Controls
- Size bridge path now uses:
  - bounded input
  - narrowed SQL (`pm.meta_value IN (...)`) instead of full scan
  - short-TTL caching (transient + object cache) for option map and product-id sets

### Expected Behavior
- Allowed:
  - `/shop/?filter_color-family=blu`
  - `/shop/?filter_color-family=blu,verde`
  - `/shop/?filter_size=24,25`
- Rejected (`400`):
  - color with >2 values
  - any unknown color value
  - size with non-numeric value
  - size outside `20..44`
  - overlong raw filter strings

## Size Filter Rollout (Temporary Bridge + Migration)
### Implementation Status (as of 2026-05-02)
- Implemented in theme code:
  - `filter_size` + `query_type_size=or` URL contract support
  - Size filter UI section in `inc/woocommerce/custom-shop-filters.php`
  - Size filter UI upgraded to grouped chips (not a flat numeric list):
    - `Bimbo (20-35)`
    - `Adulto (36-44)`
  - Numeric-only UI policy:
    - show only numeric sizes `20..44`
    - do not show non-numeric sizes (`XS`, `S`, `M`, `L`, `XL`, `XXL`, `one-size`, etc.)
  - Temporary bridge query logic:
    - matches `pa_size` terms when present
    - matches legacy variation meta keys `attribute_size` / `attribute_taglia`
    - maps matches to parent product IDs for archive filtering
    - stock-aware: variation matching only from `_stock_status=instock`
  - Active filter cancellation chips:
    - include both color and size selections
    - allow one-click removal of a single selected size/color value
  - Migration script created: `inc/migrations/size-taxonomy-migration.php`
  - Audit script created: `inc/migrations/size-taxonomy-audit.php`
- Implemented verification in local theme workspace:
  - PHP lint checks pass for modified files
- Not executed yet (live operations step):
  - running manual one-by-one migration workflow in `sciuuusadmin` Product Attributes -> `Taglia (pa_size)` tab
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
  - only counts in-stock variations for size availability/matching
- Migration script:
  - `inc/migrations/size-taxonomy-migration.php`
- Audit script:
  - `inc/migrations/size-taxonomy-audit.php`
- Canonical operational workflow:
  - use `sciuuusadmin` Product Attributes -> `Taglia (pa_size)` for visual one-by-one review and row save
  - numeric sizes only (`20..44`)
  - non-numeric sizes are out of policy and must not be migrated into `pa_size`
- Bridge stays enabled until audit coverage is 100% on `pa_size`.
- After each migration batch:
  - run full lookup regeneration
  - clear transients only if filter/catalog output remains stale
- Required cross-team reminder:
  - follow the `Taglia (pa_size)` tab workflow first for published products, then remaining statuses.

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
- Taglia filter UI is theme-owned and now grouped by numeric range with chip controls.

## Live Ops Reality (important)
- Swatch rendering priority:
  1) `product_attribute_image` term-meta attachment
  2) fallback `assets/swatches/<slug>.png`
- If a swatch file update is not visible, term-meta attachment is usually overriding it.
- After bulk term/meta updates, Woo product-attributes lookup and transients may be stale.
  - Regenerate lookup table + clear transients, then clear page/CDN cache.
- If a size appears unexpectedly in frontend filters/results after fixes:
  - verify variation `_stock_status` and size meta on the product
  - then clear page/CDN cache (guest cache may serve stale HTML)

## Filter UI Plan (Taglia)
- Objective:
  - provide a compact, usable size filter UI for live shop behavior.
- UX decisions:
  - grouped chip sections instead of one long list
  - numeric-only display (`20..44`) with adult support up to `44`
  - active filter chips support per-value cancellation
- Data/logic decisions:
  - size options sourced from in-stock variations only
  - bridge matching constrained to in-stock variation rows only
  - legacy keys are still read temporarily, but non-numeric values are out of policy
- Operational workflow:
  - manual one-by-one migration in `sciuuusadmin` Taglia tab remains canonical process
  - process published products first (54), then remaining statuses

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
4. Size migration execution:
  - process `publish` products first (54 live), then `private/draft` until all 100 are checked.

## Command Snippets (Live)
```bash
# Audit (read-only)
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-audit.php --path=/opt/bitnami/wordpress

# Visibility one-off write
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-make-visible.php --path=/opt/bitnami/wordpress

# Regenerate Woo lookup + clear transients (replace <admin_login>)
sudo /opt/bitnami/wp-cli/bin/wp wc tool run regenerate_product_attributes_lookup_table --user=<admin_login> --path=/opt/bitnami/wordpress
sudo /opt/bitnami/wp-cli/bin/wp wc tool run clear_transients --user=<admin_login> --path=/opt/bitnami/wordpress

# Size audit helper (read-only fallback; main process is manual in sciuuusadmin Taglia tab)
sudo /opt/bitnami/wp-cli/bin/wp eval-file /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-audit.php --path=/opt/bitnami/wordpress
```
