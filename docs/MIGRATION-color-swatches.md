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
- Inserts 8 family terms:
  - `bianco`, `nero`, `verde`, `blu`, `rosa`, `marrone`, `multicolore`, `fantasia`
- Sideloads theme swatch PNGs from `assets/swatches/` and stores each as `product_attribute_image` term-meta.
- Auto-tags products using existing color signals, skipping products that already have at least one `pa_color-family` term.

## What it does not do

- It does not change variation color behavior (`pa_color`).
- It does not guarantee every product gets a family term (manual curation may still be required).
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

## Visual checks

1. Open `/shop` and confirm `Colore` appears in the sidebar with swatches.
2. Click one color and confirm results change and URL includes `filter_color-family=<slug>`.
3. Multi-select colors and confirm URL includes comma-separated values plus `query_type_color-family=or`.
4. Clear filters and confirm baseline shop result set is restored.

Note: if Woo setting `woocommerce_hide_out_of_stock_items=yes`, filtered result counts can be lower than taxonomy term counts.

## Rollback notes

- To remove taxonomy/data and related widget remnants, run:
  - `inc/migrations/colour-family-reset.php`
- Because migration is idempotent, re-running after fixes is safe.
