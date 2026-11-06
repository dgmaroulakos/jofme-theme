<?php
/**
 * Theme Customizer.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function jofme_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'jofme_options',
		array(
			'title'    => __( 'Jofme Options', 'jofme' ),
			'priority' => 30,
		)
	);

	// Header.
	$wp_customize->add_section(
		'jofme_header',
		array(
			'title' => __( 'Header', 'jofme' ),
			'panel' => 'jofme_options',
		)
	);

	$wp_customize->add_setting(
		'jofme_header_layout',
		array(
			'default'           => 'classic',
			'sanitize_callback' => 'jofme_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'jofme_header_layout',
		array(
			'label'   => __( 'Header layout', 'jofme' ),
			'section' => 'jofme_header',
			'type'    => 'select',
			'choices' => array(
				'classic' => __( 'Classic (logo left)', 'jofme' ),
				'centered' => __( 'Centered logo', 'jofme' ),
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_sticky_header',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'jofme_sticky_header',
		array(
			'label'   => __( 'Sticky header', 'jofme' ),
			'section' => 'jofme_header',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'jofme_smooth_scroll',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'jofme_smooth_scroll',
		array(
			'label'       => __( 'Smooth page scroll', 'jofme' ),
			'description' => __( 'Adds a controlled scroll feel across the site. Disabled automatically when the visitor prefers reduced motion.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'jofme_scroll_speed',
		array(
			'default'           => 'medium',
			'sanitize_callback' => 'jofme_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'jofme_scroll_speed',
		array(
			'label'   => __( 'Scroll speed', 'jofme' ),
			'section' => 'jofme_header',
			'type'    => 'select',
			'choices' => array(
				'slow'   => __( 'Slow', 'jofme' ),
				'medium' => __( 'Medium', 'jofme' ),
				'fast'   => __( 'Fast', 'jofme' ),
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_announcement_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'jofme_announcement_text',
		array(
			'label'       => __( 'Announcement bar text', 'jofme' ),
			'description' => __( 'Leave empty to hide.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'text',
		)
	);

	// Shop.
	$wp_customize->add_section(
		'jofme_shop',
		array(
			'title' => __( 'Shop', 'jofme' ),
			'panel' => 'jofme_options',
		)
	);

	$wp_customize->add_setting(
		'jofme_shop_columns',
		array(
			'default'           => 4,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'jofme_shop_columns',
		array(
			'label'   => __( 'Product columns', 'jofme' ),
			'section' => 'jofme_shop',
			'type'    => 'number',
			'input_attrs' => array(
				'min' => 2,
				'max' => 5,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_products_per_page',
		array(
			'default'           => 12,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'jofme_products_per_page',
		array(
			'label'   => __( 'Products per page', 'jofme' ),
			'section' => 'jofme_shop',
			'type'    => 'number',
			'input_attrs' => array(
				'min' => 4,
				'max' => 48,
			),
		)
	);

	// Colors.
	$wp_customize->add_section(
		'jofme_colors',
		array(
			'title' => __( 'Colors', 'jofme' ),
			'panel' => 'jofme_options',
		)
	);

	$color_settings = array(
		'jofme_color_primary'   => array( '#1a1512', __( 'Primary text', 'jofme' ) ),
		'jofme_color_accent'    => array( '#b8956c', __( 'Accent (gold)', 'jofme' ) ),
		'jofme_color_background'=> array( '#faf8f5', __( 'Background', 'jofme' ) ),
	);

	foreach ( $color_settings as $id => $data ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $data[0],
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$id,
				array(
					'label'   => $data[1],
					'section' => 'jofme_colors',
				)
			)
		);
	}

	// Footer.
	$wp_customize->add_section(
		'jofme_footer',
		array(
			'title' => __( 'Footer', 'jofme' ),
			'panel' => 'jofme_options',
		)
	);

	$wp_customize->add_setting(
		'jofme_footer_copyright',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		)
	);
	$wp_customize->add_control(
		'jofme_footer_copyright',
		array(
			'label'   => __( 'Copyright text', 'jofme' ),
			'section' => 'jofme_footer',
			'type'    => 'textarea',
		)
	);
}
add_action( 'customize_register', 'jofme_customize_register' );

/**
 * Sanitize select.
 *
 * @param string               $input   Value.
 * @param WP_Customize_Setting $setting Setting.
 * @return string
 */
function jofme_sanitize_select( $input, $setting ) {
	$input   = sanitize_key( $input );
	$choices = $setting->manager->get_control( $setting->id )->choices;
	return array_key_exists( $input, $choices ) ? $input : $setting->default;
}

/**
 * Output Customizer CSS variables.
 */
function jofme_customizer_css() {
	$primary = get_theme_mod( 'jofme_color_primary', '#1a1512' );
	$accent  = get_theme_mod( 'jofme_color_accent', '#b8956c' );
	$bg      = get_theme_mod( 'jofme_color_background', '#faf8f5' );

	$css = ":root{
		--jofme-color-text:{$primary};
		--jofme-color-accent:{$accent};
		--jofme-color-bg:{$bg};
	}";

	wp_add_inline_style( 'jofme-main', $css );
}
add_action( 'wp_enqueue_scripts', 'jofme_customizer_css', 20 );
