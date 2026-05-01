<?php
/**
 * Read-only audit for pa_color-family filter behavior on shop archives.
 *
 * Run:
 *   wp eval-file wp-content/themes/blocksy-child/inc/migrations/colour-family-audit.php
 *
 * Purpose:
 * - Explain why filtered frontend results can differ from raw taxonomy counts.
 * - Detect suspicious pa_color-family assignments using color signals from product data.
 * - Produce a manual-fix report without writing any DB changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "[FATAL] must run via wp eval-file\n" );
	exit( 1 );
}

$family_taxonomy  = 'pa_color-family';
$family_attr_slug = 'color-family';

$expected_terms = [
	'bianco'      => 'Bianco',
	'blu'         => 'Blu',
	'fantasia'    => 'Fantasia',
	'giallo'      => 'Giallo',
	'marrone'     => 'Marrone',
	'multicolore' => 'Multicolore',
	'nero'        => 'Nero',
	'rosa'        => 'Rosa',
	'verde'       => 'Verde',
];

$family_rules = [
	'multicolore' => [ 'multi color', 'multicolor', 'multicolore', 'vari colori', 'varie colori', 'colori misti', 'arcobaleno' ],
	'fantasia'    => [ 'fantasia', 'fantasy', 'stampa', 'stampato', 'floreale', 'flower', 'fiori', 'tropicale', 'animalier', 'zebra', 'leopard', 'pois', 'righe', 'quadri', 'camouflage', 'mimetico' ],
	'bianco'      => [ 'bianco', 'bianca', 'white', 'avorio', 'ivory', 'crema', 'latte', 'panna' ],
	'nero'        => [ 'nero', 'nera', 'black', 'antracite', 'carbone', 'grafite' ],
	'verde'       => [ 'verde', 'green', 'smeraldo', 'oliva', 'salvia', 'menta', 'foresta' ],
	'giallo'      => [ 'giallo', 'yellow', 'senape', 'ocra gialla', 'lime' ],
	'blu'         => [ 'blu', 'blue', 'azzurro', 'azzuro', 'celeste', 'navy', 'marina', 'denim', 'indaco', 'turchese' ],
	'rosa'        => [ 'rosa', 'pink', 'fucsia', 'corallo', 'lilla', 'viola', 'purple', 'rosso', 'red', 'peach', 'salmone' ],
	'marrone'     => [ 'marrone', 'brown', 'beige', 'sabbia', 'cuoio', 'terra', 'tabacco', 'camel', 'cammello', 'ocra' ],
];

$log = function ( $section, $msg ) {
	fwrite( STDOUT, sprintf( "[%s] %s\n", str_pad( $section, 12 ), $msg ) );
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
			if ( $name !== 'pa_color' && $name !== 'pa_colore' && $name !== 'color' && $name !== 'colore' && $key !== 'pa_color' && $key !== 'pa_colore' ) {
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
		if ( $variation_color ) {
			$add( $variation_color );
		}
	}

	return array_values( array_unique( $signals ) );
};

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

$is_out_of_stock = function ( $product_id ) {
	$status = (string) get_post_meta( $product_id, '_stock_status', true );
	return $status === 'outofstock';
};

$is_catalog_excluded = function ( $product_id ) {
	$visibility_terms = wp_get_post_terms( $product_id, 'product_visibility', [ 'fields' => 'slugs' ] );
	if ( is_wp_error( $visibility_terms ) ) {
		return false;
	}
	return in_array( 'exclude-from-catalog', $visibility_terms, true );
};

// ---------- Section 1: setup integrity ----------
$log( 'HEADER', 'pa_color-family audit (read-only)' );
$log( 'HEADER', 'theme=' . wp_get_theme()->get_stylesheet() );

$attribute_id = function_exists( 'wc_attribute_taxonomy_id_by_name' ) ? (int) wc_attribute_taxonomy_id_by_name( $family_attr_slug ) : 0;
$log( 'SETUP', "attribute_name=$family_attr_slug attribute_id=" . ( $attribute_id ?: 'missing' ) );
$log( 'SETUP', "taxonomy_exists=$family_taxonomy:" . ( taxonomy_exists( $family_taxonomy ) ? 'yes' : 'no' ) );

$hide_out_of_stock = get_option( 'woocommerce_hide_out_of_stock_items', 'no' );
$log( 'SETUP', "woocommerce_hide_out_of_stock_items=$hide_out_of_stock" );

$terms = get_terms(
	[
		'taxonomy'   => $family_taxonomy,
		'hide_empty' => false,
	]
);

if ( is_wp_error( $terms ) ) {
	$log( 'FATAL', 'get_terms failed: ' . $terms->get_error_message() );
	exit( 1 );
}

$actual_slugs = [];
foreach ( $terms as $term ) {
	$actual_slugs[] = $term->slug;
}
sort( $actual_slugs );
$expected_slugs = array_keys( $expected_terms );
sort( $expected_slugs );

$missing_terms = array_values( array_diff( $expected_slugs, $actual_slugs ) );
$extra_terms   = array_values( array_diff( $actual_slugs, $expected_slugs ) );

$log( 'SETUP', 'term_count=' . count( $terms ) );
$log( 'SETUP', 'missing_expected_terms=' . ( empty( $missing_terms ) ? 'none' : implode( ',', $missing_terms ) ) );
$log( 'SETUP', 'unexpected_extra_terms=' . ( empty( $extra_terms ) ? 'none' : implode( ',', $extra_terms ) ) );

// ---------- Section 2: baseline product pools ----------
$product_ids = get_posts(
	[
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]
);

$visible_ids = [];
foreach ( $product_ids as $product_id ) {
	if ( $is_catalog_excluded( $product_id ) ) {
		continue;
	}
	if ( $hide_out_of_stock === 'yes' && $is_out_of_stock( $product_id ) ) {
		continue;
	}
	$visible_ids[] = (int) $product_id;
}

$log( 'BASELINE', 'published_products=' . count( $product_ids ) );
$log( 'BASELINE', 'shop_visible_products=' . count( $visible_ids ) );

// ---------- Section 3: per-term raw vs effective ----------
$tt_counts = [];
foreach ( $terms as $term ) {
	$tt_counts[ $term->slug ] = (int) $term->count;
}

$effective_counts = [];
$effective_lists  = [];
foreach ( $expected_slugs as $slug ) {
	$effective_counts[ $slug ] = 0;
	$effective_lists[ $slug ]  = [];
}

foreach ( $visible_ids as $product_id ) {
	$assigned = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'slugs' ] );
	if ( is_wp_error( $assigned ) || empty( $assigned ) ) {
		continue;
	}
	foreach ( $assigned as $slug ) {
		if ( ! isset( $effective_counts[ $slug ] ) ) {
			$effective_counts[ $slug ] = 0;
			$effective_lists[ $slug ]  = [];
		}
		$effective_counts[ $slug ]++;
		$effective_lists[ $slug ][] = $product_id;
	}
}

$log( 'TERM_COUNTS', 'slug | raw_taxonomy_count | effective_shop_visible_count' );
foreach ( $expected_slugs as $slug ) {
	$raw = isset( $tt_counts[ $slug ] ) ? $tt_counts[ $slug ] : 0;
	$eff = isset( $effective_counts[ $slug ] ) ? $effective_counts[ $slug ] : 0;
	$log( 'TERM_COUNTS', sprintf( '%s | %d | %d', $slug, $raw, $eff ) );
}

// ---------- Section 4: zero-result trap report ----------
$log( 'ZERO_TRAP', 'terms with raw>0 and effective=0' );
$zero_traps = 0;
foreach ( $expected_slugs as $slug ) {
	$raw = isset( $tt_counts[ $slug ] ) ? $tt_counts[ $slug ] : 0;
	$eff = isset( $effective_counts[ $slug ] ) ? $effective_counts[ $slug ] : 0;
	if ( $raw > 0 && $eff === 0 ) {
		$zero_traps++;
		$log( 'ZERO_TRAP', "$slug raw=$raw effective=$eff" );
	}
}
if ( $zero_traps === 0 ) {
	$log( 'ZERO_TRAP', 'none' );
}

// ---------- Section 5: suspicious assignment report ----------
$log( 'MISMATCH', 'product_id | title | assigned | inferred | stock | issues' );

$issues_total = 0;
foreach ( $product_ids as $product_id ) {
	$assigned = wp_get_post_terms( $product_id, $family_taxonomy, [ 'fields' => 'slugs' ] );
	if ( is_wp_error( $assigned ) ) {
		continue;
	}
	$assigned = array_values( array_unique( array_filter( $assigned ) ) );

	$signals  = $collect_colour_signals( $product_id );
	$inferred = $infer_families( $signals );

	$issues = [];
	if ( empty( $assigned ) ) {
		$issues[] = 'missing_assignment';
	}
	if ( ! empty( $assigned ) && empty( $inferred ) ) {
		$issues[] = 'assigned_but_no_signal';
	}
	if ( ! empty( $assigned ) && ! empty( $inferred ) ) {
		$diff = array_values( array_diff( $assigned, $inferred ) );
		if ( ! empty( $diff ) ) {
			$issues[] = 'assigned_not_inferred:' . implode( ',', $diff );
		}
	}
	if ( empty( $issues ) ) {
		continue;
	}

	$issues_total++;
	$stock_status = (string) get_post_meta( $product_id, '_stock_status', true );
	$log(
		'MISMATCH',
		sprintf(
			'%d | %s | %s | %s | %s | %s',
			$product_id,
			str_replace( '|', '/', get_the_title( $product_id ) ),
			empty( $assigned ) ? '-' : implode( ',', $assigned ),
			empty( $inferred ) ? '-' : implode( ',', $inferred ),
			$stock_status !== '' ? $stock_status : '(none)',
			implode( ';', $issues )
		)
	);
}

if ( $issues_total === 0 ) {
	$log( 'MISMATCH', 'none' );
}

$log( 'DONE', 'audit complete (no changes written)' );
