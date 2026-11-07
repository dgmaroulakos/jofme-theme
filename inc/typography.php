<?php
/** Typography choices, CDN fonts and merchant-uploaded webfonts. @package Jofme */
defined( 'ABSPATH' ) || exit;
function jofme_font_choices() {
 return array( 'manrope' => 'Manrope', 'cormorant' => 'Cormorant Garamond', 'aeonik-light' => 'Aeonik Pro Light (CDN)', 'custom-aeonik' => __( 'Aeonik Pro (custom font files)', 'jofme' ), 'system' => __( 'System sans-serif', 'jofme' ), 'serif' => __( 'System serif', 'jofme' ) );
}
function jofme_font_url_sanitize( $value ) {
 $url = esc_url_raw( trim( (string) $value ), array( 'http', 'https' ) );
 return preg_match( '/\.woff2?$/i', (string) wp_parse_url( $url, PHP_URL_PATH ) ) ? $url : '';
}
/**
 * Sanitize menu font size in px (8–32).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function jofme_sanitize_nav_font_size( $value ) {
 return max( 8, min( 32, absint( $value ) ) );
}

/**
 * Sanitize letter-spacing as em (e.g. 0.05).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function jofme_sanitize_nav_letter_spacing( $value ) {
 $value = is_numeric( $value ) ? (float) $value : 0.05;
 $value = max( -0.1, min( 0.5, $value ) );
 return rtrim( rtrim( number_format( $value, 3, '.', '' ), '0' ), '.' );
}

function jofme_typography_customize( $manager ) {
 $manager->add_section( 'jofme_typography', array( 'title' => __( 'Typography', 'jofme' ), 'panel' => 'jofme_options' ) );
 foreach ( array( 'body' => array( __( 'Text & navigation font', 'jofme' ), 'manrope' ), 'display' => array( __( 'Headings font', 'jofme' ), 'cormorant' ) ) as $key => $data ) {
  $id = 'jofme_font_' . $key;
  $manager->add_setting( $id, array( 'default' => $data[1], 'sanitize_callback' => 'jofme_sanitize_select' ) );
  $manager->add_control( $id, array( 'label' => $data[0], 'section' => 'jofme_typography', 'type' => 'select', 'choices' => jofme_font_choices() ) );
 }
 $nav_fonts = array_merge( array( 'inherit' => __( 'Same as text font', 'jofme' ) ), jofme_font_choices() );
 $manager->add_setting( 'jofme_nav_font', array( 'default' => 'inherit', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control( 'jofme_nav_font', array( 'label' => __( 'Menu font', 'jofme' ), 'section' => 'jofme_typography', 'type' => 'select', 'choices' => $nav_fonts ) );
 $manager->add_setting( 'jofme_nav_font_size', array( 'default' => 11, 'sanitize_callback' => 'jofme_sanitize_nav_font_size' ) );
 $manager->add_control( 'jofme_nav_font_size', array( 'label' => __( 'Menu font size (px)', 'jofme' ), 'section' => 'jofme_typography', 'type' => 'number', 'input_attrs' => array( 'min' => 8, 'max' => 32, 'step' => 1 ) ) );
 $manager->add_setting( 'jofme_nav_font_weight', array( 'default' => '600', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control( 'jofme_nav_font_weight', array( 'label' => __( 'Menu font weight', 'jofme' ), 'section' => 'jofme_typography', 'type' => 'select', 'choices' => array( '400' => '400', '500' => '500', '600' => '600', '700' => '700' ) ) );
 $manager->add_setting( 'jofme_nav_letter_spacing', array( 'default' => '0.055', 'sanitize_callback' => 'jofme_sanitize_nav_letter_spacing' ) );
 $manager->add_control( 'jofme_nav_letter_spacing', array( 'label' => __( 'Menu letter spacing (em)', 'jofme' ), 'section' => 'jofme_typography', 'type' => 'number', 'input_attrs' => array( 'min' => -0.1, 'max' => 0.5, 'step' => 0.005 ) ) );
 $manager->add_setting( 'jofme_nav_text_transform', array( 'default' => 'uppercase', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control( 'jofme_nav_text_transform', array( 'label' => __( 'Menu text transform', 'jofme' ), 'section' => 'jofme_typography', 'type' => 'select', 'choices' => array( 'none' => __( 'None', 'jofme' ), 'uppercase' => __( 'Uppercase', 'jofme' ), 'capitalize' => __( 'Capitalize', 'jofme' ) ) ) );
 foreach ( array( 'regular' => '400', 'medium' => '500', 'bold' => '700' ) as $name => $weight ) {
  $id = 'jofme_font_file_' . $name;
  $manager->add_setting( $id, array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
  $manager->add_control( new WP_Customize_Media_Control( $manager, $id, array( 'label' => sprintf( __( 'Custom Aeonik Pro — %s file', 'jofme' ), $name ), 'section' => 'jofme_typography', 'mime_type' => 'font', 'description' => __( 'Optional WOFF/WOFF2 upload for the custom font choice.', 'jofme' ) ) ) );
  $cdn = 'jofme_font_cdn_' . $name;
  $manager->add_setting( $cdn, array( 'default' => '', 'sanitize_callback' => 'jofme_font_url_sanitize' ) );
  $manager->add_control( $cdn, array( 'label' => sprintf( __( 'Custom Aeonik Pro — %s CDN file URL', 'jofme' ), $name ), 'section' => 'jofme_typography', 'type' => 'url', 'description' => __( 'Optional direct .woff or .woff2 URL. Used when no font file is uploaded. The Aeonik Pro Light choice already uses its supplied CDN stylesheet.', 'jofme' ) ) );
 }
}
add_action( 'customize_register', 'jofme_typography_customize', 25 );
function jofme_font_upload_types( $mimes ) {
 if ( current_user_can( 'manage_options' ) ) { $mimes['woff'] = 'font/woff'; $mimes['woff2'] = 'font/woff2'; }
 return $mimes;
}
add_filter( 'upload_mimes', 'jofme_font_upload_types' );
function jofme_font_stack( $choice, $fallback ) {
 $stacks = array( 'manrope' => '"Manrope",system-ui,sans-serif', 'cormorant' => '"Cormorant Garamond",Georgia,serif', 'aeonik-light' => '"Aeonik Pro Light","Manrope",system-ui,sans-serif', 'custom-aeonik' => '"Jofme Aeonik Pro","Manrope",system-ui,sans-serif', 'system' => 'system-ui,-apple-system,"Segoe UI",sans-serif', 'serif' => 'Georgia,"Times New Roman",serif' );
 return isset( $stacks[ $choice ] ) ? $stacks[ $choice ] : $stacks[ $fallback ];
}
function jofme_typography_assets() {
 $body = get_theme_mod( 'jofme_font_body', 'manrope' ); $display = get_theme_mod( 'jofme_font_display', 'cormorant' );
 if ( in_array( 'aeonik-light', array( $body, $display ), true ) ) {
  wp_enqueue_style( 'jofme-aeonik-light', 'https://db.onlinewebfonts.com/c/c6da2799f8877386e9a261e8744b2885?family=Aeonik+Pro+Light', array(), null );
 }
 $css = '';
 if ( in_array( 'custom-aeonik', array( $body, $display ), true ) ) {
  foreach ( array( 'regular' => 400, 'medium' => 500, 'bold' => 700 ) as $name => $weight ) {
   $file = absint( get_theme_mod( 'jofme_font_file_' . $name, 0 ) );
   $url = $file ? wp_get_attachment_url( $file ) : get_theme_mod( 'jofme_font_cdn_' . $name, '' );
   $url = jofme_font_url_sanitize( $url ?: '' );
   if ( ! $url ) { continue; }
   $format = 'woff2' === strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ? 'woff2' : 'woff';
   $css .= '@font-face{font-family:"Jofme Aeonik Pro";src:url(' . wp_json_encode( $url, JSON_UNESCAPED_SLASHES ) . ') format("' . $format . '");font-style:normal;font-weight:' . $weight . ';font-display:swap;}';
  }
 }
 $font_choices = jofme_font_choices();
 $nav_choice   = get_theme_mod( 'jofme_nav_font', 'inherit' );
 $nav_font     = ( 'inherit' === $nav_choice || ! isset( $font_choices[ $nav_choice ] ) ) ? 'var(--jofme-font-body)' : jofme_font_stack( $nav_choice, 'manrope' );
 if ( 'aeonik-light' === $nav_choice ) {
  wp_enqueue_style( 'jofme-aeonik-light', 'https://db.onlinewebfonts.com/c/c6da2799f8877386e9a261e8744b2885?family=Aeonik+Pro+Light', array(), null );
 }
 if ( 'custom-aeonik' === $nav_choice && ! in_array( 'custom-aeonik', array( $body, $display ), true ) ) {
  foreach ( array( 'regular' => 400, 'medium' => 500, 'bold' => 700 ) as $name => $weight ) {
   $file = absint( get_theme_mod( 'jofme_font_file_' . $name, 0 ) );
   $url  = $file ? wp_get_attachment_url( $file ) : get_theme_mod( 'jofme_font_cdn_' . $name, '' );
   $url  = jofme_font_url_sanitize( $url ?: '' );
   if ( ! $url ) { continue; }
   $format = 'woff2' === strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) ? 'woff2' : 'woff';
   $css   .= '@font-face{font-family:"Jofme Aeonik Pro";src:url(' . wp_json_encode( $url, JSON_UNESCAPED_SLASHES ) . ') format("' . $format . '");font-style:normal;font-weight:' . $weight . ';font-display:swap;}';
  }
 }
 $nav_size      = jofme_sanitize_nav_font_size( get_theme_mod( 'jofme_nav_font_size', 11 ) );
 $nav_weight    = get_theme_mod( 'jofme_nav_font_weight', '600' );
 $nav_weight    = in_array( (string) $nav_weight, array( '400', '500', '600', '700' ), true ) ? (string) $nav_weight : '600';
 $nav_tracking  = jofme_sanitize_nav_letter_spacing( get_theme_mod( 'jofme_nav_letter_spacing', '0.055' ) );
 $nav_transform = get_theme_mod( 'jofme_nav_text_transform', 'uppercase' );
 $nav_transform = in_array( $nav_transform, array( 'none', 'uppercase', 'capitalize' ), true ) ? $nav_transform : 'uppercase';
 $css .= ':root{--jofme-font-body:' . jofme_font_stack( $body, 'manrope' ) . ';--jofme-font-display:' . jofme_font_stack( $display, 'cormorant' ) . ';--jofme-nav-font:' . $nav_font . ';--jofme-nav-size:' . absint( $nav_size ) . 'px;--jofme-nav-weight:' . $nav_weight . ';--jofme-nav-tracking:' . $nav_tracking . 'em;--jofme-nav-transform:' . $nav_transform . ';}';
 $css .= 'input,select,textarea{font-family:var(--jofme-font-body);}';
 $css .= '.main-navigation a,.jofme-mobile-nav a{font-family:var(--jofme-nav-font);font-size:var(--jofme-nav-size);font-weight:var(--jofme-nav-weight);letter-spacing:var(--jofme-nav-tracking);text-transform:var(--jofme-nav-transform);}';
 wp_add_inline_style( 'jofme-main', $css );
}
add_action( 'wp_enqueue_scripts', 'jofme_typography_assets', 21 );
