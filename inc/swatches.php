<?php
/**
 * Attribute color swatches for cards, filters and product pages.
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Default slug → hex map when a term has no saved color.
 *
 * @return array<string,string>
 */
function jofme_default_swatch_colors() {
	return apply_filters(
		'jofme_swatch_colors',
		array(
			'gold'      => '#c6a15b',
			'silver'    => '#c5c8cc',
			'rose-gold' => '#c68f83',
			'black'     => '#222222',
			'white'     => '#ffffff',
			'red'       => '#9c2534',
			'blue'      => '#355575',
			'green'     => '#476653',
			'beige'     => '#d5c5aa',
		)
	);
}

/**
 * Resolve a taxonomy or attribute key to the WooCommerce attribute object.
 *
 * @param string $attribute Taxonomy (pa_color) or attribute name (color).
 * @return object|null
 */
function jofme_get_attribute_object( $attribute ) {
	$attribute = (string) $attribute;
	if ( '' === $attribute ) {
		return null;
	}

	$slug = 0 === strpos( $attribute, 'pa_' ) ? wc_attribute_taxonomy_slug( $attribute ) : sanitize_title( $attribute );

	foreach ( wc_get_attribute_taxonomies() as $tax ) {
		if ( $tax->attribute_name === $slug ) {
			return $tax;
		}
	}

	return null;
}

/**
 * Whether an attribute should render as color swatches.
 *
 * Explicit Products → Attributes setting wins; otherwise slug heuristics keep
 * existing color/finish/metal attributes working.
 *
 * @param string $attribute Taxonomy or attribute name.
 * @return bool
 */
function jofme_is_swatch_attribute( $attribute ) {
	$tax = jofme_get_attribute_object( $attribute );
	if ( $tax ) {
		$flag = get_option( 'jofme_attribute_swatch_' . (int) $tax->attribute_id, '' );
		if ( 'yes' === $flag ) {
			return true;
		}
		if ( 'no' === $flag ) {
			return false;
		}
	}

	$name = $tax ? $tax->attribute_name : (string) $attribute;
	return (bool) preg_match( '/color|colour|χρωμ|finish|metal/u', $name );
}

/**
 * Hex color for an attribute term.
 *
 * @param WP_Term|object $term Term.
 * @return string Sanitized hex or empty string.
 */
function jofme_term_swatch_color( $term ) {
	if ( ! $term || empty( $term->term_id ) ) {
		return '';
	}

	$color = sanitize_hex_color( get_term_meta( (int) $term->term_id, 'jofme_swatch_color', true ) );
	if ( $color ) {
		return $color;
	}

	$defaults = jofme_default_swatch_colors();
	if ( ! empty( $term->slug ) && isset( $defaults[ $term->slug ] ) ) {
		return sanitize_hex_color( $defaults[ $term->slug ] ) ?: '';
	}

	return '';
}

/**
 * Markup for one color swatch control.
 *
 * @param array $args {
 *     @type string $tag       HTML tag: a|button|label.
 *     @type string $name      Term label.
 *     @type string $color     Hex color.
 *     @type string $url       Optional href for anchors.
 *     @type string $value     Optional data-value / radio value.
 *     @type bool   $selected  Selected state.
 *     @type bool   $disabled  Disabled state.
 *     @type string $input     Optional radio input HTML inside a label.
 * }
 * @return string
 */
function jofme_swatch_html( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'tag'      => 'button',
			'name'     => '',
			'color'    => '',
			'url'      => '',
			'value'    => '',
			'selected' => false,
			'disabled' => false,
			'input'    => '',
		)
	);

	$classes = array( 'jofme-swatch' );
	if ( $args['color'] ) {
		$classes[] = 'is-color';
	}
	if ( $args['selected'] ) {
		$classes[] = 'is-selected';
	}

	$attrs = 'class="' . esc_attr( implode( ' ', $classes ) ) . '" title="' . esc_attr( $args['name'] ) . '"';
	if ( $args['color'] ) {
		$attrs .= ' style="--swatch:' . esc_attr( $args['color'] ) . '"';
	}
	if ( '' !== $args['value'] ) {
		$attrs .= ' data-value="' . esc_attr( $args['value'] ) . '"';
	}

	$label = $args['color']
		? '<span class="screen-reader-text">' . esc_html( $args['name'] ) . '</span>'
		: '<span>' . esc_html( $args['name'] ) . '</span>';

	if ( 'a' === $args['tag'] ) {
		return '<a ' . $attrs . ' href="' . esc_url( $args['url'] ) . '">' . $label . '</a>';
	}

	if ( 'label' === $args['tag'] ) {
		return '<label ' . $attrs . '>' . $args['input'] . $label . '</label>';
	}

	$attrs .= ' type="button" aria-pressed="' . ( $args['selected'] ? 'true' : 'false' ) . '"';
	if ( $args['disabled'] ) {
		$attrs .= ' disabled';
	}

	return '<button ' . $attrs . '>' . $label . '</button>';
}

/**
 * Attribute admin: enable color swatches for this attribute.
 */
function jofme_attribute_swatch_add_field() {
	?>
	<div class="form-field">
		<label for="jofme_attribute_swatch">
			<input name="jofme_attribute_swatch" id="jofme_attribute_swatch" type="checkbox" value="1">
			<?php esc_html_e( 'Display as color swatches', 'jofme' ); ?>
		</label>
		<p><?php esc_html_e( 'Show term colors on product cards, catalog filters, and the product page. Set each term color under Configure terms.', 'jofme' ); ?></p>
	</div>
	<?php
}
add_action( 'woocommerce_after_add_attribute_fields', 'jofme_attribute_swatch_add_field' );

/**
 * Attribute admin edit field.
 */
function jofme_attribute_swatch_edit_field() {
	$id      = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin screen id.
	$value   = $id ? get_option( 'jofme_attribute_swatch_' . $id, '' ) : '';
	$checked = ( 'yes' === $value );

	// No saved preference yet: pre-check attributes that already match the color slug heuristic.
	if ( '' === $value && $id ) {
		$attr = function_exists( 'wc_get_attribute' ) ? wc_get_attribute( $id ) : null;
		if ( $attr && ! empty( $attr->slug ) ) {
			$checked = (bool) preg_match( '/color|colour|χρωμ|finish|metal/u', $attr->slug );
		}
	}
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="jofme_attribute_swatch"><?php esc_html_e( 'Color swatches', 'jofme' ); ?></label>
		</th>
		<td>
			<label for="jofme_attribute_swatch">
				<input name="jofme_attribute_swatch" id="jofme_attribute_swatch" type="checkbox" value="1" <?php checked( $checked ); ?>>
				<?php esc_html_e( 'Display as color swatches', 'jofme' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Show term colors on product cards, catalog filters, and the product page. Set each term color under Configure terms.', 'jofme' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'woocommerce_after_edit_attribute_fields', 'jofme_attribute_swatch_edit_field' );

/**
 * Persist attribute swatch setting. WooCommerce verifies the attribute form nonce.
 *
 * @param int $id Attribute ID.
 */
function jofme_save_attribute_swatch( $id ) {
	$id = absint( $id );
	if ( ! $id || ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}

	$enabled = isset( $_POST['jofme_attribute_swatch'] ) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WC attribute form nonce already verified.
	update_option( 'jofme_attribute_swatch_' . $id, $enabled, false );
}
add_action( 'woocommerce_attribute_added', 'jofme_save_attribute_swatch', 10, 1 );
add_action( 'woocommerce_attribute_updated', 'jofme_save_attribute_swatch', 10, 1 );

/**
 * Remove stored flag when an attribute is deleted.
 *
 * @param int $id Attribute ID.
 */
function jofme_delete_attribute_swatch( $id ) {
	delete_option( 'jofme_attribute_swatch_' . absint( $id ) );
}
add_action( 'woocommerce_attribute_deleted', 'jofme_delete_attribute_swatch', 10, 1 );

/**
 * Term color field markup (add + edit screens).
 *
 * @param WP_Term|string $term Term on edit, taxonomy name on add.
 */
function jofme_swatch_term_field( $term = null ) {
	$is_edit = $term instanceof WP_Term;
	$value   = $is_edit ? (string) get_term_meta( $term->term_id, 'jofme_swatch_color', true ) : '';
	$picker  = sanitize_hex_color( $value ) ? $value : '#c6a15b';
	$nonce   = wp_nonce_field( 'jofme_save_swatch', 'jofme_swatch_nonce', true, false );

	$input  = $nonce;
	$input .= '<div class="jofme-swatch-picker">';
	$input .= '<input type="color" data-swatch-picker value="' . esc_attr( $picker ) . '" aria-label="' . esc_attr__( 'Choose swatch color', 'jofme' ) . '">';
	$input .= '<input id="jofme-swatch-color" name="jofme_swatch_color" type="text" data-swatch-hex placeholder="#c6a15b" pattern="^#([A-Fa-f0-9]{6})$" value="' . esc_attr( $value ) . '">';
	$input .= '</div>';
	$input .= '<p>' . esc_html__( 'Used on product cards, catalog filters, and variation swatches. Leave blank to use a default color from the term slug, or a text label when none matches.', 'jofme' ) . '</p>';

	if ( $is_edit ) {
		echo '<tr class="form-field"><th><label for="jofme-swatch-color">' . esc_html__( 'Swatch color', 'jofme' ) . '</label></th><td>' . $input . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled with escaped parts above.
	} else {
		echo '<div class="form-field"><label for="jofme-swatch-color">' . esc_html__( 'Swatch color', 'jofme' ) . '</label>' . $input . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Save term swatch color.
 *
 * @param int $term_id Term ID.
 */
function jofme_save_swatch( $term_id ) {
	if (
		! current_user_can( 'manage_product_terms' )
		|| ! isset( $_POST['jofme_swatch_nonce'], $_POST['jofme_swatch_color'] )
		|| ! is_scalar( $_POST['jofme_swatch_nonce'] )
		|| ! is_scalar( $_POST['jofme_swatch_color'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jofme_swatch_nonce'] ) ), 'jofme_save_swatch' )
	) {
		return;
	}

	$color = sanitize_hex_color( wp_unslash( $_POST['jofme_swatch_color'] ) );
	update_term_meta( $term_id, 'jofme_swatch_color', $color ? $color : '' );
}

/**
 * Register term color fields on every product attribute taxonomy.
 */
function jofme_register_swatch_fields() {
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
		add_action( $taxonomy . '_add_form_fields', 'jofme_swatch_term_field' );
		add_action( $taxonomy . '_edit_form_fields', 'jofme_swatch_term_field' );
		add_action( 'created_' . $taxonomy, 'jofme_save_swatch' );
		add_action( 'edited_' . $taxonomy, 'jofme_save_swatch' );
	}
}
add_action( 'init', 'jofme_register_swatch_fields', 20 );

/**
 * Admin assets for the color picker sync.
 *
 * @param string $hook Current admin page.
 */
function jofme_swatch_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return;
	}

	$load = ( 'product_page_product_attributes' === $screen->id );
	if ( ! $load && in_array( $screen->base, array( 'edit-tags', 'term' ), true ) && ! empty( $screen->taxonomy ) && function_exists( 'taxonomy_is_product_attribute' ) && taxonomy_is_product_attribute( $screen->taxonomy ) ) {
		$load = true;
	}
	if ( ! $load ) {
		return;
	}

	wp_enqueue_style( 'jofme-swatches-admin', JOFME_URI . '/assets/css/swatches-admin.css', array(), JOFME_VERSION );
	wp_enqueue_script( 'jofme-swatches-admin', JOFME_URI . '/assets/js/swatches-admin.js', array(), JOFME_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'jofme_swatch_admin_assets' );

/**
 * Frontend swatch assets.
 */
function jofme_swatch_assets() {
	wp_enqueue_style( 'jofme-swatches', JOFME_URI . '/assets/css/swatches.css', array( 'jofme-premium' ), JOFME_VERSION );

	if ( is_product() ) {
		wp_enqueue_script(
			'jofme-swatches',
			JOFME_URI . '/assets/js/swatches.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			JOFME_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'jofme_swatch_assets', 30 );

/**
 * Color swatches on catalog product cards.
 */
function jofme_card_swatches() {
	global $product;
	if ( ! $product ) {
		return;
	}

	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->is_taxonomy() || ! jofme_is_swatch_attribute( $attribute->get_name() ) ) {
			continue;
		}

		$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name() );
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}

		echo '<div class="jofme-swatches" aria-label="' . esc_attr( wc_attribute_label( $attribute->get_name() ) ) . '">';
		foreach ( $terms as $term ) {
			$url = add_query_arg( 'attribute_' . $attribute->get_name(), $term->slug, get_permalink( $product->get_id() ) );
			echo jofme_swatch_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'tag'   => 'a',
					'name'  => $term->name,
					'color' => jofme_term_swatch_color( $term ),
					'url'   => $url,
					'value' => $term->slug,
				)
			);
		}
		echo '</div>';
	}
}
// Swatch links must follow the main product link, never be nested inside it.
add_action( 'woocommerce_after_shop_loop_item', 'jofme_card_swatches', 7 );

/**
 * Catalog filter control: swatches for color attributes, select otherwise.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $key      Query arg.
 * @param string $label    Visible label.
 */
function jofme_filter_attribute_control( $taxonomy, $key, $label ) {
	if ( jofme_is_swatch_attribute( $taxonomy ) ) {
		jofme_filter_swatches( $taxonomy, $key, $label );
		return;
	}
	jofme_filter_select( $taxonomy, $key, $label );
}

/**
 * Color swatch radios for catalog filters.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $key      Query arg.
 * @param string $label    Visible label.
 */
function jofme_filter_swatches( $taxonomy, $key, $label ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	$current = jofme_filter_value( $key );
	echo '<fieldset class="jofme-filter-swatches"><legend>' . esc_html( $label ) . '</legend>';
	echo '<div class="jofme-swatches" role="presentation">';

	$all_id = 'jofme-filter-' . sanitize_html_class( $key ) . '-all';
	echo '<label class="jofme-swatch jofme-swatch--all' . ( '' === $current ? ' is-selected' : '' ) . '" for="' . esc_attr( $all_id ) . '">';
	echo '<input id="' . esc_attr( $all_id ) . '" type="radio" name="' . esc_attr( $key ) . '" value="" ' . checked( $current, '', false ) . '>';
	echo '<span>' . esc_html__( 'All', 'jofme' ) . '</span></label>';

	foreach ( $terms as $term ) {
		$id    = 'jofme-filter-' . sanitize_html_class( $key . '-' . $term->slug );
		$color = jofme_term_swatch_color( $term );
		$input = '<input id="' . esc_attr( $id ) . '" type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( $current, $term->slug, false ) . '>';
		echo jofme_swatch_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'tag'      => 'label',
				'name'     => $term->name,
				'color'    => $color,
				'value'    => $term->slug,
				'selected' => ( $current === $term->slug ),
				'input'    => $input,
			)
		);
	}

	echo '</div></fieldset>';
}

/**
 * Replace variation dropdowns with color swatches (select kept for WooCommerce JS).
 *
 * @param string $html Dropdown HTML.
 * @param array  $args Dropdown args.
 * @return string
 */
function jofme_variation_swatches_html( $html, $args ) {
	$attribute = isset( $args['attribute'] ) ? (string) $args['attribute'] : '';
	if ( ! $attribute || ! taxonomy_exists( $attribute ) || ! jofme_is_swatch_attribute( $attribute ) ) {
		return $html;
	}

	$options   = isset( $args['options'] ) && is_array( $args['options'] ) ? $args['options'] : array();
	$product   = isset( $args['product'] ) ? $args['product'] : null;
	$selected  = isset( $args['selected'] ) ? (string) $args['selected'] : '';
	$swatches  = '<div class="jofme-swatches jofme-variation-swatches__list" role="listbox" aria-label="' . esc_attr( wc_attribute_label( $attribute ) ) . '">';

	if ( empty( $options ) && $product instanceof WC_Product ) {
		$options = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $options ) ) {
			$options = array();
		}
	}

	foreach ( $options as $option ) {
		$term = get_term_by( 'slug', $option, $attribute );
		$name = $term instanceof WP_Term ? $term->name : (string) $option;
		$color = $term instanceof WP_Term ? jofme_term_swatch_color( $term ) : '';
		$is_selected = ( sanitize_title( $selected ) === sanitize_title( $option ) );

		$swatches .= jofme_swatch_html(
			array(
				'tag'      => 'button',
				'name'     => $name,
				'color'    => $color,
				'value'    => $option,
				'selected' => $is_selected,
			)
		);
	}

	$swatches .= '</div>';

	return '<div class="jofme-variation-swatches" data-attribute="' . esc_attr( $attribute ) . '">' . $swatches . $html . '</div>';
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'jofme_variation_swatches_html', 20, 2 );
