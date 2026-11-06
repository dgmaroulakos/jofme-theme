<?php
/** Premium catalog tools. @package Jofme */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

function jofme_catalog_assets() {
 wp_enqueue_style( 'jofme-premium', JOFME_URI . '/assets/css/premium-shop.css', array( 'jofme-woocommerce' ), JOFME_VERSION );
 wp_enqueue_script( 'jofme-premium', JOFME_URI . '/assets/js/premium-shop.js', array(), JOFME_VERSION, true );
 wp_localize_script( 'jofme-premium', 'jofmeShop', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'jofme_preview' ), 'loading' => __( 'Loading…', 'jofme' ), 'error' => __( 'Unable to load this product. Please try again.', 'jofme' ) ) );
}
add_action( 'wp_enqueue_scripts', 'jofme_catalog_assets', 25 );

function jofme_catalog_header() {
 if ( ! is_shop() && ! is_product_taxonomy() ) { return; }
 $cover = jofme_category_cover_image();
 echo '<header class="jofme-catalog-heading' . ( $cover ? ' jofme-catalog-heading--cover' : '' ) . '">' . $cover . '<div class="jofme-catalog-heading__content"><p class="jofme-eyebrow">' . esc_html__( 'The collection', 'jofme' ) . '</p><h1>' . esc_html( woocommerce_page_title( false ) ) . '</h1>';
 do_action( 'woocommerce_archive_description' );
 echo '</div></header>';
}

/** Category navigation and filters stay with the product results. */
function jofme_catalog_tools() {
 if ( ! is_shop() && ! is_product_taxonomy() ) { return; }
 $categories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => is_product_category() ? get_queried_object_id() : 0, 'hide_empty' => true ) );
 if ( ! is_wp_error( $categories ) && $categories ) {
  echo '<nav class="jofme-category-nav" aria-label="' . esc_attr__( 'Product categories', 'jofme' ) . '">';
  foreach ( $categories as $term ) { $url = get_term_link( $term ); if ( ! is_wp_error( $url ) ) { echo '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . ' <small>' . absint( $term->count ) . '</small></a>'; } }
  echo '</nav>';
 }
 jofme_catalog_filters();
}
add_action( 'woocommerce_before_main_content', 'jofme_catalog_tools', 25 );

function jofme_filter_value( $key ) {
 return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : ''; // Public read-only catalog parameters.
}
function jofme_catalog_filters() {
 $action = is_product_taxonomy() ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' );
 if ( is_wp_error( $action ) ) { return; }
 echo '<details class="jofme-filters"><summary>' . esc_html__( 'Filter collection', 'jofme' ) . '</summary><form method="get" action="' . esc_url( $action ) . '"><div class="jofme-filter-fields">';
 foreach ( array( 's', 'post_type', 'orderby', 'lang' ) as $key ) { if ( jofme_filter_value( $key ) !== '' ) { echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( jofme_filter_value( $key ) ) . '">'; } }
 // Preserve taxonomy routing for sites using plain permalinks.
 if ( is_product_taxonomy() && ! get_option( 'permalink_structure' ) ) { $term = get_queried_object(); echo '<input type="hidden" name="' . esc_attr( $term->taxonomy ) . '" value="' . esc_attr( $term->slug ) . '">'; }
 foreach ( wc_get_attribute_taxonomies() as $attribute ) {
  $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
  jofme_filter_attribute_control( $taxonomy, 'filter_' . $attribute->attribute_name, $attribute->attribute_label );
 }
 jofme_filter_select( 'product_tag', 'jofme_collection', __( 'Collection', 'jofme' ) );
 echo '<fieldset><legend>' . esc_html__( 'Price range', 'jofme' ) . ' (' . esc_html( get_woocommerce_currency_symbol() ) . ')</legend>';
 foreach ( array( 'min_price' => __( 'Minimum price', 'jofme' ), 'max_price' => __( 'Maximum price', 'jofme' ) ) as $key => $label ) { echo '<label>' . esc_html( $label ) . '<input type="number" min="0" step="0.01" name="' . esc_attr( $key ) . '" value="' . esc_attr( jofme_filter_value( $key ) ) . '"></label>'; }
 echo '</fieldset></div><div class="jofme-filter-actions"><button type="submit">' . esc_html__( 'Apply filters', 'jofme' ) . '</button><a href="' . esc_url( $action ) . '">' . esc_html__( 'Clear filters', 'jofme' ) . '</a></div></form></details>';
 echo '<div class="jofme-active-filters">';
 foreach ( $_GET as $key => $value ) { if ( is_scalar( $value ) && '' !== $value && ( 0 === strpos( $key, 'filter_' ) || in_array( $key, array( 'min_price', 'max_price', 'jofme_collection' ), true ) ) ) { echo '<a href="' . esc_url( remove_query_arg( array( $key, 'paged', 'product-page' ) ) ) . '">' . esc_html( sanitize_text_field( wp_unslash( $value ) ) ) . ' × <span class="screen-reader-text">' . esc_html__( 'Remove filter', 'jofme' ) . '</span></a>'; } }
 echo '</div>';
}
function jofme_filter_select( $taxonomy, $key, $label ) {
 $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
 if ( is_wp_error( $terms ) || ! $terms ) { return; }
 echo '<label>' . esc_html( $label ) . '<select name="' . esc_attr( $key ) . '"><option value="">' . esc_html__( 'All', 'jofme' ) . '</option>';
 foreach ( $terms as $term ) { echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( jofme_filter_value( $key ), $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>'; }
 echo '</select></label>';
}
function jofme_collection_query( $query ) {
 $slug = jofme_filter_value( 'jofme_collection' );
 if ( $slug ) { $tax = $query->get( 'tax_query' ); $query->set( 'tax_query', array( 'relation' => 'AND', is_array( $tax ) ? $tax : array(), array( 'taxonomy' => 'product_tag', 'field' => 'slug', 'terms' => $slug ) ) ); }
}
add_action( 'woocommerce_product_query', 'jofme_collection_query' );

/** One default view for server markup, the Customizer and visitor controls. */
function jofme_catalog_default_view() {
 $view = (string) get_theme_mod( 'jofme_default_view', '' );
 if ( in_array( $view, array( '1', '2', '3', '4', 'list' ), true ) ) { return $view; }
 return (string) max( 1, min( 4, absint( get_theme_mod( 'jofme_shop_columns', 3 ) ) ) );
}

function jofme_catalog_view() {
 echo '<div class="jofme-view" role="group" aria-label="' . esc_attr__( 'Product view', 'jofme' ) . '">';
 $views = array( '1' => __( '1 column', 'jofme' ), '2' => __( '2 columns', 'jofme' ), '3' => __( '3 columns', 'jofme' ), '4' => __( '4 columns', 'jofme' ), 'list' => __( 'List view', 'jofme' ) );
 foreach ( $views as $view => $label ) {
  echo '<button type="button" data-view="' . esc_attr( $view ) . '" aria-pressed="false" aria-label="' . esc_attr( $label ) . '" title="' . esc_attr( $label ) . '"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false">';
  if ( 'list' === $view ) {
   echo '<path d="M4 6h16M4 12h16M4 18h16"/>';
  } else {
   $columns = (int) $view;
   for ( $i = 0; $i < $columns; $i++ ) {
    $x = 12 - ( $columns - 1 ) * 3 + $i * 6;
    echo '<path d="M' . absint( $x ) . ' 4v16"/>';
   }
  }
  echo '</svg></button>';
 }
 echo '</div>';
}
add_action( 'woocommerce_before_shop_loop', 'jofme_catalog_view', 35 );

function jofme_card_actions() {
 global $product;
 echo '<div class="jofme-card-actions"><button type="button" class="jofme-preview" data-product="' . absint( $product->get_id() ) . '" aria-label="' . esc_attr__( 'Quick view', 'jofme' ) . '"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button><a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" aria-label="' . esc_attr__( 'Product details', 'jofme' ) . '"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div>';
}
function jofme_card_description() { global $product; echo '<p class="jofme-card-description">' . esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 28 ) ) . '</p>'; }
add_action( 'woocommerce_after_shop_loop_item', 'jofme_card_actions', 12 );
add_action( 'woocommerce_after_shop_loop_item_title', 'jofme_card_description', 16 );

function jofme_quick_view() {
 check_ajax_referer( 'jofme_preview', 'nonce' );
 $product = wc_get_product( isset( $_POST['product'] ) ? absint( $_POST['product'] ) : 0 );
 if ( ! $product || 'publish' !== get_post_status( $product->get_id() ) || post_password_required( $product->get_id() ) || ! $product->is_visible() ) { wp_send_json_error( null, 404 ); }
 ob_start();
 echo '<div class="jofme-preview-grid"><div>' . wp_kses_post( $product->get_image( 'large' ) ) . '</div><div><p class="jofme-eyebrow">' . esc_html__( 'A closer look', 'jofme' ) . '</p><h2 id="jofme-preview-title">' . esc_html( $product->get_name() ) . '</h2><div class="price">' . wp_kses_post( $product->get_price_html() ) . '</div><p>' . esc_html( wp_strip_all_tags( $product->get_short_description() ) ) . '</p><p>' . wp_kses_post( wc_get_stock_html( $product ) ) . '</p><a class="button" href="' . esc_url( get_permalink( $product->get_id() ) ) . '">' . esc_html__( 'View product & choose options', 'jofme' ) . '</a></div></div>';
 wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'wp_ajax_jofme_quick_view', 'jofme_quick_view' );
add_action( 'wp_ajax_nopriv_jofme_quick_view', 'jofme_quick_view' );
function jofme_preview_dialog() {
 echo '<dialog id="jofme-preview-dialog" aria-label="' . esc_attr__( 'Quick view', 'jofme' ) . '"><button type="button" class="jofme-preview-close" aria-label="' . esc_attr__( 'Close', 'jofme' ) . '">×</button><div class="jofme-preview-content" aria-live="polite"></div></dialog>';
}
add_action( 'wp_footer', 'jofme_preview_dialog' );
function jofme_premium_customizer( $manager ) {
 $manager->remove_control( 'jofme_shop_columns' );
 foreach ( array( 'jofme_card_layout' => array( __( 'Product card layout', 'jofme' ), 'minimal', array( 'minimal' => __( 'Minimal', 'jofme' ), 'framed' => __( 'Framed', 'jofme' ), 'editorial' => __( 'Editorial', 'jofme' ), 'gallery' => __( 'Gallery (minimal product grid)', 'jofme' ) ) ), 'jofme_default_view' => array( __( 'Default catalog view', 'jofme' ), jofme_catalog_default_view(), array( '1' => '1 col', '2' => '2 col', '3' => '3 col', '4' => '4 col', 'list' => __( 'List', 'jofme' ) ) ) ) as $key => $config ) {
  $manager->add_setting( $key, array( 'default' => $config[1], 'sanitize_callback' => 'jofme_sanitize_select' ) );
  $manager->add_control( $key, array( 'label' => $config[0], 'section' => 'jofme_shop', 'type' => 'select', 'choices' => $config[2] ) );
 }
}
add_action( 'customize_register', 'jofme_premium_customizer', 20 );
function jofme_premium_classes( $classes ) {
 $layout = get_theme_mod( 'jofme_card_layout', 'minimal' );
 $view = jofme_catalog_default_view();
 $classes[] = 'jofme-card-' . ( in_array( $layout, array( 'minimal', 'framed', 'editorial', 'gallery' ), true ) ? $layout : 'minimal' );
 $classes[] = 'jofme-default-view-' . ( in_array( (string) $view, array( '1', '2', '3', '4', 'list' ), true ) ? $view : '3' );
 return $classes;
}
add_filter( 'body_class', 'jofme_premium_classes' );
