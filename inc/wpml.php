<?php
/**
 * WPML / multilingual compatibility.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register strings that WPML / Polylang may need from Customizer.
 */
function jofme_wpml_register_strings() {
	if ( ! function_exists( 'icl_register_string' ) && ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$announcement = get_theme_mod( 'jofme_announcement_text', '' );
	$footer_copy  = get_theme_mod( 'jofme_footer_copyright', '' );

	if ( function_exists( 'icl_register_string' ) ) {
		if ( $announcement ) {
			icl_register_string( 'Jofme', 'Announcement bar', $announcement );
		}
		if ( $footer_copy ) {
			icl_register_string( 'Jofme', 'Footer copyright', $footer_copy );
		}
	}

	if ( function_exists( 'pll_register_string' ) ) {
		if ( $announcement ) {
			pll_register_string( 'jofme_announcement', $announcement, 'Jofme', true );
		}
		if ( $footer_copy ) {
			pll_register_string( 'jofme_footer_copyright', $footer_copy, 'Jofme', true );
		}
	}
}
add_action( 'init', 'jofme_wpml_register_strings' );

/**
 * Translate a Customizer string via WPML or Polylang when available.
 *
 * @param string $value  Original value.
 * @param string $name   String name.
 * @param string $pll_key Polylang key.
 * @return string
 */
function jofme_translate_mod( $value, $name, $pll_key ) {
	if ( ! $value ) {
		return $value;
	}

	if ( function_exists( 'icl_t' ) ) {
		return icl_t( 'Jofme', $name, $value );
	}

	if ( function_exists( 'pll__' ) ) {
		return pll__( $value );
	}

	return $value;
}

/**
 * Get translated announcement text.
 *
 * @return string
 */
function jofme_get_announcement_text() {
	$text = get_theme_mod( 'jofme_announcement_text', '' );
	return jofme_translate_mod( $text, 'Announcement bar', 'jofme_announcement' );
}

/**
 * Get translated footer copyright.
 *
 * @return string
 */
function jofme_get_footer_copyright() {
	$default = sprintf(
		/* translators: %1$s: year, %2$s: site name */
		__( '© %1$s %2$s. All rights reserved.', 'jofme' ),
		gmdate( 'Y' ),
		get_bloginfo( 'name' )
	);
	$text = get_theme_mod( 'jofme_footer_copyright', $default );
	return jofme_translate_mod( $text, 'Footer copyright', 'jofme_footer_copyright' );
}

/**
 * Make language switcher available in menus when WPML is active.
 *
 * @param array $items Menu items.
 * @param object $args Menu args.
 * @return array
 */
function jofme_wpml_menu_language_switcher( $items, $args ) {
	if ( 'primary' !== $args->theme_location ) {
		return $items;
	}

	if ( ! apply_filters( 'jofme_show_language_in_menu', false ) ) {
		return $items;
	}

	if ( function_exists( 'icl_get_languages' ) ) {
		$languages = icl_get_languages( 'skip_missing=0' );
		if ( empty( $languages ) ) {
			return $items;
		}
		foreach ( $languages as $lang ) {
			if ( ! empty( $lang['active'] ) ) {
				continue;
			}
			$items .= sprintf(
				'<li class="menu-item jofme-lang-item"><a href="%1$s">%2$s</a></li>',
				esc_url( $lang['url'] ),
				esc_html( strtoupper( $lang['language_code'] ) )
			);
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_items', 'jofme_wpml_menu_language_switcher', 10, 2 );
