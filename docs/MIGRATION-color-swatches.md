# Migration runbook — pa_color-family + theme-owned shop color filter

This runbook deploys and verifies the `pa_color-family` taxonomy data and the current theme-owned color filter UI on shop archives.

The migration script is idempotent and safe to re-run.

## Current architecture (important)

- Product color-family data source:
  - Woo attribute name: `color-family`
  - Woo taxonomy: `pa_color-family`
- Shop filter UI is rendered by theme code:
  - `inc/woocommerce/custom-shop-filters.php`
  - `assets/css/shop-filters.css`
- Filter query-string keys must be:
  - `filter_color-family`
  - `query_type_color-family=or`
- The storefront no longer depends on Woo widget-block markup for color swatches.

## What the migration script does

- Creates global product attribute `pa_color-family` (attribute name `color-family`, label `Famiglia Colore`).
- Inserts 9 family terms:
  - `bianco`, `nero`, `verde`, `giallo`, `blu` (display label can be `Blu/Azzurro`), `rosa`, `marrone`, `multicolore`, `fantasia`
- Sideloads theme swatch PNGs from `assets/swatches/` and stores each as `product_attribute_image` term-meta.
- Auto-tags products using existing color signals, skipping products that already have at least one `pa_color-family` term.

## What it does not do

- It does not change variation color behavior (`pa_color`).
- It does not guarantee every product gets a family term (manual curation may still be required).
- Multi-family assignment is valid and expected for genuinely multi-color products.
- Primary product-card image may show one color while additional colors exist in variation photos.
- It does not require widget-block edits for color filtering anymore.

## Pre-flight

1. Take a DB backup/snapshot.
2. Deploy theme code first so these files exist on live:
   - `inc/migrations/colour-family-migration.php`
   - `inc/woocommerce/custom-shop-filters.php`
   - `assets/css/shop-filters.css`
   - `assets/swatches/*.png`
3. Run commands on the target WordPress host.

## Deploy (Lightsail Bitnami)

```bash
cd /opt/bitnami/wordpress
sudo /opt/bitnami/wp-cli/bin/wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-migration.php --path=/opt/bitnami/wordpress
```

If required by stack user permissions:

```bash
sudo -u daemon /opt/bitnami/wp-cli/bin/wp eval-file \
  /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-migration.php \
  --path=/opt/bitnami/wordpress
```

## Verification checklist (Lightsail Bitnami)

```bash
# 1) Attribute row exists and naming is correct
sudo /opt/bitnami/wp-cli/bin/wp db query \
  "SELECT attribute_id, attribute_name, attribute_label, attribute_type FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name IN ('color','color-family')" \
  --path=/opt/bitnami/wordpress

# 2) pa_color-family has expected terms
sudo /opt/bitnami/wp-cli/bin/wp term list pa_color-family --format=table --path=/opt/bitnami/wordpress

# 3) Every family term has product_attribute_image meta
sudo /opt/bitnami/wp-cli/bin/wp eval '
foreach (get_terms(["taxonomy"=>"pa_color-family","hide_empty"=>false]) as $t) {
  $a = (int) get_term_meta($t->term_id, "product_attribute_image", true);
  echo $t->slug . " -> att " . $a . PHP_EOL;
}
' --path=/opt/bitnami/wordpress
```

```bash
# 4) Verify display labels/slugs
sudo /opt/bitnami/wp-cli/bin/wp term list pa_color-family --fields=term_id,name,slug,count --path=/opt/bitnami/wordpress
```

## Swatch source priority (important)

Shop filter swatches use this priority:
1. `product_attribute_image` term-meta attachment (if valid image)
2. Theme fallback file `assets/swatches/<slug>.png`

If fallback file changes do not appear, term-meta attachment is probably overriding it.

## Troubleshooting checklist (live, read-only)

When filters show strange combinations or empty results, run the audit script first:

```bash
sudo /opt/bitnami/wp-cli/bin/wp eval-file \
  /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-audit.php \
  --path=/opt/bitnami/wordpress
```

The audit prints:

- `SETUP`: attribute/taxonomy integrity and expected term set.
- `BASELINE`: published products vs shop-visible products after visibility/stock constraints.
- `TERM_COUNTS`: raw taxonomy counts vs effective shop-visible counts for each family slug.
- `ZERO_TRAP`: terms that have assignments but produce 0 visible results.
- `MISMATCH`: product-level suspicious assignments (`assigned` vs `inferred` from color signals).

Use `MISMATCH` output as the manual fix list in wp-admin products.
No DB changes are made by this audit script.

## Visibility fix (one-off write)

If products need `Famiglia Colore` visible in product attributes/details, run:

```bash
sudo /opt/bitnami/wp-cli/bin/wp eval-file \
  /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-make-visible.php \
  --path=/opt/bitnami/wordpress
```

Optional dry-run first:

```bash
sudo SCIUUUS_DRY_RUN=1 /opt/bitnami/wp-cli/bin/wp eval-file \
  /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-make-visible.php \
  --path=/opt/bitnami/wordpress
```

Optional verbose output:

```bash
sudo SCIUUUS_VERBOSE=1 /opt/bitnami/wp-cli/bin/wp eval-file \
  /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-make-visible.php \
  --path=/opt/bitnami/wordpress
```

## Woo lookup/index refresh (after bulk term/meta changes)

After running migration/remediation/visibility scripts, Woo filters can remain stale until lookup/transients are refreshed.

```bash
# Use a real admin username/login for --user (ID=1 can fail with 403 on some sites)
sudo /opt/bitnami/wp-cli/bin/wp user list --fields=ID,user_login,roles --path=/opt/bitnami/wordpress
sudo /opt/bitnami/wp-cli/bin/wp wc tool run regenerate_product_attributes_lookup_table --user=<admin_login> --path=/opt/bitnami/wordpress
sudo /opt/bitnami/wp-cli/bin/wp wc tool run clear_transients --user=<admin_login> --path=/opt/bitnami/wordpress
```

If WC tool command fails by permission layer, fallback:

```bash
sudo /opt/bitnami/wp-cli/bin/wp eval '
if ( class_exists("\Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore") ) {
  $store = wc_get_container()->get(\Automattic\WooCommerce\Internal\ProductAttributesLookup\LookupDataStore::class);
  if ( method_exists($store, "regenerate")) { $store->regenerate(); echo "lookup_regenerated\n"; }
}
if ( function_exists("wc_delete_product_transients") ) { wc_delete_product_transients(); }
echo "done\n";
' --path=/opt/bitnami/wordpress
```

## Visual checks

1. Open `/shop` and confirm `Colore` appears in the sidebar with swatches.
2. Click one color and confirm results change and URL includes `filter_color-family=<slug>`.
3. Multi-select colors and confirm URL includes comma-separated values plus `query_type_color-family=or`.
4. Clear filters and confirm baseline shop result set is restored.
5. Confirm products can appear in more than one family when they genuinely include multiple colors.
6. Confirm product card color chips explain inclusion when main image color differs from variation colors.

Note: if Woo setting `woocommerce_hide_out_of_stock_items=yes`, filtered result counts can be lower than taxonomy term counts.

## Rollback notes

- To remove taxonomy/data and related widget remnants, run:
  - `inc/migrations/colour-family-reset.php`
- Because migration is idempotent, re-running after fixes is safe.
