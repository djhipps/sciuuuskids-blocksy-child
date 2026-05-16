<?php
/**
 * Setup script for the pa_size global attribute taxonomy.
 *
 * Invocation:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-migration.php
 *
 * Purpose — infrastructure setup ONLY:
 * - Create global WC attribute `size` (taxonomy pa_size) if missing.
 * - Create normalized numeric size terms (18..45) if missing.
 *
 * This script does NOT touch any products or variation meta.
 * Per-product conversion is done manually via:
 *   sciuuusadmin → Product Attributes → Taglia (pa_size) → Convert button
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

if ( ! function_exists( 'wc_create_attribute' ) ) {
	fwrite( STDERR, "[FATAL] WooCommerce not loaded\n" );
	exit( 1 );
}

$size_attr_slug = 'size';
$size_taxonomy  = 'pa_size';
$size_label     = 'Taglia';

$size_terms = [
	'18' => '18',
	'19' => '19',
	'20' => '20',
	'21' => '21',
	'22' => '22',
	'23' => '23',
	'24' => '24',
	'25' => '25',
	'26' => '26',
	'27' => '27',
	'28' => '28',
	'29' => '29',
	'30' => '30',
	'31' => '31',
	'32' => '32',
	'33' => '33',
	'34' => '34',
	'35' => '35',
	'36' => '36',
	'37' => '37',
	'38' => '38',
	'39' => '39',
	'40' => '40',
	'41' => '41',
	'42' => '42',
	'43' => '43',
	'44' => '44',
	'45' => '45',
];

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-6s] %s\n", $tag, $msg ) );
};

// ---------- 1. Create/update global attribute ----------
$attribute_id = wc_attribute_taxonomy_id_by_name( $size_attr_slug );
if ( $attribute_id ) {
	$log( 'SKIP', "attribute $size_taxonomy already exists (id $attribute_id)" );
} else {
	$attribute_id = wc_create_attribute(
		[
			'name'         => $size_label,
			'slug'         => $size_attr_slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		]
	);
	if ( is_wp_error( $attribute_id ) ) {
		$log( 'FAIL', 'wc_create_attribute: ' . $attribute_id->get_error_message() );
		exit( 1 );
	}
	$log( 'OK', "created attribute $size_taxonomy (id $attribute_id)" );
	delete_transient( 'wc_attribute_taxonomies' );
}

// ---------- 2. Register taxonomy in current process ----------
if ( ! taxonomy_exists( $size_taxonomy ) ) {
	register_taxonomy(
		$size_taxonomy,
		'product',
		[
			'hierarchical' => false,
			'show_ui'      => false,
			'query_var'    => true,
			'rewrite'      => false,
		]
	);
	$log( 'OK', "registered taxonomy $size_taxonomy in current process" );
}

// ---------- 3. Create size terms (18–45, idempotent) ----------
$created = 0;
foreach ( $size_terms as $slug => $name ) {
	$existing = get_term_by( 'slug', $slug, $size_taxonomy );
	if ( $existing ) {
		continue;
	}
	$res = wp_insert_term( $name, $size_taxonomy, [ 'slug' => $slug ] );
	if ( is_wp_error( $res ) ) {
		$log( 'FAIL', "wp_insert_term($slug): " . $res->get_error_message() );
		continue;
	}
	$log( 'OK', "created term $slug (id {$res['term_id']})" );
	$created++;
}

if ( $created === 0 ) {
	$log( 'SKIP', 'all terms 18–45 already exist in ' . $size_taxonomy );
}

$log( 'DONE', 'Setup complete — attribute and terms ready.' );
$log( 'NEXT', 'Use sciuuusadmin → Product Attributes → Taglia tab to convert products one by one.' );
$log( 'NEXT', 'The ⚡ Convert button handles variation meta + attribute cleanup per product.' );
$log( 'NEXT', 'Use the "Rebuild Lookup Table" button in the Taglia tab to test native pa_size filters.' );
