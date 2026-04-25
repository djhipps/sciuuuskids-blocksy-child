<?php
/**
 * pa_color-family swatch injection for the shop sidebar filter block.
 *
 * - Hides pa_color-family from variation attribute maps (defence-in-depth;
 *   the attribute is also flagged non-variation in the product admin).
 * - Hooks render_block on woocommerce/product-filter-attribute and injects
 *   an <img class="sciuuus-family-swatch"> beside each checkbox whose value
 *   matches a pa_color-family term, sourcing the URL from the term's
 *   `product_attribute_image` term-meta (with theme-bundled fallback).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SCIUUUS_COLOR_FAMILY_TAXONOMY = 'pa_color-family';

add_filter( 'woocommerce_product_variation_attributes', function ( $attributes ) {
	if ( is_array( $attributes ) ) {
		unset( $attributes[ SCIUUUS_COLOR_FAMILY_TAXONOMY ] );
	}
	return $attributes;
} );

add_filter( 'render_block', 'sciuuus_color_family_render_block', 10, 2 );

function sciuuus_color_family_render_block( $block_content, $block ) {
	if ( ( $block['blockName'] ?? '' ) !== 'woocommerce/product-filter-attribute' ) {
		return $block_content;
	}

	$attrs    = $block['attrs'] ?? [];
	$taxonomy = null;

	if ( ! empty( $attrs['taxonomy'] ) && is_string( $attrs['taxonomy'] ) ) {
		$taxonomy = $attrs['taxonomy'];
	} elseif ( ! empty( $attrs['attributeId'] ) ) {
		$taxonomy = wc_attribute_taxonomy_name_by_id( (int) $attrs['attributeId'] );
	}

	if ( $taxonomy !== SCIUUUS_COLOR_FAMILY_TAXONOMY ) {
		return $block_content;
	}

	return sciuuus_inject_family_swatches( $block_content );
}

function sciuuus_color_family_swatch_map() {
	static $cache = null;
	if ( $cache !== null ) {
		return $cache;
	}
	$cache = [];

	if ( ! taxonomy_exists( SCIUUUS_COLOR_FAMILY_TAXONOMY ) ) {
		return $cache;
	}

	$terms = get_terms( [
		'taxonomy'   => SCIUUUS_COLOR_FAMILY_TAXONOMY,
		'hide_empty' => false,
	] );
	if ( is_wp_error( $terms ) ) {
		return $cache;
	}

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();

	foreach ( $terms as $term ) {
		$attachment_id = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
		$url           = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '';
		if ( ! $url ) {
			$bundled = $theme_dir . '/assets/swatches/' . $term->slug . '.png';
			if ( file_exists( $bundled ) ) {
				$url = $theme_uri . '/assets/swatches/' . $term->slug . '.png';
			}
		}
		if ( $url ) {
			$cache[ $term->slug ] = [
				'url'  => $url,
				'name' => $term->name,
			];
		}
	}

	return $cache;
}

function sciuuus_inject_family_swatches( $html ) {
	$map = sciuuus_color_family_swatch_map();
	if ( empty( $map ) ) {
		return $html;
	}

	return preg_replace_callback(
		'/<input\b[^>]*\bvalue="([^"]+)"[^>]*>/i',
		function ( $m ) use ( $map ) {
			$slug = $m[1];
			if ( ! isset( $map[ $slug ] ) ) {
				return $m[0];
			}
			$img = sprintf(
				'<img class="sciuuus-family-swatch" src="%s" alt="%s" width="18" height="18" loading="lazy" decoding="async" />',
				esc_url( $map[ $slug ]['url'] ),
				esc_attr( $map[ $slug ]['name'] )
			);

			$input = preg_replace(
				'/^<input\b/i',
				'<input data-sciuuus-family="1"',
				$m[0],
				1
			);

			$input = preg_replace(
				'/\bclass="([^"]*)"/i',
				'class="$1 sciuuus-family-input"',
				$input,
				1,
				$replaced
			);
			if ( ! $replaced ) {
				$input = preg_replace(
					'/^<input\b/i',
					'<input class="sciuuus-family-input"',
					$input,
					1
				);
			}

			return $input . $img;
		},
		$html
	);
}
