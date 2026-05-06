<?php
/**
 * Idempotent migration for global size attribute taxonomy (pa_size).
 *
 * Invocation:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-migration.php
 *
 * Purpose:
 * - Create global attribute `size` (taxonomy pa_size) if missing.
 * - Create normalized size terms (edit $size_terms if needed before first live run).
 * - Map legacy variation keys attribute_size / attribute_taglia to attribute_pa_size.
 * - Assign product-level pa_size terms for stable archive filtering.
 *
 * NOTE: This script is intentionally not loaded by functions.php.
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

/*
 * Canonical dictionary. Confirm with business before running on production.
 * Keys are normalized slugs, values are human labels.
 */
$size_terms = [
	'20'      => '20',
	'21'      => '21',
	'22'      => '22',
	'23'      => '23',
	'24'      => '24',
	'25'      => '25',
	'26'      => '26',
	'27'      => '27',
	'28'      => '28',
	'29'      => '29',
	'30'      => '30',
	'31'      => '31',
	'32'      => '32',
	'33'      => '33',
	'34'      => '34',
	'35'      => '35',
	'36'      => '36',
	'37'      => '37',
	'38'      => '38',
	'39'      => '39',
	'40'      => '40',
	'xs'      => 'XS',
	's'       => 'S',
	'm'       => 'M',
	'l'       => 'L',
	'xl'      => 'XL',
	'xxl'     => 'XXL',
	'one-size'=> 'Taglia unica',
];

$legacy_keys = [ 'attribute_size', 'attribute_taglia' ];

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-6s] %s\n", $tag, $msg ) );
};

$normalize_slug = function ( $raw ) {
	$value = strtolower( trim( (string) $raw ) );
	if ( $value === '' ) {
		return '';
	}
	$value = str_replace( [ '_', ',', ';', '/' ], [ '-', '-', '-', '-' ], $value );
	$value = preg_replace( '/\s+/', '-', $value );
	$value = preg_replace( '/[^a-z0-9\-]/', '', $value );
	$value = preg_replace( '/-+/', '-', $value );
	$value = trim( $value, '-' );
	return sanitize_title( $value );
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

// ---------- 3. Create size terms ----------
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
}

// ---------- 4. Migrate variation keys and assign product terms ----------
$products = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'private' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

$variation_rows_updated = 0;
$variation_rows_deleted = 0;
$product_terms_set      = 0;
$product_attr_backfills = 0;
$unknown_values         = [];

foreach ( $products as $product_id ) {
	$variation_ids = get_posts(
		[
			'post_type'      => 'product_variation',
			'post_status'    => [ 'publish', 'private' ],
			'post_parent'    => (int) $product_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]
	);

	if ( empty( $variation_ids ) ) {
		continue;
	}

	$product_slugs = [];

	foreach ( $variation_ids as $variation_id ) {
		$current_pa_size = (string) get_post_meta( $variation_id, 'attribute_pa_size', true );
		if ( $current_pa_size !== '' ) {
			$product_slugs[] = sanitize_title( $current_pa_size );
		}

		$legacy_value = '';
		$legacy_key   = '';
		foreach ( $legacy_keys as $key ) {
			$raw = (string) get_post_meta( $variation_id, $key, true );
			if ( $raw !== '' ) {
				$legacy_value = $raw;
				$legacy_key   = $key;
				break;
			}
		}

		if ( $legacy_value === '' ) {
			continue;
		}

		$normalized = $normalize_slug( $legacy_value );
		if ( $normalized === '' ) {
			$unknown_values[] = $legacy_value;
			continue;
		}

		if ( ! isset( $size_terms[ $normalized ] ) ) {
			$unknown_values[] = $legacy_value;
			continue;
		}

		if ( $current_pa_size !== $normalized ) {
			update_post_meta( $variation_id, 'attribute_pa_size', $normalized );
			$variation_rows_updated++;
		}

		$product_slugs[] = $normalized;

		if ( $legacy_key === 'attribute_size' ) {
			delete_post_meta( $variation_id, 'attribute_taglia' );
		} elseif ( $legacy_key === 'attribute_taglia' ) {
			delete_post_meta( $variation_id, 'attribute_size' );
		}
		delete_post_meta( $variation_id, $legacy_key );
		$variation_rows_deleted++;
	}

	$product_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', $product_slugs ) ) ) );
	if ( ! empty( $product_slugs ) ) {
		$res = wp_set_object_terms( $product_id, $product_slugs, $size_taxonomy, false );
		if ( ! is_wp_error( $res ) ) {
			$product_terms_set++;
		}

		$attrs = get_post_meta( $product_id, '_product_attributes', true );
		if ( ! is_array( $attrs ) ) {
			$attrs = [];
		}
		if ( empty( $attrs[ $size_taxonomy ] ) || ! is_array( $attrs[ $size_taxonomy ] ) ) {
			$attrs[ $size_taxonomy ] = [
				'name'         => $size_taxonomy,
				'value'        => '',
				'position'     => count( $attrs ),
				'is_visible'   => 1,
				'is_variation' => 1,
				'is_taxonomy'  => 1,
			];
			update_post_meta( $product_id, '_product_attributes', $attrs );
			$product_attr_backfills++;
		}
	}
}

$unknown_values = array_values( array_unique( array_filter( array_map( 'trim', $unknown_values ) ) ) );

$log( 'INFO', "variation rows updated to attribute_pa_size: $variation_rows_updated" );
$log( 'INFO', "legacy variation rows deleted: $variation_rows_deleted" );
$log( 'INFO', "products assigned pa_size terms: $product_terms_set" );
$log( 'INFO', "products backfilled _product_attributes[pa_size]: $product_attr_backfills" );

if ( ! empty( $unknown_values ) ) {
	$log( 'WARN', 'unmapped legacy size values (extend dictionary before rerun):' );
	foreach ( $unknown_values as $raw ) {
		$log( 'WARN', " - $raw" );
	}
} else {
	$log( 'OK', 'no unmapped legacy size values detected in this run' );
}

$log( 'NEXT', 'run full Woo lookup regeneration:' );
$log( 'NEXT', 'wp wc tool run regenerate_product_attributes_lookup_table --user=<admin_login>' );
$log( 'NEXT', 'clear transients only if archive/filter output remains stale.' );
