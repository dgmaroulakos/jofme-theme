<?php
/** Separate covers for WooCommerce product categories. @package Jofme */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

function jofme_category_cover_field( $term = null ) {
 $editing = $term instanceof WP_Term;
 $id = $editing ? absint( get_term_meta( $term->term_id, 'jofme_category_cover_id', true ) ) : 0;
 $url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
 if ( $editing ) { echo '<tr class="form-field"><th scope="row"><label for="jofme-category-cover-id">' . esc_html__( 'Category cover', 'jofme' ) . '</label></th><td>'; }
 else { echo '<div class="form-field"><label for="jofme-category-cover-id">' . esc_html__( 'Category cover', 'jofme' ) . '</label>'; }
 echo '<div class="jofme-category-cover-field"><input type="hidden" id="jofme-category-cover-id" name="jofme_category_cover_id" value="' . absint( $id ) . '">';
 wp_nonce_field( 'jofme_save_category_cover', 'jofme_category_cover_nonce' );
 echo '<div class="jofme-category-cover-preview"' . ( $url ? '' : ' hidden' ) . '><img' . ( $url ? ' src="' . esc_url( $url ) . '"' : '' ) . ' alt=""></div>';
 echo '<button type="button" class="button jofme-cover-select">' . esc_html__( 'Choose cover', 'jofme' ) . '</button> <button type="button" class="button jofme-cover-remove">' . esc_html__( 'Remove cover', 'jofme' ) . '</button></div>';
 echo '<p class="description">' . esc_html__( 'Wide image behind this category’s title, above products and sidebar. The category thumbnail remains separate.', 'jofme' ) . '</p>';
 echo $editing ? '</td></tr>' : '</div>';
}
add_action( 'product_cat_add_form_fields', 'jofme_category_cover_field' );
add_action( 'product_cat_edit_form_fields', 'jofme_category_cover_field' );
function jofme_category_cover_save( $term_id ) {
 if ( ! current_user_can( 'manage_product_terms' ) || ! isset( $_POST['jofme_category_cover_nonce'], $_POST['jofme_category_cover_id'] ) || ! is_scalar( $_POST['jofme_category_cover_nonce'] ) || ! is_scalar( $_POST['jofme_category_cover_id'] ) ) { return; }
 if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jofme_category_cover_nonce'] ) ), 'jofme_save_category_cover' ) ) { return; }
 $id = absint( $_POST['jofme_category_cover_id'] );
 if ( ! $id ) { delete_term_meta( $term_id, 'jofme_category_cover_id' ); }
 elseif ( wp_attachment_is_image( $id ) ) { update_term_meta( $term_id, 'jofme_category_cover_id', $id ); }
}
add_action( 'created_product_cat', 'jofme_category_cover_save' );
add_action( 'edited_product_cat', 'jofme_category_cover_save' );
function jofme_category_cover_admin_assets() {
 $screen = get_current_screen();
 if ( ! $screen || 'product_cat' !== $screen->taxonomy ) { return; }
 wp_enqueue_media();
 wp_enqueue_script( 'jofme-category-cover-admin', JOFME_URI . '/assets/js/category-cover-admin.js', array( 'media-views' ), JOFME_VERSION, true );
 wp_localize_script( 'jofme-category-cover-admin', 'jofmeCover', array( 'title' => __( 'Choose category cover', 'jofme' ), 'button' => __( 'Use as cover', 'jofme' ) ) );
 wp_enqueue_style( 'jofme-category-cover-admin', JOFME_URI . '/assets/css/category-cover-admin.css', array(), JOFME_VERSION );
}
add_action( 'admin_enqueue_scripts', 'jofme_category_cover_admin_assets' );
/**
 * Attachment ID for the current product category cover, if set.
 *
 * @return int
 */
function jofme_category_cover_id() {
 if ( ! is_product_category() ) {
  return 0;
 }
 $id = absint( get_term_meta( get_queried_object_id(), 'jofme_category_cover_id', true ) );
 return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
}

/**
 * Whether the current category archive has a cover image.
 *
 * @return bool
 */
function jofme_catalog_has_cover() {
 return (bool) jofme_category_cover_id();
}

function jofme_category_cover_image() {
 $id = jofme_category_cover_id();
 if ( ! $id ) {
  return '';
 }
 return wp_get_attachment_image( $id, 'full', false, array( 'class' => 'jofme-catalog-cover', 'alt' => '', 'aria-hidden' => 'true', 'loading' => 'eager', 'fetchpriority' => 'high' ) );
}
