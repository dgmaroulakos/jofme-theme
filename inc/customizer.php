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
			'label'       => __( 'Header layout', 'jofme' ),
			'description' => __( 'Choose how logo, menu and icons are arranged in the header.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'select',
			'choices'     => array(
				'classic'   => __( 'Classic (logo left)', 'jofme' ),
				'centered'  => __( 'Centered logo', 'jofme' ),
				'editorial' => __( 'Editorial', 'jofme' ),
				'stacked'   => __( 'Stacked (logo above menu)', 'jofme' ),
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_header_width',
		array(
			'default'           => 'content',
			'sanitize_callback' => 'jofme_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'jofme_header_width',
		array(
			'label'       => __( 'Header width', 'jofme' ),
			'description' => __( 'Horizontal width of the header bar (logo / menu row).', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'select',
			'choices'     => array(
				'content' => __( 'Same as page content', 'jofme' ),
				'boxed'   => __( 'Boxed (1320px)', 'jofme' ),
				'wide'    => __( 'Wide (1600px)', 'jofme' ),
				'narrow'  => __( 'Narrow (960px)', 'jofme' ),
				'full'    => __( 'Full width', 'jofme' ),
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
		'jofme_logo_max_height',
		array(
			'default'           => 100,
			'sanitize_callback' => 'jofme_sanitize_logo_max_height',
		)
	);
	$wp_customize->add_control(
		'jofme_logo_max_height',
		array(
			'label'       => __( 'Logo max height — desktop (px)', 'jofme' ),
			'description' => __( 'Maximum logo height on desktop/tablet. Default: 100.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 24,
				'max'  => 240,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_logo_max_width_mobile',
		array(
			'default'           => 90,
			'sanitize_callback' => 'jofme_sanitize_logo_max_width_mobile',
		)
	);
	$wp_customize->add_control(
		'jofme_logo_max_width_mobile',
		array(
			'label'       => __( 'Logo max width — mobile (px)', 'jofme' ),
			'description' => __( 'Maximum logo width under 900px. Default: 90.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 40,
				'max'  => 200,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_header_min_height',
		array(
			'default'           => 132,
			'sanitize_callback' => 'jofme_sanitize_header_min_height',
		)
	);
	$wp_customize->add_control(
		'jofme_header_min_height',
		array(
			'label'       => __( 'Header min height (px)', 'jofme' ),
			'description' => __( 'Minimum height of the header bar (logo / menu row) on desktop/tablet.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 60,
				'max'  => 240,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_header_min_height_mobile',
		array(
			'default'           => 80,
			'sanitize_callback' => 'jofme_sanitize_header_min_height_mobile',
		)
	);
	$wp_customize->add_control(
		'jofme_header_min_height_mobile',
		array(
			'label'       => __( 'Header min height — mobile (px)', 'jofme' ),
			'description' => __( 'Minimum height of the header bar under 900px width. Default: 80.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 48,
				'max'  => 200,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_sticky_header_height',
		array(
			'default'           => 100,
			'sanitize_callback' => 'jofme_sanitize_sticky_header_height',
		)
	);
	$wp_customize->add_control(
		'jofme_sticky_header_height',
		array(
			'label'       => __( 'Stuck header height (px)', 'jofme' ),
			'description' => __( 'Header bar height after it sticks on scroll (desktop/tablet).', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 48,
				'max'  => 240,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_sticky_header_height_mobile',
		array(
			'default'           => 64,
			'sanitize_callback' => 'jofme_sanitize_sticky_header_height_mobile',
		)
	);
	$wp_customize->add_control(
		'jofme_sticky_header_height_mobile',
		array(
			'label'       => __( 'Stuck header height — mobile (px)', 'jofme' ),
			'description' => __( 'Stuck header height under 900px width. Default: 64.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 40,
				'max'  => 200,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_sticky_header_padding',
		array(
			'default'           => 12,
			'sanitize_callback' => 'jofme_sanitize_sticky_header_padding',
		)
	);
	$wp_customize->add_control(
		'jofme_sticky_header_padding',
		array(
			'label'       => __( 'Sticky header padding (px)', 'jofme' ),
			'description' => __( 'Top/bottom padding inside the header when it is stuck while scrolling.', 'jofme' ),
			'section'     => 'jofme_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 0,
				'max'  => 64,
				'step' => 1,
			),
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
		'jofme_color_background'=> array( '#e8caaf', __( 'Background', 'jofme' ) ),
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

	// Product tabs.
	$wp_customize->add_section(
		'jofme_product_tabs',
		array(
			'title'       => __( 'Product tabs', 'jofme' ),
			'description' => __( 'Appearance of Description / Additional information / Reviews on single product pages.', 'jofme' ),
			'panel'       => 'jofme_options',
		)
	);

	$wp_customize->add_setting(
		'jofme_tabs_style',
		array(
			'default'           => 'underline',
			'sanitize_callback' => 'jofme_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'jofme_tabs_style',
		array(
			'label'   => __( 'Tab style', 'jofme' ),
			'section' => 'jofme_product_tabs',
			'type'    => 'select',
			'choices' => array(
				'underline' => __( 'Underline', 'jofme' ),
				'pill'      => __( 'Pill', 'jofme' ),
				'boxed'     => __( 'Boxed', 'jofme' ),
			),
		)
	);

	$tab_colors = array(
		'jofme_tabs_bg'           => array( '#111111', __( 'Tabs background', 'jofme' ) ),
		'jofme_tabs_text'         => array( '#ffffff', __( 'Tabs text', 'jofme' ) ),
		'jofme_tabs_active_text'  => array( '#ffffff', __( 'Active tab text', 'jofme' ) ),
		'jofme_tabs_active_border'=> array( '#ffffff', __( 'Active tab accent', 'jofme' ) ),
		'jofme_tabs_active_bg'    => array( '#ffffff', __( 'Active tab background (pill/boxed)', 'jofme' ) ),
		'jofme_tabs_panel_bg'     => array( '', __( 'Panel background (optional)', 'jofme' ) ),
	);

	foreach ( $tab_colors as $id => $data ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $data[0],
				'sanitize_callback' => 'jofme_sanitize_optional_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$id,
				array(
					'label'   => $data[1],
					'section' => 'jofme_product_tabs',
				)
			)
		);
	}

	$wp_customize->add_setting(
		'jofme_tabs_font_size',
		array(
			'default'           => 12,
			'sanitize_callback' => 'jofme_sanitize_tabs_font_size',
		)
	);
	$wp_customize->add_control(
		'jofme_tabs_font_size',
		array(
			'label'       => __( 'Tab label font size (px)', 'jofme' ),
			'section'     => 'jofme_product_tabs',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 10,
				'max' => 22,
			),
		)
	);

	$wp_customize->add_setting(
		'jofme_tabs_padding_x',
		array(
			'default'           => 15,
			'sanitize_callback' => 'jofme_sanitize_tabs_padding_x',
		)
	);
	$wp_customize->add_control(
		'jofme_tabs_padding_x',
		array(
			'label'       => __( 'Horizontal padding (%)', 'jofme' ),
			'section'     => 'jofme_product_tabs',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 0,
				'max' => 30,
			),
		)
	);

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
 * Sanitize optional hex color (empty allowed).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function jofme_sanitize_optional_hex_color( $value ) {
	if ( '' === $value || null === $value ) {
		return '';
	}
	$color = sanitize_hex_color( $value );
	return $color ? $color : '';
}

/**
 * Sanitize product tabs font size (10–22).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_tabs_font_size( $value ) {
	return max( 10, min( 22, absint( $value ) ) );
}

/**
 * Sanitize product tabs horizontal padding percent (0–30).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_tabs_padding_x( $value ) {
	return max( 0, min( 30, absint( $value ) ) );
}

/**
 * Sanitize header min-height in px (60–240).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_header_min_height( $value ) {
	return max( 60, min( 240, absint( $value ) ) );
}

/**
 * Sanitize mobile header min-height in px (48–200).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_header_min_height_mobile( $value ) {
	return max( 48, min( 200, absint( $value ) ) );
}

/**
 * Sanitize sticky header padding in px (0–64).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_sticky_header_padding( $value ) {
	return max( 0, min( 64, absint( $value ) ) );
}

/**
 * Sanitize stuck header height in px (48–240).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_sticky_header_height( $value ) {
	return max( 48, min( 240, absint( $value ) ) );
}

/**
 * Sanitize mobile stuck header height in px (40–200).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_sticky_header_height_mobile( $value ) {
	return max( 40, min( 200, absint( $value ) ) );
}

/**
 * Sanitize logo max height in px (24–240).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_logo_max_height( $value ) {
	return max( 24, min( 240, absint( $value ) ) );
}

/**
 * Sanitize mobile logo max width in px (40–200).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_logo_max_width_mobile( $value ) {
	return max( 40, min( 200, absint( $value ) ) );
}

/**
 * Output Customizer CSS variables.
 */
function jofme_customizer_css() {
	$primary      = get_theme_mod( 'jofme_color_primary', '#1a1512' );
	$accent       = get_theme_mod( 'jofme_color_accent', '#b8956c' );
	$bg           = get_theme_mod( 'jofme_color_background', '#e8caaf' );
	$min_h        = jofme_sanitize_header_min_height( get_theme_mod( 'jofme_header_min_height', 132 ) );
	$min_h_mobile = jofme_sanitize_header_min_height_mobile( get_theme_mod( 'jofme_header_min_height_mobile', 80 ) );
	$stuck_h      = jofme_sanitize_sticky_header_height( get_theme_mod( 'jofme_sticky_header_height', 100 ) );
	$stuck_h_m    = jofme_sanitize_sticky_header_height_mobile( get_theme_mod( 'jofme_sticky_header_height_mobile', 64 ) );
	$sticky       = jofme_sanitize_sticky_header_padding( get_theme_mod( 'jofme_sticky_header_padding', 12 ) );
	$logo_h       = jofme_sanitize_logo_max_height( get_theme_mod( 'jofme_logo_max_height', 100 ) );
	$logo_w_m     = jofme_sanitize_logo_max_width_mobile( get_theme_mod( 'jofme_logo_max_width_mobile', 90 ) );
	$tabs_bg      = jofme_sanitize_optional_hex_color( get_theme_mod( 'jofme_tabs_bg', '#111111' ) );
	$tabs_text    = jofme_sanitize_optional_hex_color( get_theme_mod( 'jofme_tabs_text', '#ffffff' ) );
	$tabs_active  = jofme_sanitize_optional_hex_color( get_theme_mod( 'jofme_tabs_active_text', '#ffffff' ) );
	$tabs_border  = jofme_sanitize_optional_hex_color( get_theme_mod( 'jofme_tabs_active_border', '#ffffff' ) );
	$tabs_active_bg = jofme_sanitize_optional_hex_color( get_theme_mod( 'jofme_tabs_active_bg', '#ffffff' ) );
	$tabs_panel_bg  = jofme_sanitize_optional_hex_color( get_theme_mod( 'jofme_tabs_panel_bg', '' ) );
	$tabs_font      = jofme_sanitize_tabs_font_size( get_theme_mod( 'jofme_tabs_font_size', 12 ) );
	$tabs_pad_x     = jofme_sanitize_tabs_padding_x( get_theme_mod( 'jofme_tabs_padding_x', 15 ) );

	if ( ! $tabs_bg ) {
		$tabs_bg = '#111111';
	}
	if ( ! $tabs_text ) {
		$tabs_text = '#ffffff';
	}
	if ( ! $tabs_active ) {
		$tabs_active = $tabs_text;
	}
	if ( ! $tabs_border ) {
		$tabs_border = $tabs_text;
	}
	if ( ! $tabs_active_bg ) {
		$tabs_active_bg = '#ffffff';
	}
	$tabs_panel_bg_css = $tabs_panel_bg ? $tabs_panel_bg : 'transparent';

	$css = ":root{
		--jofme-color-text:{$primary};
		--jofme-color-accent:{$accent};
		--jofme-color-bg:{$bg};
		--jofme-header-h:{$min_h}px;
		--jofme-header-h-mobile:{$min_h_mobile}px;
		--jofme-header-stuck-h:{$stuck_h}px;
		--jofme-header-stuck-h-mobile:{$stuck_h_m}px;
		--jofme-sticky-header-pad:{$sticky}px;
		--jofme-logo-max-h:{$logo_h}px;
		--jofme-logo-max-w-mobile:{$logo_w_m}px;
		--jofme-tabs-bg:{$tabs_bg};
		--jofme-tabs-text:{$tabs_text};
		--jofme-tabs-active-text:{$tabs_active};
		--jofme-tabs-active-border:{$tabs_border};
		--jofme-tabs-active-bg:{$tabs_active_bg};
		--jofme-tabs-panel-bg:{$tabs_panel_bg_css};
		--jofme-tabs-font-size:{$tabs_font}px;
		--jofme-tabs-padding-x:{$tabs_pad_x}%;
	}";
	$css .= '@media(max-width:899px){:root{--jofme-header-h:var(--jofme-header-h-mobile);--jofme-header-stuck-h:var(--jofme-header-stuck-h-mobile);} }';

	wp_add_inline_style( 'jofme-main', $css );
}
add_action( 'wp_enqueue_scripts', 'jofme_customizer_css', 20 );

/**
 * Body class for product tab style variant.
 *
 * @param array $classes Classes.
 * @return array
 */
function jofme_tabs_style_body_class( $classes ) {
	$style = get_theme_mod( 'jofme_tabs_style', 'underline' );
	if ( ! in_array( $style, array( 'underline', 'pill', 'boxed' ), true ) ) {
		$style = 'underline';
	}
	$classes[] = 'jofme-tabs-' . $style;
	return $classes;
}
add_filter( 'body_class', 'jofme_tabs_style_body_class' );
