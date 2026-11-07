<?php
/**
 * Template tags and helpers.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print site logo or title.
 *
 * When a transparent-header logo is set, both logos are output and CSS swaps them.
 */
function jofme_site_branding() {
	if ( has_custom_logo() ) {
		$html = get_custom_logo();
		$transparent_id = absint( get_theme_mod( 'jofme_transparent_logo', 0 ) );
		if ( $transparent_id && wp_attachment_is_image( $transparent_id ) ) {
			$html = preg_replace( '/\bclass="custom-logo"/', 'class="custom-logo custom-logo--default"', $html, 1 );
			$alt  = get_post_meta( $transparent_id, '_wp_attachment_image_alt', true );
			$img  = wp_get_attachment_image(
				$transparent_id,
				'full',
				false,
				array(
					'class'   => 'custom-logo custom-logo--transparent',
					'alt'     => $alt ? $alt : get_bloginfo( 'name' ),
					'loading' => 'eager',
				)
			);
			if ( $img && false !== strpos( $html, '</a>' ) ) {
				$html = str_replace( '</a>', $img . '</a>', $html );
			}
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core + escaped attachment markup.
		return;
	}

	$tag = is_front_page() && is_home() ? 'h1' : 'p';
	printf(
		'<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
		esc_attr( $tag ),
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);

	$description = get_bloginfo( 'description', 'display' );
	if ( $description || is_customize_preview() ) {
		printf( '<p class="site-description">%s</p>', esc_html( $description ) );
	}
}

/**
 * Posted on / by meta.
 */
function jofme_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf(
		$time_string,
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( DATE_W3C ) ),
		esc_html( get_the_modified_date() )
	);

	printf(
		'<span class="posted-on">%1$s <a href="%2$s" rel="bookmark">%3$s</a></span>',
		esc_html__( 'Posted on', 'jofme' ),
		esc_url( get_permalink() ),
		$time_string // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Entry footer categories / tags.
 */
function jofme_entry_footer() {
	if ( 'post' !== get_post_type() ) {
		return;
	}

	$categories = get_the_category_list( esc_html__( ', ', 'jofme' ) );
	if ( $categories ) {
		printf(
			'<span class="cat-links">%1$s %2$s</span>',
			esc_html__( 'Categories:', 'jofme' ),
			$categories // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	$tags = get_the_tag_list( '', esc_html__( ', ', 'jofme' ) );
	if ( $tags ) {
		printf(
			'<span class="tags-links">%1$s %2$s</span>',
			esc_html__( 'Tags:', 'jofme' ),
			$tags // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}

/**
 * Cart count badge HTML for header.
 *
 * @return string
 */
function jofme_cart_link() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

	ob_start();
	?>
	<a class="jofme-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'View cart', 'jofme' ); ?>">
		<span class="jofme-cart-link__icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6h15l-1.5 9h-12L6 6zm0 0L5 3H2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="20" r="1.2" fill="currentColor"/><circle cx="18" cy="20" r="1.2" fill="currentColor"/></svg>
		</span>
		<span class="jofme-cart-count<?php echo $count ? '' : ' is-empty'; ?>"><?php echo esc_html( (string) $count ); ?></span>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * Whether Elementor should render the header location.
 *
 * @return bool
 */
function jofme_has_elementor_header() {
	return function_exists( 'jofme_elementor_location_exists' ) && jofme_elementor_location_exists( 'header' );
}

/**
 * Whether Elementor should render the footer location.
 *
 * @return bool
 */
function jofme_has_elementor_footer() {
	return function_exists( 'jofme_elementor_location_exists' ) && jofme_elementor_location_exists( 'footer' );
}
