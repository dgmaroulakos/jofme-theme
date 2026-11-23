<?php
/**
 * Prebuilt footer layout (logo/contact on the left + 3 columns).
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register dedicated footer column menu locations.
 */
function jofme_footer_menus() {
	register_nav_menus(
		array(
			'footer_col_1' => __( 'Footer column 1', 'jofme' ),
			'footer_col_2' => __( 'Footer column 2', 'jofme' ),
			'footer_col_3' => __( 'Footer column 3', 'jofme' ),
		)
	);
}
add_action( 'after_setup_theme', 'jofme_footer_menus', 20 );

/**
 * Customizer: footer layout + contact.
 *
 * @param WP_Customize_Manager $manager Manager.
 */
function jofme_footer_customize( $manager ) {
	$manager->add_setting(
		'jofme_footer_layout',
		array(
			'default'           => 'widgets',
			'sanitize_callback' => 'jofme_sanitize_select',
		)
	);
	$manager->add_control(
		'jofme_footer_layout',
		array(
			'label'       => __( 'Footer layout', 'jofme' ),
			'description' => __( 'Prebuilt brand layout places the logo and contact on the left, with three columns beside it.', 'jofme' ),
			'section'     => 'jofme_footer',
			'type'        => 'select',
			'choices'     => array(
				'widgets'    => __( 'Widget columns (classic)', 'jofme' ),
				'brand_left' => __( 'Prebuilt: logo/contact left + 3 columns', 'jofme' ),
			),
			'priority'    => 5,
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		$id = 'jofme_footer_col_' . $i . '_title';
		$manager->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$manager->add_control(
			$id,
			array(
				/* translators: %d: column number */
				'label'           => sprintf( __( 'Column %d title', 'jofme' ), $i ),
				'section'         => 'jofme_footer',
				'type'            => 'text',
				'active_callback' => 'jofme_footer_is_prebuilt',
			)
		);
	}

	$contact_fields = array(
		'jofme_footer_address' => array( __( 'Address', 'jofme' ), 'textarea', 'sanitize_textarea_field' ),
		'jofme_footer_phone'   => array( __( 'Phone', 'jofme' ), 'text', 'sanitize_text_field' ),
		'jofme_footer_email'   => array( __( 'Email', 'jofme' ), 'email', 'sanitize_email' ),
		'jofme_footer_hours'   => array( __( 'Opening hours', 'jofme' ), 'textarea', 'sanitize_textarea_field' ),
	);

	foreach ( $contact_fields as $id => $data ) {
		$manager->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => $data[2],
			)
		);
		$manager->add_control(
			$id,
			array(
				'label'           => $data[0],
				'section'         => 'jofme_footer',
				'type'            => $data[1],
				'active_callback' => 'jofme_footer_is_prebuilt',
			)
		);
	}

	$manager->add_setting(
		'jofme_footer_logo',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$manager->add_control(
		new WP_Customize_Media_Control(
			$manager,
			'jofme_footer_logo',
			array(
				'label'           => __( 'Footer logo', 'jofme' ),
				'description'     => __( 'Optional. Leave empty to use the Site Identity logo from Customizer.', 'jofme' ),
				'section'         => 'jofme_footer',
				'mime_type'       => 'image',
				'active_callback' => 'jofme_footer_is_prebuilt',
			)
		)
	);

	$manager->add_setting(
		'jofme_footer_logo_invert',
		array(
			'default'           => true,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$manager->add_control(
		'jofme_footer_logo_invert',
		array(
			'label'           => __( 'Invert footer logo (light on dark)', 'jofme' ),
			'description'     => __( 'Turn off to show the Customizer logo colors as uploaded.', 'jofme' ),
			'section'         => 'jofme_footer',
			'type'            => 'checkbox',
			'active_callback' => 'jofme_footer_is_prebuilt',
		)
	);
}
add_action( 'customize_register', 'jofme_footer_customize', 25 );

/**
 * Whether the prebuilt logo/contact footer is selected.
 *
 * @return bool
 */
function jofme_footer_is_prebuilt() {
	return 'brand_left' === jofme_footer_layout();
}

/**
 * Footer layout slug.
 *
 * @return string widgets|brand_left
 */
function jofme_footer_layout() {
	$layout = get_theme_mod( 'jofme_footer_layout', 'widgets' );
	// Migrate previous inverted option.
	if ( 'brand_right' === $layout ) {
		$layout = 'brand_left';
	}
	return 'brand_left' === $layout ? 'brand_left' : 'widgets';
}

/**
 * Contact lines for the brand column.
 *
 * @return array<string,string>
 */
function jofme_footer_contact() {
	$fields = array(
		'address' => get_theme_mod( 'jofme_footer_address', '' ),
		'phone'   => get_theme_mod( 'jofme_footer_phone', '' ),
		'email'   => get_theme_mod( 'jofme_footer_email', '' ),
		'hours'   => get_theme_mod( 'jofme_footer_hours', '' ),
	);
	$out = array();
	foreach ( $fields as $key => $value ) {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( '' !== $value ) {
			$out[ $key ] = $value;
		}
	}
	return $out;
}

/**
 * Render one prebuilt footer column (menu preferred, else widget area).
 *
 * @param int $index 1–3.
 */
function jofme_footer_prebuilt_col( $index ) {
	$index = max( 1, min( 3, (int) $index ) );
	$title = get_theme_mod( 'jofme_footer_col_' . $index . '_title', '' );
	$loc   = 'footer_col_' . $index;
	$side  = 'footer-' . $index;

	echo '<div class="site-footer__col site-footer__col--' . absint( $index ) . '">';
	if ( is_string( $title ) && '' !== trim( $title ) ) {
		echo '<h3 class="widget-title site-footer__col-title">' . esc_html( $title ) . '</h3>';
	}
	if ( has_nav_menu( $loc ) ) {
		wp_nav_menu(
			array(
				'theme_location'  => $loc,
				'container'       => 'nav',
				'container_class' => 'site-footer__col-nav',
				'menu_class'      => 'menu',
				'depth'           => 1,
				'fallback_cb'     => false,
			)
		);
	} elseif ( is_active_sidebar( $side ) ) {
		dynamic_sidebar( $side );
	}
	echo '</div>';
}

/**
 * Attachment ID for the prebuilt footer logo.
 *
 * Prefers an optional Footer logo; falls back to Site Identity custom_logo.
 *
 * @return int
 */
function jofme_footer_logo_id() {
	$footer_id = absint( get_theme_mod( 'jofme_footer_logo', 0 ) );
	if ( $footer_id && wp_attachment_is_image( $footer_id ) ) {
		return $footer_id;
	}

	$site_id = absint( get_theme_mod( 'custom_logo', 0 ) );
	if ( $site_id && wp_attachment_is_image( $site_id ) ) {
		return $site_id;
	}

	return 0;
}

/**
 * Whether the prebuilt footer should CSS-invert the logo.
 *
 * @return bool
 */
function jofme_footer_logo_invert() {
	return (bool) get_theme_mod( 'jofme_footer_logo_invert', true );
}

/**
 * Print the prebuilt footer logo from Customizer (Site Identity or Footer logo).
 *
 * Avoids the transparent-header dual-logo markup from jofme_site_branding().
 */
function jofme_footer_brand_logo() {
	$logo_id = jofme_footer_logo_id();
	if ( $logo_id ) {
		$alt = get_post_meta( $logo_id, '_wp_attachment_image_alt', true );
		$img = wp_get_attachment_image(
			$logo_id,
			'full',
			false,
			array(
				'class'   => 'custom-logo',
				'alt'     => $alt ? $alt : get_bloginfo( 'name' ),
				'loading' => 'lazy',
			)
		);
		if ( $img ) {
			printf(
				'<a href="%1$s" class="custom-logo-link" rel="home">%2$s</a>',
				esc_url( home_url( '/' ) ),
				$img // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image().
			);
			return;
		}
	}

	printf(
		'<p class="site-title"><a href="%1$s" rel="home">%2$s</a></p>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Brand column: logo + contact.
 */
function jofme_footer_brand_column() {
	$contact = jofme_footer_contact();
	echo '<div class="site-footer__brand">';
	echo '<div class="site-footer__brand-logo">';
	jofme_footer_brand_logo();
	echo '</div>';
	if ( $contact ) {
		echo '<ul class="site-footer__contact">';
		if ( ! empty( $contact['address'] ) ) {
			echo '<li class="site-footer__contact-item site-footer__contact-item--address">' . nl2br( esc_html( $contact['address'] ) ) . '</li>';
		}
		if ( ! empty( $contact['phone'] ) ) {
			$tel = preg_replace( '/[^0-9+]/', '', $contact['phone'] );
			echo '<li class="site-footer__contact-item site-footer__contact-item--phone"><a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $contact['phone'] ) . '</a></li>';
		}
		if ( ! empty( $contact['email'] ) ) {
			echo '<li class="site-footer__contact-item site-footer__contact-item--email"><a href="mailto:' . esc_attr( $contact['email'] ) . '">' . esc_html( $contact['email'] ) . '</a></li>';
		}
		if ( ! empty( $contact['hours'] ) ) {
			echo '<li class="site-footer__contact-item site-footer__contact-item--hours">' . nl2br( esc_html( $contact['hours'] ) ) . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}
