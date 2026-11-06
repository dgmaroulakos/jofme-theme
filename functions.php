<?php
/**
 * Jofme theme bootstrap.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JOFME_VERSION', '1.2.1' );
define( 'JOFME_DIR', get_template_directory() );
define( 'JOFME_URI', get_template_directory_uri() );

$jofme_includes = array(
	'/inc/setup.php',
	'/inc/theme-updates.php',
	'/inc/enqueue.php',
	'/inc/template-tags.php',
	'/inc/customizer.php',
	'/inc/layout.php',
	'/inc/woocommerce.php',
	'/inc/premium-shop.php',
	'/inc/product-hover.php',
	'/inc/elementor.php',
	'/inc/wpml.php',
);

foreach ( $jofme_includes as $file ) {
	$path = JOFME_DIR . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
