<?php
/**
 * Stacked gallery helpers without a full WordPress bootstrap.
 */

define( 'ABSPATH', __DIR__ );

class WooCommerce {}
class WC_Product {}

function add_action() {}
function add_filter() {}
function remove_action() {}
function __ ( $text ) { return $text; }
function esc_html__( $text ) { return $text; }
function esc_attr__( $text ) { return $text; }
function get_theme_mod( $key, $default = false ) { return $default; }
function is_product() { return true; }

class GalleryProduct extends WC_Product {
 private $image_id;
 private $gallery;
 public function __construct( $image_id, $gallery ) {
  $this->image_id = $image_id;
  $this->gallery  = $gallery;
 }
 public function get_image_id() { return $this->image_id; }
 public function get_gallery_image_ids() { return $this->gallery; }
}

function verify( $expected, $actual, $label ) {
 if ( $expected !== $actual ) {
  throw new RuntimeException( $label . ' — expected ' . var_export( $expected, true ) . ' got ' . var_export( $actual, true ) );
 }
}

require dirname( __DIR__ ) . '/inc/gallery-design.php';

verify( array(), jofme_stacked_gallery_ids( null ), 'missing product' );
verify( array( 10, 20, 30 ), jofme_stacked_gallery_ids( new GalleryProduct( 10, array( 20, 30 ) ) ), 'featured plus gallery' );
verify( array( 10, 20 ), jofme_stacked_gallery_ids( new GalleryProduct( 10, array( 10, 20, 10 ) ) ), 'dedupe featured from gallery' );
verify( array( 20, 30 ), jofme_stacked_gallery_ids( new GalleryProduct( 0, array( 20, 30 ) ) ), 'gallery without featured' );
verify( array(), jofme_stacked_gallery_ids( new GalleryProduct( 0, array() ) ), 'empty product images' );

echo "PASS: stacked gallery ids, dedupe, empty and featured-only cases\n";
