# Collo del Piede Taxonomy Migration — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Convert the "Collo del piede" custom text attribute on ~30 products into a WooCommerce global taxonomy (`pa_collo-del-piede`) with 3 canonical terms (basso / medio / alto), and add a "Collo" admin tab in sciuuusadmin matching the Taglia/size workflow.

**Architecture:** Follows the identical pattern used for `pa_size` (Taglia). A setup-only WP-CLI migration script creates the attribute + terms. Three sciuuusadmin service classes (audit / plan / apply) handle the per-product conversion. The "Collo" tab is wired into `class-product-attributes-page.php` alongside the existing Color Family and Taglia tabs.

**Tech Stack:** PHP 8.x, WooCommerce WC_Product_Attribute API, WP_Query, WP-CLI eval-file, Docker Compose CLI sidecar.

---

## Context

"Collo del piede" (foot collar height) is currently stored on each product as a **custom text attribute** (not a taxonomy) in `_product_attributes['collo-del-piede']`. The value is a pipe-separated string like `basso | medio | alto` — with inconsistent capitalisation and ordering but no spelling errors. There are ~30 products with this attribute. It is **not** a variation attribute (`is_variation=0`). The goal is to normalise to 3 canonical slugs (`basso`, `medio`, `alto`) and migrate each product via the admin UI, exactly as was done for `pa_size`.

---

## Files

| Action | Path |
|--------|------|
| **Create** | `inc/migrations/collo-del-piede-migration.php` |
| **Create** | `wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-audit-service.php` |
| **Create** | `wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-plan-service.php` |
| **Create** | `wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-apply-service.php` |
| **Modify** | `wp-content/plugins/sciuuusadmin/sciuuusadmin.php` (3 require_once lines) |
| **Modify** | `wp-content/plugins/sciuuusadmin/includes/class-product-attributes-page.php` (add collo tab) |

Paths for service files are relative to `/home/dh24839/projects/wp-docker/wordpress/`.
Migration script path is relative to the blocksy-child theme root.

---

## Task 1 — Migration script (setup only)

**Files:**
- Create: `inc/migrations/collo-del-piede-migration.php`

- [ ] **Step 1: Create the file**

```php
<?php
/**
 * WP-CLI migration: create pa_collo-del-piede attribute + terms.
 * Setup-only — does NOT modify any product meta. Safe to re-run.
 *
 * Usage (local Docker):
 *   docker compose run --rm --no-deps wordpress-cli wp eval-file \
 *     wp-content/themes/blocksy-child/inc/migrations/collo-del-piede-migration.php
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_create_attribute' ) ) {
    echo "[FAIL] WooCommerce not available.\n";
    return;
}

$attribute_slug = 'collo-del-piede';
$attribute_name = 'Collo del Piede';
$taxonomy       = 'pa_collo-del-piede';

// ── 1. Create or verify global WC attribute ───────────────────────────────────
$existing_id = wc_attribute_taxonomy_id_by_name( $attribute_slug );

if ( $existing_id ) {
    echo "[SKIP] WC attribute '{$attribute_slug}' already exists (ID {$existing_id}).\n";
    $attribute_id = $existing_id;
} else {
    $attribute_id = wc_create_attribute( [
        'name'         => $attribute_name,
        'slug'         => $attribute_slug,
        'type'         => 'select',
        'order_by'     => 'menu_order',
        'has_archives' => false,
    ] );

    if ( is_wp_error( $attribute_id ) ) {
        echo "[FAIL] Could not create attribute: " . $attribute_id->get_error_message() . "\n";
        return;
    }

    delete_transient( 'wc_attribute_taxonomies' );
    echo "[OK] Created WC attribute '{$attribute_slug}' (ID {$attribute_id}).\n";
}

// ── 2. Register taxonomy in current process ───────────────────────────────────
if ( ! taxonomy_exists( $taxonomy ) ) {
    register_taxonomy( $taxonomy, 'product', [
        'labels'       => [ 'name' => $attribute_name ],
        'public'       => false,
        'hierarchical' => false,
        'show_ui'      => false,
        'query_var'    => false,
        'rewrite'      => false,
    ] );
    echo "[OK] Registered taxonomy '{$taxonomy}' for this process.\n";
} else {
    echo "[SKIP] Taxonomy '{$taxonomy}' already registered.\n";
}

// ── 3. Create 3 canonical terms ───────────────────────────────────────────────
$terms = [
    [ 'slug' => 'basso', 'name' => 'Basso', 'order' => 0 ],
    [ 'slug' => 'medio', 'name' => 'Medio', 'order' => 1 ],
    [ 'slug' => 'alto',  'name' => 'Alto',  'order' => 2 ],
];

foreach ( $terms as $t ) {
    $existing = get_term_by( 'slug', $t['slug'], $taxonomy );
    if ( $existing instanceof WP_Term ) {
        echo "[SKIP] Term '{$t['slug']}' already exists (ID {$existing->term_id}).\n";
        continue;
    }
    $result = wp_insert_term( $t['name'], $taxonomy, [ 'slug' => $t['slug'] ] );
    if ( is_wp_error( $result ) ) {
        echo "[FAIL] Term '{$t['slug']}': " . $result->get_error_message() . "\n";
    } else {
        update_term_meta( $result['term_id'], 'order_pa_collo-del-piede', $t['order'] );
        echo "[OK] Created term '{$t['slug']}' (ID {$result['term_id']}).\n";
    }
}

// ── 4. Verify ─────────────────────────────────────────────────────────────────
$final_terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
echo "\n=== Verification ===\n";
echo "Attribute ID : {$attribute_id}\n";
echo "Taxonomy     : {$taxonomy}\n";
if ( is_wp_error( $final_terms ) || empty( $final_terms ) ) {
    echo "[WARN] No terms found — check taxonomy registration.\n";
} else {
    foreach ( $final_terms as $ft ) {
        echo "  term: {$ft->slug} ({$ft->name}) ID={$ft->term_id}\n";
    }
}
echo "\n[DONE] Run sciuuusadmin → Product Attributes → Collo to convert products.\n";
```

- [ ] **Step 2: PHP lint**

```bash
php -l inc/migrations/collo-del-piede-migration.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Run the migration locally**

```bash
cd /home/dh24839/projects/wp-docker
docker compose run --rm --no-deps wordpress-cli wp eval-file \
  wp-content/themes/blocksy-child/inc/migrations/collo-del-piede-migration.php
```

Expected output:
```
[OK] Created WC attribute 'collo-del-piede' (ID N).
[OK] Registered taxonomy 'pa_collo-del-piede' for this process.
[OK] Created term 'basso' (ID N).
[OK] Created term 'medio' (ID N).
[OK] Created term 'alto' (ID N).

=== Verification ===
Attribute ID : N
Taxonomy     : pa_collo-del-piede
  term: basso (Basso) ID=N
  term: medio (Medio) ID=N
  term: alto  (Alto)  ID=N

[DONE] Run sciuuusadmin → Product Attributes → Collo to convert products.
```

If you see `[SKIP]` lines on re-run, that is correct — the script is idempotent.

- [ ] **Step 4: Commit**

```bash
git add inc/migrations/collo-del-piede-migration.php
git commit -m "feat: add pa_collo-del-piede migration script (setup + terms)"
```

---

## Task 2 — Audit service

**Files:**
- Create: `wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-audit-service.php`

- [ ] **Step 1: Create the file**

```php
<?php

namespace SciuuusAdmin;

defined( 'ABSPATH' ) || exit;

class Product_Collo_Attribute_Audit_Service {

    private const TARGET_TAXONOMY  = 'pa_collo-del-piede';
    private const LEGACY_ATTR_KEY  = 'collo-del-piede';
    private const ALLOWED_SLUGS    = [ 'basso', 'medio', 'alto' ];
    private const CANONICAL_ORDER  = [ 'basso', 'medio', 'alto' ];

    /**
     * @return array<string, mixed>
     */
    public function audit_products( $search = '', $paged = 1, $per_page = 50 ) {
        $paged    = max( 1, (int) $paged );
        $per_page = max( 1, min( 200, (int) $per_page ) );
        $search   = sanitize_text_field( (string) $search );

        $query = new \WP_Query( [
            'post_type'      => 'product',
            'post_status'    => [ 'publish', 'private', 'draft' ],
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'ID',
            'order'          => 'DESC',
            'fields'         => 'ids',
            's'              => $search,
            'meta_query'     => [
                'relation' => 'OR',
                [ 'key' => '_product_attributes', 'value' => self::LEGACY_ATTR_KEY,  'compare' => 'LIKE' ],
                [ 'key' => '_product_attributes', 'value' => self::TARGET_TAXONOMY,   'compare' => 'LIKE' ],
            ],
        ] );

        $rows = [];
        foreach ( (array) $query->posts as $product_id ) {
            $rows[] = $this->build_row( (int) $product_id );
        }

        return [
            'rows'     => $rows,
            'total'    => (int) $query->found_posts,
            'pages'    => (int) $query->max_num_pages,
            'paged'    => $paged,
            'per_page' => $per_page,
            'search'   => $search,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function build_row( $product_id ) {
        // Current taxonomy assignment
        $current_terms = wp_get_post_terms( $product_id, self::TARGET_TAXONOMY, [ 'fields' => 'all' ] );
        if ( is_wp_error( $current_terms ) ) {
            $current_terms = [];
        }

        $current_slugs = [];
        $current_names = [];
        foreach ( (array) $current_terms as $term ) {
            if ( ! ( $term instanceof \WP_Term ) ) {
                continue;
            }
            $current_slugs[] = (string) $term->slug;
            $current_names[] = (string) $term->name;
        }
        $current_slugs = $this->sort_canonical( $current_slugs );

        // Recommended terms parsed from legacy text attribute
        $recommended       = $this->compute_recommended_terms( $product_id );
        $recommended_slugs = $recommended['slugs'];
        $raw_text_value    = $recommended['raw_text'];
        $invalid_sources   = $recommended['invalid_sources'];

        $has_legacy = $this->has_legacy_text_attribute( $product_id );
        $visible    = $this->is_attribute_visible( $product_id );

        // Derive status
        $status = 'OK';
        if ( $has_legacy && empty( $current_slugs ) ) {
            $status = 'Needs Convert';
        } elseif ( ! empty( $recommended_slugs ) && $current_slugs !== $recommended_slugs ) {
            $status = 'Mismatch';
        } elseif ( empty( $current_slugs ) ) {
            $status = 'No Data';
        }
        if ( ! $visible && ! empty( $current_slugs ) ) {
            $status = ( $status === 'OK' ) ? 'Visibility off' : $status . ' + Visibility off';
        }

        return [
            'product_id'        => $product_id,
            'product_name'      => get_the_title( $product_id ),
            'post_status'       => (string) get_post_status( $product_id ),
            'sku'               => (string) get_post_meta( $product_id, '_sku', true ),
            'current_slugs'     => $current_slugs,
            'current_names'     => $current_names,
            'recommended_slugs' => $recommended_slugs,
            'raw_text_value'    => $raw_text_value,
            'invalid_sources'   => $invalid_sources,
            'visible'           => $visible,
            'status'            => $status,
            'has_legacy'        => $has_legacy,
        ];
    }

    /**
     * @return array{slugs: array<int,string>, raw_text: string, invalid_sources: array<int,string>}
     */
    private function compute_recommended_terms( $product_id ) {
        $attrs = get_post_meta( $product_id, '_product_attributes', true );
        if ( ! is_array( $attrs ) || ! isset( $attrs[ self::LEGACY_ATTR_KEY ] ) ) {
            return [ 'slugs' => [], 'raw_text' => '', 'invalid_sources' => [] ];
        }

        $raw_text = (string) ( $attrs[ self::LEGACY_ATTR_KEY ]['value'] ?? '' );
        $parts    = array_map( 'trim', explode( '|', strtolower( $raw_text ) ) );
        $parts    = array_filter( $parts, static fn( $p ) => $p !== '' );

        $valid   = [];
        $invalid = [];
        foreach ( $parts as $part ) {
            $slug = sanitize_title( $part );
            if ( in_array( $slug, self::ALLOWED_SLUGS, true ) ) {
                $valid[] = $slug;
            } else {
                $invalid[] = $part;
            }
        }

        return [
            'slugs'          => $this->sort_canonical( array_values( array_unique( $valid ) ) ),
            'raw_text'       => $raw_text,
            'invalid_sources'=> array_values( array_unique( $invalid ) ),
        ];
    }

    private function has_legacy_text_attribute( $product_id ): bool {
        $attrs = get_post_meta( $product_id, '_product_attributes', true );
        return is_array( $attrs ) && isset( $attrs[ self::LEGACY_ATTR_KEY ] );
    }

    private function is_attribute_visible( $product_id ): bool {
        $attrs = get_post_meta( $product_id, '_product_attributes', true );
        if ( ! is_array( $attrs ) ) {
            return false;
        }
        foreach ( [ self::TARGET_TAXONOMY, self::LEGACY_ATTR_KEY ] as $key ) {
            if ( isset( $attrs[ $key ] ) ) {
                return ! empty( $attrs[ $key ]['is_visible'] );
            }
        }
        return false;
    }

    /**
     * @param array<int,string> $slugs
     * @return array<int,string>
     */
    private function sort_canonical( array $slugs ): array {
        $order = array_flip( self::CANONICAL_ORDER );
        usort( $slugs, static fn( $a, $b ) => ( $order[ $a ] ?? 99 ) <=> ( $order[ $b ] ?? 99 ) );
        return array_values( array_unique( $slugs ) );
    }
}
```

- [ ] **Step 2: PHP lint**

```bash
php -l wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-audit-service.php
```
Expected: `No syntax errors detected`

---

## Task 3 — Plan service

**Files:**
- Create: `wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-plan-service.php`

- [ ] **Step 1: Create the file**

```php
<?php

namespace SciuuusAdmin;

defined( 'ABSPATH' ) || exit;

class Product_Collo_Attribute_Plan_Service {

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array{updates: array<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function build_plan( array $rows ): array {
        $updates = [];
        $summary = [
            'products_checked'              => count( $rows ),
            'products_to_update_terms'      => 0,
            'products_to_update_visibility' => 0,
            'products_unchanged'            => 0,
        ];

        foreach ( $rows as $row ) {
            $needs_terms      = $row['current_slugs'] !== $row['recommended_slugs']
                                && ! empty( $row['recommended_slugs'] );
            $needs_visibility = ! $row['visible']
                                && ! empty( array_merge( $row['current_slugs'], $row['recommended_slugs'] ) );

            if ( ! $needs_terms && ! $needs_visibility ) {
                $summary['products_unchanged']++;
                continue;
            }

            $updates[] = [
                'product_id'       => $row['product_id'],
                'product_name'     => $row['product_name'],
                'set_terms_to'     => $row['recommended_slugs'],
                'needs_terms'      => $needs_terms,
                'needs_visibility' => $needs_visibility,
            ];

            if ( $needs_terms ) {
                $summary['products_to_update_terms']++;
            }
            if ( $needs_visibility ) {
                $summary['products_to_update_visibility']++;
            }
        }

        return [ 'updates' => $updates, 'summary' => $summary ];
    }
}
```

- [ ] **Step 2: PHP lint**

```bash
php -l wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-plan-service.php
```
Expected: `No syntax errors detected`

---

## Task 4 — Apply service

**Files:**
- Create: `wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-apply-service.php`

- [ ] **Step 1: Create the file**

```php
<?php

namespace SciuuusAdmin;

defined( 'ABSPATH' ) || exit;

class Product_Collo_Attribute_Apply_Service {

    private const TARGET_TAXONOMY = 'pa_collo-del-piede';
    private const LEGACY_ATTR_KEY = 'collo-del-piede';
    private const ALLOWED_SLUGS   = [ 'basso', 'medio', 'alto' ];

    /**
     * @param array<int, array<string, mixed>> $updates
     * @return array{updated: int, failed: int, details: array<int, string>}
     */
    public function apply( array $updates ): array {
        $result = [ 'updated' => 0, 'failed' => 0, 'details' => [] ];

        foreach ( $updates as $update ) {
            $product_id = (int) ( $update['product_id'] ?? 0 );
            if ( $product_id <= 0 ) {
                continue;
            }

            $slugs   = array_values( array_filter( (array) ( $update['set_terms_to'] ?? [] ), 'is_string' ) );
            $slugs   = $this->sanitize_collo_slugs( $slugs );
            $visible = true;

            $ok = $this->update_product_attribute_via_wc_api( $product_id, $slugs, $visible, $error_message );
            if ( $ok ) {
                $result['updated']++;
            } else {
                $result['failed']++;
                $result['details'][] = sprintf( 'Product %d: %s', $product_id, (string) $error_message );
            }
        }

        return $result;
    }

    public function apply_manual_row( $product_id, array $slugs, $visible, &$error_message = '' ): bool {
        $slugs = $this->sanitize_collo_slugs( $slugs );
        return $this->update_product_attribute_via_wc_api( (int) $product_id, $slugs, (bool) $visible, $error_message );
    }

    /**
     * @param array<int, string> $slugs
     * @return array<int, string>
     */
    private function sanitize_collo_slugs( array $slugs ): array {
        $clean = [];
        foreach ( $slugs as $slug ) {
            $slug = sanitize_title( (string) $slug );
            if ( in_array( $slug, self::ALLOWED_SLUGS, true ) ) {
                $clean[] = $slug;
            }
        }
        return array_values( array_unique( $clean ) );
    }

    private function update_product_attribute_via_wc_api( $product_id, array $slugs, $visible, &$error_message ): bool {
        $error_message = '';

        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            $error_message = 'Product not found';
            return false;
        }

        $term_ids = [];
        foreach ( $slugs as $slug ) {
            $term = get_term_by( 'slug', (string) $slug, self::TARGET_TAXONOMY );
            if ( $term instanceof \WP_Term ) {
                $term_ids[] = (int) $term->term_id;
            }
        }
        $term_ids = array_values( array_unique( array_filter( $term_ids ) ) );

        $attributes = $product->get_attributes();

        // Preserve position from old text attribute if available.
        $position = count( $attributes );
        if ( isset( $attributes[ self::LEGACY_ATTR_KEY ] ) && $attributes[ self::LEGACY_ATTR_KEY ] instanceof \WC_Product_Attribute ) {
            $position = (int) $attributes[ self::LEGACY_ATTR_KEY ]->get_position();
        } elseif ( isset( $attributes[ self::TARGET_TAXONOMY ] ) && $attributes[ self::TARGET_TAXONOMY ] instanceof \WC_Product_Attribute ) {
            $position = (int) $attributes[ self::TARGET_TAXONOMY ]->get_position();
        }

        $attribute = ( isset( $attributes[ self::TARGET_TAXONOMY ] ) && $attributes[ self::TARGET_TAXONOMY ] instanceof \WC_Product_Attribute )
            ? $attributes[ self::TARGET_TAXONOMY ]
            : new \WC_Product_Attribute();

        $taxonomy_id = (int) wc_attribute_taxonomy_id_by_name( self::TARGET_TAXONOMY );
        $attribute->set_id( $taxonomy_id );
        $attribute->set_name( self::TARGET_TAXONOMY );
        $attribute->set_options( $term_ids );
        $attribute->set_position( $position );
        $attribute->set_visible( (bool) $visible );
        $attribute->set_variation( false ); // collo is NOT a variation selector

        // Replace text attribute with taxonomy attribute.
        unset( $attributes[ self::LEGACY_ATTR_KEY ] );
        $attributes[ self::TARGET_TAXONOMY ] = $attribute;

        $product->set_attributes( $attributes );

        try {
            $product->save();
            $this->clear_caches( $product_id );
            return true;
        } catch ( \Throwable $e ) {
            $error_message = $e->getMessage();
            return false;
        }
    }

    private function clear_caches( $product_id ): void {
        if ( function_exists( 'wc_delete_product_transients' ) ) {
            wc_delete_product_transients( $product_id );
        }
        clean_post_cache( $product_id );
    }
}
```

- [ ] **Step 2: PHP lint**

```bash
php -l wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-apply-service.php
```
Expected: `No syntax errors detected`

---

## Task 5 — Wire up in sciuuusadmin.php

**Files:**
- Modify: `wp-content/plugins/sciuuusadmin/sciuuusadmin.php`

The three new service classes need to be required before `class-product-attributes-page.php`.

- [ ] **Step 1: Add three require_once lines**

In `sciuuusadmin.php`, after line 36 (the last size-service require_once) and before line 37 (the attributes-page require_once), insert:

```php
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-collo-attribute-audit-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-collo-attribute-plan-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-collo-attribute-apply-service.php';
```

The block should now read (lines 30–40):
```php
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-settings.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-attribute-audit-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-attribute-plan-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-attribute-apply-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-size-attribute-audit-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-size-attribute-plan-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-size-attribute-apply-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-collo-attribute-audit-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-collo-attribute-plan-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-collo-attribute-apply-service.php';
require_once SCIUUUSADMIN_PLUGIN_DIR . 'includes/class-product-attributes-page.php';
```

- [ ] **Step 2: PHP lint**

```bash
php -l wp-content/plugins/sciuuusadmin/sciuuusadmin.php
```
Expected: `No syntax errors detected`

---

## Task 6 — Add Collo tab to class-product-attributes-page.php

**Files:**
- Modify: `wp-content/plugins/sciuuusadmin/includes/class-product-attributes-page.php`

Read the full file before editing. There are six distinct spots to update.

### 6a — Add private service properties (after line 25, the last size service property)

After:
```php
    private Product_Size_Attribute_Apply_Service $size_apply_service;
```

Insert:
```php

    private Product_Collo_Attribute_Audit_Service $collo_audit_service;
    private Product_Collo_Attribute_Plan_Service $collo_plan_service;
    private Product_Collo_Attribute_Apply_Service $collo_apply_service;
```

### 6b — Wire up in __construct (after line 35, last size service instantiation)

After:
```php
        $this->size_apply_service = new Product_Size_Attribute_Apply_Service();
```

Insert:
```php

        $this->collo_audit_service = new Product_Collo_Attribute_Audit_Service();
        $this->collo_plan_service  = new Product_Collo_Attribute_Plan_Service();
        $this->collo_apply_service = new Product_Collo_Attribute_Apply_Service();
```

### 6c — Add 'collo' to sanitize_tab()

Change:
```php
        return in_array( $tab, [ 'color-family', 'size' ], true ) ? $tab : 'color-family';
```
To:
```php
        return in_array( $tab, [ 'color-family', 'size', 'collo' ], true ) ? $tab : 'color-family';
```

### 6d — Add tab link in render() (after the Taglia tab link, around line 110)

After:
```php
        echo '<a href="' . esc_url( $this->tab_url( 'size' ) ) . '" class="nav-tab ' . ( $tab === 'size' ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Taglia (pa_size)', 'sciuuusadmin' ) . '</a>';
```

Insert:
```php
        echo '<a href="' . esc_url( $this->tab_url( 'collo' ) ) . '" class="nav-tab ' . ( $tab === 'collo' ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Collo (pa_collo-del-piede)', 'sciuuusadmin' ) . '</a>';
```

### 6e — Add audit routing in audit_for_tab() (around line 137)

Change:
```php
        if ( $tab === 'size' ) {
            return $this->size_audit_service->audit_products( $search, $paged, $per_page, $status_filter );
        }
        return $this->color_audit_service->audit_products( $search, $paged, $per_page );
```
To:
```php
        if ( $tab === 'size' ) {
            return $this->size_audit_service->audit_products( $search, $paged, $per_page, $status_filter );
        }
        if ( $tab === 'collo' ) {
            return $this->collo_audit_service->audit_products( $search, $paged, $per_page );
        }
        return $this->color_audit_service->audit_products( $search, $paged, $per_page );
```

### 6f — Add collo handling in action router (the block that processes POST actions)

The render() method contains a block that switches on `$action`. Find the section handling `ACTION_SAVE_ROW` (around line 140–170). It currently has a pattern like:

```php
$ok = $tab === 'size'
    ? $this->size_apply_service->apply_manual_row( ... )
    : $this->color_apply_service->apply_manual_row( ... );
```

Replace that ternary with a if/elseif/else:

```php
if ( $tab === 'size' ) {
    $ok = $this->size_apply_service->apply_manual_row( $product_id, $wanted_slugs, $visible, $error_message );
} elseif ( $tab === 'collo' ) {
    $ok = $this->collo_apply_service->apply_manual_row( $product_id, $wanted_slugs, $visible, $error_message );
} else {
    $ok = $this->color_apply_service->apply_manual_row( $product_id, $wanted_slugs, $visible, $error_message );
}
```

Do the same for `ACTION_DRY_RUN` and `ACTION_APPLY` where the plan/apply services are called — add `elseif ($tab === 'collo')` branches using `$this->collo_plan_service` and `$this->collo_apply_service`.

For `ACTION_REFRESH_SELECTED` and `ACTION_REFRESH_PAGE`, both currently call audit services to rebuild rows. Add collo routing:

```php
if ( $tab === 'size' ) {
    $row = $this->size_audit_service->build_row( $product_id );
} elseif ( $tab === 'collo' ) {
    $row = $this->collo_audit_service->build_row( $product_id );
} else {
    $row = $this->color_audit_service->build_row( $product_id );
}
```

For `ACTION_CONVERT_PAGE` and `ACTION_REBUILD_LOOKUP` — these are size-specific. No equivalent needed for collo (all 30 products can be handled row by row). Add guard so they do nothing when tab=collo:

```php
} elseif ( $action === self::ACTION_CONVERT_PAGE && $tab === 'size' ) {
    // (unchanged)
} elseif ( $action === self::ACTION_REBUILD_LOOKUP && $tab === 'size' ) {
    // (unchanged)
}
```
No changes needed here — the existing guards already scope these to `$tab === 'size'`.

### 6g — Add collo column in table header (around line 428)

After the size-specific "taglia → Taglia" th (which is only rendered when `$tab === 'size'`):

```php
            if ( $tab === 'size' ) {
                echo '<th>taglia &rarr; Taglia</th>';
            }
```

Add immediately after:
```php
            if ( $tab === 'collo' ) {
                echo '<th>Text value</th>';
            }
```

### 6h — Add collo column in table rows (around line 463)

After the existing `if ($tab === 'size') { ... }` block that renders the convert column, add:

```php
                if ( $tab === 'collo' ) {
                    $raw = esc_html( $row['raw_text_value'] ?? '' );
                    $legacy_badge = $row['has_legacy']
                        ? '<span style="color:#b45309;font-weight:700;">&#9888; Text attr</span>'
                        : '<span style="color:#16a34a;">&#10003; Converted</span>';
                    echo '<td>' . $legacy_badge . ( $raw !== '' ? '<br><small style="color:#6b7280;">' . $raw . '</small>' : '' ) . '</td>';
                }
```

### 6i — Update manual_terms placeholder for collo tab (around line 515)

Change:
```php
                    placeholder="' . ( $tab === 'size' ? '20,21,22' : 'blu, verde' ) . '"
```
To:
```php
                    placeholder="' . ( $tab === 'size' ? '20,21,22' : ( $tab === 'collo' ? 'basso,medio,alto' : 'blu, verde' ) ) . '"
```

### 6j — Update colspan for collo (around line 438)

Change:
```php
            $empty_colspan = $tab === 'size' ? '11' : '10';
```
To:
```php
            $empty_colspan = $tab === 'size' ? '11' : ( $tab === 'collo' ? '11' : '10' );
```

- [ ] **Step 1: Apply all 6a–6j edits to class-product-attributes-page.php**

Read the file first (`Read` tool), then apply each sub-edit with the `Edit` tool. Apply them top-to-bottom (6a, 6b, 6c, 6d, 6e, 6f, 6g, 6h, 6i, 6j).

- [ ] **Step 2: PHP lint**

```bash
php -l wp-content/plugins/sciuuusadmin/includes/class-product-attributes-page.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add \
  wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-audit-service.php \
  wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-plan-service.php \
  wp-content/plugins/sciuuusadmin/includes/class-product-collo-attribute-apply-service.php \
  wp-content/plugins/sciuuusadmin/sciuuusadmin.php \
  wp-content/plugins/sciuuusadmin/includes/class-product-attributes-page.php
git commit -m "feat: add Collo tab to sciuuusadmin Product Attributes page"
```

---

## Task 7 — Verify end-to-end

- [ ] **Step 1: Confirm attribute exists in WooCommerce**

```bash
cd /home/dh24839/projects/wp-docker
docker compose run --rm --no-deps wordpress-cli wp wc product_attribute list --format=table
```

Expected: a row with `name=Collo del Piede`, `slug=collo-del-piede`.

- [ ] **Step 2: Confirm terms exist**

```bash
docker compose run --rm --no-deps wordpress-cli wp term list pa_collo-del-piede --format=table
```

Expected: 3 rows — basso, medio, alto.

- [ ] **Step 3: Open admin page in browser**

Use Playwright MCP to navigate to:
`http://localhost:8080/wp-admin/admin.php?page=sciuuusadmin-product-attributes&tab=collo`

Verify:
- "Collo (pa_collo-del-piede)" tab is active
- Table shows ~30 products
- Each row shows "Text attr" badge and the raw pipe-separated value in the Text value column
- `manual_terms` input shows placeholder `basso,medio,alto`
- Status column shows "Needs Convert" for unconverted products

- [ ] **Step 4: Convert one product**

For the first product in the list:
- Type `basso,medio,alto` in the Edit Terms input
- Check Visible
- Click Save

Verify: row reloads with status "OK", "Text attr" badge changes to "Converted".

- [ ] **Step 5: Confirm taxonomy assignment via WP-CLI**

```bash
docker compose run --rm --no-deps wordpress-cli wp post term list <product_id> pa_collo-del-piede --format=table
```

Expected: rows for basso, medio, alto.

- [ ] **Step 6: Confirm text attribute removed**

```bash
docker compose run --rm --no-deps wordpress-cli wp post meta get <product_id> _product_attributes
```

Expected: the serialised array contains `pa_collo-del-piede` (taxonomy attr) and does NOT contain `collo-del-piede` (text attr).

- [ ] **Step 7: Final commit (if any fixups were needed)**

```bash
git add -p
git commit -m "fix: collo tab verification fixups"
```

---

## Verification Summary

| Check | Expected |
|-------|----------|
| `wc product_attribute list` | row for collo-del-piede |
| `wp term list pa_collo-del-piede` | basso, medio, alto |
| Admin tab URL loads | table with ~30 products, no PHP errors |
| Save one row | status → OK, taxonomy assigned, text attr removed |
| WP-CLI term list on saved product | basso / medio / alto |
| WP-CLI meta get _product_attributes | no `collo-del-piede` key |
