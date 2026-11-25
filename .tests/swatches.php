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
function absint( $value ) {
	return abs( (int) $value );
}
function wp_get_attachment_image_url( $id, $size = 'thumbnail' ) {
	return $id ? 'https://example.com/swatch-' . (int) $id . '.jpg' : false;
}
function selected( $selected, $current, $echo = true ) {
	$out = ( (string) $selected === (string) $current ) ? ' selected="selected"' : '';
	if ( $echo ) {
		echo $out;
	}
	return $out;
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
function is_wp_error( $thing ) {
	return false;
}
function get_permalink( $post = 0 ) {
	return 'https://example.com/product/ring/';
}
function verify( $expected, $actual, $label ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $label . ' — expected ' . var_export( $expected, true ) . ' got ' . var_export( $actual, true ) );
	}
}

require dirname( __DIR__ ) . '/inc/swatches.php';

verify( true, jofme_is_swatch_attribute( 'pa_color' ), 'color slug heuristic' );
verify( 'color', jofme_attribute_swatch_type( 'pa_color' ), 'color heuristic type' );
verify( false, jofme_is_swatch_attribute( 'pa_size' ), 'size is not a swatch by default' );
verify( 'image', jofme_attribute_swatch_type( 'pa_pattern' ), 'image slug heuristic' );

$jofme_options['jofme_attribute_swatch_9'] = 'yes';
verify( true, jofme_is_swatch_attribute( 'pa_material' ), 'explicit attribute flag enables swatches' );
verify( 'color', jofme_attribute_swatch_type( 'pa_material' ), 'legacy yes maps to color' );

$jofme_options['jofme_attribute_swatch_7'] = 'image';
verify( 'image', jofme_attribute_swatch_type( 'pa_size' ), 'explicit image type' );
verify( true, jofme_is_swatch_attribute( 'pa_size' ), 'image type counts as swatch' );

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

$img_html = jofme_swatch_html(
	array(
		'tag'   => 'button',
		'name'  => 'Marble',
		'image' => 'https://example.com/marble.jpg',
		'value' => 'marble',
	)
);
verify( true, false !== strpos( $img_html, 'is-image' ), 'image class' );
verify( true, false !== strpos( $img_html, '--swatch-image:url(' ), 'image css variable' );
verify( true, false !== strpos( $img_html, 'jofme-swatch__image' ), 'image visual span' );

$jofme_term_meta[30] = array( 'jofme_swatch_image' => 99 );
$img_term = (object) array( 'term_id' => 30, 'slug' => 'marble', 'name' => 'Marble' );
verify( 99, jofme_term_swatch_image_id( $img_term ), 'saved term image id' );
verify( 'https://example.com/swatch-99.jpg', jofme_term_swatch_image_url( $img_term ), 'term image url' );
$jofme_options['jofme_attribute_swatch_7'] = 'image';
$visuals = jofme_term_swatch_visuals( 'pa_size', $img_term );
verify( 'https://example.com/swatch-99.jpg', $visuals['image'], 'visuals use image for image type' );
verify( '', $visuals['color'], 'visuals skip color for image type' );

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

$jofme_options['jofme_attribute_swatch_7'] = 'no';
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

class WC_Product {}
class FixtureSwatchProduct extends WC_Product {
	public function get_id() { return 11; }
	public function get_permalink() { return 'https://example.com/product/ring/'; }
	public function get_attributes() {
		return array(
			new class {
				public function is_taxonomy() { return true; }
				public function get_name() { return 'pa_color'; }
			},
			new class {
				public function is_taxonomy() { return true; }
				public function get_name() { return 'pa_size'; }
			},
		);
	}
}
function wc_get_product_terms( $product_id, $taxonomy ) {
	if ( 'pa_color' === $taxonomy ) {
		return array(
			(object) array( 'term_id' => 21, 'slug' => 'gold', 'name' => 'Gold' ),
			(object) array( 'term_id' => 22, 'slug' => 'silver', 'name' => 'Silver' ),
		);
	}
	return array();
}
$jofme_options = array();
$card = jofme_product_card_swatches_html( new FixtureSwatchProduct() );
verify( true, false !== strpos( $card, 'jofme-swatches' ), 'card helper wraps swatches' );
verify( true, false !== strpos( $card, 'attribute_pa_color=gold' ), 'card helper links color term' );
verify( false, false !== strpos( $card, 'pa_size' ), 'card helper skips non-swatch attributes' );
verify( '', jofme_product_card_swatches_html( null ), 'card helper empty without product' );

echo "PASS: attribute flags, term colors, swatch markup, variation wrapper, active filters, card helper\n";
