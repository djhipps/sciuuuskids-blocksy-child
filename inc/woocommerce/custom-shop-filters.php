<?php
/**
 * Theme-owned WooCommerce shop sidebar filters.
 *
 * Current scope:
 * - Color family (pa_color-family), multi-select OR behavior.
 * - Size (bridge for legacy variation keys + pa_size taxonomy), multi-select OR behavior.
 * - Active filters chips.
 * - Clear filters action.
 *
 * @package Blocksy_Child_SciuuusKids
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return selected color-family slugs from query string.
 *
 * Uses WooCommerce layered-nav query parameter shape:
 * - filter_color-family=blu,verde
 * - query_type_color-family=or
 */
function sciuuus_get_selected_color_family_slugs() {
	$raw = isset( $_GET['filter_color-family'] ) ? wp_unslash( (string) $_GET['filter_color-family'] ) : '';
	if ( $raw === '' ) {
		return [];
	}

	$slugs = array_filter(
		array_map(
			'sanitize_title',
			array_map( 'trim', explode( ',', $raw ) )
		)
	);

	return array_values( array_unique( $slugs ) );
}

/**
 * Return selected size slugs from query string.
 *
 * URL contract:
 * - filter_size=24,25-26
 * - query_type_size=or
 */
function sciuuus_get_selected_size_slugs() {
	$raw = isset( $_GET['filter_size'] ) ? wp_unslash( (string) $_GET['filter_size'] ) : '';
	if ( $raw === '' ) {
		return [];
	}

	$slugs = array_filter(
		array_map(
			'sanitize_title',
			array_map( 'trim', explode( ',', $raw ) )
		)
	);

	$blocked = [ 'nessuno', 'none', 'n-a', 'na', 'null' ];
	$slugs   = array_values( array_unique( $slugs ) );
	$slugs   = array_values( array_diff( $slugs, $blocked ) );

	return $slugs;
}

/**
 * Normalize raw legacy size values to a stable slug.
 */
function sciuuus_normalize_legacy_size_slug( $raw_value ) {
	$value = strtolower( trim( (string) $raw_value ) );
	if ( $value === '' ) {
		return '';
	}

	$value = str_replace( [ '_', ',', ';', '/' ], [ '-', '-', '-', '-' ], $value );
	$value = preg_replace( '/\s+/', '-', $value );
	$value = preg_replace( '/[^a-z0-9\-]/', '', $value );
	$value = preg_replace( '/-+/', '-', $value );
	$value = trim( $value, '-' );

	return sanitize_title( $value );
}

/**
 * Human label for a normalized size slug.
 */
function sciuuus_size_label_from_slug( $slug ) {
	$slug = (string) $slug;
	if ( $slug === '' ) {
		return '';
	}

	$lookup = [
		'xxs'   => 'XXS',
		'xs'    => 'XS',
		's'     => 'S',
		'm'     => 'M',
		'l'     => 'L',
		'xl'    => 'XL',
		'xxl'   => 'XXL',
		'3xl'   => '3XL',
		'4xl'   => '4XL',
		'one-size' => __( 'One size', 'blocksy-child' ),
		'unica' => __( 'Taglia unica', 'blocksy-child' ),
	];

	if ( isset( $lookup[ $slug ] ) ) {
		return $lookup[ $slug ];
	}

	$label = str_replace( '-', ' / ', $slug );
	return strtoupper( $label );
}

/**
 * Build size options from pa_size terms and legacy variation meta values.
 */
function sciuuus_get_size_filter_options() {
	global $wpdb;

	$options = [];
	$taxonomy = 'pa_size';
	$blocked  = [ 'nessuno', 'none', 'n-a', 'na', 'null', 'xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl', '3xl', '4xl', 'one-size', 'unica' ];

	$size_rows = $wpdb->get_results(
		"SELECT DISTINCT pm.meta_key, pm.meta_value
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} v ON v.ID = pm.post_id
		 INNER JOIN {$wpdb->posts} p ON p.ID = v.post_parent
		 INNER JOIN {$wpdb->postmeta} stock ON stock.post_id = v.ID
		 WHERE v.post_type = 'product_variation'
		   AND v.post_status IN ('publish','private')
		   AND p.post_type = 'product'
		   AND p.post_status = 'publish'
		   AND stock.meta_key = '_stock_status'
		   AND stock.meta_value = 'instock'
		   AND pm.meta_key IN ('attribute_pa_size','attribute_size','attribute_taglia')
		   AND pm.meta_value <> ''",
		ARRAY_A
	);

	if ( is_array( $size_rows ) ) {
		foreach ( $size_rows as $row ) {
			$meta_key = (string) ( $row['meta_key'] ?? '' );
			$raw      = (string) ( $row['meta_value'] ?? '' );
			if ( $raw === '' ) {
				continue;
			}

			$slug = $meta_key === 'attribute_pa_size'
				? sanitize_title( $raw )
				: sciuuus_normalize_legacy_size_slug( $raw );

			if ( $slug === '' || in_array( $slug, $blocked, true ) || isset( $options[ $slug ] ) || ! preg_match( '/^\d+$/', (string) $slug ) ) {
				continue;
			}
			$size = (int) $slug;
			if ( $size < 20 || $size > 44 ) {
				continue;
			}

			if ( taxonomy_exists( $taxonomy ) ) {
				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( $term instanceof WP_Term ) {
					$options[ $slug ] = $term->name;
					continue;
				}
			}

			$options[ $slug ] = sciuuus_size_label_from_slug( $slug );
		}
	}

	uksort(
		$options,
		static function ( $a, $b ) {
			return (int) $a <=> (int) $b;
		}
	);

	return $options;
}

/**
 * Build grouped numeric size options for UI.
 */
function sciuuus_get_grouped_size_filter_options() {
	$options = sciuuus_get_size_filter_options();
	$groups  = [
		'Bimbo (20-35)' => [],
		'Adulto (36-44)' => [],
	];

	foreach ( $options as $slug => $label ) {
		$size = (int) $slug;
		if ( $size >= 20 && $size <= 35 ) {
			$groups['Bimbo (20-35)'][ $slug ] = $label;
		} elseif ( $size >= 36 && $size <= 44 ) {
			$groups['Adulto (36-44)'][ $slug ] = $label;
		}
	}

	return array_filter( $groups );
}

/**
 * Return the current archive URL normalized to page 1.
 *
 * This prevents filter links from carrying stale `/page/N/` fragments
 * that can produce 404s once query args are applied.
 */
function sciuuus_get_filters_base_url() {
	return get_pagenum_link( 1 );
}

/**
 * Render custom color-family filter sidebar content.
 */
function sciuuus_render_custom_shop_filters() {
	$taxonomy = 'pa_color-family';

	if ( ! taxonomy_exists( $taxonomy ) ) {
		return;
	}

	$terms = get_terms(
		[
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		]
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$selected = sciuuus_get_selected_color_family_slugs();
	$size_selected = sciuuus_get_selected_size_slugs();
	$base_url = sciuuus_get_filters_base_url();
	$term_map = [];
	foreach ( $terms as $term ) {
		$term_map[ $term->slug ] = $term;
	}
	$size_groups  = sciuuus_get_grouped_size_filter_options();
	$size_map     = [];
	foreach ( $size_groups as $group_options ) {
		foreach ( $group_options as $slug => $label ) {
			$size_map[ $slug ] = $label;
		}
	}

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	?>
	<div class="sciuuus-custom-filters" aria-label="<?php esc_attr_e( 'Product filters', 'blocksy-child' ); ?>">
		<h3 class="sciuuus-custom-filters__title"><?php esc_html_e( 'Filters', 'blocksy-child' ); ?></h3>

		<?php if ( ! empty( $selected ) || ! empty( $size_selected ) ) : ?>
			<div class="sciuuus-custom-filters__active" aria-label="<?php esc_attr_e( 'Active filters', 'blocksy-child' ); ?>">
				<?php foreach ( $selected as $slug ) : ?>
					<?php if ( isset( $term_map[ $slug ] ) ) : ?>
						<?php
						$next = array_values( array_diff( $selected, [ $slug ] ) );
						$args = [
							'paged'                      => false,
							'product-page'               => false,
							'filter_size'                => ! empty( $size_selected ) ? implode( ',', $size_selected ) : false,
							'query_type_size'            => ! empty( $size_selected ) ? 'or' : false,
							'filter_color-family'        => ! empty( $next ) ? implode( ',', $next ) : false,
							'query_type_color-family'    => ! empty( $next ) ? 'or' : false,
						];
						$remove_link = add_query_arg( $args, $base_url );
						?>
						<a class="sciuuus-filter-chip" href="<?php echo esc_url( $remove_link ); ?>">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: selected color-family term name */
									__( 'Famiglia Colore: %s ×', 'blocksy-child' ),
									$term_map[ $slug ]->name
								)
							);
							?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php foreach ( $size_selected as $size_slug ) : ?>
					<?php if ( isset( $size_map[ $size_slug ] ) ) : ?>
						<?php
						$next_size = array_values( array_diff( $size_selected, [ $size_slug ] ) );
						$args      = [
							'paged'                      => false,
							'product-page'               => false,
							'filter_color-family'        => ! empty( $selected ) ? implode( ',', $selected ) : false,
							'query_type_color-family'    => ! empty( $selected ) ? 'or' : false,
							'filter_size'                => ! empty( $next_size ) ? implode( ',', $next_size ) : false,
							'query_type_size'            => ! empty( $next_size ) ? 'or' : false,
						];
						$remove_link = add_query_arg( $args, $base_url );
						?>
						<a class="sciuuus-filter-chip" href="<?php echo esc_url( $remove_link ); ?>">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: selected size value */
									__( 'Taglia: %s ×', 'blocksy-child' ),
									$size_map[ $size_slug ]
								)
							);
							?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
			$clear_url = add_query_arg(
				[
					'filter_color-family'     => false,
					'query_type_color-family' => false,
					'filter_size'             => false,
					'query_type_size'         => false,
					'paged'                      => false,
					'product-page'               => false,
				],
				$base_url
			);
			?>
			<a class="sciuuus-custom-filters__clear" href="<?php echo esc_url( $clear_url ); ?>">
				<?php esc_html_e( 'Clear filters', 'blocksy-child' ); ?>
			</a>
		<?php endif; ?>

		<div class="sciuuus-custom-filters__section">
			<h4 class="sciuuus-custom-filters__section-title"><?php esc_html_e( 'Colore', 'blocksy-child' ); ?></h4>
			<ul class="sciuuus-swatch-filter-list">
				<?php foreach ( $terms as $term ) : ?>
					<?php
					$is_active = in_array( $term->slug, $selected, true );
					$next      = $selected;

					if ( $is_active ) {
						$next = array_values( array_diff( $next, [ $term->slug ] ) );
					} else {
						$next[] = $term->slug;
						$next   = array_values( array_unique( $next ) );
					}

					$args = [
						'paged'        => false,
						'product-page' => false,
					];

					if ( empty( $next ) ) {
						$args['filter_color-family']     = false;
						$args['query_type_color-family'] = false;
					} else {
						$args['filter_color-family']     = implode( ',', $next );
						$args['query_type_color-family'] = 'or';
					}

					$link = add_query_arg( $args, $base_url );

					$attachment_id = (int) get_term_meta( $term->term_id, 'product_attribute_image', true );
					$swatch_url    = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'thumbnail' ) : '';
					if ( ! $swatch_url ) {
						$bundled = $theme_dir . '/assets/swatches/' . $term->slug . '.png';
						if ( file_exists( $bundled ) ) {
							$swatch_url = $theme_uri . '/assets/swatches/' . $term->slug . '.png';
						}
					}
					?>
					<li>
						<a class="sciuuus-swatch-filter-item<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $link ); ?>">
							<?php if ( $swatch_url ) : ?>
								<img
									class="sciuuus-swatch-filter-item__swatch"
									src="<?php echo esc_url( $swatch_url ); ?>"
									alt="<?php echo esc_attr( $term->name ); ?>"
									width="18"
									height="18"
									loading="lazy"
									decoding="async"
								/>
							<?php else : ?>
								<span class="sciuuus-swatch-filter-item__swatch sciuuus-swatch-filter-item__swatch--fallback" aria-hidden="true"></span>
							<?php endif; ?>
							<span class="sciuuus-swatch-filter-item__label"><?php echo esc_html( $term->name ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php
		$size_selected = sciuuus_get_selected_size_slugs();
		?>
		<?php if ( ! empty( $size_groups ) ) : ?>
			<div class="sciuuus-custom-filters__section">
				<h4 class="sciuuus-custom-filters__section-title"><?php esc_html_e( 'Taglia', 'blocksy-child' ); ?></h4>
				<?php foreach ( $size_groups as $group_label => $size_options ) : ?>
					<p class="sciuuus-size-group-title"><?php echo esc_html( $group_label ); ?></p>
					<ul class="sciuuus-size-chip-list">
						<?php foreach ( $size_options as $size_slug => $size_label ) : ?>
							<?php
							$is_active = in_array( $size_slug, $size_selected, true );
							$next      = $size_selected;

							if ( $is_active ) {
								$next = array_values( array_diff( $next, [ $size_slug ] ) );
							} else {
								$next[] = $size_slug;
								$next   = array_values( array_unique( $next ) );
							}

							$args = [
								'paged'        => false,
								'product-page' => false,
							];

							if ( empty( $next ) ) {
								$args['filter_size']     = false;
								$args['query_type_size'] = false;
							} else {
								$args['filter_size']     = implode( ',', $next );
								$args['query_type_size'] = 'or';
							}

							$link = add_query_arg( $args, $base_url );
							?>
							<li>
								<a class="sciuuus-size-chip<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $link ); ?>">
									<?php echo esc_html( $size_label ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Temporary bridge: filter products by legacy variation size keys and pa_size.
 *
 * Keeps `filter_size` working while live data migrates to pa_size.
 */
function sciuuus_apply_size_filter_bridge_to_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$selected = sciuuus_get_selected_size_slugs();
	if ( empty( $selected ) ) {
		return;
	}

	global $wpdb;

	$product_ids = [];
	$selected_map = array_fill_keys( $selected, true );

	$size_rows = $wpdb->get_results(
		"SELECT DISTINCT p.ID AS product_id, pm.meta_key, pm.meta_value
		 FROM {$wpdb->posts} v
		 INNER JOIN {$wpdb->posts} p ON p.ID = v.post_parent
		 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = v.ID
		 INNER JOIN {$wpdb->postmeta} stock ON stock.post_id = v.ID
		 WHERE v.post_type = 'product_variation'
		   AND v.post_status IN ('publish','private')
		   AND p.post_type = 'product'
		   AND p.post_status = 'publish'
		   AND stock.meta_key = '_stock_status'
		   AND stock.meta_value = 'instock'
		   AND pm.meta_key IN ('attribute_pa_size','attribute_size','attribute_taglia')
		   AND pm.meta_value <> ''",
		ARRAY_A
	);

	if ( is_array( $size_rows ) ) {
		foreach ( $size_rows as $row ) {
			$meta_key = (string) ( $row['meta_key'] ?? '' );
			$raw      = (string) ( $row['meta_value'] ?? '' );
			if ( $raw === '' ) {
				continue;
			}

			$size_slug = $meta_key === 'attribute_pa_size'
				? sanitize_title( $raw )
				: sciuuus_normalize_legacy_size_slug( $raw );

			if ( $size_slug === '' || ! isset( $selected_map[ $size_slug ] ) ) {
				continue;
			}

			$product_ids[] = (int) ( $row['product_id'] ?? 0 );
		}
	}

	$product_ids = array_values( array_unique( array_map( 'intval', $product_ids ) ) );
	if ( empty( $product_ids ) ) {
		$product_ids = [ 0 ];
	}

	$existing = $query->get( 'post__in' );
	if ( is_array( $existing ) && ! empty( $existing ) ) {
		$product_ids = array_values( array_intersect( $existing, $product_ids ) );
		if ( empty( $product_ids ) ) {
			$product_ids = [ 0 ];
		}
	}

	$query->set( 'post__in', $product_ids );
	$query->set( 'orderby', 'post__in' );
}
add_action( 'pre_get_posts', 'sciuuus_apply_size_filter_bridge_to_main_query', 20 );

/**
 * Prevent WooCommerce layered-nav from adding its own pa_size tax query when
 * filter_size is present; the bridge computes the full product set itself.
 */
function sciuuus_disable_native_pa_size_tax_query_for_bridge( $tax_query, $main_query ) {
	if ( ! $main_query ) {
		return $tax_query;
	}

	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return $tax_query;
	}

	$selected = sciuuus_get_selected_size_slugs();
	if ( empty( $selected ) || ! is_array( $tax_query ) ) {
		return $tax_query;
	}

	foreach ( $tax_query as $idx => $clause ) {
		if ( ! is_array( $clause ) ) {
			continue;
		}
		if ( ( $clause['taxonomy'] ?? '' ) === 'pa_size' ) {
			unset( $tax_query[ $idx ] );
		}
	}

	return array_values( $tax_query );
}
add_filter( 'woocommerce_product_query_tax_query', 'sciuuus_disable_native_pa_size_tax_query_for_bridge', 20, 2 );
