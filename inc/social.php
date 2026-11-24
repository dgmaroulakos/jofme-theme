<?php
/**
 * Social media links for header and footer.
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Supported social networks (Customizer keys → labels).
 *
 * @return array<string,string>
 */
function jofme_social_networks() {
	return array(
		'instagram' => __( 'Instagram', 'jofme' ),
		'facebook'  => __( 'Facebook', 'jofme' ),
		'tiktok'    => __( 'TikTok', 'jofme' ),
		'pinterest' => __( 'Pinterest', 'jofme' ),
		'youtube'   => __( 'YouTube', 'jofme' ),
		'x'         => __( 'X (Twitter)', 'jofme' ),
	);
}

/**
 * Customizer: Social section.
 *
 * @param WP_Customize_Manager $manager Manager.
 */
function jofme_social_customize( $manager ) {
	$manager->add_section(
		'jofme_social',
		array(
			'title'       => __( 'Social media', 'jofme' ),
			'description' => __( 'Add profile URLs to show icons in the header and footer. Leave a field empty to hide that network.', 'jofme' ),
			'panel'       => 'jofme_options',
			'priority'    => 35,
		)
	);

	$manager->add_setting(
		'jofme_social_in_header',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$manager->add_control(
		'jofme_social_in_header',
		array(
			'label'   => __( 'Show social icons in header', 'jofme' ),
			'section' => 'jofme_social',
			'type'    => 'checkbox',
		)
	);

	$manager->add_setting(
		'jofme_social_in_footer',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$manager->add_control(
		'jofme_social_in_footer',
		array(
			'label'   => __( 'Show social icons in footer', 'jofme' ),
			'section' => 'jofme_social',
			'type'    => 'checkbox',
		)
	);

	foreach ( jofme_social_networks() as $slug => $label ) {
		$id = 'jofme_social_' . $slug;
		$manager->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$manager->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'jofme_social',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'jofme_social_customize', 28 );

/**
 * Non-empty social links from Customizer.
 *
 * @return array<string,array{label:string,url:string}>
 */
function jofme_social_links() {
	$out = array();
	foreach ( jofme_social_networks() as $slug => $label ) {
		$url = get_theme_mod( 'jofme_social_' . $slug, '' );
		$url = is_string( $url ) ? esc_url_raw( trim( $url ) ) : '';
		if ( '' === $url ) {
			continue;
		}
		$out[ $slug ] = array(
			'label' => $label,
			'url'   => $url,
		);
	}
	return $out;
}

/**
 * Inline SVG icon for a social network.
 *
 * @param string $slug Network slug.
 * @return string
 */
function jofme_social_icon_svg( $slug ) {
	$icons = array(
		'instagram' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5" stroke="currentColor" stroke-width="1.4"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor"/></svg>',
		'facebook'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 8h2.5V4.5H14c-2.5 0-4 1.6-4 4V11H7.5v3.5H10V20h3.5v-5.5H16L16.8 11H13.5V8.8c0-.5.3-.8.5-.8z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>',
		'tiktok'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 4v10.2a3.8 3.8 0 1 1-2.6-3.6V8.2A6.4 6.4 0 0 0 14 8.5V4h2.2A4.8 4.8 0 0 0 20 8.2V10a7 7 0 0 1-3.8-1.1V14A6.2 6.2 0 1 1 9.2 8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'pinterest' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.4"/><path d="M11 20.2c.4-2.2.8-4.2 1.2-5.4.2-.6-.1-1.2-.7-1.4-1.4-.4-2.3-1.6-2.3-3.1 0-2 1.7-3.5 4-3.5 2.1 0 3.5 1.2 3.5 3.1 0 2.1-1 3.9-2.4 3.9-.5 0-.9-.3-1-.7l-.5 2.1" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'youtube'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="2.5" y="6" width="19" height="12" rx="3.5" stroke="currentColor" stroke-width="1.4"/><path d="M11 9.5v5l4.5-2.5L11 9.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>',
		'x'         => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5l14 14M19 5 5 19" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>',
	);

	return isset( $icons[ $slug ] ) ? $icons[ $slug ] : '';
}

/**
 * Whether social icons should render in a given context.
 *
 * @param string $context header|footer|mobile.
 * @return bool
 */
function jofme_social_show_in( $context ) {
	$links = jofme_social_links();
	if ( ! $links ) {
		return false;
	}
	if ( 'header' === $context || 'mobile' === $context ) {
		return (bool) get_theme_mod( 'jofme_social_in_header', true );
	}
	if ( 'footer' === $context ) {
		return (bool) get_theme_mod( 'jofme_social_in_footer', true );
	}
	return false;
}

/**
 * Print social icons list.
 *
 * @param string $context header|footer|mobile.
 */
function jofme_social_nav( $context = 'footer' ) {
	if ( ! jofme_social_show_in( $context ) ) {
		return;
	}

	$links = jofme_social_links();
	if ( ! $links ) {
		return;
	}

	$classes = array(
		'jofme-social',
		'jofme-social--' . sanitize_html_class( $context ),
	);

	echo '<nav class="' . esc_attr( implode( ' ', $classes ) ) . '" aria-label="' . esc_attr__( 'Social media', 'jofme' ) . '">';
	echo '<ul class="jofme-social__list">';
	foreach ( $links as $slug => $item ) {
		$svg = jofme_social_icon_svg( $slug );
		if ( ! $svg ) {
			continue;
		}
		printf(
			'<li class="jofme-social__item jofme-social__item--%1$s"><a class="jofme-social__link" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%4$s</a></li>',
			esc_attr( $slug ),
			esc_url( $item['url'] ),
			esc_attr( $item['label'] ),
			$svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hard-coded SVG markup.
		);
	}
	echo '</ul></nav>';
}
