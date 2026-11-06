<?php
/** Editorial header and Elementor sections. @package Jofme */
defined( 'ABSPATH' ) || exit;

function jofme_editorial_assets() {
 wp_enqueue_style( 'jofme-editorial-header', JOFME_URI . '/assets/css/editorial-header.css', array( 'jofme-main' ), JOFME_VERSION );
 wp_register_style( 'jofme-editorial-widgets', JOFME_URI . '/assets/css/editorial-widgets.css', array( 'jofme-main' ), JOFME_VERSION );
 wp_register_style( 'jofme-bestsellers-tabs', JOFME_URI . '/assets/css/bestsellers-tabs.css', array( 'jofme-main', 'jofme-editorial-widgets' ), JOFME_VERSION );
 wp_register_script( 'jofme-bestsellers-tabs', JOFME_URI . '/assets/js/bestsellers-tabs.js', array(), JOFME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'jofme_editorial_assets', 22 );
function jofme_editorial_menus() {
 register_nav_menus( array( 'utility-left' => __( 'Header utility left', 'jofme' ), 'utility-right' => __( 'Header utility right', 'jofme' ) ) );
}
add_action( 'after_setup_theme', 'jofme_editorial_menus' );
function jofme_editorial_customize( $manager ) {
 $control = $manager->get_control( 'jofme_header_layout' );
 if ( $control ) { $control->choices['editorial'] = __( 'Editorial (utility bar & navigation)', 'jofme' ); }
 foreach ( array( 'jofme_header_utility' => array( true, __( 'Editorial header: show utility bar', 'jofme' ) ), 'jofme_header_transparent' => array( false, __( 'Editorial header: transparent on the static homepage', 'jofme' ) ) ) as $id => $config ) {
  $manager->add_setting( $id, array( 'default' => $config[0], 'sanitize_callback' => 'wp_validate_boolean' ) );
  $manager->add_control( $id, array( 'label' => $config[1], 'section' => 'jofme_header', 'type' => 'checkbox', 'description' => 'jofme_header_transparent' === $id ? __( 'Enable when the homepage starts with a full-width photo or hero. Navigation becomes white over the hero.', 'jofme' ) : '' ) );
 }
}
add_action( 'customize_register', 'jofme_editorial_customize', 20 );
function jofme_editorial_body_class( $classes ) {
 if ( 'editorial' === get_theme_mod( 'jofme_header_layout', 'classic' ) && get_theme_mod( 'jofme_header_transparent', false ) && is_front_page() && is_page() ) { $classes[] = 'jofme-transparent-header'; }
 return $classes;
}
add_filter( 'body_class', 'jofme_editorial_body_class' );
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
