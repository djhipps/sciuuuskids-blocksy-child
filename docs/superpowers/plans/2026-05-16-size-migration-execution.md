# Size Migration Execution Plan (v2 — one-by-one with Playwright verification)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate variable products from legacy variation meta keys (`attribute_size` / `attribute_taglia`) to the `pa_size` taxonomy one product at a time, with Playwright verification after each save that variation selectors and shop filters are intact, then remove the legacy bridge.

**Architecture:** Three phases — (1) pre-conditions and apply-service bug fix, (2) one-by-one manual migration with Playwright gating, (3) bridge removal. The critical bug: `Save Row` currently updates the product-level `pa_size` attribute but never sets `attribute_pa_size` on individual variations, so product-page variation selectors break silently. Fix that first, then migrate.

**Tech Stack:** sciuuusadmin PHP (apply service fix), WP-CLI (local Docker sidecar), Playwright MCP (browser verification), `inc/woocommerce/custom-shop-filters.php` (bridge removal).

---

## Task 1: Create pa_size terms and establish baseline

**Files:**
- Read (no edit): `inc/migrations/size-taxonomy-audit.php`

The `Save Row` action silently drops any slug that isn't already a term in `pa_size`. Running the migration script term-creation step ensures slugs 20–44 exist before anything else.

- [ ] **Step 1: Run the migration script (term creation is idempotent, data migration is safe in this run too)**

```bash
docker compose run --rm --no-deps wordpress-cli wp eval-file wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-migration.php
```

Expected: `[SKIP]` or `[OK]` for attribute creation, then `[OK] created term <N>` for each term 20–44 that didn't already exist. Any `[WARN] unmapped legacy size values` lines name non-numeric raw values — note them, they'll appear in the sciuuusadmin audit rows as "Out of policy".

- [ ] **Step 2: Run the audit to record baseline numbers**

```bash
docker compose run --rm --no-deps wordpress-cli wp eval-file wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-audit.php
```

Record: `products_total`, `products_with_pa_size`, `products_with_legacy_keys`. Paste the raw output below as a comment — you'll compare against it at the end.

```
# BASELINE AUDIT OUTPUT — 2026-05-16
[SETUP   ] taxonomy_exists=yes
[SETUP   ] products_total=72
[COVERAGE] products_with_pa_size=63 (87.5%)
[LEGACY  ] products_with_legacy_keys=12
[GAP     ] products_with_size_signal_but_no_pa_size=0
[GAP     ] products_without_any_size_signal=9
[LEGACY  ] product_ids_still_on_legacy_keys=5610,5007,4664,4572,3860,3797,3337,3528,2456,2449,2436,2428
[VALUES  ] 19 => 22  (out of range — needs user decision)
[VALUES  ] 18 => 2   (out of range — needs user decision)
[VALUES  ] 45 => 1   (out of range — needs user decision)

NOTE: Migration script ran the full migration, not just term creation.
853 variation rows got attribute_pa_size set + legacy keys cleared.
Remaining 12 products have only out-of-range legacy values (18,19,45).
9 products with no size signal are likely simple (non-variable) products.
```

---

## Task 2: Fix the apply service — sync variation-level attribute_pa_size

**This is the critical prerequisite. Without it, Save Row silently leaves `attribute_pa_size` unset on individual variations, breaking the product-page variation selector after migration.**

**Files:**
- Modify: `wp-content/plugins/sciuuusadmin/includes/class-product-size-attribute-apply-service.php`

- [ ] **Step 1: Add three private helpers after `clear_product_caches()`**

Open `class-product-size-attribute-apply-service.php`. After the closing brace of `clear_product_caches()` (currently the last method in the class, around line 181), add these three methods before the final `}` of the class:

```php
/**
 * Set attribute_pa_size on each variation from legacy keys if not already set.
 * Does NOT clear legacy keys — bridge continues to work during migration.
 */
private function sync_variation_size_meta(int $product_id): int
{
    $variation_ids = get_posts([
        'post_type'   => 'product_variation',
        'post_status' => ['publish', 'private'],
        'post_parent' => $product_id,
        'numberposts' => -1,
        'fields'      => 'ids',
    ]);

    $synced = 0;
    foreach ((array) $variation_ids as $variation_id) {
        $current = (string) get_post_meta((int) $variation_id, 'attribute_pa_size', true);
        if ($current !== '' && $this->is_valid_numeric_size_slug(sanitize_title($current))) {
            continue;
        }

        $derived = '';
        foreach (['attribute_size', 'attribute_taglia'] as $legacy_key) {
            $raw = (string) get_post_meta((int) $variation_id, $legacy_key, true);
            if ($raw !== '') {
                $derived = $this->normalize_to_size_slug($raw);
                break;
            }
        }

        if ($derived === '' || !$this->is_valid_numeric_size_slug($derived)) {
            continue;
        }

        update_post_meta((int) $variation_id, 'attribute_pa_size', $derived);
        $synced++;
    }

    return $synced;
}

private function normalize_to_size_slug(string $raw): string
{
    $value = strtolower(trim($raw));
    if ($value === '') {
        return '';
    }
    $value = str_replace(['_', ',', ';', '/'], '-', $value);
    $value = (string) preg_replace('/\s+/', '-', $value);
    $value = (string) preg_replace('/[^a-z0-9\-]/', '', $value);
    $value = (string) preg_replace('/-+/', '-', $value);
    $value = trim($value, '-');
    return sanitize_title($value);
}

private function is_valid_numeric_size_slug(string $slug): bool
{
    if (!preg_match('/^\d+$/', $slug)) {
        return false;
    }
    $size = (int) $slug;
    return $size >= self::MIN_SIZE && $size <= self::MAX_SIZE;
}
```

- [ ] **Step 2: Call `sync_variation_size_meta()` from `update_product_attribute_via_wc_api()`**

Find the `try { $product->save(); ... }` block in `update_product_attribute_via_wc_api()`. After `$this->clear_product_caches((int) $product_id);`, add the sync call:

```php
try {
    $product->save();
    $this->clear_product_caches((int) $product_id);
    $this->sync_variation_size_meta((int) $product_id);  // ← add this line
    return true;
} catch (\Throwable $e) {
    $error_message = $e->getMessage();
    return false;
}
```

- [ ] **Step 3: Lint the file**

```bash
php -l /home/dh24839/projects/wp-docker/wordpress/wp-content/plugins/sciuuusadmin/includes/class-product-size-attribute-apply-service.php
```

Expected: `No syntax errors detected`

- [ ] **Step 4: Commit the fix**

```bash
git -C /home/dh24839/projects/wp-docker/wordpress/wp-content/plugins/sciuuusadmin add includes/class-product-size-attribute-apply-service.php
git -C /home/dh24839/projects/wp-docker/wordpress/wp-content/plugins/sciuuusadmin commit -m "fix: sync attribute_pa_size on variations during size Save Row

Save Row previously set product-level pa_size attribute only.
Without attribute_pa_size on each variation, WC variation selectors
were silently broken after migration."
```

---

## Task 3: Verify the fix with one product (confidence gate)

Before migrating everything, confirm the fix works end-to-end with a single publish product.

**Files:** None — browser verification only via Playwright.

- [ ] **Step 1: Open sciuuusadmin Taglia tab — find a publish product that shows "Missing" or "Mismatch"**

Navigate Playwright to:

```
http://localhost:8080/wp-admin/admin.php?page=sciuuusadmin-product-attributes&tab=size&post_status_filter=publish
```

Note the first product that has status `Missing` or `Mismatch` and has a non-empty "Recommended Terms" column. Record its:
- Product ID
- Product URL (click the product name link — it opens the WP edit page; derive the front-end URL from the slug)
- Recommended slugs (e.g. `24, 26, 28`)

- [ ] **Step 2: Before-save screenshot — product page variation selector**

```javascript
// Playwright — navigate to the product front-end page (not wp-admin)
// Replace with actual product slug
await page.goto('http://localhost:8080/product/<slug>/');
await page.screenshot({ path: '/tmp/before-migration-variation.png' });
```

Note what the size selector shows (or if it's missing/broken). This is your before state.

- [ ] **Step 3: Save Row in sciuuusadmin**

In the Playwright browser (or your own browser):
1. Go back to the sciuuusadmin Taglia tab
2. Find the product row
3. The "Edit Terms" field should already be pre-filled with the recommended slugs
4. Confirm the "Visible" checkbox is checked
5. Click "Save Row"
6. Check the notice banner: it should say `Saved product <ID>. Terms set to: 24, 26, 28` (with actual slugs)

If the notice shows `Terms set to: ` (empty), the terms don't exist in pa_size yet — re-run Task 1 Step 1.

- [ ] **Step 4: After-save — verify audit status changed to OK**

The table should now show the product's status as `OK`. If still `Missing` or `Mismatch`, something is wrong — stop and debug before proceeding.

- [ ] **Step 5: Playwright — verify product page variation selector**

```javascript
await page.goto('http://localhost:8080/product/<slug>/');
// WC variation dropdown — check options
const select = page.locator('select[name="attribute_pa_size"]');
const options = await select.locator('option').allTextContents();
console.log('Size options:', options);
// Expected: includes the migrated sizes, e.g. ['Choose an option', '24', '26', '28']

await page.screenshot({ path: '/tmp/after-migration-variation.png' });
```

If the variation selector is missing entirely, or shows wrong options, the apply fix did not work. Do not proceed with more products.

- [ ] **Step 6: Playwright — verify shop size filter shows this product**

```javascript
// Use the smallest size for this product, e.g. 24
await page.goto('http://localhost:8080/shop/?filter_size=24&query_type_size=or');
await page.screenshot({ path: '/tmp/shop-filter-size-24.png' });
// Visually confirm the product appears in the grid
const productLink = page.locator(`a[href*="/<slug>/"]`);
const isVisible = await productLink.isVisible();
console.log('Product visible in filter:', isVisible); // Expected: true
```

If both checks pass, the fix is working. Move to Task 4.

---

## Task 4: Migrate publish products one by one (repeat template)

**This task is a template — repeat for each publish product until all 54 are done.**

For each product in the sciuuusadmin Taglia tab (post_status_filter=publish, work top-to-bottom):

- [ ] **4.1 — Record the product being migrated**

Note: Product ID, name, recommended slugs.

- [ ] **4.2 — Save Row in sciuuusadmin**

1. Open: `http://localhost:8080/wp-admin/admin.php?page=sciuuusadmin-product-attributes&tab=size&post_status_filter=publish`
2. Find the product row. If status is already `OK` — skip this product.
3. Confirm the "Edit Terms" field matches the Recommended Terms.
4. Check Visible is on.
5. Click Save Row.
6. Confirm notice shows the correct slugs.
7. Confirm row status changes to `OK`.

- [ ] **4.3 — Playwright: product page variation selector check**

```javascript
await page.goto('http://localhost:8080/product/<slug>/');
const select = page.locator('select[name="attribute_pa_size"]');
const opts = await select.locator('option').allTextContents();
// Verify opts matches the expected sizes for this product
console.log(opts);
await page.screenshot({ path: `/tmp/migrated-<product-id>.png` });
```

If the selector is wrong or absent — STOP. Do not proceed to the next product. Debug the apply service or the product data.

- [ ] **4.4 — Playwright: shop filter check (one size)**

```javascript
await page.goto(`http://localhost:8080/shop/?filter_size=<one_of_the_sizes>&query_type_size=or`);
// Confirm the product appears
```

- [ ] **4.5 — Every 10 products: regenerate Woo lookup + clear transients**

After every batch of ~10 products saved:

```bash
docker compose run --rm --no-deps wordpress-cli wp wc tool run regenerate_product_attributes_lookup_table --user=admin
docker compose run --rm --no-deps wordpress-cli wp wc tool run clear_transients --user=admin
```

---

## Task 5: Migrate draft/private products

Same template as Task 4 but switch the filter to `post_status_filter=all` and work through remaining ~46 products.

Skip products where the status is already `OK`. For products with `Out of policy` in the audit (non-numeric sizes), leave them for now — note the product IDs and handle them separately.

After completing all: regenerate lookup + clear transients once more.

---

## Task 6: Final audit — confirm exit criteria

- [ ] **Step 1: Run audit script**

```bash
docker compose run --rm --no-deps wordpress-cli wp eval-file wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-audit.php
```

**Exit criteria (all must pass before Task 7):**
- `[LEGACY  ] products_with_legacy_keys=0`
- `[GAP     ] products_with_size_signal_but_no_pa_size=0`
- `[COVERAGE] products_with_pa_size=N` where N = `products_total` for all variable products

If any legacy keys remain, return to the sciuuusadmin page and search by the listed product IDs.

- [ ] **Step 2: Run sciuuusadmin audit button**

In the Taglia tab, click "Run Audit". The notice should show:
`Audit complete. OK: N, Missing: 0, Mismatch: 0, Out of policy: 0, ...`

---

## Task 7: Replace bridge with clean tax-query hook

Now safe to remove the legacy SQL bridge from the theme.

**Files:**
- Modify: `inc/woocommerce/custom-shop-filters.php`

The bridge consists of three things to remove:
1. `sciuuus_apply_size_filter_bridge_to_main_query()` function + its `add_action` (lines ~683–758)
2. `sciuuus_disable_native_pa_size_tax_query_for_bridge()` function + its `add_filter` (lines ~764–789)
3. `sciuuus_normalize_legacy_size_slug()` function (lines ~181–194) — only used by the old options query

And simplify `sciuuus_get_size_filter_options()` (lines ~230–309) which currently has raw SQL for legacy keys.

### 7a — Replace `sciuuus_get_size_filter_options()`

- [ ] **Step 1: Replace the function body**

Find `function sciuuus_get_size_filter_options()` and replace its entire body with:

```php
function sciuuus_get_size_filter_options() {
	$cache_key = 'sciuuus_size_filter_options_v2';
	$cached    = wp_cache_get( $cache_key, 'sciuuus_filters' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$cached = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		wp_cache_set( $cache_key, $cached, 'sciuuus_filters', 300 );
		return $cached;
	}

	$policy = sciuuus_get_size_filter_policy();
	$terms  = get_terms(
		[
			'taxonomy'   => 'pa_size',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]
	);

	$options = [];
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$slug = $term->slug;
			if ( ! preg_match( '/^\d+$/', $slug ) ) {
				continue;
			}
			$size = (int) $slug;
			if ( $size < (int) $policy['min'] || $size > (int) $policy['max'] ) {
				continue;
			}
			$options[ $slug ] = $term->name;
		}
	}

	uksort(
		$options,
		static function ( $a, $b ) {
			return (int) $a <=> (int) $b;
		}
	);

	set_transient( $cache_key, $options, 5 * MINUTE_IN_SECONDS );
	wp_cache_set( $cache_key, $options, 'sciuuus_filters', 300 );

	return $options;
}
```

### 7b — Remove bridge functions, add tax-query hook

- [ ] **Step 2: Remove `sciuuus_normalize_legacy_size_slug()` (the whole function)**

This function (introduced purely for legacy-key normalization in the options query) is now dead code.

- [ ] **Step 3: Remove `sciuuus_apply_size_filter_bridge_to_main_query()` and its `add_action`**

Delete the full function and the `add_action( 'pre_get_posts', 'sciuuus_apply_size_filter_bridge_to_main_query', 20 );` line that follows it.

- [ ] **Step 4: Remove `sciuuus_disable_native_pa_size_tax_query_for_bridge()` and its `add_filter`**

Delete the full function and the `add_filter( 'woocommerce_product_query_tax_query', ... );` line.

- [ ] **Step 5: Add the clean tax-query hook in their place**

Add this function where the bridge used to be:

```php
/**
 * Translate filter_size URL param into a pa_size tax_query.
 *
 * Keeps the filter_size URL contract intact post-bridge.
 */
function sciuuus_apply_size_tax_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$selected = sciuuus_get_selected_size_slugs();
	if ( empty( $selected ) ) {
		return;
	}

	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = [
		'taxonomy' => 'pa_size',
		'field'    => 'slug',
		'terms'    => $selected,
		'operator' => 'IN',
	];
	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'sciuuus_apply_size_tax_query', 20 );
```

- [ ] **Step 6: Lint**

```bash
php -l /home/dh24839/projects/wp-docker/wordpress/wp-content/themes/blocksy-child/inc/woocommerce/custom-shop-filters.php
```

Expected: `No syntax errors detected`

---

## Task 8: Playwright — end-to-end verification after bridge removal

- [ ] **Step 1: Clear transients**

```bash
docker compose run --rm --no-deps wordpress-cli wp wc tool run clear_transients --user=admin
```

- [ ] **Step 2: Test single-size filter**

```javascript
await page.goto('http://localhost:8080/shop/?filter_size=24&query_type_size=or');
// Confirm size chip shows in active filters
const chip = page.locator('.sciuuus-filter-chip', { hasText: 'Taglia: 24' });
await expect(chip).toBeVisible();
// Confirm product grid is not empty
const products = page.locator('.product');
await expect(products.first()).toBeVisible();
await page.screenshot({ path: '/tmp/bridge-removed-filter-24.png' });
```

- [ ] **Step 3: Test multi-size filter**

```javascript
await page.goto('http://localhost:8080/shop/?filter_size=24,28&query_type_size=or');
await page.screenshot({ path: '/tmp/bridge-removed-filter-24-28.png' });
```

- [ ] **Step 4: Test combined colour + size filter**

```javascript
await page.goto('http://localhost:8080/shop/?filter_color-family=blu&filter_size=24&query_type_color-family=or&query_type_size=or');
await page.screenshot({ path: '/tmp/bridge-removed-colour-size.png' });
```

- [ ] **Step 5: Test a product page variation selector still works**

```javascript
await page.goto('http://localhost:8080/product/<any-migrated-slug>/');
const select = page.locator('select[name="attribute_pa_size"]');
await expect(select).toBeVisible();
const opts = await select.locator('option').allTextContents();
console.log('Post-bridge-removal size options:', opts);
// Verify opts is non-empty and contains expected sizes
```

---

## Task 9: Commit theme changes

- [ ] **Step 1: Stage and commit**

```bash
git add inc/woocommerce/custom-shop-filters.php
git commit -m "Size filter: remove legacy bridge, replace with pa_size tax_query

Migration complete — all products have attribute_pa_size on variations and
pa_size terms on the product. Bridge SQL / post__in approach removed.
Size options now from get_terms('pa_size'). URL contract filter_size unchanged."
```

- [ ] **Step 2: Update FILTER-HANDOVER.md to record bridge removal and migration completion**

In the "Size Filter Rollout" section, add:

```markdown
### Bridge Removed (2026-05-16)
- All products migrated to pa_size via sciuuusadmin Taglia tab + apply service fix.
- Bridge functions removed from custom-shop-filters.php.
- Size filter now uses a direct pa_size tax_query.
- Migration scripts archived in inc/migrations/ for reference.
```

```bash
git add docs/FILTER-HANDOVER.md
git commit -m "docs: record size bridge removal and migration completion"
```

---

## Post-plan: optional cleanup

- **`sciuuus_size_label_from_slug()`** in `custom-shop-filters.php` still has lookup entries for non-numeric labels (`XS`, `S`, etc.) — these are dead code after bridge removal. Remove the non-numeric entries from the lookup array.
- **Migration scripts** — tag `size-taxonomy-migration.php` and `size-taxonomy-audit.php` with a header comment `// Bridge removed 2026-05-16 — kept for reference.` rather than deleting them.
