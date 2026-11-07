<?php
/**
 * Attribute color swatch helpers without a full WordPress bootstrap.
 */

define( 'ABSPATH', __DIR__ );

class WooCommerce {}

$jofme_options = array();
$jofme_term_meta = array();
$jofme_attributes = array(
	(object) array(
		'attribute_id'    => 3,
		'attribute_name'  => 'color',
		'attribute_label' => 'Color',
	),
	(object) array(
		'attribute_id'    => 7,
		'attribute_name'  => 'size',
		'attribute_label' => 'Size',
	),
	(object) array(
		'attribute_id'    => 9,
		'attribute_name'  => 'material',
		'attribute_label' => 'Material',
	),
);

function apply_filters( $tag, $value ) { return $value; }
function add_action() {}
function add_filter() {}
function __ ( $text ) { return $text; }
function esc_html__( $text ) { return $text; }
function esc_attr__( $text ) { return $text; }
function esc_html( $text ) { return $text; }
function esc_attr( $text ) { return $text; }
function esc_url( $url ) { return $url; }
function sanitize_title( $title ) { return strtolower( $title ); }
function sanitize_hex_color( $color ) {
	return is_string( $color ) && preg_match( '/^#([A-Fa-f0-9]{6})$/', $color ) ? $color : null;
}
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function wc_attribute_taxonomy_slug( $taxonomy ) {
	return 0 === strpos( $taxonomy, 'pa_' ) ? substr( $taxonomy, 3 ) : $taxonomy;
}
function wc_get_attribute_taxonomies() {
	global $jofme_attributes;
	return $jofme_attributes;
}
function wc_attribute_taxonomy_name( $name ) {
	return 'pa_' . $name;
}
function sanitize_text_field( $text ) { return trim( (string) $text ); }
function wp_unslash( $value ) { return $value; }
function jofme_filter_value( $key ) {
	return isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( $_GET[ $key ] ) : '';
}
function add_query_arg( $key, $value = false, $url = false ) {
	if ( is_array( $key ) ) {
		foreach ( $key as $k => $v ) {
			$url = add_query_arg( $k, $v, $url );
		}
		return $url;
	}
	$sep = false === strpos( $url, '?' ) ? '?' : '&';
	return $url . $sep . rawurlencode( $key ) . '=' . rawurlencode( (string) $value );
}
function get_option( $key, $default = false ) {
	global $jofme_options;
	return array_key_exists( $key, $jofme_options ) ? $jofme_options[ $key ] : $default;
}
function get_term_meta( $term_id, $key, $single = false ) {
	global $jofme_term_meta;
	return $jofme_term_meta[ $term_id ][ $key ] ?? '';
}
function taxonomy_exists( $taxonomy ) {
	return 0 === strpos( (string) $taxonomy, 'pa_' );
}
function wc_attribute_label( $attribute ) {
	return ucfirst( str_replace( 'pa_', '', $attribute ) );
}
function get_term_by( $field, $value, $taxonomy ) {
	return (object) array(
		'term_id' => 42,
		'slug'    => $value,
		'name'    => ucfirst( $value ),
	);
}
function verify( $expected, $actual, $label ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $label . ' — expected ' . var_export( $expected, true ) . ' got ' . var_export( $actual, true ) );
	}
}

require dirname( __DIR__ ) . '/inc/swatches.php';

verify( true, jofme_is_swatch_attribute( 'pa_color' ), 'color slug heuristic' );
verify( false, jofme_is_swatch_attribute( 'pa_size' ), 'size is not a swatch by default' );

$jofme_options['jofme_attribute_swatch_9'] = 'yes';
verify( true, jofme_is_swatch_attribute( 'pa_material' ), 'explicit attribute flag enables swatches' );

$jofme_options['jofme_attribute_swatch_3'] = 'no';
verify( false, jofme_is_swatch_attribute( 'pa_color' ), 'explicit no disables color heuristic' );

$jofme_term_meta[15] = array( 'jofme_swatch_color' => '#AABBCC' );
$term = (object) array( 'term_id' => 15, 'slug' => 'custom', 'name' => 'Custom' );
verify( '#AABBCC', jofme_term_swatch_color( $term ), 'saved term hex' );

$fallback = (object) array( 'term_id' => 16, 'slug' => 'gold', 'name' => 'Gold' );
verify( '#c6a15b', jofme_term_swatch_color( $fallback ), 'default slug color' );

$html = jofme_swatch_html(
	array(
		'tag'      => 'button',
		'name'     => 'Gold',
		'color'    => '#c6a15b',
		'value'    => 'gold',
		'selected' => true,
	)
);
verify( true, false !== strpos( $html, 'is-color' ), 'color class' );
verify( true, false !== strpos( $html, '--swatch:#c6a15b' ), 'swatch css variable' );
verify( true, false !== strpos( $html, 'aria-pressed="true"' ), 'selected state' );
verify( true, false !== strpos( $html, 'screen-reader-text' ), 'accessible label' );

$variation = jofme_variation_swatches_html(
	'<select name="attribute_pa_material"><option value="linen">Linen</option></select>',
	array(
		'attribute' => 'pa_material',
		'options'   => array( 'linen' ),
		'selected'  => 'linen',
	)
);
verify( true, false !== strpos( $variation, 'jofme-variation-swatches' ), 'variation wrapper' );
verify( true, false !== strpos( $variation, '<select' ), 'native select preserved' );

$plain = jofme_variation_swatches_html(
	'<select name="attribute_pa_size"><option value="m">M</option></select>',
	array(
		'attribute' => 'pa_size',
		'options'   => array( 'm' ),
		'selected'  => '',
	)
);
verify( '<select name="attribute_pa_size"><option value="m">M</option></select>', $plain, 'non-swatch dropdown unchanged' );

$_GET = array( 'filter_color' => 'xriso' );
$jofme_options = array();
verify( array( 'pa_color' => 'xriso' ), jofme_active_swatch_filters(), 'active filter_color maps to pa_color' );
$_GET = array( 'filter_color' => 'xriso,asimenio' );
verify( array( 'pa_color' => 'xriso' ), jofme_active_swatch_filters(), 'comma-separated filter uses first slug' );

$link = jofme_loop_product_link_with_filters( 'https://example.com/product/ring/', null );
verify( 'https://example.com/product/ring/', $link, 'missing product leaves link unchanged' );
$link = jofme_loop_product_link_with_filters( 'https://example.com/product/ring/', (object) array() );
verify( true, false !== strpos( $link, 'attribute_pa_color=xriso' ), 'loop link gets active color attribute' );

$_GET = array();
verify( array(), jofme_active_swatch_filters(), 'no active filters when query empty' );

echo "PASS: attribute flags, term colors, swatch markup, variation wrapper, active filters\n";
