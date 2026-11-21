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
   'url'          => admin_url( 'admin-ajax.php' ),
   'nonce'        => wp_create_nonce( 'jofme_preview' ),
   'loading'      => __( 'Loading…', 'jofme' ),
   'error'        => __( 'Unable to load this product. Please try again.', 'jofme' ),
   'filterError'  => __( 'Unable to update filters. Please try again.', 'jofme' ),
   'added'        => __( 'Added to cart', 'jofme' ),
   'view'         => __( 'View cart', 'jofme' ),
   'more'         => __( 'More', 'jofme' ),
   'less'         => __( 'Less', 'jofme' ),
   'ajaxFilters'  => true,
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
 if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public catalog query args.
  return '';
 }
 $value = wp_unslash( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
 if ( is_array( $value ) ) {
  $parts = array();
  foreach ( $value as $part ) {
   if ( is_scalar( $part ) && '' !== $part ) {
    $parts[] = sanitize_title( (string) $part );
   }
  }
  return implode( ',', array_values( array_unique( array_filter( $parts ) ) ) );
 }
 return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
}

/**
 * Selected catalog filter term slugs for a query key.
 *
 * @param string $key Query arg (filter_color, jofme_collection, …).
 * @return string[]
 */
function jofme_filter_selected_slugs( $key ) {
 $value = jofme_filter_value( $key );
 if ( '' === $value ) {
  return array();
 }
 return array_values( array_filter( array_map( 'sanitize_title', explode( ',', $value ) ) ) );
}

/**
 * Normalize multi-checkbox filter args to WooCommerce comma-separated strings.
 */
function jofme_normalize_catalog_filter_query() {
 if ( is_admin() || empty( $_GET ) || ! is_array( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  return;
 }
 foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  if ( ! is_string( $key ) || ! is_array( $value ) ) {
   continue;
  }
  if ( 0 !== strpos( $key, 'filter_' ) && 'jofme_collection' !== $key ) {
   continue;
  }
  $normalized = jofme_filter_value( $key );
  $_GET[ $key ]     = $normalized; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $_REQUEST[ $key ] = $normalized;
 }
}
add_action( 'init', 'jofme_normalize_catalog_filter_query', 0 );

/**
 * Theme mod key for a catalog filter attribute checkbox.
 *
 * @param string $attribute_name Attribute slug (e.g. color).
 * @return string
 */
function jofme_catalog_filter_attr_mod_key( $attribute_name ) {
 return 'jofme_filter_attr_' . sanitize_key( $attribute_name );
}

/**
 * Whether a global attribute should appear in the shop Filters panel.
 *
 * Defaults to enabled so newly created attributes show until turned off.
 *
 * @param string $attribute_name Attribute slug.
 * @return bool
 */
function jofme_catalog_filter_attribute_enabled( $attribute_name ) {
 $attribute_name = sanitize_key( $attribute_name );
 if ( '' === $attribute_name ) {
  return false;
 }
 return (bool) get_theme_mod( jofme_catalog_filter_attr_mod_key( $attribute_name ), true );
}

/**
 * Whether the Collection (product_tag) filter is enabled.
 *
 * @return bool
 */
function jofme_catalog_filter_collection_enabled() {
 return (bool) get_theme_mod( 'jofme_filter_show_collection', true );
}

/**
 * Whether the price range filter is enabled.
 *
 * @return bool
 */
function jofme_catalog_filter_price_enabled() {
 return (bool) get_theme_mod( 'jofme_filter_show_price', true );
}

/**
 * Enabled global attributes for the catalog Filters panel.
 *
 * @return array<int,object>
 */
function jofme_catalog_filter_attributes() {
 if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
  return array();
 }
 $enabled = array();
 foreach ( wc_get_attribute_taxonomies() as $attribute ) {
  if ( ! empty( $attribute->attribute_name ) && jofme_catalog_filter_attribute_enabled( $attribute->attribute_name ) ) {
   $enabled[] = $attribute;
  }
 }
 return $enabled;
}

/**
 * Product IDs that scope catalog filter options to the current archive.
 *
 * On a product category / tag / attribute archive, only terms used by those
 * products are offered. On the main shop, returns null (unscoped / global).
 *
 * @return int[]|null Product IDs, empty array when the archive has no products, or null when unscoped.
 */
function jofme_catalog_filter_object_ids() {
 static $resolved = false;
 static $ids      = null;

 if ( $resolved ) {
  return $ids;
 }
 $resolved = true;

 if ( ! function_exists( 'is_product_taxonomy' ) || ! is_product_taxonomy() ) {
  $ids = null;
  return $ids;
 }

 $term = get_queried_object();
 if ( ! $term || empty( $term->term_id ) || empty( $term->taxonomy ) || ! taxonomy_exists( $term->taxonomy ) ) {
  $ids = array();
  return $ids;
 }

 $args = array(
  'status' => 'publish',
  'limit'  => -1,
  'return' => 'ids',
 );

 if ( 'product_cat' === $term->taxonomy ) {
  $args['category'] = array( $term->slug );
 } elseif ( 'product_tag' === $term->taxonomy ) {
  $args['tag'] = array( $term->slug );
 } else {
  $args['tax_query'] = array(
   array(
    'taxonomy' => $term->taxonomy,
    'field'    => 'term_id',
    'terms'    => array( (int) $term->term_id ),
   ),
  );
 }

 if ( function_exists( 'wc_get_products' ) ) {
  $found = wc_get_products( $args );
  $ids   = array_values( array_filter( array_map( 'absint', (array) $found ) ) );
  return $ids;
 }

 $query = new WP_Query(
  array(
   'post_type'              => 'product',
   'post_status'            => 'publish',
   'posts_per_page'         => -1,
   'fields'                 => 'ids',
   'no_found_rows'          => true,
   'update_post_meta_cache' => false,
   'update_post_term_cache' => false,
   'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- scoped catalog filter lookup.
    array(
     'taxonomy'         => $term->taxonomy,
     'field'            => 'term_id',
     'terms'            => array( (int) $term->term_id ),
     'include_children' => ( 'product_cat' === $term->taxonomy ),
    ),
   ),
  )
 );
 $ids = array_values( array_filter( array_map( 'absint', (array) $query->posts ) ) );
 return $ids;
}

/**
 * Terms for a catalog filter, scoped to products in the current archive when applicable.
 *
 * @param string $taxonomy Taxonomy name.
 * @param int    $number   Optional max terms (0 = all).
 * @return WP_Term[]
 */
function jofme_catalog_filter_terms( $taxonomy, $number = 0 ) {
 if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
  return array();
 }

 $args = array(
  'taxonomy'   => $taxonomy,
  'hide_empty' => true,
 );
 if ( $number > 0 ) {
  $args['number'] = absint( $number );
  $args['fields'] = 'all';
 }

 $object_ids = jofme_catalog_filter_object_ids();
 if ( is_array( $object_ids ) ) {
  if ( ! $object_ids ) {
   return array();
  }
  $args['object_ids'] = $object_ids;
 }

 $terms = get_terms( $args );
 return ( is_wp_error( $terms ) || ! $terms ) ? array() : $terms;
}

/**
 * Whether the catalog has any attribute/tag/price filters to show.
 *
 * Used so the Filters panel can open even when the shop sidebar has no widgets.
 *
 * @return bool
 */
function jofme_catalog_attribute_filters_available() {
 if ( ! function_exists( 'wc_get_attribute_taxonomies' ) || ! function_exists( 'wc_attribute_taxonomy_name' ) ) {
  return false;
 }
 foreach ( jofme_catalog_filter_attributes() as $attribute ) {
  $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
  if ( jofme_catalog_filter_terms( $taxonomy, 1 ) ) {
   return true;
  }
 }
 if ( jofme_catalog_filter_collection_enabled() && jofme_catalog_filter_terms( 'product_tag', 1 ) ) {
  return true;
 }
 return jofme_catalog_filter_price_enabled();
}

/**
 * Auto attribute / collection / price filters for the shop sidebar (off-canvas).
 *
 * Only enabled attributes (Customizer → Shop) are listed. WooCommerce layered-nav
 * widgets in Shop Sidebar are suppressed to avoid duplicate filters.
 */
function jofme_catalog_filters() {
 if ( ! is_shop() && ! is_product_taxonomy() ) {
  return;
 }
 if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
  return;
 }
 $action = is_product_taxonomy() ? get_term_link( get_queried_object() ) : wc_get_page_permalink( 'shop' );
 if ( is_wp_error( $action ) || ! $action ) {
  return;
 }

 $attributes = jofme_catalog_filter_attributes();
 $show_collection = jofme_catalog_filter_collection_enabled();
 $show_price      = jofme_catalog_filter_price_enabled();
 if ( ! $attributes && ! $show_collection && ! $show_price ) {
  return;
 }

 echo '<div class="jofme-filters jofme-filters--sidebar" data-jofme-ajax-filters="1">';
 echo '<form method="get" action="' . esc_url( $action ) . '" class="jofme-filters__form" data-jofme-ajax-filters-form="1">';
 echo '<div class="jofme-filter-fields">';

 foreach ( array( 's', 'post_type', 'orderby', 'lang' ) as $key ) {
  if ( jofme_filter_value( $key ) !== '' ) {
   echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( jofme_filter_value( $key ) ) . '">';
  }
 }
 // Preserve taxonomy routing for sites using plain permalinks.
 if ( is_product_taxonomy() && ! get_option( 'permalink_structure' ) ) {
  $term = get_queried_object();
  if ( $term && ! empty( $term->taxonomy ) && ! empty( $term->slug ) ) {
   echo '<input type="hidden" name="' . esc_attr( $term->taxonomy ) . '" value="' . esc_attr( $term->slug ) . '">';
  }
 }

 foreach ( $attributes as $attribute ) {
  $taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
  if ( function_exists( 'jofme_filter_attribute_control' ) ) {
   jofme_filter_attribute_control( $taxonomy, 'filter_' . $attribute->attribute_name, $attribute->attribute_label );
  } else {
   jofme_filter_select( $taxonomy, 'filter_' . $attribute->attribute_name, $attribute->attribute_label );
  }
 }

 if ( $show_collection ) {
  jofme_filter_select( 'product_tag', 'jofme_collection', __( 'Collection', 'jofme' ) );
 }

 if ( $show_price ) {
  jofme_filter_price_slider();
 }
 echo '</div>';
 echo '<div class="jofme-filter-actions"><button type="submit">' . esc_html__( 'Apply filters', 'jofme' ) . '</button><a href="' . esc_url( $action ) . '">' . esc_html__( 'Clear filters', 'jofme' ) . '</a></div>';
 echo '</form>';

 echo '<div class="jofme-active-filters">';
 foreach ( array_keys( $_GET ) as $key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public catalog query args.
  if ( ! is_string( $key ) ) {
   continue;
  }
  $is_filter = ( 0 === strpos( $key, 'filter_' ) || in_array( $key, array( 'min_price', 'max_price', 'jofme_collection' ), true ) );
  if ( ! $is_filter ) {
   continue;
  }
  $raw = jofme_filter_value( $key );
  if ( '' === $raw ) {
   continue;
  }
  if ( in_array( $key, array( 'min_price', 'max_price' ), true ) ) {
   echo '<a href="' . esc_url( remove_query_arg( array( $key, 'paged', 'product-page' ) ) ) . '">' . esc_html( $raw ) . ' × <span class="screen-reader-text">' . esc_html__( 'Remove filter', 'jofme' ) . '</span></a>';
   continue;
  }
  foreach ( jofme_filter_selected_slugs( $key ) as $slug ) {
   echo '<a href="' . esc_url( jofme_filter_remove_url( $key, $slug ) ) . '">' . esc_html( $slug ) . ' × <span class="screen-reader-text">' . esc_html__( 'Remove filter', 'jofme' ) . '</span></a>';
  }
 }
 echo '</div></div>';
}
add_action( 'jofme_shop_sidebar_filters', 'jofme_catalog_filters' );

/**
 * Hide WooCommerce filter widgets in Shop Sidebar so attributes are not listed twice.
 *
 * Theme auto-filters (Customizer) are the single source for catalog filters.
 *
 * @param array     $instance Widget settings.
 * @param WP_Widget $widget   Widget instance.
 * @param array     $args     Sidebar args.
 * @return array|false
 */
function jofme_suppress_duplicate_shop_filter_widgets( $instance, $widget, $args ) {
 if ( false === $instance || ! is_array( $args ) || empty( $args['id'] ) || 'shop-sidebar' !== $args['id'] ) {
  return $instance;
 }
 if ( ! function_exists( 'is_shop' ) || ( ! is_shop() && ! is_product_taxonomy() ) ) {
  return $instance;
 }
 $id_base = isset( $widget->id_base ) ? (string) $widget->id_base : '';
 $suppress = array(
  'woocommerce_layered_nav',
  'woocommerce_layered_nav_filters',
  'woocommerce_price_filter',
  'woocommerce_rating_filter',
  'woocommerce_attribute_filter',
 );
 if ( in_array( $id_base, $suppress, true ) ) {
  return false;
 }
 return $instance;
}
add_filter( 'widget_display_callback', 'jofme_suppress_duplicate_shop_filter_widgets', 10, 3 );

/**
 * Min/max product prices for the catalog price slider (scoped to archive when applicable).
 *
 * @return array{min:float,max:float,step:float}|null Null when unavailable.
 */
function jofme_catalog_price_bounds() {
 static $bounds = null;
 static $done   = false;
 if ( $done ) {
  return $bounds;
 }
 $done = true;

 global $wpdb;
 if ( empty( $wpdb->wc_product_meta_lookup ) ) {
  $bounds = null;
  return $bounds;
 }

 $object_ids = function_exists( 'jofme_catalog_filter_object_ids' ) ? jofme_catalog_filter_object_ids() : null;
 if ( is_array( $object_ids ) ) {
  if ( ! $object_ids ) {
   $bounds = null;
   return $bounds;
  }
  $ids = implode( ',', array_map( 'absint', $object_ids ) );
  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- IDs are absint-cast.
  $row = $wpdb->get_row( "SELECT MIN(min_price) AS min_price, MAX(max_price) AS max_price FROM {$wpdb->wc_product_meta_lookup} WHERE product_id IN ({$ids})" );
 } else {
  $row = $wpdb->get_row(
   "SELECT MIN(lookup.min_price) AS min_price, MAX(lookup.max_price) AS max_price
   FROM {$wpdb->wc_product_meta_lookup} AS lookup
   INNER JOIN {$wpdb->posts} AS posts ON lookup.product_id = posts.ID
   WHERE posts.post_type IN ('product') AND posts.post_status = 'publish'"
  );
 }

 $min = isset( $row->min_price ) ? (float) $row->min_price : 0.0;
 $max = isset( $row->max_price ) ? (float) $row->max_price : 0.0;
 if ( $max <= 0 && $min <= 0 ) {
  $bounds = null;
  return $bounds;
 }
 $min  = floor( $min );
 $max  = ceil( $max );
 if ( $max <= $min ) {
  $max = $min + 1;
 }
 $step   = ( $max - $min ) >= 10 ? 1.0 : 0.01;
 $bounds = array(
  'min'  => $min,
  'max'  => $max,
  'step' => $step,
 );
 return $bounds;
}

/**
 * Dual-range price slider for the catalog Filters panel.
 */
function jofme_filter_price_slider() {
 $bounds = jofme_catalog_price_bounds();
 if ( ! $bounds ) {
  return;
 }

 $floor = (float) $bounds['min'];
 $ceil  = (float) $bounds['max'];
 $step  = (float) $bounds['step'];
 $symbol = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '';

 $current_min = jofme_filter_value( 'min_price' );
 $current_max = jofme_filter_value( 'max_price' );
 $min_value   = '' !== $current_min ? max( $floor, min( $ceil, (float) $current_min ) ) : $floor;
 $max_value   = '' !== $current_max ? max( $floor, min( $ceil, (float) $current_max ) ) : $ceil;
 if ( $min_value > $max_value ) {
  $tmp        = $min_value;
  $min_value  = $max_value;
  $max_value  = $tmp;
 }

 $min_name = ( abs( $min_value - $floor ) > 0.0001 ) ? (string) $min_value : '';
 $max_name = ( abs( $max_value - $ceil ) > 0.0001 ) ? (string) $max_value : '';

 $format = static function ( $amount ) use ( $symbol, $step ) {
  $decimals = ( $step < 1 ) ? 2 : 0;
  $number   = number_format_i18n( (float) $amount, $decimals );
  return $symbol ? ( $symbol . $number ) : $number;
 };

 echo '<details class="jofme-filter-panel" open data-filter-key="price">';
 echo '<summary class="jofme-filter-panel__summary">' . esc_html__( 'Price', 'jofme' ) . '</summary>';
 echo '<div class="jofme-filter-panel__price jofme-price-slider" data-jofme-price-slider';
 echo ' data-min="' . esc_attr( (string) $floor ) . '" data-max="' . esc_attr( (string) $ceil ) . '" data-step="' . esc_attr( (string) $step ) . '" data-currency="' . esc_attr( $symbol ) . '">';
 echo '<div class="jofme-price-slider__values" aria-live="polite">';
 echo '<span class="jofme-price-slider__value" data-jofme-price-min-label>' . esc_html( $format( $min_value ) ) . '</span>';
 echo '<span class="jofme-price-slider__sep" aria-hidden="true">–</span>';
 echo '<span class="jofme-price-slider__value" data-jofme-price-max-label>' . esc_html( $format( $max_value ) ) . '</span>';
 echo '</div>';
 echo '<div class="jofme-price-slider__track">';
 echo '<div class="jofme-price-slider__range" data-jofme-price-range></div>';
 echo '<input type="range" class="jofme-price-slider__input jofme-price-slider__input--min" min="' . esc_attr( (string) $floor ) . '" max="' . esc_attr( (string) $ceil ) . '" step="' . esc_attr( (string) $step ) . '" value="' . esc_attr( (string) $min_value ) . '" aria-label="' . esc_attr__( 'Minimum price', 'jofme' ) . '">';
 echo '<input type="range" class="jofme-price-slider__input jofme-price-slider__input--max" min="' . esc_attr( (string) $floor ) . '" max="' . esc_attr( (string) $ceil ) . '" step="' . esc_attr( (string) $step ) . '" value="' . esc_attr( (string) $max_value ) . '" aria-label="' . esc_attr__( 'Maximum price', 'jofme' ) . '">';
 echo '</div>';
 echo '<input type="hidden" name="min_price" value="' . esc_attr( $min_name ) . '" data-jofme-price-min>';
 echo '<input type="hidden" name="max_price" value="' . esc_attr( $max_name ) . '" data-jofme-price-max>';
 echo '</div></details>';
}

/**
 * Product count for a filter term within the current archive scope.
 *
 * @param WP_Term     $term       Term.
 * @param int[]|null  $object_ids Scoped product IDs, or null for global shop.
 * @return int
 */
function jofme_catalog_filter_term_count( $term, $object_ids = null ) {
 if ( ! ( $term instanceof WP_Term ) ) {
  return 0;
 }
 if ( null === $object_ids && function_exists( 'jofme_catalog_filter_object_ids' ) ) {
  $object_ids = jofme_catalog_filter_object_ids();
 }
 if ( ! is_array( $object_ids ) ) {
  return absint( $term->count );
 }
 if ( ! $object_ids ) {
  return 0;
 }
 $objects = get_objects_in_term( (int) $term->term_id, $term->taxonomy );
 if ( is_wp_error( $objects ) || ! $objects ) {
  return 0;
 }
 return count( array_intersect( array_map( 'intval', $objects ), array_map( 'intval', $object_ids ) ) );
}

/**
 * URL that removes one selected filter slug (or the whole key when $slug is empty).
 *
 * @param string $key  Query arg.
 * @param string $slug Optional single term slug to remove.
 * @return string
 */
function jofme_filter_remove_url( $key, $slug = '' ) {
 $attr_key = ( 0 === strpos( $key, 'filter_' ) ) ? 'query_type_' . substr( $key, 7 ) : '';
 $drop     = array( 'paged', 'product-page' );
 if ( '' === $slug ) {
  $drop[] = $key;
  if ( $attr_key ) {
   $drop[] = $attr_key;
  }
  return remove_query_arg( $drop );
 }
 $remaining = array_values( array_diff( jofme_filter_selected_slugs( $key ), array( sanitize_title( $slug ) ) ) );
 if ( ! $remaining ) {
  $drop[] = $key;
  if ( $attr_key ) {
   $drop[] = $attr_key;
  }
  return remove_query_arg( $drop );
 }
 return remove_query_arg( array( 'paged', 'product-page' ), add_query_arg( $key, implode( ',', $remaining ) ) );
}

/**
 * Accordion checklist filter (checkbox + label + count), matching the shop sidebar layout.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $key      Query arg.
 * @param string $label    Panel title.
 */
function jofme_filter_checklist( $taxonomy, $key, $label ) {
 $terms = function_exists( 'jofme_catalog_filter_terms' )
  ? jofme_catalog_filter_terms( $taxonomy )
  : get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
 if ( is_wp_error( $terms ) || ! $terms ) {
  return;
 }

 $selected    = jofme_filter_selected_slugs( $key );
 $object_ids  = function_exists( 'jofme_catalog_filter_object_ids' ) ? jofme_catalog_filter_object_ids() : null;
 $has_options = false;

 ob_start();
 echo '<ul class="jofme-filter-panel__list">';
 foreach ( $terms as $term ) {
  $count = jofme_catalog_filter_term_count( $term, $object_ids );
  if ( $count < 1 ) {
   continue;
  }
  $has_options = true;
  $id          = 'jofme-filter-' . sanitize_html_class( $key . '-' . $term->slug );
  $is_checked  = in_array( $term->slug, $selected, true );
  echo '<li class="jofme-filter-panel__item">';
  echo '<label class="jofme-filter-panel__option" for="' . esc_attr( $id ) . '">';
  echo '<input id="' . esc_attr( $id ) . '" class="jofme-filter-panel__checkbox" type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $term->slug ) . '" ' . checked( $is_checked, true, false ) . '>';
  echo '<span class="jofme-filter-panel__name">' . esc_html( $term->name ) . '</span>';
  echo '<span class="jofme-filter-panel__count">' . absint( $count ) . '</span>';
  echo '</label></li>';
 }
 echo '</ul>';
 $list_html = ob_get_clean();

 if ( ! $has_options ) {
  return;
 }

 echo '<details class="jofme-filter-panel" open data-filter-key="' . esc_attr( $key ) . '">';
 echo '<summary class="jofme-filter-panel__summary">' . esc_html( $label ) . '</summary>';
 echo $list_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with escaped helpers above.
 if ( 0 === strpos( $key, 'filter_' ) ) {
  // Multi-select attributes use OR so checking several metals keeps matching products.
  echo '<input type="hidden" name="query_type_' . esc_attr( substr( $key, 7 ) ) . '" value="or">';
 }
 echo '</details>';
}

/**
 * Backward-compatible alias: catalog filters render as accordion checklists.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $key      Query arg.
 * @param string $label    Panel title.
 */
function jofme_filter_select( $taxonomy, $key, $label ) {
 jofme_filter_checklist( $taxonomy, $key, $label );
}

/**
 * Apply the Collection (product_tag) filter from the catalog form.
 *
 * @param WC_Query $query Product query.
 */
function jofme_collection_query( $query ) {
 $slugs = jofme_filter_selected_slugs( 'jofme_collection' );
 if ( ! $slugs ) {
  return;
 }
 $tax = $query->get( 'tax_query' );
 $query->set(
  'tax_query',
  array(
   'relation' => 'AND',
   is_array( $tax ) ? $tax : array(),
   array(
    'taxonomy' => 'product_tag',
    'field'    => 'slug',
    'terms'    => $slugs,
    'operator' => 'IN',
   ),
  )
 );
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
 * Open AJAX-replaceable catalog results wrapper (toolbar + products + pagination).
 */
function jofme_catalog_results_open() {
 if ( ! is_shop() && ! is_product_taxonomy() ) {
  return;
 }
 if ( ! empty( $GLOBALS['jofme_catalog_results_open'] ) ) {
  return;
 }
 $GLOBALS['jofme_catalog_results_open'] = true;
 echo '<div class="jofme-catalog-results" id="jofme-catalog-results" data-jofme-catalog-results="1" aria-live="polite">';
}

/**
 * Close AJAX-replaceable catalog results wrapper.
 */
function jofme_catalog_results_close() {
 if ( empty( $GLOBALS['jofme_catalog_results_open'] ) ) {
  return;
 }
 $GLOBALS['jofme_catalog_results_open'] = false;
 echo '</div>';
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
add_action( 'woocommerce_before_shop_loop', 'jofme_catalog_results_open', 5 );
add_action( 'woocommerce_before_shop_loop', 'jofme_catalog_toolbar', 20 );
add_action( 'woocommerce_after_shop_loop', 'jofme_catalog_results_close', 99 );
add_action( 'woocommerce_no_products_found', 'jofme_catalog_results_open', 0 );
add_action( 'woocommerce_no_products_found', 'jofme_shop_sidebar_toggle', 5 );
add_action( 'woocommerce_no_products_found', 'jofme_catalog_results_close', 99 );

function jofme_card_actions() {
 global $product;
 if ( ! $product ) {
  return;
 }
 $wishlist = function_exists( 'jofme_wishlist_button_html' ) ? jofme_wishlist_button_html( $product ) : '';
 echo '<div class="jofme-card-actions">';
 echo $wishlist; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with escaped helpers.
 echo '<button type="button" class="jofme-preview" data-product="' . absint( $product->get_id() ) . '" aria-label="' . esc_attr__( 'Quick view', 'jofme' ) . '"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button><a href="' . esc_url( get_permalink( $product->get_id() ) ) . '" aria-label="' . esc_attr__( 'Product details', 'jofme' ) . '"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14"/></svg></a></div>';
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
 $card_choices = function_exists( 'jofme_product_card_layout_choices' )
  ? jofme_product_card_layout_choices()
  : array(
   'bestsellers' => __( 'Bestsellers (tabs card)', 'jofme' ),
   'minimal'     => __( 'Minimal', 'jofme' ),
   'framed'      => __( 'Framed', 'jofme' ),
   'editorial'   => __( 'Editorial', 'jofme' ),
   'gallery'     => __( 'Gallery (minimal product grid)', 'jofme' ),
  );
 foreach (
  array(
   'jofme_card_layout'  => array( __( 'Product card layout', 'jofme' ), 'bestsellers', $card_choices ),
   'jofme_default_view' => array( __( 'Default catalog view', 'jofme' ), jofme_catalog_default_view(), array( '1' => '1 col', '2' => '2 col', '3' => '3 col', '4' => '4 col', 'list' => __( 'List', 'jofme' ) ) ),
  ) as $key => $config
 ) {
  $manager->add_setting( $key, array( 'default' => $config[1], 'sanitize_callback' => 'jofme_sanitize_select' ) );
  $manager->add_control( $key, array( 'label' => $config[0], 'section' => 'jofme_shop', 'type' => 'select', 'choices' => $config[2] ) );
 }

 $manager->add_setting(
  'jofme_card_title_size',
  array(
   'default'           => 30,
   'sanitize_callback' => 'jofme_sanitize_card_title_size',
  )
 );
 $manager->add_control(
  'jofme_card_title_size',
  array(
   'label'       => __( 'Product card title size (px)', 'jofme' ),
   'description' => __( 'Applies to Bestsellers-style shop cards and the Bestsellers Tabs widget. Scales down automatically on small screens.', 'jofme' ),
   'section'     => 'jofme_shop',
   'type'        => 'number',
   'input_attrs' => array(
    'min' => 12,
    'max' => 40,
   ),
  )
 );

 $manager->add_setting(
  'jofme_card_icon_size',
  array(
   'default'           => 40,
   'sanitize_callback' => 'jofme_sanitize_card_icon_size',
  )
 );
 $manager->add_control(
  'jofme_card_icon_size',
  array(
   'label'       => __( 'Product card icon size (px)', 'jofme' ),
   'description' => __( 'Quick view, product link, and wishlist heart buttons on product cards.', 'jofme' ),
   'section'     => 'jofme_shop',
   'type'        => 'number',
   'input_attrs' => array(
    'min' => 28,
    'max' => 52,
   ),
  )
 );
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

 $manager->add_setting( 'jofme_filter_show_collection', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
 $manager->add_control(
  'jofme_filter_show_collection',
  array(
   'label'       => __( 'Show Collection filter', 'jofme' ),
   'description' => __( 'Filters panel: enable/disable Collection, Price, and each product attribute below. New attributes are on by default. WooCommerce filter widgets in Shop Sidebar are hidden so attributes are not listed twice.', 'jofme' ),
   'section'     => 'jofme_shop',
   'type'        => 'checkbox',
  )
 );
 $manager->add_setting( 'jofme_filter_show_price', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
 $manager->add_control(
  'jofme_filter_show_price',
  array(
   'label'   => __( 'Show Price range filter', 'jofme' ),
   'section' => 'jofme_shop',
   'type'    => 'checkbox',
  )
 );

 if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
  foreach ( wc_get_attribute_taxonomies() as $attribute ) {
   if ( empty( $attribute->attribute_name ) ) {
    continue;
   }
   $mod_key = jofme_catalog_filter_attr_mod_key( $attribute->attribute_name );
   $label   = ! empty( $attribute->attribute_label )
    ? $attribute->attribute_label
    : $attribute->attribute_name;
   $manager->add_setting(
    $mod_key,
    array(
     'default'           => true,
     'sanitize_callback' => 'wp_validate_boolean',
    )
   );
   $manager->add_control(
    $mod_key,
    array(
     /* translators: %s: attribute label */
     'label'   => sprintf( __( 'Show “%s” filter', 'jofme' ), $label ),
     'section' => 'jofme_shop',
     'type'    => 'checkbox',
    )
   );
  }
 }
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

/**
 * Sanitize product card title size (12–40).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_card_title_size( $value ) {
 return max( 12, min( 40, absint( $value ) ) );
}

/**
 * Sanitize product card icon button size (28–52).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_card_icon_size( $value ) {
 return max( 28, min( 52, absint( $value ) ) );
}

/**
 * Enqueue product card styles + Customizer CSS variables.
 */
function jofme_product_card_assets() {
 wp_enqueue_style( 'jofme-product-cards', JOFME_URI . '/assets/css/product-cards.css', array( 'jofme-premium' ), JOFME_VERSION );

 $title = jofme_sanitize_card_title_size( get_theme_mod( 'jofme_card_title_size', 30 ) );
 $icon  = jofme_sanitize_card_icon_size( get_theme_mod( 'jofme_card_icon_size', 40 ) );
 $icon_svg = max( 16, (int) round( $icon * 0.5 ) );
 $title_mobile = max( 14, (int) round( $title * 0.7 ) );
 $icon_mobile  = max( 30, (int) round( $icon * 0.85 ) );
 $icon_svg_m   = max( 14, (int) round( $icon_mobile * 0.5 ) );

 $css  = ':root{';
 $css .= '--jofme-card-title-size:' . $title . 'px;';
 $css .= '--jofme-card-title-size-mobile:' . $title_mobile . 'px;';
 $css .= '--jofme-card-icon-size:' . $icon . 'px;';
 $css .= '--jofme-card-icon-svg:' . $icon_svg . 'px;';
 $css .= '--jofme-card-icon-size-mobile:' . $icon_mobile . 'px;';
 $css .= '--jofme-card-icon-svg-mobile:' . $icon_svg_m . 'px;';
 $css .= '}';
 wp_add_inline_style( 'jofme-product-cards', $css );
}
add_action( 'wp_enqueue_scripts', 'jofme_product_card_assets', 28 );
