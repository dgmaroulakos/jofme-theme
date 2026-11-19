<?php
/**
 * Product card wishlist heart (localStorage + YITH/TI bridges).
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether YITH Wishlist is available.
 *
 * @return bool
 */
function jofme_wishlist_has_yith() {
	return function_exists( 'YITH_WCWL' ) || shortcode_exists( 'yith_wcwl_add_to_wishlist' );
}

/**
 * Whether TI WooCommerce Wishlist is available.
 *
 * @return bool
 */
function jofme_wishlist_has_ti() {
	return function_exists( 'tinv_url_wishlist_default' ) || class_exists( 'TInvWL_Public_AddToWishlist' );
}

/**
 * Render wishlist control markup for a product.
 *
 * @param WC_Product|null $product Product.
 * @return string
 */
function jofme_wishlist_button_html( $product = null ) {
	if ( ! $product && isset( $GLOBALS['product'] ) ) {
		$product = $GLOBALS['product'];
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$product_id = (int) $product->get_id();
	if ( ! $product_id ) {
		return '';
	}

	if ( jofme_wishlist_has_yith() && shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) {
		return '<div class="jofme-wishlist jofme-wishlist--plugin">' . do_shortcode( '[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]' ) . '</div>';
	}

	if ( jofme_wishlist_has_ti() && function_exists( 'tinv_wishlist_button' ) ) {
		ob_start();
		echo '<div class="jofme-wishlist jofme-wishlist--plugin">';
		tinv_wishlist_button();
		echo '</div>';
		return (string) ob_get_clean();
	}

	$label_add    = __( 'Add to wishlist', 'jofme' );
	$label_remove = __( 'Remove from wishlist', 'jofme' );
	$icon         = '<svg class="jofme-wishlist__icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path class="jofme-wishlist__path" d="M12 20.5s-7.2-4.35-9.33-8.2C1.1 9.55 2.05 6.2 5.1 5.2c1.85-.6 3.7.15 4.9 1.55C11.2 5.35 13.05 4.6 14.9 5.2c3.05 1 4 4.35 2.43 7.1C19.2 16.15 12 20.5 12 20.5Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>';

	return sprintf(
		'<button type="button" class="jofme-wishlist" data-jofme-wishlist data-product-id="%1$d" aria-pressed="false" aria-label="%2$s" data-label-add="%2$s" data-label-remove="%3$s">%4$s</button>',
		$product_id,
		esc_attr( $label_add ),
		esc_attr( $label_remove ),
		$icon
	);
}

/**
 * Enqueue wishlist assets on shop-like views.
 */
function jofme_wishlist_assets() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	wp_enqueue_style(
		'jofme-wishlist',
		JOFME_URI . '/assets/css/wishlist.css',
		array( 'jofme-premium' ),
		JOFME_VERSION
	);

	if ( jofme_wishlist_has_yith() || jofme_wishlist_has_ti() ) {
		return;
	}

	wp_enqueue_script(
		'jofme-wishlist',
		JOFME_URI . '/assets/js/wishlist.js',
		array(),
		JOFME_VERSION,
		true
	);
	wp_localize_script(
		'jofme-wishlist',
		'jofmeWishlist',
		array(
			'storageKey' => 'jofme_wishlist_ids',
			'i18n'       => array(
				'add'    => __( 'Add to wishlist', 'jofme' ),
				'remove' => __( 'Remove from wishlist', 'jofme' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'jofme_wishlist_assets', 36 );
