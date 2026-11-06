<?php
/**
 * Elementor compatibility.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Elementor theme locations (Theme Builder).
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager Manager.
 */
function jofme_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'jofme_register_elementor_locations' );

/**
 * Check if an Elementor location has a template.
 *
 * @param string $location Location slug.
 * @return bool
 */
function jofme_elementor_location_exists( $location ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}

	if ( ! class_exists( '\ElementorPro\Plugin' ) ) {
		return false;
	}

	$documents = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' );
	if ( ! $documents ) {
		return false;
	}

	$conditions_manager = $documents->get_conditions_manager();
	$documents_for_loc  = $conditions_manager->get_documents_for_location( $location );

	return ! empty( $documents_for_loc );
}

/**
 * Render Elementor location if available.
 *
 * @param string $location Location slug.
 * @return bool True if rendered.
 */
function jofme_elementor_do_location( $location ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) ) {
		return false;
	}

	return elementor_theme_do_location( $location );
}

/**
 * Add Elementor support flags.
 */
function jofme_elementor_support() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}

	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'jofme_elementor_support' );

/**
 * Widen Elementor default content width to match theme.
 *
 * @return int
 */
function jofme_elementor_content_width() {
	return 1200;
}
add_filter( 'elementor/theme/get_content_width', 'jofme_elementor_content_width' );

/**
 * Post CSS print method compatibility note: disable theme wrappers on canvas.
 *
 * @param array $classes Body classes.
 * @return array
 */
function jofme_elementor_canvas_body_class( $classes ) {
	if ( class_exists( '\Elementor\Plugin' ) ) {
		$page_template = get_page_template_slug();
		if ( 'elementor_canvas' === $page_template || 'elementor_header_footer' === $page_template ) {
			$classes[] = 'jofme-elementor-template';
		}
	}
	return $classes;
}
add_filter( 'body_class', 'jofme_elementor_canvas_body_class' );
