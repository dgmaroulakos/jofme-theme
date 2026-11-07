<?php
/** Optional second product image on hover. @package Jofme */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

/**
 * Sanitize product card hover scale (1–1.25).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function jofme_sanitize_hover_scale( $value ) {
 $value = is_numeric( $value ) ? (float) $value : 1.05;
 $value = max( 1, min( 1.25, $value ) );
 return number_format( $value, 2, '.', '' );
}

function jofme_hover_customize( $manager ) {
 $manager->add_setting( 'jofme_product_image_hover', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
 $manager->add_control( 'jofme_product_image_hover', array(
  'label' => __( 'Change product image on hover', 'jofme' ),
  'description' => __( 'Show the first gallery image over the main image on product cards. Products without another image keep their main photo.', 'jofme' ),
  'section' => 'jofme_shop', 'type' => 'checkbox',
 ) );
 $manager->add_setting( 'jofme_product_hover_scale', array( 'default' => '1.05', 'sanitize_callback' => 'jofme_sanitize_hover_scale' ) );
 $manager->add_control( 'jofme_product_hover_scale', array(
  'label'       => __( 'Product image hover scale', 'jofme' ),
  'description' => __( 'Zoom factor on card image hover. Default 1.05. Use 1 for no zoom.', 'jofme' ),
  'section'     => 'jofme_shop',
  'type'        => 'number',
  'input_attrs' => array( 'min' => 1, 'max' => 1.25, 'step' => 0.01 ),
 ) );
 $manager->add_setting( 'jofme_archive_scroll_products', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
 $manager->add_control( 'jofme_archive_scroll_products', array(
  'label'       => __( 'Smooth scroll to products on archive load', 'jofme' ),
  'description' => __( 'On shop and category pages, gently scroll to the product grid after the page loads.', 'jofme' ),
  'section'     => 'jofme_shop',
  'type'        => 'checkbox',
 ) );
}
add_action( 'customize_register', 'jofme_hover_customize', 25 );

/**
 * Publish hover-scale CSS variable for product cards.
 */
function jofme_hover_scale_css() {
 $scale = jofme_sanitize_hover_scale( get_theme_mod( 'jofme_product_hover_scale', '1.05' ) );
 $css   = ':root{--jofme-card-hover-scale:' . $scale . ';}';
 wp_add_inline_style( 'jofme-product-cards', $css );
 wp_add_inline_style( 'jofme-product-hover', $css );
}
add_action( 'wp_enqueue_scripts', 'jofme_hover_scale_css', 35 );

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

/**
 * Markup for primary + optional hover image (shop loop and widgets).
 *
 * @param WC_Product $product Product.
 * @param string     $size    Image size.
 * @return string
 */
/**
 * Preferred catalog image id when an active color filter matches a variation.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function jofme_filtered_variation_image_id( $product ) {
 if ( ! $product || ! function_exists( 'jofme_matching_variation_for_filters' ) ) {
  return 0;
 }
 $variation = jofme_matching_variation_for_filters( $product );
 if ( ! $variation ) {
  return 0;
 }
 if ( ! empty( $variation['image_id'] ) ) {
  return absint( $variation['image_id'] );
 }
 if ( ! empty( $variation['image']['id'] ) ) {
  return absint( $variation['image']['id'] );
 }
 return 0;
}

function jofme_product_card_media_html( $product, $size = 'woocommerce_thumbnail' ) {
 if ( ! $product ) {
  return '';
 }
 $filtered_id = jofme_filtered_variation_image_id( $product );
 $primary     = $filtered_id
  ? wp_get_attachment_image( $filtered_id, $size, false, array( 'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail', 'alt' => esc_attr( $product->get_name() ) ) )
  : $product->get_image( $size );
 if ( ! $primary ) {
  $primary = $product->get_image( $size );
 }
 $secondary = '';
 $id        = jofme_hover_image_id( $product );
 if ( $id && $id !== $filtered_id ) {
  $secondary = wp_get_attachment_image(
   $id,
   $size,
   false,
   array(
    'class'       => 'jofme-hover-image',
    'alt'         => '',
    'aria-hidden' => 'true',
    'loading'     => 'lazy',
    'decoding'    => 'async',
   )
  );
 }
 return '<span class="jofme-card-media">' . $primary . $secondary . '</span>';
}

function jofme_loop_product_images() {
 global $product;
 $filtered_id = jofme_filtered_variation_image_id( $product );
 $id          = jofme_hover_image_id( $product );
 $primary     = '';
 if ( $filtered_id ) {
  $primary = wp_get_attachment_image(
   $filtered_id,
   'woocommerce_thumbnail',
   false,
   array(
    'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image',
    'alt'   => $product ? esc_attr( $product->get_name() ) : '',
   )
  );
 }
 echo '<span class="jofme-card-media">';
 if ( $primary ) {
  echo $primary; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
 } else {
  woocommerce_template_loop_product_thumbnail();
 }
 if ( $id && $id !== $filtered_id ) {
  $secondary = wp_get_attachment_image( $id, 'woocommerce_thumbnail', false, array( 'class' => 'jofme-hover-image', 'alt' => '', 'aria-hidden' => 'true', 'loading' => 'lazy', 'decoding' => 'async' ) );
  if ( $secondary ) {
   echo $secondary; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  }
 }
 echo '</span>';
}
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'jofme_loop_product_images', 10 );
function jofme_hover_assets() {
 wp_enqueue_style( 'jofme-product-hover', JOFME_URI . '/assets/css/product-hover.css', array( 'jofme-premium' ), JOFME_VERSION );
 if ( get_theme_mod( 'jofme_product_image_hover', false ) ) {
  wp_enqueue_script( 'jofme-product-hover', JOFME_URI . '/assets/js/product-hover.js', array(), JOFME_VERSION, true );
 }
}
add_action( 'wp_enqueue_scripts', 'jofme_hover_assets', 30 );
