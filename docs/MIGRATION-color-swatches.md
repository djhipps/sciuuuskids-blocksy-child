# Migration runbook — pa_color-family swatches

This runbook deploys the colour-family filter taxonomy on the live WordPress instance. The migration script is idempotent, so it is safe to re-run.

## What this migration does

- Creates global product attribute `pa_color-family` (taxonomy `pa_color-family`, label `Famiglia Colore`).
- Inserts 8 family terms: `bianco`, `nero`, `verde`, `blu`, `rosa`, `marrone`, `multicolore`, `fantasia`.
- Sideloads the 8 theme-bundled PNGs from `assets/swatches/` and stores each as `product_attribute_image` term-meta.
- Inserts a `pa_color-family` filter block into the `sidebar-woocommerce` widget (block-11), positioned before the Category filter (heading `Colore`).
- Auto-tags products with inferred families from existing colour signals, but skips products that already have at least one `pa_color-family` term.

What it does NOT do:

- It does not modify `pa_color` type. `pa_color` stays as-is (`color`), so specific variation colours remain untouched.
- It does not replace manual catalogue curation for ambiguous products.

## Pre-flight

1. Take a DB backup/snapshot.
2. Deploy theme code first so the following files exist on live:
   - `inc/migrations/colour-family-migration.php`
   - `assets/swatches/*.png`
3. Run all commands on the Lightsail Bitnami WordPress host (not Docker).

## Deploy (Lightsail Bitnami)

Use the Bitnami WP-CLI binary and the WordPress path:

```bash
cd /opt/bitnami/wordpress 
sudo /opt/bitnami/wp-cli/bin/wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-migration.php --path=/opt/bitnami/wordpress
```

If your stack requires running as `daemon`, use this variant:

```bash
sudo -u daemon /opt/bitnami/wp-cli/bin/wp eval-file \
  /opt/bitnami/wordpress/wp-content/themes/blocksy-child/inc/migrations/colour-family-migration.php \
  --path=/opt/bitnami/wordpress
```

## Verification checklist (Lightsail Bitnami)

```bash
# 1) Attribute row exists
sudo /opt/bitnami/wp-cli/bin/wp db query \
  "SELECT attribute_id, attribute_name, attribute_label, attribute_type FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name IN ('color','color-family')" \
  --path=/opt/bitnami/wordpress

# 2) pa_color-family has 8 terms
sudo /opt/bitnami/wp-cli/bin/wp term list pa_color-family --format=table --path=/opt/bitnami/wordpress

# 3) Every family term has product_attribute_image meta
sudo /opt/bitnami/wp-cli/bin/wp eval '
foreach (get_terms(["taxonomy"=>"pa_color-family","hide_empty"=>false]) as $t) {
  $a = (int) get_term_meta($t->term_id, "product_attribute_image", true);
  echo $t->slug . " -> att " . $a . PHP_EOL;
}
' --path=/opt/bitnami/wordpress

# 4) Sidebar widget includes the Colore filter block
sudo /opt/bitnami/wp-cli/bin/wp eval '
$c = get_option("widget_block")[11]["content"] ?? "";
echo (strpos($c, "pa_color-family") !== false || strpos($c, "Colore</h3>") !== false) ? "PRESENT\n" : "MISSING\n";
' --path=/opt/bitnami/wordpress
```

## Visual checks

1. Open `/shop` and confirm a `Colore` filter section appears with swatch images.
2. Click one family filter and confirm product grid narrows.
3. Open one variable product and confirm product-page colour behaviour is unchanged.

## Rollback notes

- Remove the inserted filter block in Widgets if needed.
- Remove `pa_color-family` attribute via WP admin or WP-CLI if rollback is required.
- Because migration is idempotent, re-running is safe after fixes.
