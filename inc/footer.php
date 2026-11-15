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
 * Brand column: logo + contact.
 */
function jofme_footer_brand_column() {
	$contact = jofme_footer_contact();
	echo '<div class="site-footer__brand">';
	echo '<div class="site-footer__brand-logo">';
	jofme_site_branding();
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
