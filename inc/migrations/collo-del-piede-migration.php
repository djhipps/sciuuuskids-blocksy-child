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
