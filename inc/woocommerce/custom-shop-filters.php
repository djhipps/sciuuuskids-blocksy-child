<?php
/**
 * Theme-owned WooCommerce shop sidebar filters.
 *
 * Current scope:
 * - Color family (pa_color-family), multi-select OR behavior.
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
	$base_url = sciuuus_get_filters_base_url();
	$term_map = [];
	foreach ( $terms as $term ) {
		$term_map[ $term->slug ] = $term;
	}

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();
	?>
	<div class="sciuuus-custom-filters" aria-label="<?php esc_attr_e( 'Product filters', 'blocksy-child' ); ?>">
		<h3 class="sciuuus-custom-filters__title"><?php esc_html_e( 'Filters', 'blocksy-child' ); ?></h3>

		<?php if ( ! empty( $selected ) ) : ?>
			<div class="sciuuus-custom-filters__active" aria-label="<?php esc_attr_e( 'Active filters', 'blocksy-child' ); ?>">
				<?php foreach ( $selected as $slug ) : ?>
					<?php if ( isset( $term_map[ $slug ] ) ) : ?>
						<span class="sciuuus-filter-chip">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: selected color-family term name */
									__( 'Famiglia Colore: %s', 'blocksy-child' ),
									$term_map[ $slug ]->name
								)
							);
							?>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
			$clear_url = add_query_arg(
				[
					'filter_color-family'     => false,
					'query_type_color-family' => false,
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
	</div>
	<?php
}
