<?php
/**
 * Theme setup.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function jofme_setup() {
	load_theme_textdomain( 'jofme', JOFME_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'jofme-product', 600, 750, true );
	add_image_size( 'jofme-product-thumb', 120, 150, true );
	add_image_size( 'jofme-hero', 1920, 900, true );
	add_image_size( 'jofme-blog', 800, 520, true );

	register_nav_menus(
		array(
			'primary'   => __( 'Primary Menu', 'jofme' ),
			'secondary' => __( 'Secondary Menu', 'jofme' ),
			'footer'    => __( 'Footer Menu', 'jofme' ),
			'mobile'    => __( 'Mobile Menu', 'jofme' ),
		)
	);
}
add_action( 'after_setup_theme', 'jofme_setup' );

/**
 * Content width.
 */
function jofme_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'jofme_content_width', 1200 );
}
add_action( 'after_setup_theme', 'jofme_content_width', 0 );

/**
 * Register widget areas.
 */
function jofme_widgets_init() {
	$sidebars = array(
		'sidebar-1'       => __( 'Blog Sidebar', 'jofme' ),
		'shop-sidebar'    => __( 'Shop Sidebar', 'jofme' ),
		'footer-1'        => __( 'Footer Column 1', 'jofme' ),
		'footer-2'        => __( 'Footer Column 2', 'jofme' ),
		'footer-3'        => __( 'Footer Column 3', 'jofme' ),
		'footer-4'        => __( 'Footer Column 4', 'jofme' ),
	);

	foreach ( $sidebars as $id => $name ) {
		register_sidebar(
			array(
				'name'          => $name,
				'id'            => $id,
				'description'   => sprintf(
					/* translators: %s: sidebar name */
					__( 'Widgets in %s.', 'jofme' ),
					$name
				),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'jofme_widgets_init' );

/**
 * Body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function jofme_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	if ( is_active_sidebar( 'sidebar-1' ) && ( is_home() || is_archive() || is_singular( 'post' ) ) ) {
		$classes[] = 'has-sidebar';
	}

	$header_layout = get_theme_mod( 'jofme_header_layout', 'classic' );
	$classes[]     = 'header-' . sanitize_html_class( $header_layout );

	if ( get_theme_mod( 'jofme_sticky_header', true ) ) {
		$classes[] = 'has-sticky-header';
	}

	return $classes;
}
add_filter( 'body_class', 'jofme_body_classes' );
