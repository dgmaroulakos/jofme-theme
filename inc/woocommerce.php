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
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 5,
		),
	) );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'jofme_woocommerce_setup' );

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
	return (int) get_theme_mod( 'jofme_products_per_page', 12 );
}
add_filter( 'loop_shop_per_page', 'jofme_products_per_page' );

/**
 * Loop columns.
 *
 * @return int
 */
function jofme_loop_columns() {
	return (int) get_theme_mod( 'jofme_shop_columns', 4 );
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
