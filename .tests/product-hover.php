<?php
/** Gallery fallback behavior without WordPress. */
define( 'ABSPATH', __DIR__ );
class WooCommerce {}
function add_action() {} function remove_action() {}
$enabled = false;
function get_theme_mod( $key, $default ) { global $enabled; return $enabled; }
function absint( $id ) { return abs( (int) $id ); }
function wp_attachment_is_image( $id ) { return in_array( $id, array( 10, 20, 30 ), true ); }
class HoverProduct {
 private $gallery;
 public function __construct( $gallery ) { $this->gallery = $gallery; }
 public function get_image_id() { return 10; }
 public function get_gallery_image_ids() { return $this->gallery; }
}
function verify( $expected, $actual ) { if ( $expected !== $actual ) { throw new RuntimeException( 'Unexpected hover image: ' . $actual ); } }
require dirname( __DIR__ ) . '/inc/product-hover.php';
verify( 0, jofme_hover_image_id( new HoverProduct( array( 20 ) ) ) );
$enabled = true;
verify( 0, jofme_hover_image_id( null ) );
verify( 0, jofme_hover_image_id( new HoverProduct( array() ) ) );
verify( 0, jofme_hover_image_id( new HoverProduct( array( 10, 99 ) ) ) );
verify( 20, jofme_hover_image_id( new HoverProduct( array( 10, 99, 20, 30 ) ) ) );
echo "PASS: hover disabled, missing gallery, duplicate featured, invalid attachment, first valid gallery image\n";
