<?php
/** Premium catalog tools. @package Jofme */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

function jofme_catalog_assets() {
 wp_enqueue_style( 'jofme-premium', JOFME_URI . '/assets/css/premium-shop.css', array( 'jofme-woocommerce' ), JOFME_VERSION );
 wp_enqueue_script( 'wc-add-to-cart' );
 wp_enqueue_script( 'wc-add-to-cart-variation' );
 wp_enqueue_script(
  'jofme-premium',
  JOFME_URI . '/assets/js/premium-shop.js',
  array( 'jquery', 'wc-add-to-cart', 'wc-add-to-cart-variation' ),
  JOFME_VERSION,
  true
 );
 wp_localize_script(
  'jofme-premium',
  'jofmeShop',
  array(
   'url'     => admin_url( 'admin-ajax.php' ),
   'nonce'   => wp_create_nonce( 'jofme_preview' ),
   'loading' => __( 'Loading…', 'jofme' ),
   'error'   => __( 'Unable to load this product. Please try again.', 'jofme' ),
   'added'   => __( 'Added to cart', 'jofme' ),
   'view'    => __( 'View cart', 'jofme' ),
   'more'    => __( 'More', 'jofme' ),
   'less'    => __( 'Less', 'jofme' ),
  )
 );
}
add_action( 'wp_enqueue_scripts', 'jofme_catalog_assets', 25 );

/**
 * Collapsible taxonomy description: full on desktop, 20 words + More on mobile.
 *
 * @param string $html Formatted description HTML.
 */
function jofme_render_term_description( $html ) {
 $html = trim( (string) $html );
 if ( '' === $html ) {
  return;
 }
 $text  = trim( wp_strip_all_tags( $html ) );
 $words = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
 $limit = 20;
 $long  = is_array( $words ) && count( $words ) > $limit;

 if ( ! $long ) {
  echo '<div class="term-description">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WC-formatted content.
  return;
 }

 $preview = esc_html( implode( ' ', array_slice( $words, 0, $limit ) ) ) . '&hellip;';
 echo '<div class="term-description jofme-term-description">';
 echo '<div class="jofme-term-description__preview">' . $preview . '</div>';
 echo '<div class="jofme-term-description__full">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
 echo '<button type="button" class="jofme-term-description__more" aria-expanded="false">';
 echo '<span class="jofme-term-description__more-label">' . esc_html__( 'More', 'jofme' ) . '</span>';
 echo '<svg class="jofme-term-description__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
 echo '</button>';
 echo '</div>';
}

/**
 * Replace WooCommerce taxonomy archive description with collapsible markup.
 */
function jofme_taxonomy_archive_description() {
 if ( ! is_product_taxonomy() || 0 !== absint( get_query_var( 'paged' ) ) ) {
  return;
 }
 $term = get_queried_object();
 if ( ! $term || empty( $term->description ) ) {
  return;
 }
 jofme_render_term_description( wc_format_content( wp_kses_post( $term->description ) ) );
}
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
add_action( 'woocommerce_archive_description', 'jofme_taxonomy_archive_description', 10 );

function jofme_catalog_header() {
 if ( ! is_shop() && ! is_product_taxonomy() ) { return; }
 $cover = jofme_category_cover_image();
 echo '<header class="jofme-catalog-heading' . ( $cover ? ' jofme-catalog-heading--cover' : '' ) . '">' . $cover . '<div class="jofme-catalog-heading__content"><p class="jofme-eyebrow">' . esc_html__( 'The collection', 'jofme' ) . '</p><h1>' . esc_html( woocommerce_page_title( false ) ) . '</h1>';
 do_action( 'woocommerce_archive_description' );
 echo '</div></header>';
}

 /** Category navigation stays with the product results; filters live in the shop sidebar. */
function jofme_catalog_tools() {
 if ( ! is_shop() && ! is_product_taxonomy() ) { return; }
 $categories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => is_product_category() ? get_queried_object_id() : 0, 'hide_empty' => true ) );
 if ( ! is_wp_error( $categories ) && $categories ) {
  echo '<nav class="jofme-category-nav" aria-label="' . esc_attr__( 'Product categories', 'jofme' ) . '">';
  foreach ( $categories as $term ) { $url = get_term_link( $term ); if ( ! is_wp_error( $url ) ) { echo '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . ' <small>' . absint( $term->count ) . '</small></a>'; } }
  echo '</nav>';
 }
}
add_action( 'woocommerce_before_main_content', 'jofme_catalog_tools', 25 );

function jofme_filter_value( $key ) {
 return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : ''; // Public read-only catalog parameters.
}
function jofme_filter_select( $taxonomy, $key, $label ) {
 $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
 if ( is_wp_error( $terms ) || ! $terms ) { return; }
 echo '<label>' . esc_html( $label ) . '<select name="' . esc_attr( $key ) . '"><option value="">' . esc_html__( 'All', 'jofme' ) . '</option>';
 foreach ( $terms as $term ) { echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( jofme_filter_value( $key ), $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>'; }
 echo '</select></label>';
}

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
  $mobile = in_array( (string) $view, array( '1', '2' ), true ) ? '' : ' jofme-view__btn--desktop';
  echo '<button type="button" class="jofme-view__btn' . esc_attr( $mobile ) . '" data-view="' . esc_attr( $view ) . '" aria-pressed="false" aria-label="' . esc_attr( $label ) . '" title="' . esc_attr( $label ) . '"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false">';
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

/**
 * Allow SVG uploads for the filters icon Customizer control.
 *
 * @param array $mimes Mime types.
 * @return array
 */
function jofme_allow_svg_uploads( $mimes ) {
 $mimes['svg']  = 'image/svg+xml';
 $mimes['svgz'] = 'image/svg+xml';
 return $mimes;
}
add_filter( 'upload_mimes', 'jofme_allow_svg_uploads' );

/**
 * Keep WordPress from rejecting valid SVG uploads on filetype checks.
 *
 * @param array  $data     File data.
 * @param string $file     Full path.
 * @param string $filename File name.
 * @param array  $mimes    Allowed mimes.
 * @return array
 */
function jofme_fix_svg_filetype( $data, $file, $filename, $mimes ) {
 $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
 if ( 'svg' !== $ext && 'svgz' !== $ext ) {
  return $data;
 }
 $data['ext']  = 'svg';
 $data['type'] = 'image/svg+xml';
 return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'jofme_fix_svg_filetype', 10, 4 );

/**
 * Icon markup for the off-canvas filters button (Customizer SVG/image or default).
 *
 * @return string
 */
function jofme_shop_filters_icon_html() {
 $id = absint( get_theme_mod( 'jofme_shop_filters_icon', 0 ) );
 if ( $id ) {
  $url  = wp_get_attachment_url( $id );
  $mime = get_post_mime_type( $id );
  if ( $url && ( ! $mime || 0 === strpos( (string) $mime, 'image/' ) ) ) {
   return '<img class="jofme-shop-sidebar-toggle__icon" src="' . esc_url( $url ) . '" alt="" width="20" height="20" decoding="async">';
  }
 }
 return '<svg class="jofme-shop-sidebar-toggle__icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M4 6h16M4 12h10M4 18h14"/></svg>';
}

/** Off-canvas filters control — icon only. */
function jofme_shop_sidebar_toggle() {
 if ( ! function_exists( 'jofme_layout_sidebar_is_offcanvas' ) || ! jofme_layout_sidebar_is_offcanvas() ) {
  return;
 }
 echo '<button type="button" class="jofme-shop-sidebar-toggle" aria-expanded="false" aria-controls="secondary" aria-label="' . esc_attr__( 'Filters', 'jofme' ) . '" title="' . esc_attr__( 'Filters', 'jofme' ) . '">';
 echo jofme_shop_filters_icon_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper / trusted SVG.
 echo '<span class="screen-reader-text">' . esc_html__( 'Filters', 'jofme' ) . '</span>';
 echo '</button>';
}

/**
 * Catalog toolbar above products: filters + sorting + columns, then result count alone.
 */
function jofme_catalog_toolbar() {
 if ( ! is_shop() && ! is_product_taxonomy() ) {
  return;
 }
 echo '<div class="jofme-catalog-toolbar" id="jofme-catalog-products">';
 echo '<div class="jofme-catalog-toolbar__controls">';
 jofme_shop_sidebar_toggle();
 if ( function_exists( 'woocommerce_catalog_ordering' ) ) {
  woocommerce_catalog_ordering();
 }
 jofme_catalog_view();
 echo '</div>';
 if ( function_exists( 'woocommerce_result_count' ) ) {
  woocommerce_result_count();
 }
 echo '</div>';
}
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', 'jofme_catalog_toolbar', 20 );
add_action( 'woocommerce_no_products_found', 'jofme_shop_sidebar_toggle', 5 );

function jofme_card_actions() {
 global $product;
 echo '<div class="jofme-card-actions"><button type="button" class="jofme-preview" data-product="' . absint( $product->get_id() ) . '" aria-label="' . esc_attr__( 'Quick view', 'jofme' ) . '"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button><a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" aria-label="' . esc_attr__( 'Product details', 'jofme' ) . '"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div>';
}
/**
 * Short description for list view: up to 20 words, then a link to the product.
 *
 * Rendered after the product link closes so “View more” is not a nested anchor.
 */
function jofme_card_description() {
 global $product;
 if ( ! $product ) {
  return;
 }
 $text = trim( wp_strip_all_tags( $product->get_short_description() ) );
 if ( '' === $text ) {
  $text = trim( wp_strip_all_tags( $product->get_description() ) );
 }
 if ( '' === $text ) {
  return;
 }
 $words     = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
 $limit     = 20;
 $truncated = is_array( $words ) && count( $words ) > $limit;
 $excerpt   = $truncated ? implode( ' ', array_slice( $words, 0, $limit ) ) : $text;
 echo '<p class="jofme-card-description">' . esc_html( $excerpt );
 if ( $truncated ) {
  echo ' <a class="jofme-card-description__more" href="' . esc_url( get_permalink( $product->get_id() ) ) . '">' . esc_html__( 'View more', 'jofme' ) . '</a>';
 }
 echo '</p>';
}
add_action( 'woocommerce_after_shop_loop_item', 'jofme_card_description', 7 );
add_action( 'woocommerce_after_shop_loop_item', 'jofme_card_actions', 12 );

/**
 * Main + thumbnail gallery for quick view (variation-aware).
 *
 * @param WC_Product $product Product.
 */
function jofme_preview_gallery( $product ) {
 if ( ! $product ) {
  return;
 }
 $ids = function_exists( 'jofme_stacked_gallery_ids' ) ? jofme_stacked_gallery_ids( $product ) : array();
 if ( ! $ids ) {
  $main = (int) $product->get_image_id();
  if ( $main ) {
   $ids = array( $main );
  }
 }
 echo '<div class="images jofme-preview-gallery">';
 if ( ! $ids ) {
  echo '<figure class="jofme-preview-gallery__main">' . wp_kses_post( wc_placeholder_img( 'woocommerce_single' ) ) . '</figure>';
  echo '</div>';
  return;
 }
 $primary = (int) $ids[0];
 echo '<figure class="jofme-preview-gallery__main">';
 echo wp_get_attachment_image(
  $primary,
  'woocommerce_single',
  false,
  array(
   'class'              => 'wp-post-image',
   'loading'            => 'eager',
   'decoding'           => 'async',
   'data-attachment_id' => (string) $primary,
  )
 );
 echo '</figure>';
 if ( count( $ids ) > 1 ) {
  echo '<div class="jofme-preview-gallery__thumbs" role="list">';
  foreach ( $ids as $index => $attachment_id ) {
   $attachment_id = (int) $attachment_id;
   $full          = wp_get_attachment_image_src( $attachment_id, 'woocommerce_single' );
   $srcset        = wp_get_attachment_image_srcset( $attachment_id, 'woocommerce_single' );
   $sizes         = wp_get_attachment_image_sizes( $attachment_id, 'woocommerce_single' );
   echo '<button type="button" class="jofme-preview-gallery__thumb' . ( 0 === $index ? ' is-active' : '' ) . '" role="listitem" data-attachment="' . absint( $attachment_id ) . '" data-src="' . esc_url( $full ? $full[0] : '' ) . '" data-srcset="' . esc_attr( $srcset ? $srcset : '' ) . '" data-sizes="' . esc_attr( $sizes ? $sizes : '' ) . '" aria-label="' . esc_attr( sprintf( /* translators: %d image number */ __( 'Show image %d', 'jofme' ), $index + 1 ) ) . '"' . ( 0 === $index ? ' aria-current="true"' : '' ) . '>';
   echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'alt' => '' ) );
   echo '</button>';
  }
  echo '</div>';
 }
 echo '</div>';
}

/**
 * AJAX quick view: product page summary (gallery, price, variations, add to cart).
 */
function jofme_quick_view() {
 check_ajax_referer( 'jofme_preview', 'nonce' );
 $product = wc_get_product( isset( $_POST['product'] ) ? absint( $_POST['product'] ) : 0 );
 if ( ! $product || 'publish' !== get_post_status( $product->get_id() ) || post_password_required( $product->get_id() ) || ! $product->is_visible() ) {
  wp_send_json_error( null, 404 );
 }

 $post_object = get_post( $product->get_id() );
 if ( ! $post_object ) {
  wp_send_json_error( null, 404 );
 }

 $previous_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
 $previous_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
 $GLOBALS['post']  = $post_object;
 setup_postdata( $post_object );
 wc_setup_product_data( $post_object );

 if ( function_exists( 'jofme_quantity_minus' ) ) {
  add_action( 'woocommerce_before_quantity_input_field', 'jofme_quantity_minus' );
 }
 if ( function_exists( 'jofme_quantity_plus' ) ) {
  add_action( 'woocommerce_after_quantity_input_field', 'jofme_quantity_plus' );
 }

 ob_start();
 echo '<div id="product-' . absint( $product->get_id() ) . '" class="product jofme-preview-product ' . esc_attr( implode( ' ', wc_get_product_class( '', $product ) ) ) . '">';
 echo '<div class="jofme-preview-grid">';
 jofme_preview_gallery( $product );
 echo '<div class="summary entry-summary">';
 $cats = wc_get_product_category_list( $product->get_id(), ', ' );
 if ( $cats ) {
  echo '<div class="jofme-product-category">' . wp_kses_post( $cats ) . '</div>';
 }
 echo '<h2 id="jofme-preview-title" class="product_title entry-title">' . esc_html( $product->get_name() ) . '</h2>';
 woocommerce_template_single_rating();
 woocommerce_template_single_price();
 woocommerce_template_single_excerpt();
 woocommerce_template_single_add_to_cart();
 echo '<p class="jofme-preview-more"><a href="' . esc_url( get_permalink( $product->get_id() ) ) . '">' . esc_html__( 'View full details', 'jofme' ) . '</a></p>';
 echo '</div></div></div>';
 $html = ob_get_clean();

 if ( function_exists( 'jofme_quantity_minus' ) ) {
  remove_action( 'woocommerce_before_quantity_input_field', 'jofme_quantity_minus' );
 }
 if ( function_exists( 'jofme_quantity_plus' ) ) {
  remove_action( 'woocommerce_after_quantity_input_field', 'jofme_quantity_plus' );
 }

 if ( $previous_post ) {
  $GLOBALS['post'] = $previous_post;
  setup_postdata( $previous_post );
 } else {
  wp_reset_postdata();
 }
 if ( null !== $previous_product ) {
  $GLOBALS['product'] = $previous_product;
 }

 wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_jofme_quick_view', 'jofme_quick_view' );
add_action( 'wp_ajax_nopriv_jofme_quick_view', 'jofme_quick_view' );
function jofme_preview_dialog() {
 echo '<dialog id="jofme-preview-dialog" aria-label="' . esc_attr__( 'Quick view', 'jofme' ) . '"><button type="button" class="jofme-preview-close" aria-label="' . esc_attr__( 'Close', 'jofme' ) . '">×</button><div class="jofme-preview-content" aria-live="polite"></div></dialog>';
}
add_action( 'wp_footer', 'jofme_preview_dialog' );
function jofme_premium_customizer( $manager ) {
 $manager->remove_control( 'jofme_shop_columns' );
 foreach ( array( 'jofme_card_layout' => array( __( 'Product card layout', 'jofme' ), 'bestsellers', array( 'bestsellers' => __( 'Bestsellers (tabs card)', 'jofme' ), 'minimal' => __( 'Minimal', 'jofme' ), 'framed' => __( 'Framed', 'jofme' ), 'editorial' => __( 'Editorial', 'jofme' ), 'gallery' => __( 'Gallery (minimal product grid)', 'jofme' ) ) ), 'jofme_default_view' => array( __( 'Default catalog view', 'jofme' ), jofme_catalog_default_view(), array( '1' => '1 col', '2' => '2 col', '3' => '3 col', '4' => '4 col', 'list' => __( 'List', 'jofme' ) ) ) ) as $key => $config ) {
  $manager->add_setting( $key, array( 'default' => $config[1], 'sanitize_callback' => 'jofme_sanitize_select' ) );
  $manager->add_control( $key, array( 'label' => $config[0], 'section' => 'jofme_shop', 'type' => 'select', 'choices' => $config[2] ) );
 }
 $manager->add_setting( 'jofme_shop_filters_icon', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
 $manager->add_control(
  new WP_Customize_Media_Control(
   $manager,
   'jofme_shop_filters_icon',
   array(
    'label'       => __( 'Filters button icon (SVG)', 'jofme' ),
    'description' => __( 'Optional SVG (or image) for the mobile/tablet Filters off-canvas button. Leave empty for the default icon.', 'jofme' ),
    'section'     => 'jofme_shop',
    'mime_type'   => 'image',
   )
  )
 );
}
add_action( 'customize_register', 'jofme_premium_customizer', 20 );
function jofme_premium_classes( $classes ) {
 $layout = get_theme_mod( 'jofme_card_layout', 'bestsellers' );
 $view = jofme_catalog_default_view();
 $classes[] = 'jofme-card-' . ( in_array( $layout, array( 'bestsellers', 'minimal', 'framed', 'editorial', 'gallery' ), true ) ? $layout : 'bestsellers' );
 $classes[] = 'jofme-default-view-' . ( in_array( (string) $view, array( '1', '2', '3', '4', 'list' ), true ) ? $view : '3' );
 return $classes;
}
add_filter( 'body_class', 'jofme_premium_classes' );

/**
 * Circular sale badge with discount percent when available.
 *
 * @param WC_Product $product Product.
 * @param string     $class   Extra class.
 * @return string
 */
function jofme_product_sale_badge_html( $product, $class = 'jofme-sale-badge' ) {
 if ( ! $product || ! $product->is_on_sale() ) {
  return '';
 }
 $regular = (float) $product->get_regular_price();
 $sale    = (float) $product->get_sale_price();
 if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
  $percent = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
  return '<span class="' . esc_attr( $class ) . '">-' . absint( $percent ) . '%</span>';
 }
 return '<span class="' . esc_attr( $class ) . '">' . esc_html__( 'Sale', 'jofme' ) . '</span>';
}

/**
 * Replace WooCommerce sale flash on bestsellers cards.
 *
 * @param string     $html    Flash HTML.
 * @param WP_Post    $post    Post.
 * @param WC_Product $product Product.
 * @return string
 */
function jofme_bestsellers_sale_flash( $html, $post, $product ) {
 if ( 'bestsellers' !== get_theme_mod( 'jofme_card_layout', 'bestsellers' ) || ! $product ) {
  return $html;
 }
 return jofme_product_sale_badge_html( $product, 'onsale jofme-sale-badge' );
}
add_filter( 'woocommerce_sale_flash', 'jofme_bestsellers_sale_flash', 20, 3 );

function jofme_product_card_assets() {
 wp_enqueue_style( 'jofme-product-cards', JOFME_URI . '/assets/css/product-cards.css', array( 'jofme-premium' ), JOFME_VERSION );
}
add_action( 'wp_enqueue_scripts', 'jofme_product_card_assets', 28 );
