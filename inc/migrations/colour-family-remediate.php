<?php
/**
 * Strong remediation script for pa_color-family.
 *
 * Run:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-remediate.php
 *
 * Safety:
 * - Never modifies pa_color variations/terms.
 * - Can be re-run safely.
 * - Optional hard reset of pa_color-family assignments (OFF by default).
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

if ( ! function_exists( 'wc_create_attribute' ) ) {
	fwrite( STDERR, "[FATAL] WooCommerce not loaded\n" );
	exit( 1 );
}

// ------------------ knobs ------------------
$hard_reset_assignments = false;      // Set true only if you want to clear all pa_color-family product assignments first.
$family_attr_slug       = 'color-family';
$family_taxonomy        = 'pa_color-family';
$family_label           = 'Famiglia Colore'; // admin attribute label (distinct from pa_color)
$filter_heading         = 'Colore';          // storefront filter heading
$swatches_dir           = get_stylesheet_directory() . '/assets/swatches';

$expected_terms = [
	'bianco'      => 'Bianco',
	'nero'        => 'Nero',
	'verde'       => 'Verde',
	'giallo'      => 'Giallo',
	'blu'         => 'Blu',
	'rosa'        => 'Rosa',
	'marrone'     => 'Marrone',
	'multicolore' => 'Multicolore',
	'fantasia'    => 'Fantasia',
];

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-6s] %s\n", $tag, $msg ) );
};

// ------------------ helpers ------------------
$normalize = function ( $value ) {
	$value = (string) $value;
	$value = remove_accents( strtolower( $value ) );
	$value = str_replace( [ '-', '_', '/', '.', ',', ';', ':', '|', '+' ], ' ', $value );
	$value = preg_replace( '/\s+/', ' ', $value );
	return trim( $value );
};

$collect_colour_signals = function ( $product_id ) use ( $normalize ) {
	$signals = [];
	$add     = function ( $raw ) use ( &$signals, $normalize ) {
		$n = $normalize( $raw );
		if ( $n !== '' ) {
			$signals[] = $n;
		}
	};

	$add( get_the_title( $product_id ) );

	$color_terms = wp_get_post_terms( $product_id, 'pa_color', [ 'fields' => 'all' ] );
	if ( ! is_wp_error( $color_terms ) ) {
		foreach ( $color_terms as $term ) {
			$add( $term->name );
			$add( $term->slug );
		}
	}
	$colore_terms = wp_get_post_terms( $product_id, 'pa_colore', [ 'fields' => 'all' ] );
	if ( ! is_wp_error( $colore_terms ) ) {
		foreach ( $colore_terms as $term ) {
			$add( $term->name );
			$add( $term->slug );
		}
	}

	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	if ( is_array( $attributes ) ) {
		foreach ( $attributes as $key => $attr ) {
			if ( ! is_array( $attr ) ) {
				continue;
			}
			$name = isset( $attr['name'] ) ? strtolower( (string) $attr['name'] ) : '';
			if ( $name !== 'pa_color' && $name !== 'color' && $name !== 'colore' && $key !== 'pa_color' ) {
				continue;
			}
			if ( ! empty( $attr['is_taxonomy'] ) ) {
				continue;
			}
			$options = isset( $attr['value'] ) ? (string) $attr['value'] : '';
			foreach ( array_filter( array_map( 'trim', explode( '|', $options ) ) ) as $part ) {
				$add( $part );
			}
		}
	}

	$variation_ids = get_posts(
		[
			'post_type'      => 'product_variation',
			'post_status'    => [ 'publish', 'private' ],
			'post_parent'    => (int) $product_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]
	);
	foreach ( $variation_ids as $variation_id ) {
		$variation_color = get_post_meta( $variation_id, 'attribute_pa_color', true );
		if ( ! $variation_color ) {
			$variation_color = get_post_meta( $variation_id, 'attribute_pa_colore', true );
		}
		if ( ! $variation_color ) {
			continue;
		}
		$add( $variation_color );
		$term = get_term_by( 'slug', $variation_color, 'pa_color' );
		if ( ! ( $term && ! is_wp_error( $term ) ) ) {
			$term = get_term_by( 'slug', $variation_color, 'pa_colore' );
		}
		if ( $term && ! is_wp_error( $term ) ) {
			$add( $term->name );
		}
	}

	return array_values( array_unique( $signals ) );
};

$family_rules = [
	'multicolore' => [ 'multi color', 'multicolor', 'multicolore', 'vari colori', 'colori misti', 'arcobaleno' ],
	'fantasia'    => [ 'fantasia', 'stampa', 'stampato', 'floreale', 'flower', 'fiori', 'tropicale', 'animalier', 'zebra', 'leopard', 'pois', 'righe', 'quadri', 'camouflage', 'mimetico' ],
	'bianco'      => [ 'bianco', 'bianca', 'white', 'avorio', 'ivory', 'crema', 'latte', 'panna' ],
	'nero'        => [ 'nero', 'nera', 'black', 'antracite', 'carbone', 'grafite' ],
	'verde'       => [ 'verde', 'green', 'smeraldo', 'oliva', 'salvia', 'menta', 'foresta' ],
	'giallo'      => [ 'giallo', 'yellow', 'senape', 'ocra gialla', 'lime' ],
	'blu'         => [ 'blu', 'blue', 'azzurro', 'azzuro', 'celeste', 'navy', 'marina', 'denim', 'indaco', 'turchese' ],
	'rosa'        => [ 'rosa', 'pink', 'fucsia', 'corallo', 'lilla', 'viola', 'purple', 'rosso', 'red', 'peach', 'salmone' ],
	'marrone'     => [ 'marrone', 'brown', 'beige', 'sabbia', 'cuoio', 'terra', 'tabacco', 'camel', 'cammello', 'ocra' ],
];

$infer_families = function ( $signals ) use ( $family_rules ) {
	$out = [];
	foreach ( $signals as $signal ) {
		foreach ( $family_rules as $family => $keywords ) {
			foreach ( $keywords as $keyword ) {
				if ( strpos( $signal, $keyword ) !== false ) {
					$out[ $family ] = true;
					break;
				}
			}
		}
	}
	return array_keys( $out );
};

// ------------------ 1) attribute ------------------
$attribute_id = wc_attribute_taxonomy_id_by_name( $family_attr_slug );
if ( ! $attribute_id ) {
	$attribute_id = wc_create_attribute(
		[
			'name'         => $family_label,
			'slug'         => $family_attr_slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		]
	);
	if ( is_wp_error( $attribute_id ) ) {
		$log( 'FAIL', 'wc_create_attribute: ' . $attribute_id->get_error_message() );
		exit( 1 );
	}
	delete_transient( 'wc_attribute_taxonomies' );
	$log( 'OK', "created attribute $family_taxonomy (id $attribute_id)" );
} else {
	$log( 'SKIP', "attribute $family_taxonomy exists (id $attribute_id)" );
}

global $wpdb;
$current_label = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
		$family_attr_slug
	)
);
if ( $current_label !== null && $current_label !== $family_label ) {
	$wpdb->update(
		"{$wpdb->prefix}woocommerce_attribute_taxonomies",
		[ 'attribute_label' => $family_label ],
		[ 'attribute_name' => $family_attr_slug ],
		[ '%s' ],
		[ '%s' ]
	);
	delete_transient( 'wc_attribute_taxonomies' );
	$log( 'OK', "set attribute label to \"$family_label\"" );
}

if ( ! taxonomy_exists( $family_taxonomy ) ) {
	register_taxonomy(
		$family_taxonomy,
		'product',
		[
			'hierarchical' => false,
			'show_ui'      => false,
			'query_var'    => true,
			'rewrite'      => false,
		]
	);
	$log( 'OK', "registered taxonomy $family_taxonomy in process" );
}

// ------------------ 2) terms + images ------------------
foreach ( $expected_terms as $slug => $name ) {
	$term = get_term_by( 'slug', $slug, $family_taxonomy );
	if ( ! $term ) {
		$res = wp_insert_term( $name, $family_taxonomy, [ 'slug' => $slug ] );
		if ( is_wp_error( $res ) ) {
			$log( 'WARN', "term $slug create failed: " . $res->get_error_message() );
			continue;
		}
		$term = get_term( (int) $res['term_id'], $family_taxonomy );
		$log( 'OK', "created term $slug (id {$res['term_id']})" );
	}
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

foreach ( array_keys( $expected_terms ) as $slug ) {
	$term = get_term_by( 'slug', $slug, $family_taxonomy );
	if ( ! $term ) {
		continue;
	}
	$att_id = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
	if ( $att_id && wp_attachment_is_image( $att_id ) ) {
		continue;
	}
	$src = $swatches_dir . '/' . $slug . '.png';
	if ( ! file_exists( $src ) ) {
		$log( 'WARN', "missing swatch source $src" );
		continue;
	}
	$tmp = wp_tempnam( $slug . '.png' );
	if ( ! @copy( $src, $tmp ) ) {
		$log( 'WARN', "copy failed for $slug swatch" );
		continue;
	}
	$file_array = [ 'name' => $slug . '.png', 'tmp_name' => $tmp ];
	$new_att_id = media_handle_sideload( $file_array, 0, "pa_color-family swatch: $slug" );
	if ( is_wp_error( $new_att_id ) ) {
		@unlink( $tmp );
		$log( 'WARN', "sideload failed for $slug: " . $new_att_id->get_error_message() );
		continue;
	}
	update_term_meta( $term->term_id, 'product_attribute_image', $new_att_id );
	$log( 'OK', "set image for $slug (att $new_att_id)" );
}

// ------------------ 3) optional hard reset ------------------
$product_ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'private' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

if ( $hard_reset_assignments ) {
	$cleared = 0;
	foreach ( $product_ids as $product_id ) {
		$current = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'ids' ] );
		if ( is_wp_error( $current ) || empty( $current ) ) {
			continue;
		}
		wp_set_object_terms( $product_id, [], $family_taxonomy, false );
		$cleared++;
	}
	$log( 'INFO', "hard reset cleared assignments on $cleared products" );
}

// ------------------ 4) auto-tag ------------------
$tagged = 0;
$skipped_existing = 0;
$unmatched = 0;
foreach ( $product_ids as $product_id ) {
	$current = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'ids' ] );
	if ( ! is_wp_error( $current ) && ! empty( $current ) ) {
		$skipped_existing++;
		continue;
	}
	$families = $infer_families( $collect_colour_signals( $product_id ) );
	if ( empty( $families ) ) {
		$unmatched++;
		continue;
	}
	$res = wp_set_object_terms( $product_id, $families, $family_taxonomy, false );
	if ( is_wp_error( $res ) ) {
		$log( 'WARN', "auto-tag failed for $product_id: " . $res->get_error_message() );
		continue;
	}
	$tagged++;
}
$log( 'INFO', "auto-tag summary tagged=$tagged skipped_existing=$skipped_existing unmatched=$unmatched" );

// ------------------ 5) ensure product-level attribute row ------------------
$rows_added = 0;
$variation_flags_fixed = 0;
foreach ( $product_ids as $product_id ) {
	$family_terms = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'ids' ] );
	if ( is_wp_error( $family_terms ) || empty( $family_terms ) ) {
		continue;
	}
	$attributes = get_post_meta( $product_id, '_product_attributes', true );
	if ( ! is_array( $attributes ) ) {
		$attributes = [];
	}
	if ( empty( $attributes[ $family_taxonomy ] ) || ! is_array( $attributes[ $family_taxonomy ] ) ) {
		$max_position = -1;
		foreach ( $attributes as $row ) {
			if ( is_array( $row ) && isset( $row['position'] ) ) {
				$max_position = max( $max_position, (int) $row['position'] );
			}
		}
		$attributes[ $family_taxonomy ] = [
			'name'         => $family_taxonomy,
			'value'        => '',
			'position'     => $max_position + 1,
			'is_visible'   => 0,
			'is_variation' => 0,
			'is_taxonomy'  => 1,
		];
		$rows_added++;
	}
	if ( ! empty( $attributes[ $family_taxonomy ]['is_variation'] ) ) {
		$attributes[ $family_taxonomy ]['is_variation'] = 0;
		$variation_flags_fixed++;
	}
	$attributes[ $family_taxonomy ]['is_taxonomy'] = 1;
	$attributes[ $family_taxonomy ]['name'] = $family_taxonomy;
	update_post_meta( $product_id, '_product_attributes', $attributes );
}
$log( 'INFO', "attribute-row backfill added=$rows_added is_variation_fixed=$variation_flags_fixed" );

// ------------------ 6) widget dedupe + heading fixes ------------------
$widget_blocks   = get_option( 'widget_block', [] );
$sidebars        = get_option( 'sidebars_widgets', [] );
$sidebar_widgets = isset( $sidebars['sidebar-woocommerce'] ) && is_array( $sidebars['sidebar-woocommerce'] )
	? $sidebars['sidebar-woocommerce']
	: [];

$widget_block_ids = [];
foreach ( $sidebar_widgets as $widget_ref ) {
	if ( preg_match( '/^block-(\d+)$/', (string) $widget_ref, $m ) ) {
		$widget_block_ids[] = (int) $m[1];
	}
}
$widget_block_ids = array_values( array_unique( $widget_block_ids ) );

if ( empty( $widget_block_ids ) ) {
	$log( 'WARN', 'sidebar-woocommerce has no block-* widgets; skipped widget normalization' );
} else {
	$attrs_json = wp_json_encode( [ 'attributeId' => (int) $attribute_id, 'queryType' => 'or' ] );
	$new_block  = "\n\n<!-- wp:woocommerce/product-filter-attribute $attrs_json -->\n";
	$new_block .= "<div class=\"wp-block-woocommerce-product-filter-attribute\">";
	$new_block .= "<!-- wp:heading {\"level\":3,\"style\":{\"spacing\":{\"margin\":{\"bottom\":\"0.625rem\",\"top\":\"0\"}}}} -->\n";
	$new_block .= "<h3 class=\"wp-block-heading\" style=\"margin-top:0;margin-bottom:0.625rem\">$filter_heading</h3>\n";
	$new_block .= "<!-- /wp:heading -->\n\n";
	$new_block .= "<!-- wp:woocommerce/product-filter-checkbox-list -->\n";
	$new_block .= "<div class=\"wp-block-woocommerce-product-filter-checkbox-list wc-block-product-filter-checkbox-list\"></div>\n";
	$new_block .= "<!-- /wp:woocommerce/product-filter-checkbox-list --></div>\n";
	$new_block .= "<!-- /wp:woocommerce/product-filter-attribute -->";

	$updated_blocks = 0;
	foreach ( $widget_block_ids as $block_id ) {
		if ( empty( $widget_blocks[ $block_id ]['content'] ) ) {
			continue;
		}
		$content = $widget_blocks[ $block_id ]['content'];
		if ( strpos( $content, '<!-- wp:woocommerce/product-filters' ) === false ) {
			continue;
		}

		$cf_pattern = '/<!-- wp:woocommerce\/product-filter-attribute\s+\{[^}]*"attributeId":' . preg_quote( (string) $attribute_id, '/' ) . '[^}]*\}\s+-->.*?<!-- \/wp:woocommerce\/product-filter-attribute -->/s';
		$content = preg_replace( $cf_pattern, '', $content );

		$needle = '<!-- wp:woocommerce/product-filter-taxonomy';
		$pos    = strpos( $content, $needle );
		if ( $pos !== false ) {
			$content = substr( $content, 0, $pos ) . trim( $new_block ) . "\n\n" . substr( $content, $pos );
		} else {
			$content = preg_replace(
				'#<!-- /wp:woocommerce/product-filters -->\s*$#',
				trim( $new_block ) . "\n\n<!-- /wp:woocommerce/product-filters -->",
				$content
			);
		}

		$content = preg_replace(
			'/(<!-- wp:woocommerce\/product-filter-price -->.*?<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">)Famiglia Colore(<\/h3>.*?<!-- \/wp:woocommerce\/product-filter-price -->)/s',
			'$1Price$2',
			$content
		);

		$widget_blocks[ $block_id ]['content'] = $content;
		$updated_blocks++;
	}

	if ( $updated_blocks > 0 ) {
		update_option( 'widget_block', $widget_blocks );
		$log( 'OK', "widget normalization applied to $updated_blocks block(s) in sidebar-woocommerce" );
	} else {
		$log( 'WARN', 'no woocommerce/product-filters block found in sidebar-woocommerce widgets' );
	}
}

// ------------------ 7) verification ------------------
$with_family = 0;
foreach ( $product_ids as $product_id ) {
	$t = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'ids' ] );
	if ( ! is_wp_error( $t ) && ! empty( $t ) ) {
		$with_family++;
	}
}
$log( 'VERIFY', "products_total=" . count( $product_ids ) . " products_with_family=$with_family" );

$pa_color_rows = $wpdb->get_results(
	"SELECT attribute_name, attribute_label, attribute_type
	 FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
	 WHERE attribute_name IN ('color','colore','color-family')",
	ARRAY_A
);
if ( ! empty( $pa_color_rows ) ) {
	foreach ( $pa_color_rows as $row ) {
		$log( 'VERIFY', sprintf( '%s (%s) type=%s', $row['attribute_name'], $row['attribute_label'], $row['attribute_type'] ) );
	}
} else {
	$log( 'VERIFY', 'no color-like attributes found in wp_woocommerce_attribute_taxonomies' );
}
$log( 'DONE', 'remediation complete' );
