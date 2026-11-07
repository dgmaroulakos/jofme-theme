<?php
/** Editorial header and Elementor sections. @package Jofme */
defined( 'ABSPATH' ) || exit;

function jofme_editorial_assets() {
 wp_enqueue_style( 'jofme-editorial-header', JOFME_URI . '/assets/css/editorial-header.css', array( 'jofme-main' ), JOFME_VERSION );
 wp_register_style( 'jofme-editorial-widgets', JOFME_URI . '/assets/css/editorial-widgets.css', array( 'jofme-main' ), JOFME_VERSION );
 wp_register_style( 'jofme-bestsellers-tabs', JOFME_URI . '/assets/css/bestsellers-tabs.css', array( 'jofme-main', 'jofme-editorial-widgets', 'jofme-product-cards' ), JOFME_VERSION );
 wp_register_script( 'jofme-bestsellers-tabs', JOFME_URI . '/assets/js/bestsellers-tabs.js', array(), JOFME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'jofme_editorial_assets', 22 );
function jofme_editorial_customize( $manager ) {
 $control = $manager->get_control( 'jofme_header_layout' );
 if ( $control ) { $control->choices['editorial'] = __( 'Editorial', 'jofme' ); }
 $manager->remove_control( 'jofme_header_utility' );
 $manager->add_setting( 'jofme_header_transparent', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
 $manager->add_control( 'jofme_header_transparent', array( 'label' => __( 'Editorial header: transparent on the static homepage', 'jofme' ), 'section' => 'jofme_header', 'type' => 'checkbox', 'description' => __( 'Enable when the homepage starts with a full-width photo or hero.', 'jofme' ) ) );
 $manager->add_setting( 'jofme_transparent_logo', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
 $manager->add_control(
  new WP_Customize_Media_Control(
   $manager,
   'jofme_transparent_logo',
   array(
    'label'       => __( 'Transparent header logo', 'jofme' ),
    'description' => __( 'Optional logo used while the header is transparent over the hero. Leave empty to keep the site logo (with color invert).', 'jofme' ),
    'section'     => 'jofme_header',
    'mime_type'   => 'image',
   )
  )
 );
 $manager->add_setting( 'jofme_transparent_header_color', array( 'default' => '#ffffff', 'sanitize_callback' => 'sanitize_hex_color' ) );
 $manager->add_control(
  new WP_Customize_Color_Control(
   $manager,
   'jofme_transparent_header_color',
   array(
    'label'       => __( 'Transparent header text color', 'jofme' ),
    'description' => __( 'Menu, icons and title color while the header is transparent.', 'jofme' ),
    'section'     => 'jofme_header',
   )
  )
 );
}
add_action( 'customize_register', 'jofme_editorial_customize', 20 );
function jofme_editorial_body_class( $classes ) {
 if ( 'editorial' === get_theme_mod( 'jofme_header_layout', 'classic' ) && get_theme_mod( 'jofme_header_transparent', false ) && is_front_page() && is_page() ) {
  $classes[] = 'jofme-transparent-header';
  if ( absint( get_theme_mod( 'jofme_transparent_logo', 0 ) ) ) {
   $classes[] = 'jofme-has-transparent-logo';
  }
 }
 return $classes;
}
add_filter( 'body_class', 'jofme_editorial_body_class' );

/**
 * Inline CSS for transparent header color.
 */
function jofme_editorial_header_css() {
 $color = sanitize_hex_color( get_theme_mod( 'jofme_transparent_header_color', '#ffffff' ) );
 if ( ! $color ) {
  $color = '#ffffff';
 }
 $css = ':root{--jofme-transparent-header-color:' . $color . ';}';
 wp_add_inline_style( 'jofme-editorial-header', $css );
}
add_action( 'wp_enqueue_scripts', 'jofme_editorial_header_css', 23 );
function jofme_widget_category( $manager ) { $manager->add_category( 'jofme', array( 'title' => __( 'Jofme', 'jofme' ), 'icon' => 'eicon-diamond' ) ); }
add_action( 'elementor/elements/categories_registered', 'jofme_widget_category' );
function jofme_register_editorial_widgets( $manager ) {
 require_once JOFME_DIR . '/inc/widgets/class-editorial-story.php';
 require_once JOFME_DIR . '/inc/widgets/class-weekly-sales.php';
 require_once JOFME_DIR . '/inc/widgets/class-bestsellers-tabs.php';
 $manager->register( new Jofme_Editorial_Story_Widget() );
 $manager->register( new Jofme_Weekly_Sales_Widget() );
 $manager->register( new Jofme_Bestsellers_Tabs_Widget() );
}
add_action( 'elementor/widgets/register', 'jofme_register_editorial_widgets' );

/** Return a public, visible product; never leak drafts or password-protected products. */
function jofme_editorial_product( $id = 0 ) {
 if ( ! function_exists( 'wc_get_product' ) ) { return false; }
 $products = $id ? array( wc_get_product( absint( $id ) ) ) : array();
 if ( ! $id ) {
  $ids = wc_get_product_ids_on_sale();
  if ( ! $ids ) { return false; }
  $products = wc_get_products( array( 'include' => $ids, 'status' => 'publish', 'limit' => 12, 'orderby' => 'date', 'order' => 'DESC' ) );
 }
 foreach ( $products as $product ) {
  if ( $product && ! $product->is_type( 'variation' ) && 'publish' === get_post_status( $product->get_id() ) && ! post_password_required( $product->get_id() ) && $product->is_visible() && ( $id || $product->is_on_sale() ) ) { return $product; }
 }
 return false;
}
/** Render an attachment with its Media Library alt text. */
function jofme_editorial_image( $image, $size = 'large', $class = '' ) {
 if ( ! empty( $image['id'] ) ) {
  $html = wp_get_attachment_image( absint( $image['id'] ), $size, false, array( 'class' => $class, 'loading' => 'lazy' ) );
  if ( $html ) { return $html; }
 }
 $url = ! empty( $image['url'] ) ? $image['url'] : \Elementor\Utils::get_placeholder_image_src();
 return '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $url ) . '" alt="" loading="lazy">';
}
