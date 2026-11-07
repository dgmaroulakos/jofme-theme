<?php
/** Context-specific layout settings. @package Jofme */
defined( 'ABSPATH' ) || exit;

function jofme_layout_context() {
 if ( function_exists( 'is_product' ) && is_product() ) { return 'product'; }
 if ( function_exists( 'is_product_taxonomy' ) && ( is_product_taxonomy() || is_shop() ) ) { return 'catalog'; }
 if ( is_category() ) { return 'category'; }
 if ( is_page() ) { return 'page'; }
 return '';
}
function jofme_layout_option( $context, $option ) {
 $defaults = array( 'width' => 'boxed', 'sidebar' => in_array( $context, array( 'catalog', 'category' ), true ) ? 'right' : 'none' );
 $allowed = array( 'width' => array( 'boxed', 'wide', 'narrow', 'full' ), 'sidebar' => array( 'none', 'left', 'right' ) );
 if ( ! isset( $defaults[ $option ] ) ) { return ''; }
 $value = get_theme_mod( 'jofme_' . $context . '_' . $option, $defaults[ $option ] );
 return in_array( $value, $allowed[ $option ], true ) ? $value : $defaults[ $option ];
}
function jofme_layout_sidebar_id() {
 $ids = array( 'page' => 'page-sidebar', 'category' => 'sidebar-1', 'catalog' => 'shop-sidebar', 'product' => 'product-sidebar' );
 $context = jofme_layout_context();
 return isset( $ids[ $context ] ) ? $ids[ $context ] : '';
}
function jofme_layout_has_sidebar() {
 $context = jofme_layout_context();
 $id = jofme_layout_sidebar_id();
 return $context && 'none' !== jofme_layout_option( $context, 'sidebar' ) && $id && is_active_sidebar( $id );
}
/** Allowed off-canvas open effects for the shop sidebar on mobile/tablet. */
function jofme_catalog_sidebar_effects() {
 return array(
  'slide-left'   => __( 'Slide from left', 'jofme' ),
  'slide-right'  => __( 'Slide from right', 'jofme' ),
  'slide-bottom' => __( 'Slide from bottom', 'jofme' ),
  'fade'         => __( 'Fade', 'jofme' ),
  'scale'        => __( 'Scale', 'jofme' ),
 );
}
function jofme_catalog_sidebar_effect() {
 $effect = get_theme_mod( 'jofme_catalog_sidebar_effect', 'slide-left' );
 $allowed = array_keys( jofme_catalog_sidebar_effects() );
 return in_array( $effect, $allowed, true ) ? $effect : 'slide-left';
}
function jofme_layout_sidebar_is_offcanvas() {
 return 'catalog' === jofme_layout_context() && jofme_layout_has_sidebar();
}
function jofme_layout_sidebar() {
 if ( ! jofme_layout_has_sidebar() ) { return; }
 $offcanvas = jofme_layout_sidebar_is_offcanvas();
 $classes = 'widget-area jofme-layout-sidebar';
 if ( $offcanvas ) {
  $classes .= ' jofme-shop-sidebar-panel';
 }
 echo '<aside id="secondary" class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Sidebar', 'jofme' ) . '"' . ( $offcanvas ? ' data-effect="' . esc_attr( jofme_catalog_sidebar_effect() ) . '"' : '' ) . '>';
 if ( $offcanvas ) {
  echo '<div class="jofme-shop-sidebar-panel__header"><p class="jofme-shop-sidebar-panel__title">' . esc_html__( 'Filters', 'jofme' ) . '</p><button type="button" class="jofme-shop-sidebar-close" aria-label="' . esc_attr__( 'Close filters', 'jofme' ) . '"><span aria-hidden="true">×</span></button></div>';
 }
 dynamic_sidebar( jofme_layout_sidebar_id() );
 echo '</aside>';
}
function jofme_layout_body_classes( $classes ) {
 $context = jofme_layout_context();
 if ( ! $context ) { return $classes; }
 $classes = array_diff( $classes, array( 'has-sidebar' ) );
 $classes[] = 'jofme-layout-settings';
 $classes[] = 'jofme-width-' . jofme_layout_option( $context, 'width' );
 $classes[] = 'jofme-sidebar-' . ( jofme_layout_has_sidebar() ? jofme_layout_option( $context, 'sidebar' ) : 'none' );
 if ( jofme_layout_sidebar_is_offcanvas() ) {
  $classes[] = 'jofme-shop-sidebar-offcanvas';
  $classes[] = 'jofme-sidebar-effect-' . jofme_catalog_sidebar_effect();
 }
 if ( 'product' === $context ) {
  $layout = get_theme_mod( 'jofme_product_gallery_layout', 'left' );
  $classes[] = 'jofme-gallery-' . ( in_array( $layout, array( 'left', 'right', 'stacked' ), true ) ? $layout : 'left' );
 }
 return array_values( $classes );
}
add_filter( 'body_class', 'jofme_layout_body_classes', 30 );
function jofme_layout_customize( $manager ) {
 $manager->add_section( 'jofme_layout', array( 'title' => __( 'Layout Settings', 'jofme' ), 'panel' => 'jofme_options' ) );
 foreach ( array( 'page' => __( 'Pages', 'jofme' ), 'category' => __( 'Blog categories', 'jofme' ), 'catalog' => __( 'Shop & product categories', 'jofme' ), 'product' => __( 'Product pages', 'jofme' ) ) as $context => $label ) {
  foreach ( array( 'width' => __( 'Content width', 'jofme' ), 'sidebar' => __( 'Sidebar position', 'jofme' ) ) as $option => $option_label ) {
   $id = 'jofme_' . $context . '_' . $option;
   $choices = 'width' === $option ? array( 'boxed' => __( 'Boxed (1320px)', 'jofme' ), 'wide' => __( 'Wide (1600px)', 'jofme' ), 'narrow' => __( 'Narrow (960px)', 'jofme' ), 'full' => __( 'Full width', 'jofme' ) ) : array( 'none' => __( 'No sidebar', 'jofme' ), 'left' => __( 'Left sidebar', 'jofme' ), 'right' => __( 'Right sidebar', 'jofme' ) );
   $default = 'width' === $option ? 'boxed' : ( in_array( $context, array( 'catalog', 'category' ), true ) ? 'right' : 'none' );
   $manager->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => 'jofme_sanitize_select' ) );
   $desc = '';
   if ( 'sidebar' === $option ) {
    $desc = 'catalog' === $context
     ? __( 'The shop sidebar appears when its widget area contains widgets. On mobile and tablet it opens as an off-canvas panel.', 'jofme' )
     : __( 'The sidebar appears when its widget area contains widgets. On mobile it follows the content.', 'jofme' );
   }
   $manager->add_control( $id, array( 'label' => $label . ' — ' . $option_label, 'section' => 'jofme_layout', 'type' => 'select', 'choices' => $choices, 'description' => $desc ) );
  }
 }
 $manager->add_setting( 'jofme_catalog_sidebar_effect', array( 'default' => 'slide-left', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control( 'jofme_catalog_sidebar_effect', array(
  'label'       => __( 'Shop & product categories — Off-canvas open effect', 'jofme' ),
  'description' => __( 'How the filters sidebar opens on mobile and tablet (below 960px).', 'jofme' ),
  'section'     => 'jofme_layout',
  'type'        => 'select',
  'choices'     => jofme_catalog_sidebar_effects(),
 ) );
 $manager->add_setting( 'jofme_product_gallery_layout', array( 'default' => 'left', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control( 'jofme_product_gallery_layout', array( 'label' => __( 'Product pages — Gallery layout', 'jofme' ), 'section' => 'jofme_layout', 'type' => 'select', 'choices' => array( 'left' => __( 'Gallery left', 'jofme' ), 'right' => __( 'Gallery right', 'jofme' ), 'stacked' => __( 'Gallery above summary', 'jofme' ) ) ) );
}
add_action( 'customize_register', 'jofme_layout_customize', 25 );
function jofme_layout_assets() {
 wp_enqueue_style( 'jofme-layout', JOFME_URI . '/assets/css/layout.css', class_exists( 'WooCommerce' ) ? array( 'jofme-premium' ) : array( 'jofme-main' ), JOFME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'jofme_layout_assets', 30 );
