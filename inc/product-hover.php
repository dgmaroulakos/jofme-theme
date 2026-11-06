<?php
/** Optional second product image on hover. @package Jofme */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

function jofme_hover_customize( $manager ) {
 $manager->add_setting( 'jofme_product_image_hover', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
 $manager->add_control( 'jofme_product_image_hover', array(
  'label' => __( 'Change product image on hover', 'jofme' ),
  'description' => __( 'Show the first gallery image over the main image on product cards. Products without another image keep their main photo.', 'jofme' ),
  'section' => 'jofme_shop', 'type' => 'checkbox',
 ) );
}
add_action( 'customize_register', 'jofme_hover_customize', 25 );

/** Return a usable gallery image different from the featured image. */
function jofme_hover_image_id( $product ) {
 if ( ! $product || ! get_theme_mod( 'jofme_product_image_hover', false ) ) { return 0; }
 $primary = (int) $product->get_image_id();
 foreach ( $product->get_gallery_image_ids() as $id ) {
  $id = absint( $id );
  if ( $id && $id !== $primary && wp_attachment_is_image( $id ) ) { return $id; }
 }
 return 0;
}
function jofme_loop_product_images() {
 global $product;
 $id = jofme_hover_image_id( $product );
 if ( ! $id ) { woocommerce_template_loop_product_thumbnail(); return; }
 $secondary = wp_get_attachment_image( $id, 'woocommerce_thumbnail', false, array( 'class' => 'jofme-hover-image', 'alt' => '', 'aria-hidden' => 'true', 'loading' => 'lazy', 'decoding' => 'async' ) );
 if ( ! $secondary ) { woocommerce_template_loop_product_thumbnail(); return; }
 echo '<span class="jofme-card-media">';
 woocommerce_template_loop_product_thumbnail();
 echo $secondary; // WordPress generates escaped attachment markup.
 echo '</span>';
}
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'jofme_loop_product_images', 10 );
function jofme_hover_assets() {
 if ( ! get_theme_mod( 'jofme_product_image_hover', false ) ) { return; }
 wp_enqueue_style( 'jofme-product-hover', JOFME_URI . '/assets/css/product-hover.css', array( 'jofme-premium' ), JOFME_VERSION );
 wp_enqueue_script( 'jofme-product-hover', JOFME_URI . '/assets/js/product-hover.js', array(), JOFME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'jofme_hover_assets', 30 );
