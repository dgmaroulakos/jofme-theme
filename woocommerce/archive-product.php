<?php
/**
 * Product archive — shop & category.
 *
 * @package Jofme
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'jofme_shop_layout_open' );

// Full-bleed category cover sits outside the layout container.
$has_cover = function_exists( 'jofme_catalog_has_cover' ) && jofme_catalog_has_cover();
if ( $has_cover ) {
	jofme_catalog_header();
}
?>

<div class="jofme-container jofme-layout jofme-layout--shop">
	<?php
	// Title without cover stays inside the layout with products/sidebar.
	if ( ! $has_cover ) {
		jofme_catalog_header();
	}
	do_action( 'woocommerce_before_main_content' );

	if ( woocommerce_product_loop() ) {
		do_action( 'woocommerce_before_shop_loop' );
		woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}

		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
	} else {
		do_action( 'woocommerce_no_products_found' );
	}

	do_action( 'woocommerce_after_main_content' );

	/**
	 * Shop sidebar.
	 */
	jofme_layout_sidebar();
	?>
</div>

<?php
get_footer( 'shop' );
