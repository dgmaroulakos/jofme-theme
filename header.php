<?php
/**
 * Header template.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'jofme' ); ?></a>

	<?php
	if ( jofme_has_elementor_header() && jofme_elementor_do_location( 'header' ) ) {
		// Elementor Theme Builder header.
	} else {
		get_template_part( 'template-parts/header/site', 'header' );
	}
	?>
