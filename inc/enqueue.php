<?php
/**
 * Scripts and styles.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue front-end assets.
 */
function jofme_scripts() {
	wp_enqueue_style(
		'jofme-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Manrope:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'jofme-main',
		JOFME_URI . '/assets/css/main.css',
		array( 'jofme-fonts' ),
		JOFME_VERSION
	);

	wp_enqueue_script(
		'jofme-main',
		JOFME_URI . '/assets/js/main.js',
		array(),
		JOFME_VERSION,
		true
	);

	wp_localize_script(
		'jofme-main',
		'jofmeData',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'jofme_nonce' ),
			'i18n'    => array(
				'menuOpen'  => __( 'Open menu', 'jofme' ),
				'menuClose' => __( 'Close menu', 'jofme' ),
				'search'    => __( 'Search', 'jofme' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'jofme_scripts' );
