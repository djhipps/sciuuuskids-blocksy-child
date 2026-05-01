<?php
/**
 * One-off fixer: ensure pa_color-family is visible on product pages.
 *
 * Run:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-make-visible.php
 *
 * Options via env vars:
 * - SCIUUUS_DRY_RUN=1   -> report only, do not write.
 * - SCIUUUS_VERBOSE=1   -> print per-product changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

$taxonomy = 'pa_color-family';
$dry_run  = getenv( 'SCIUUUS_DRY_RUN' ) === '1';
$verbose  = getenv( 'SCIUUUS_VERBOSE' ) === '1';

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-8s] %s\n", $tag, $msg ) );
};

$products = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'private', 'draft', 'pending', 'future' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

$checked   = 0;
$with_term = 0;
$changed   = 0;
$added     = 0;
$unchanged = 0;

foreach ( $products as $product_id ) {
	$checked++;
	$terms = wp_get_post_terms( $product_id, $taxonomy, [ 'fields' => 'ids' ] );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		continue;
	}

	$with_term++;
	$attrs = get_post_meta( $product_id, '_product_attributes', true );
	if ( ! is_array( $attrs ) ) {
		$attrs = [];
	}

	$before = isset( $attrs[ $taxonomy ] ) && is_array( $attrs[ $taxonomy ] ) ? $attrs[ $taxonomy ] : null;
	$row_added = false;

	if ( ! $before ) {
		$row_added = true;
		$max_position = -1;
		foreach ( $attrs as $row ) {
			if ( is_array( $row ) && isset( $row['position'] ) ) {
				$max_position = max( $max_position, (int) $row['position'] );
			}
		}
		$attrs[ $taxonomy ] = [
			'name'         => $taxonomy,
			'value'        => '',
			'position'     => $max_position + 1,
			'is_visible'   => 1,
			'is_variation' => 0,
			'is_taxonomy'  => 1,
		];
	} else {
		$attrs[ $taxonomy ]['name']         = $taxonomy;
		$attrs[ $taxonomy ]['is_taxonomy']  = 1;
		$attrs[ $taxonomy ]['is_variation'] = 0;
		$attrs[ $taxonomy ]['is_visible']   = 1;
	}

	$after = $attrs[ $taxonomy ];

	$is_changed = $row_added
		|| ! is_array( $before )
		|| (string) ( $before['name'] ?? '' ) !== $taxonomy
		|| (int) ( $before['is_taxonomy'] ?? 0 ) !== 1
		|| (int) ( $before['is_variation'] ?? 0 ) !== 0
		|| (int) ( $before['is_visible'] ?? 0 ) !== 1;

	if ( ! $is_changed ) {
		$unchanged++;
		continue;
	}

	$changed++;
	if ( $row_added ) {
		$added++;
	}

	if ( $verbose ) {
		$log(
			'ITEM',
			sprintf(
				'product_id=%d title="%s" row_added=%s visible_before=%s visible_after=%s',
				$product_id,
				get_the_title( $product_id ),
				$row_added ? 'yes' : 'no',
				is_array( $before ) ? (string) ( $before['is_visible'] ?? '(unset)' ) : '(none)',
				(string) ( $after['is_visible'] ?? '(unset)' )
			)
		);
	}

	if ( ! $dry_run ) {
		update_post_meta( $product_id, '_product_attributes', $attrs );
	}
}

$log( 'MODE', $dry_run ? 'dry-run (no writes)' : 'write' );
$log( 'RESULT', "checked=$checked with_family=$with_term changed=$changed added_rows=$added unchanged=$unchanged" );
$log( 'DONE', 'visibility fixer complete' );
