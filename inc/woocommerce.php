<?php
/**
 * WooCommerce integration.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Declare WooCommerce support and gallery features.
 */
function jofme_woocommerce_setup() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 800,
		'product_grid'          => array(
			'default_rows'    => 4,
			'min_rows'        => 1,
			'max_rows'        => 8,
			'default_columns' => 3,
			'min_columns'     => 1,
			'max_columns'     => 4,
		),
	) );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'jofme_woocommerce_setup' );

/**
 * Keep WooCommerce single product images uncropped (original aspect).
 *
 * Existing thumbnails stay cropped until regenerated; Gallery design
 * serves the original file directly to avoid intermediate crops.
 *
 * @param array $size Image size args.
 * @return array
 */
function jofme_woocommerce_uncropped_single( $size ) {
	if ( is_array( $size ) ) {
		$size['crop'] = 0;
	}
	return $size;
}
add_filter( 'woocommerce_get_image_size_single', 'jofme_woocommerce_uncropped_single' );

/**
 * Enqueue WooCommerce-specific styles.
 */
function jofme_woocommerce_scripts() {
	wp_enqueue_style(
		'jofme-woocommerce',
		JOFME_URI . '/assets/css/woocommerce.css',
		array( 'jofme-main' ),
		JOFME_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'jofme_woocommerce_scripts' );

/**
 * Products per page.
 *
 * @return int
 */
function jofme_products_per_page() {
	return max( 4, min( 48, (int) get_theme_mod( 'jofme_products_per_page', 12 ) ) );
}
add_filter( 'loop_shop_per_page', 'jofme_products_per_page' );

/**
 * Loop columns.
 *
 * @return int
 */
function jofme_loop_columns() {
	$view = jofme_catalog_default_view();
	return 'list' === $view ? 1 : (int) $view;
}
add_filter( 'loop_shop_columns', 'jofme_loop_columns' );

/**
 * Related products args.
 *
 * @param array $args Related args.
 * @return array
 */
function jofme_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jofme_related_products_args' );

/**
 * Wrap shop content.
 */
function jofme_woocommerce_wrapper_before() {
	// archive-product.php already opens the layout container.
	if ( ! did_action( 'jofme_shop_layout_open' ) ) {
		echo '<div class="jofme-container jofme-layout jofme-layout--shop">';
	}
	echo '<div id="primary" class="content-area"><main id="main" class="site-main jofme-shop">';
}
add_action( 'woocommerce_before_main_content', 'jofme_woocommerce_wrapper_before', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

/**
 * Close shop content.
 */
function jofme_woocommerce_wrapper_after() {
	echo '</main></div>';
	if ( ! did_action( 'jofme_shop_layout_open' ) ) {
		jofme_layout_sidebar();
	}
	if ( ! did_action( 'jofme_shop_layout_open' ) ) {
		echo '</div>';
	}
}
add_action( 'woocommerce_after_main_content', 'jofme_woocommerce_wrapper_after', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/**
 * Update cart count fragments for AJAX add-to-cart.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function jofme_cart_link_fragment( $fragments ) {
	$fragments['span.jofme-cart-count'] = '<span class="jofme-cart-count">' . esc_html( (string) WC()->cart->get_cart_contents_count() ) . '</span>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'jofme_cart_link_fragment' );

/**
 * Prevent double add-to-cart on ?wc-ajax=add_to_cart requests.
 *
 * Full form serializations include the hidden `add-to-cart` field. WooCommerce
 * then adds once in WC_Form_Handler::add_to_cart_action (wp_loaded:20) and
 * again in WC_AJAX::add_to_cart (template_redirect) — qty becomes 2.
 *
 * Strip `add-to-cart` before the form handler runs so only the AJAX endpoint adds.
 */
function jofme_prevent_duplicate_ajax_add_to_cart() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public wc-ajax endpoint flag.
	$ajax = isset( $_GET['wc-ajax'] ) ? sanitize_text_field( wp_unslash( $_GET['wc-ajax'] ) ) : '';
	if ( 'add_to_cart' !== $ajax ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
	unset( $_REQUEST['add-to-cart'], $_POST['add-to-cart'] );
}
add_action( 'wp_loaded', 'jofme_prevent_duplicate_ajax_add_to_cart', 5 );

/**
 * Body class for WooCommerce pages.
 *
 * @param array $classes Classes.
 * @return array
 */
function jofme_woocommerce_body_class( $classes ) {
	$classes[] = 'jofme-woocommerce';

	if ( is_shop() || is_product_taxonomy() ) {
		$classes[] = 'shop-columns-' . absint( jofme_loop_columns() );
	}

	return $classes;
}
add_filter( 'body_class', 'jofme_woocommerce_body_class' );

// Sidebars are rendered inside the theme layout wrappers.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Variable products: show "From {min}" only (hide range max / second price).
 *
 * @param string     $price Formatted range HTML.
 * @param string|int $from  Min price (numeric or already formatted).
 * @param string|int $to    Max price (unused).
 * @return string
 */
function jofme_format_price_range_from_only( $price, $from, $to ) {
	unset( $price, $to );

	$formatted = is_numeric( $from ) ? wc_price( (float) $from ) : $from;

	/* translators: %s: lowest variation price. Uses WooCommerce string for i18n. */
	return sprintf( __( 'From %s', 'woocommerce' ), $formatted );
}
add_filter( 'woocommerce_format_price_range', 'jofme_format_price_range_from_only', 10, 3 );

/**
 * Refine product review form labels and submit control.
 *
 * @param array $args Comment form args.
 * @return array
 */
function jofme_product_review_comment_form_args( $args ) {
	$args['title_reply']          = __( 'Γράψε την αξιολόγησή σου', 'jofme' );
	$args['title_reply_to']       = __( 'Απάντηση σε %s', 'jofme' );
	$args['label_submit']         = __( 'Υποβολή αξιολόγησης', 'jofme' );
	$args['class_submit']         = 'submit jofme-review-submit';
	$args['submit_button']        = '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>';
	$args['comment_notes_before'] = '';

	return $args;
}
add_filter( 'woocommerce_product_review_comment_form_args', 'jofme_product_review_comment_form_args' );
