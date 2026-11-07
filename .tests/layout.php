<?php
/** Run without WordPress: php .tests/layout.php */
define( 'ABSPATH', __DIR__ );
function add_action() {} function add_filter() {}
function __( $text ) { return $text; }
$context = ''; $mods = array(); $widgets = array();
function is_product() { global $context; return 'product' === $context; }
function is_product_taxonomy() { global $context; return 'catalog' === $context; }
function is_shop() { global $context; return 'shop' === $context; }
function is_category() { global $context; return 'category' === $context; }
function is_page() { global $context; return 'page' === $context; }
function get_theme_mod( $key, $default ) { global $mods; return isset( $mods[$key] ) ? $mods[$key] : $default; }
function is_active_sidebar( $id ) { global $widgets; return in_array( $id, $widgets, true ); }
function verify( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
require dirname( __DIR__ ) . '/inc/layout.php';
foreach ( array( 'page' => 'page', 'category' => 'category', 'catalog' => 'catalog', 'shop' => 'catalog', 'product' => 'product', '' => '' ) as $input => $expected ) {
 $context = $input; verify( $expected === jofme_layout_context(), 'Context: ' . $input );
}
$context = 'page'; $mods['jofme_page_sidebar'] = 'left';
verify( ! jofme_layout_has_sidebar(), 'Empty sidebar must collapse' );
$widgets = array( 'page-sidebar' );
verify( jofme_layout_has_sidebar(), 'Populated sidebar must appear' );
$classes = jofme_layout_body_classes( array( 'has-sidebar' ) );
verify( in_array( 'jofme-sidebar-left', $classes, true ) && ! in_array( 'has-sidebar', $classes, true ), 'Override legacy classes' );
$mods['jofme_page_sidebar'] = 'none'; verify( ! jofme_layout_has_sidebar(), 'Explicit no sidebar' );
$mods['jofme_page_width'] = 'invalid'; verify( 'boxed' === jofme_layout_option( 'page', 'width' ), 'Invalid width fallback' );
$mods['jofme_page_sidebar'] = 'invalid'; verify( 'none' === jofme_layout_option( 'page', 'sidebar' ), 'Invalid sidebar fallback' );
$context = 'product'; $mods['jofme_product_gallery_layout'] = 'right';
$css = file_get_contents( dirname( __DIR__ ) . '/assets/css/main.css' );
verify( (bool) preg_match( '/\.jofme-container\s*\{[^}]*--jofme-layout-width/s', $css ), 'Containers follow layout width' );
$layout_css = file_get_contents( dirname( __DIR__ ) . '/assets/css/layout.css' );
verify( false !== strpos( $layout_css, 'body.jofme-width-boxed' ), 'Boxed width sets layout variable' );
verify( false !== strpos( $layout_css, '.site-header .jofme-container' ), 'Header container follows layout width' );
verify( in_array( 'jofme-gallery-right', jofme_layout_body_classes( array() ), true ), 'Product gallery layout' );
$mods['jofme_product_gallery_layout'] = 'invalid';
verify( in_array( 'jofme-gallery-left', jofme_layout_body_classes( array() ), true ), 'Invalid gallery fallback' );
$context = 'catalog'; $widgets = array( 'shop-sidebar' ); $mods['jofme_catalog_sidebar'] = 'left';
$mods['jofme_catalog_sidebar_effect'] = 'slide-bottom';
$classes = jofme_layout_body_classes( array() );
verify( in_array( 'jofme-shop-sidebar-offcanvas', $classes, true ), 'Catalog off-canvas class' );
verify( in_array( 'jofme-sidebar-effect-slide-bottom', $classes, true ), 'Catalog off-canvas effect class' );
verify( 'slide-bottom' === jofme_catalog_sidebar_effect(), 'Catalog effect helper' );
$mods['jofme_catalog_sidebar_effect'] = 'nope';
verify( 'slide-left' === jofme_catalog_sidebar_effect(), 'Invalid effect fallback' );
$context = ''; verify( array( 'original' ) === jofme_layout_body_classes( array( 'original' ) ), 'Unrelated contexts unchanged' );
echo "PASS: layout contexts, empty sidebars, positions, invalid settings, product gallery, off-canvas effect\n";
