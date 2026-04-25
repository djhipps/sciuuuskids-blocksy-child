<?php
/**
 * Idempotent migration for the pa_color-family taxonomy.
 *
 * Invocation (run manually on the target WordPress host):
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-migration.php
 *
 * Steps (each gated on existing state — re-runnable):
 *   1. Create global attribute pa_color-family (type=select, label "Famiglia Colore").
 *   2. Register taxonomy in this CLI run.
 *   3. Create 8 family terms.
 *   4. Sideload theme-bundled swatch PNGs as media; store attachment id as
 *      term meta `product_attribute_image`.
 *   5. Insert a pa_color-family product-filter-attribute block into the
 *      sidebar-woocommerce widget (block-11) before the Category filter.
 *   6. Auto-tag products with families inferred from existing colour signals
 *      (skips any product that already has at least one pa_color-family term).
 *   7. Print verification summary.
 *
 * NOT loaded by functions.php — only reachable via wp eval-file.
 *
 * NOTE: This migration deliberately leaves pa_color (attribute_type=color)
 * untouched. The product-page variation selector continues to use the
 * existing hex-colour swatches. pa_color-family is a separate, filter-only
 * taxonomy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

if ( ! function_exists( 'wc_create_attribute' ) ) {
	fwrite( STDERR, "[FATAL] WooCommerce not loaded\n" );
	exit( 1 );
}

$family_attr_slug = 'color-family';
$family_taxonomy  = 'pa_color-family';
$family_label     = 'Famiglia Colore'; // admin attribute label (distinct from pa_color)
$family_heading   = 'Colore';          // storefront filter heading
$widget_block_id  = 11;
$swatches_dir     = get_stylesheet_directory() . '/assets/swatches';

$terms = [
	'bianco'      => 'Bianco',
	'nero'        => 'Nero',
	'verde'       => 'Verde',
	'blu'         => 'Blu',
	'rosa'        => 'Rosa',
	'marrone'     => 'Marrone',
	'multicolore' => 'Multicolore',
	'fantasia'    => 'Fantasia',
];

$log = function ( $tag, $msg ) {
	fwrite( STDOUT, sprintf( "[%-6s] %s\n", $tag, $msg ) );
};

$normalize = function ( $value ) {
	$value = (string) $value;
	$value = remove_accents( strtolower( $value ) );
	$value = str_replace( [ '-', '_', '/', '.', ',', ';', ':', '|', '+' ], ' ', $value );
	$value = preg_replace( '/\s+/', ' ', $value );
	return trim( $value );
};

$collect_colour_signals = function ( $product_id ) use ( $normalize ) {
	$signals = [];

	$add_signal = function ( $raw ) use ( &$signals, $normalize ) {
		$normalized = $normalize( $raw );
		if ( $normalized !== '' ) {
			$signals[] = $normalized;
		}
	};

	$add_signal( get_the_title( $product_id ) );

	$color_terms = wp_get_post_terms( $product_id, 'pa_color', [ 'fields' => 'all' ] );
	if ( ! is_wp_error( $color_terms ) ) {
		foreach ( $color_terms as $term ) {
			$add_signal( $term->name );
			$add_signal( $term->slug );
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
				$add_signal( $part );
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
		if ( $variation_color ) {
			$add_signal( $variation_color );
			$term = get_term_by( 'slug', $variation_color, 'pa_color' );
			if ( $term && ! is_wp_error( $term ) ) {
				$add_signal( $term->name );
			}
		}
	}

	return array_values( array_unique( $signals ) );
};

$family_rules = [
	'multicolore' => [
		'multi color', 'multicolor', 'multicolore', 'vari colori', 'varie colori', 'colori misti', 'arcobaleno',
	],
	'fantasia'    => [
		'fantasia', 'fantasy', 'stampa', 'stampato', 'floreale', 'flower', 'fiori', 'tropicale', 'animalier', 'zebra', 'leopard', 'pois', 'righe', 'quadri', 'camouflage', 'mimetico',
	],
	'bianco'      => [
		'bianco', 'bianca', 'white', 'avorio', 'ivory', 'crema', 'latte', 'panna',
	],
	'nero'        => [
		'nero', 'nera', 'black', 'antracite', 'carbone', 'grafite',
	],
	'verde'       => [
		'verde', 'green', 'smeraldo', 'oliva', 'salvia', 'menta', 'foresta',
	],
	'blu'         => [
		'blu', 'blue', 'azzurro', 'celeste', 'navy', 'marina', 'denim', 'indaco', 'turchese',
	],
	'rosa'        => [
		'rosa', 'pink', 'fucsia', 'corallo', 'lilla', 'viola', 'purple', 'rosso', 'red', 'peach', 'salmone',
	],
	'marrone'     => [
		'marrone', 'brown', 'beige', 'sabbia', 'cuoio', 'terra', 'tabacco', 'camel', 'cammello', 'ocra',
	],
];

$infer_families = function ( $signals ) use ( $family_rules ) {
	$matches = [];
	foreach ( $signals as $signal ) {
		foreach ( $family_rules as $family_slug => $keywords ) {
			foreach ( $keywords as $keyword ) {
				if ( strpos( $signal, $keyword ) !== false ) {
					$matches[ $family_slug ] = true;
					break;
				}
			}
		}
	}
	return array_keys( $matches );
};

// ---------- 1. Create attribute ----------
$attribute_id = wc_attribute_taxonomy_id_by_name( $family_attr_slug );
if ( $attribute_id ) {
	$log( 'SKIP', "attribute $family_taxonomy already exists (id $attribute_id)" );
} else {
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
	$log( 'OK', "created attribute $family_taxonomy (id $attribute_id)" );
	delete_transient( 'wc_attribute_taxonomies' );
}

// Ensure the attribute label stays distinct from the variation colour attribute.
global $wpdb;
$current_family_label = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
		$family_attr_slug
	)
);
if ( $current_family_label !== null && $current_family_label !== $family_label ) {
	$wpdb->update(
		"{$wpdb->prefix}woocommerce_attribute_taxonomies",
		[ 'attribute_label' => $family_label ],
		[ 'attribute_name' => $family_attr_slug ],
		[ '%s' ],
		[ '%s' ]
	);
	delete_transient( 'wc_attribute_taxonomies' );
	$log( 'OK', "updated $family_taxonomy label to \"$family_label\"" );
}

// ---------- 2. Register taxonomy for this run ----------
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
	$log( 'OK', "registered taxonomy $family_taxonomy in current process" );
}

// ---------- 3. Create terms ----------
foreach ( $terms as $slug => $name ) {
	$existing = get_term_by( 'slug', $slug, $family_taxonomy );
	if ( $existing ) {
		$log( 'SKIP', "term $slug already exists (id {$existing->term_id})" );
		continue;
	}
	$res = wp_insert_term( $name, $family_taxonomy, [ 'slug' => $slug ] );
	if ( is_wp_error( $res ) ) {
		$log( 'FAIL', "wp_insert_term($slug): " . $res->get_error_message() );
		continue;
	}
	$log( 'OK', "created term $slug (id {$res['term_id']})" );
}

// ---------- 4. Sideload swatch PNGs ----------
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

foreach ( array_keys( $terms ) as $slug ) {
	$term = get_term_by( 'slug', $slug, $family_taxonomy );
	if ( ! $term ) {
		$log( 'WARN', "term $slug missing during attachment step" );
		continue;
	}
	$existing_att = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
	if ( $existing_att && wp_attachment_is_image( $existing_att ) ) {
		$log( 'SKIP', "term $slug already has attachment id $existing_att" );
		continue;
	}
	$src = $swatches_dir . '/' . $slug . '.png';
	if ( ! file_exists( $src ) ) {
		$log( 'WARN', "swatch source missing on disk: $src" );
		continue;
	}
	$tmp = wp_tempnam( $slug . '.png' );
	if ( ! @copy( $src, $tmp ) ) {
		$log( 'FAIL', "copy failed: $src → $tmp" );
		continue;
	}
	$file_array = [
		'name'     => $slug . '.png',
		'tmp_name' => $tmp,
	];
	$att_id = media_handle_sideload( $file_array, 0, "pa_color-family swatch: $slug" );
	if ( is_wp_error( $att_id ) ) {
		@unlink( $tmp );
		$log( 'FAIL', "media_handle_sideload($slug): " . $att_id->get_error_message() );
		continue;
	}
	update_term_meta( $term->term_id, 'product_attribute_image', $att_id );
	$log( 'OK', "uploaded swatch for $slug (att id $att_id)" );
}

// ---------- 5. Insert filter block into sidebar-woocommerce widget ----------
$widget_blocks = get_option( 'widget_block', [] );
if ( ! isset( $widget_blocks[ $widget_block_id ]['content'] ) ) {
	$log( 'WARN', "widget_block[$widget_block_id] not found — skipping widget update" );
} else {
	$content = $widget_blocks[ $widget_block_id ]['content'];
	$id_int  = (int) $attribute_id;
	$marker  = '"attributeId":' . $id_int;
	if ( strpos( $content, $marker ) !== false ) {
		$block_pattern = '/(<!-- wp:woocommerce\/product-filter-attribute\s+\{[^}]*"attributeId":' . preg_quote( (string) $id_int, '/' ) . '[^}]*\}\s+-->.*?<!-- \/wp:woocommerce\/product-filter-attribute -->)/s';
		$updated_content = preg_replace_callback(
			$block_pattern,
			function ( $matches ) use ( $family_heading ) {
				$block = $matches[1];
				$block = preg_replace(
					'/<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">[^<]+<\/h3>/',
					'<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">' . esc_html( $family_heading ) . '</h3>',
					$block,
					1
				);
				return $block;
			},
			$content,
			1,
			$heading_updates
		);
		if ( $heading_updates ) {
			$widget_blocks[ $widget_block_id ]['content'] = $updated_content;
			update_option( 'widget_block', $widget_blocks );
			$log( 'OK', "updated pa_color-family widget heading to \"$family_heading\"" );
		}

		// Repair older bad state where the Price filter heading was accidentally renamed.
		$price_pattern = '/(<!-- wp:woocommerce\/product-filter-price -->.*?<h3 class="wp-block-heading" style="margin-top:0;margin-bottom:0.625rem">)Famiglia Colore(<\/h3>.*?<!-- \/wp:woocommerce\/product-filter-price -->)/s';
		$price_fixed_content = preg_replace( $price_pattern, '$1Price$2', $widget_blocks[ $widget_block_id ]['content'], 1, $price_fixes );
		if ( $price_fixes ) {
			$widget_blocks[ $widget_block_id ]['content'] = $price_fixed_content;
			update_option( 'widget_block', $widget_blocks );
			$log( 'OK', 'restored price filter heading to "Price"' );
		}

		$log( 'SKIP', 'widget already contains pa_color-family filter block' );
	} else {
		$attrs_json = wp_json_encode( [ 'attributeId' => $id_int, 'queryType' => 'or' ] );
		$new_block  = "\n\n<!-- wp:woocommerce/product-filter-attribute $attrs_json -->\n";
		$new_block .= '<div class="wp-block-woocommerce-product-filter-attribute">';
		$new_block .= "<!-- wp:heading {\"level\":3,\"style\":{\"spacing\":{\"margin\":{\"bottom\":\"0.625rem\",\"top\":\"0\"}}}} -->\n";
		$new_block .= "<h3 class=\"wp-block-heading\" style=\"margin-top:0;margin-bottom:0.625rem\">$family_heading</h3>\n";
		$new_block .= "<!-- /wp:heading -->\n\n";
		$new_block .= "<!-- wp:woocommerce/product-filter-checkbox-list -->\n";
		$new_block .= "<div class=\"wp-block-woocommerce-product-filter-checkbox-list wc-block-product-filter-checkbox-list\"></div>\n";
		$new_block .= "<!-- /wp:woocommerce/product-filter-checkbox-list --></div>\n";
		$new_block .= '<!-- /wp:woocommerce/product-filter-attribute -->';

		$needle = '<!-- wp:woocommerce/product-filter-taxonomy';
		$pos    = strpos( $content, $needle );
		if ( $pos === false ) {
			$log( 'WARN', 'product-filter-taxonomy anchor not found; appending at end' );
			$content = preg_replace(
				'#<!-- /wp:woocommerce/product-filters -->\s*$#',
				$new_block . "\n\n<!-- /wp:woocommerce/product-filters -->",
				$content
			);
		} else {
			$content = substr( $content, 0, $pos ) . $new_block . "\n\n" . substr( $content, $pos );
		}
		$widget_blocks[ $widget_block_id ]['content'] = $content;
		update_option( 'widget_block', $widget_blocks );
		$log( 'OK', "inserted pa_color-family filter (attributeId=$id_int) into widget block-$widget_block_id" );
	}
}

// ---------- 6. Auto-tag products with inferred families ----------
$product_ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => [ 'publish', 'private' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

$tagged_count   = 0;
$skipped_count  = 0;
$unmatched_count = 0;

foreach ( $product_ids as $product_id ) {
	$existing = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'ids' ] );
	if ( ! is_wp_error( $existing ) && ! empty( $existing ) ) {
		$skipped_count++;
		continue;
	}

	$signals = $collect_colour_signals( $product_id );
	$families = $infer_families( $signals );

	if ( empty( $families ) ) {
		$unmatched_count++;
		continue;
	}

	$set = wp_set_object_terms( $product_id, $families, $family_taxonomy, false );
	if ( is_wp_error( $set ) ) {
		$log( 'WARN', "auto-tag failed for product $product_id: " . $set->get_error_message() );
		continue;
	}

	$tagged_count++;
	$log( 'OK', 'auto-tag product ' . $product_id . ' -> ' . implode( ', ', $families ) );
}

$log( 'INFO', "auto-tag summary: tagged=$tagged_count skipped_existing=$skipped_count unmatched=$unmatched_count" );

// Safety pass: pa_color-family must remain a product-level attribute, never a variation selector.
// This is non-destructive and does not touch pa_color terms/variations.
$variation_flag_fixes = 0;
$attribute_row_backfills = 0;
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
		foreach ( $attributes as $attr_row ) {
			if ( is_array( $attr_row ) && isset( $attr_row['position'] ) ) {
				$max_position = max( $max_position, (int) $attr_row['position'] );
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
		$attribute_row_backfills++;
	}

	if ( ! empty( $attributes[ $family_taxonomy ]['is_variation'] ) ) {
		$attributes[ $family_taxonomy ]['is_variation'] = 0;
		$variation_flag_fixes++;
	}

	$attributes[ $family_taxonomy ]['is_taxonomy'] = 1;
	$attributes[ $family_taxonomy ]['name']        = $family_taxonomy;
	update_post_meta( $product_id, '_product_attributes', $attributes );
}
if ( $attribute_row_backfills > 0 ) {
	$log( 'INFO', "added _product_attributes row for $attribute_row_backfills products on $family_taxonomy" );
}
if ( $variation_flag_fixes > 0 ) {
	$log( 'INFO', "forced is_variation=0 for $variation_flag_fixes products on $family_taxonomy" );
}

// ---------- 7. Verification ----------
$log( 'VERIFY', '--- final state ---' );
$log( 'VERIFY', "attribute $family_taxonomy id = " . wc_attribute_taxonomy_id_by_name( $family_attr_slug ) );

$final_terms = get_terms(
	[
		'taxonomy'   => $family_taxonomy,
		'hide_empty' => false,
	]
);
if ( ! is_wp_error( $final_terms ) ) {
	foreach ( $final_terms as $term ) {
		$att_id = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
		$log(
			'VERIFY',
			sprintf( 'term %-12s id=%-4d att=%s', $term->slug, $term->term_id, $att_id ?: '(none)' )
		);
	}
}

global $wpdb;
$pa_color_type = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT attribute_type FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
		'color'
	)
);
if ( $pa_color_type !== null ) {
	$log( 'VERIFY', 'pa_color attribute_type = ' . $pa_color_type );
} else {
	$log( 'VERIFY', 'pa_color attribute_type = (missing)' );
}

$log( 'DONE', 'migration complete' );
