<?php
/**
 * Audit script for size migration coverage.
 *
 * Invocation:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/size-taxonomy-audit.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

$size_taxonomy = 'pa_size';
$legacy_keys   = [ 'attribute_size', 'attribute_taglia' ];

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-8s] %s\n", $tag, $msg ) );
};

$products = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'private' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

$products_total             = count( $products );
$products_with_pa_size      = 0;
$products_with_legacy_keys  = 0;
$products_without_any_size  = 0;
$legacy_value_counts        = [];
$products_with_legacy_list  = [];
$products_without_pa_size   = [];

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

	$has_legacy      = false;
	$has_any_size    = false;
	$has_pa_size_var = false;

	foreach ( $variation_ids as $variation_id ) {
		$pa_size = (string) get_post_meta( $variation_id, 'attribute_pa_size', true );
		if ( $pa_size !== '' ) {
			$has_any_size    = true;
			$has_pa_size_var = true;
		}

		foreach ( $legacy_keys as $legacy_key ) {
			$legacy = (string) get_post_meta( $variation_id, $legacy_key, true );
			if ( $legacy === '' ) {
				continue;
			}
			$has_legacy   = true;
			$has_any_size = true;
			$token = trim( $legacy );
			if ( $token !== '' ) {
				if ( ! isset( $legacy_value_counts[ $token ] ) ) {
					$legacy_value_counts[ $token ] = 0;
				}
				$legacy_value_counts[ $token ]++;
			}
		}
	}

	$assigned = [];
	if ( taxonomy_exists( $size_taxonomy ) ) {
		$assigned = wp_get_post_terms( $product_id, $size_taxonomy, [ 'fields' => 'slugs' ] );
		if ( ! is_wp_error( $assigned ) && ! empty( $assigned ) ) {
			$products_with_pa_size++;
		}
	}

	if ( $has_legacy ) {
		$products_with_legacy_keys++;
		$products_with_legacy_list[] = $product_id;
	}

	if ( $has_any_size && empty( $assigned ) ) {
		$products_without_pa_size[] = $product_id;
	}

	if ( ! $has_any_size && empty( $assigned ) && ! $has_pa_size_var ) {
		$products_without_any_size++;
	}
}

arsort( $legacy_value_counts, SORT_NATURAL | SORT_FLAG_CASE );

$coverage = $products_total > 0 ? round( ( $products_with_pa_size / $products_total ) * 100, 2 ) : 0;
$log( 'SETUP', 'taxonomy_exists=' . ( taxonomy_exists( $size_taxonomy ) ? 'yes' : 'no' ) );
$log( 'SETUP', "products_total=$products_total" );
$log( 'COVERAGE', "products_with_pa_size=$products_with_pa_size ({$coverage}%)" );
$log( 'LEGACY', "products_with_legacy_keys=$products_with_legacy_keys" );
$log( 'GAP', 'products_with_size_signal_but_no_pa_size=' . count( $products_without_pa_size ) );
$log( 'GAP', "products_without_any_size_signal=$products_without_any_size" );

if ( ! empty( $products_with_legacy_list ) ) {
	$log( 'LEGACY', 'product_ids_still_on_legacy_keys=' . implode( ',', $products_with_legacy_list ) );
}

if ( ! empty( $products_without_pa_size ) ) {
	$log( 'GAP', 'product_ids_missing_pa_size=' . implode( ',', $products_without_pa_size ) );
}

if ( ! empty( $legacy_value_counts ) ) {
	$log( 'VALUES', 'legacy raw values observed (value => count):' );
	foreach ( $legacy_value_counts as $value => $count ) {
		$log( 'VALUES', "$value => $count" );
	}
}

$log( 'NEXT', 'after each migration batch run:' );
$log( 'NEXT', 'wp wc tool run regenerate_product_attributes_lookup_table --user=<admin_login>' );
$log( 'NEXT', 'clear transients only if filter output remains stale.' );
