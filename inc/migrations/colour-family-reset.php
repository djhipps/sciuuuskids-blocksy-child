<?php
/**
 * Full reset for pa_color-family setup.
 *
 * Run:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-reset.php
 *
 * What it removes:
 * - Product term assignments for pa_color-family
 * - _product_attributes row for pa_color-family on products
 * - pa_color-family terms (and optional swatch attachments linked via term meta)
 * - Global attribute definition color-family (Woo attribute taxonomy row)
 * - Woo sidebar widget blocks referencing Famiglia Colore / pa_color-family
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

if ( ! function_exists( 'wc_delete_attribute' ) ) {
	fwrite( STDERR, "[FATAL] WooCommerce not loaded\n" );
	exit( 1 );
}

$family_attr_slug = 'color-family';
$family_taxonomy  = 'pa_color-family';
$family_label     = 'Famiglia Colore';
$delete_swatch_attachments = true;

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-6s] %s\n", $tag, $msg ) );
};

$attribute_id = (int) wc_attribute_taxonomy_id_by_name( $family_attr_slug );
$log( 'INFO', "starting reset for $family_taxonomy (attribute_id=" . ( $attribute_id ?: 'none' ) . ')' );

$product_ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'private', 'draft', 'pending', 'future' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

$cleared_terms = 0;
$attr_rows_removed = 0;

foreach ( $product_ids as $product_id ) {
	$current_terms = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'ids' ] );
	if ( ! is_wp_error( $current_terms ) && ! empty( $current_terms ) ) {
		wp_set_object_terms( $product_id, [], $family_taxonomy, false );
		$cleared_terms++;
	}

	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	if ( is_array( $attributes ) && isset( $attributes[ $family_taxonomy ] ) ) {
		unset( $attributes[ $family_taxonomy ] );
		update_post_meta( $product_id, '_product_attributes', $attributes );
		$attr_rows_removed++;
	}
}

$log( 'OK', "cleared product term assignments on $cleared_terms products" );
$log( 'OK', "removed _product_attributes[$family_taxonomy] from $attr_rows_removed products" );

$deleted_terms = 0;
$deleted_attachments = 0;

if ( taxonomy_exists( $family_taxonomy ) ) {
	$terms = get_terms(
		[
			'taxonomy'   => $family_taxonomy,
			'hide_empty' => false,
		]
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$att_id = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
			if ( $delete_swatch_attachments && $att_id > 0 ) {
				$deleted = wp_delete_attachment( $att_id, true );
				if ( $deleted ) {
					$deleted_attachments++;
				}
			}

			$res = wp_delete_term( $term->term_id, $family_taxonomy );
			if ( ! is_wp_error( $res ) && $res ) {
				$deleted_terms++;
			}
		}
	}
}

$log( 'OK', "deleted $deleted_terms terms from $family_taxonomy" );
if ( $delete_swatch_attachments ) {
	$log( 'OK', "deleted $deleted_attachments swatch attachments" );
}

if ( $attribute_id > 0 ) {
	$res = wc_delete_attribute( $attribute_id );
	if ( is_wp_error( $res ) ) {
		$log( 'WARN', 'wc_delete_attribute failed: ' . $res->get_error_message() );
	} else {
		$log( 'OK', "deleted Woo attribute $family_attr_slug (id $attribute_id)" );
	}
} else {
	$log( 'SKIP', "Woo attribute $family_attr_slug not found" );
}

delete_transient( 'wc_attribute_taxonomies' );
if ( function_exists( 'wc_delete_product_transients' ) ) {
	wc_delete_product_transients();
}

$sidebars = get_option( 'sidebars_widgets', [] );
$blocks   = get_option( 'widget_block', [] );
$removed_widget_blocks = 0;

foreach ( (array) ( $sidebars['sidebar-woocommerce'] ?? [] ) as $wid ) {
	if ( strpos( (string) $wid, 'block-' ) !== 0 ) {
		continue;
	}

	$block_id = (int) str_replace( 'block-', '', (string) $wid );
	if ( ! isset( $blocks[ $block_id ]['content'] ) ) {
		continue;
	}

	$content = (string) $blocks[ $block_id ]['content'];
	$original = $content;

	$patterns = [
		'/\n?<!-- wp:woocommerce\/product-filter-attribute\b[^>]*-->.*?<!-- \/wp:woocommerce\/product-filter-attribute -->\n?/s',
		'/\n?<!-- wp:woocommerce\/product-filter-checkbox-list\b[^>]*-->\s*<div class="wp-block-woocommerce-product-filter-checkbox-list wc-block-product-filter-checkbox-list"><\/div>\s*<!-- \/wp:woocommerce\/product-filter-checkbox-list -->\n?/s',
	];

	foreach ( $patterns as $pattern ) {
		$content = preg_replace_callback(
			$pattern,
			function ( $matches ) use ( $family_attr_slug, $family_taxonomy, $family_label ) {
				$block = $matches[0];
				if (
					strpos( $block, 'attributeId' ) !== false ||
					strpos( $block, $family_taxonomy ) !== false ||
					strpos( strtolower( $block ), strtolower( $family_label ) ) !== false ||
					strpos( strtolower( $block ), strtolower( $family_attr_slug ) ) !== false
				) {
					return '';
				}
				return $block;
			},
			$content
		);
	}

	// Fallback cleanup for malformed blocks containing label but missing attributeId.
	$content = preg_replace(
		'/\n?<h3 class="wp-block-heading"[^>]*>\s*Famiglia Colore\s*<\/h3>\n?/i',
		"\n",
		$content
	);

	$content = preg_replace( "/\n{3,}/", "\n\n", $content );

	if ( $content !== $original ) {
		$blocks[ $block_id ]['content'] = $content;
		$removed_widget_blocks++;
	}
}

update_option( 'widget_block', $blocks );

$log( 'OK', "cleaned related filter blocks in $removed_widget_blocks widget block(s)" );

$remaining = [];
if ( taxonomy_exists( $family_taxonomy ) ) {
	$remaining = get_terms(
		[
			'taxonomy'   => $family_taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		]
	);
}

$remaining_count = is_array( $remaining ) ? count( $remaining ) : 0;
$attr_check = (int) wc_attribute_taxonomy_id_by_name( $family_attr_slug );

$log( 'VERIFY', "$family_taxonomy remaining terms=$remaining_count" );
$log( 'VERIFY', "$family_attr_slug attribute id after reset=" . ( $attr_check ?: 'none' ) );
$log( 'DONE', 'colour-family reset complete; safe to re-run migration script' );
