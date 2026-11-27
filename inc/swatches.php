<?php
/**
 * Attribute swatches (color + image) for cards, filters and product pages.
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
 * Swatch display type for an attribute: color, image, or empty (off).
 *
 * Explicit Products → Attributes setting wins. Legacy option value "yes"
 * maps to color. Slug heuristics keep color/finish/metal working.
 *
 * @param string $attribute Taxonomy or attribute name.
 * @return string color|image|'' 
 */
function jofme_attribute_swatch_type( $attribute ) {
	$tax = jofme_get_attribute_object( $attribute );
	if ( $tax ) {
		$flag = get_option( 'jofme_attribute_swatch_' . (int) $tax->attribute_id, '' );
		if ( in_array( $flag, array( 'color', 'image' ), true ) ) {
			return $flag;
		}
		if ( 'yes' === $flag ) {
			return 'color';
		}
		if ( 'no' === $flag ) {
			return '';
		}
	}

	$name = $tax ? $tax->attribute_name : (string) $attribute;
	if ( preg_match( '/image|photo|εικον|pattern|print|texture/u', $name ) ) {
		return 'image';
	}
	if ( preg_match( '/color|colour|χρωμ|finish|metal/u', $name ) ) {
		return 'color';
	}

	return '';
}

/**
 * Whether an attribute should render as swatches (color or image).
 *
 * @param string $attribute Taxonomy or attribute name.
 * @return bool
 */
function jofme_is_swatch_attribute( $attribute ) {
	return '' !== jofme_attribute_swatch_type( $attribute );
}

/**
 * Size-like attributes do not drive the product gallery.
 *
 * Picking a size should not swap photos to another color, and a size
 * variation that only inherits the parent image should keep the current set.
 *
 * @param string $attribute Taxonomy or attribute name.
 * @return bool
 */
function jofme_is_size_attribute( $attribute ) {
	$parts = array( (string) $attribute );
	$tax   = jofme_get_attribute_object( $attribute );
	if ( $tax ) {
		$parts[] = (string) $tax->attribute_name;
		$parts[] = (string) $tax->attribute_label;
	}

	return (bool) preg_match(
		'/size|megethos|μεγεθ|μήκος|μηκος|length|diameter|διάμετρ|διαμετρ|διαστα|δαχτυλ/ui',
		implode( ' ', $parts )
	);
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
 * Attachment ID for a term image swatch.
 *
 * @param WP_Term|object $term Term.
 * @return int
 */
function jofme_term_swatch_image_id( $term ) {
	if ( ! $term || empty( $term->term_id ) ) {
		return 0;
	}

	return absint( get_term_meta( (int) $term->term_id, 'jofme_swatch_image', true ) );
}

/**
 * Image URL for a term image swatch.
 *
 * @param WP_Term|object $term Term.
 * @param string         $size Image size.
 * @return string
 */
function jofme_term_swatch_image_url( $term, $size = 'thumbnail' ) {
	$id = jofme_term_swatch_image_id( $term );
	if ( ! $id ) {
		return '';
	}

	$url = '';
	if ( function_exists( 'wp_get_attachment_image_url' ) ) {
		$url = wp_get_attachment_image_url( $id, $size );
		if ( ! $url && 'thumbnail' !== $size ) {
			$url = wp_get_attachment_image_url( $id, 'thumbnail' );
		}
		if ( ! $url ) {
			$url = wp_get_attachment_image_url( $id, 'full' );
		}
	}
	if ( ! $url && function_exists( 'wp_get_attachment_url' ) ) {
		$url = wp_get_attachment_url( $id );
	}

	return $url ? $url : '';
}

/**
 * Color / image args for a term based on attribute swatch type.
 *
 * Image type uses the term image (falls back to color if missing).
 * Color type prefers a term image when one is set, otherwise the hex color —
 * so uploading a term image always surfaces on the storefront.
 *
 * @param string         $attribute Taxonomy or attribute name.
 * @param WP_Term|object $term      Term.
 * @return array{color:string,image:string}
 */
function jofme_term_swatch_visuals( $attribute, $term ) {
	$type  = jofme_attribute_swatch_type( $attribute );
	$image = jofme_term_swatch_image_url( $term );
	$color = jofme_term_swatch_color( $term );

	if ( 'image' === $type ) {
		return array(
			'color' => $image ? '' : $color,
			'image' => $image,
		);
	}
	if ( 'color' === $type ) {
		if ( $image ) {
			return array(
				'color' => '',
				'image' => $image,
			);
		}
		return array(
			'color' => $color,
			'image' => '',
		);
	}

	return array(
		'color' => '',
		'image' => '',
	);
}

/**
 * Markup for one swatch control (color, image, or text).
 *
 * @param array $args {
 *     @type string $tag       HTML tag: a|button|label.
 *     @type string $name      Term label.
 *     @type string $color     Hex color.
 *     @type string $image     Image URL for image swatches.
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
			'image'    => '',
			'url'      => '',
			'value'    => '',
			'selected' => false,
			'disabled' => false,
			'input'    => '',
		)
	);

	$classes = array( 'jofme-swatch' );
	$style   = '';
	if ( $args['image'] ) {
		$classes[] = 'is-image';
	} elseif ( $args['color'] ) {
		$classes[] = 'is-color';
		$style     = '--swatch:' . esc_attr( $args['color'] );
	}
	if ( $args['selected'] ) {
		$classes[] = 'is-selected';
	}

	$attrs = 'class="' . esc_attr( implode( ' ', $classes ) ) . '" title="' . esc_attr( $args['name'] ) . '"';
	if ( $style ) {
		$attrs .= ' style="' . esc_attr( $style ) . '"';
	}
	if ( '' !== $args['value'] ) {
		$attrs .= ' data-value="' . esc_attr( $args['value'] ) . '"';
	}

	$visual = $args['image']
		? '<span class="jofme-swatch__image" aria-hidden="true"><img src="' . esc_url( $args['image'] ) . '" alt="" loading="lazy" decoding="async"></span><span class="screen-reader-text">' . esc_html( $args['name'] ) . '</span>'
		: ( $args['color']
			? '<span class="screen-reader-text">' . esc_html( $args['name'] ) . '</span>'
			: '<span>' . esc_html( $args['name'] ) . '</span>' );

	if ( 'a' === $args['tag'] ) {
		return '<a ' . $attrs . ' href="' . esc_url( $args['url'] ) . '">' . $visual . '</a>';
	}

	if ( 'label' === $args['tag'] ) {
		return '<label ' . $attrs . '>' . $args['input'] . $visual . '</label>';
	}

	$attrs .= ' type="button" aria-pressed="' . ( $args['selected'] ? 'true' : 'false' ) . '"';
	if ( $args['disabled'] ) {
		$attrs .= ' disabled';
	}

	return '<button ' . $attrs . '>' . $visual . '</button>';
}

/**
 * Swatch type choices for attribute admin.
 *
 * @return array<string,string>
 */
function jofme_attribute_swatch_type_choices() {
	return array(
		'no'    => __( 'None (dropdown / text)', 'jofme' ),
		'color' => __( 'Color swatches', 'jofme' ),
		'image' => __( 'Image swatches', 'jofme' ),
	);
}

/**
 * Normalize stored attribute swatch option to color|image|no|''.
 *
 * @param string $value Raw option.
 * @return string
 */
function jofme_normalize_attribute_swatch_option( $value ) {
	$value = (string) $value;
	if ( in_array( $value, array( 'color', 'image', 'no' ), true ) ) {
		return $value;
	}
	if ( 'yes' === $value ) {
		return 'color';
	}
	return $value;
}

/**
 * Attribute admin: choose swatch type for this attribute.
 */
function jofme_attribute_swatch_add_field() {
	$choices = jofme_attribute_swatch_type_choices();
	?>
	<div class="form-field">
		<label for="jofme_attribute_swatch_type"><?php esc_html_e( 'Swatch display', 'jofme' ); ?></label>
		<select name="jofme_attribute_swatch_type" id="jofme_attribute_swatch_type">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, 'no' ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p><?php esc_html_e( 'Show color or image swatches on product cards, catalog filters, and the product page. Configure each term under Configure terms.', 'jofme' ); ?></p>
	</div>
	<?php
}
add_action( 'woocommerce_after_add_attribute_fields', 'jofme_attribute_swatch_add_field' );

/**
 * Attribute admin edit field.
 */
function jofme_attribute_swatch_edit_field() {
	$id      = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin screen id.
	$raw     = $id ? get_option( 'jofme_attribute_swatch_' . $id, '' ) : '';
	$current = jofme_normalize_attribute_swatch_option( $raw );

	// No saved preference yet: pre-select from slug heuristic.
	if ( '' === $raw && $id ) {
		$attr = function_exists( 'wc_get_attribute' ) ? wc_get_attribute( $id ) : null;
		if ( $attr && ! empty( $attr->slug ) ) {
			$current = jofme_attribute_swatch_type( $attr->slug );
			if ( '' === $current ) {
				$current = 'no';
			}
		} else {
			$current = 'no';
		}
	}
	if ( '' === $current ) {
		$current = 'no';
	}

	$choices = jofme_attribute_swatch_type_choices();
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="jofme_attribute_swatch_type"><?php esc_html_e( 'Swatch display', 'jofme' ); ?></label>
		</th>
		<td>
			<select name="jofme_attribute_swatch_type" id="jofme_attribute_swatch_type">
				<?php foreach ( $choices as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Show color or image swatches on product cards, catalog filters, and the product page. Configure each term under Configure terms.', 'jofme' ); ?></p>
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

	// Only persist when the attribute admin form posted our field — never wipe
	// the option on programmatic wc_update_attribute() calls.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WC attribute form nonce already verified.
	if ( ! isset( $_POST['jofme_attribute_swatch_type'] ) && ! isset( $_POST['jofme_attribute_swatch'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WC attribute form nonce already verified.
	$raw = isset( $_POST['jofme_attribute_swatch_type'] ) ? sanitize_key( wp_unslash( $_POST['jofme_attribute_swatch_type'] ) ) : '';
	// Legacy checkbox fallback if an older form posts the old field.
	if ( '' === $raw && isset( $_POST['jofme_attribute_swatch'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$raw = ! empty( $_POST['jofme_attribute_swatch'] ) ? 'color' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
	if ( ! in_array( $raw, array( 'color', 'image', 'no' ), true ) ) {
		$raw = 'no';
	}
	update_option( 'jofme_attribute_swatch_' . $id, $raw, false );
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
 * Term swatch field markup — color + image (add + edit screens).
 *
 * @param WP_Term|string $term Term on edit, taxonomy name on add.
 */
function jofme_swatch_term_field( $term = null ) {
	$is_edit  = $term instanceof WP_Term;
	$value    = $is_edit ? (string) get_term_meta( $term->term_id, 'jofme_swatch_color', true ) : '';
	$image_id = $is_edit ? absint( get_term_meta( $term->term_id, 'jofme_swatch_image', true ) ) : 0;
	$picker   = sanitize_hex_color( $value ) ? $value : '#c6a15b';
	$nonce    = wp_nonce_field( 'jofme_save_swatch', 'jofme_swatch_nonce', true, false );
	$preview  = $image_id && function_exists( 'wp_get_attachment_image_url' ) ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';

	$color  = $nonce;
	$color .= '<div class="jofme-swatch-picker">';
	$color .= '<input type="color" data-swatch-picker value="' . esc_attr( $picker ) . '" aria-label="' . esc_attr__( 'Choose swatch color', 'jofme' ) . '">';
	$color .= '<input id="jofme-swatch-color" name="jofme_swatch_color" type="text" data-swatch-hex placeholder="#c6a15b" pattern="^#([A-Fa-f0-9]{6})$" value="' . esc_attr( $value ) . '">';
	$color .= '</div>';
	$color .= '<p>' . esc_html__( 'Used when this attribute is set to Color swatches. Leave blank to use a default from the term slug.', 'jofme' ) . '</p>';

	$image  = '<div class="jofme-swatch-image-field">';
	$image .= '<input type="hidden" id="jofme-swatch-image" name="jofme_swatch_image" value="' . absint( $image_id ) . '">';
	$image .= '<div class="jofme-swatch-image-preview"' . ( $preview ? '' : ' hidden' ) . '><img' . ( $preview ? ' src="' . esc_url( $preview ) . '"' : '' ) . ' alt=""></div>';
	$image .= '<p><button type="button" class="button jofme-swatch-image-select">' . esc_html__( 'Choose image', 'jofme' ) . '</button> ';
	$image .= '<button type="button" class="button jofme-swatch-image-remove">' . esc_html__( 'Remove', 'jofme' ) . '</button></p>';
	$image .= '<p>' . esc_html__( 'Used when this attribute is set to Image swatches.', 'jofme' ) . '</p>';
	$image .= '</div>';

	if ( $is_edit ) {
		echo '<tr class="form-field"><th><label for="jofme-swatch-color">' . esc_html__( 'Swatch color', 'jofme' ) . '</label></th><td>' . $color . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled with escaped parts above.
		echo '<tr class="form-field"><th><label for="jofme-swatch-image">' . esc_html__( 'Swatch image', 'jofme' ) . '</label></th><td>' . $image . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		echo '<div class="form-field"><label for="jofme-swatch-color">' . esc_html__( 'Swatch color', 'jofme' ) . '</label>' . $color . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="form-field"><label for="jofme-swatch-image">' . esc_html__( 'Swatch image', 'jofme' ) . '</label>' . $image . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Save term swatch color + image.
 *
 * @param int $term_id Term ID.
 */
function jofme_save_swatch( $term_id ) {
	if (
		! current_user_can( 'manage_product_terms' )
		|| ! isset( $_POST['jofme_swatch_nonce'] )
		|| ! is_scalar( $_POST['jofme_swatch_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jofme_swatch_nonce'] ) ), 'jofme_save_swatch' )
	) {
		return;
	}

	if ( isset( $_POST['jofme_swatch_color'] ) && is_scalar( $_POST['jofme_swatch_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['jofme_swatch_color'] ) );
		update_term_meta( $term_id, 'jofme_swatch_color', $color ? $color : '' );
	}

	if ( isset( $_POST['jofme_swatch_image'] ) && is_scalar( $_POST['jofme_swatch_image'] ) ) {
		$image_id = absint( wp_unslash( $_POST['jofme_swatch_image'] ) );
		// Accept any attachment that resolves to a URL (incl. webp). Only reject
		// when the ID is clearly not an attachment.
		if ( $image_id && function_exists( 'get_post_type' ) && 'attachment' !== get_post_type( $image_id ) ) {
			$image_id = 0;
		}
		if ( $image_id ) {
			update_term_meta( $term_id, 'jofme_swatch_image', $image_id );
		} else {
			delete_term_meta( $term_id, 'jofme_swatch_image' );
		}
	}
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
 * Admin assets for color picker + image media frame.
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

	wp_enqueue_media();
	wp_enqueue_style( 'jofme-swatches-admin', JOFME_URI . '/assets/css/swatches-admin.css', array(), JOFME_VERSION );
	wp_enqueue_script(
		'jofme-swatches-admin',
		JOFME_URI . '/assets/js/swatches-admin.js',
		array( 'jquery', 'media-editor', 'media-views' ),
		JOFME_VERSION,
		true
	);
	wp_localize_script(
		'jofme-swatches-admin',
		'jofmeSwatch',
		array(
			'title'  => __( 'Choose swatch image', 'jofme' ),
			'button' => __( 'Use as swatch', 'jofme' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'jofme_swatch_admin_assets' );

/**
 * Frontend swatch assets.
 */
function jofme_swatch_assets() {
	wp_register_style( 'jofme-swatches', JOFME_URI . '/assets/css/swatches.css', array( 'jofme-premium' ), JOFME_VERSION );
	wp_enqueue_style( 'jofme-swatches' );

	if ( is_product() || is_shop() || is_product_taxonomy() ) {
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
 * Active color/swatch filters from the catalog query (filter_color=xriso, etc.).
 *
 * @return array<string,string> Taxonomy => term slug.
 */
function jofme_active_swatch_filters() {
	$active = array();
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return $active;
	}

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
		if ( ! jofme_is_swatch_attribute( $taxonomy ) ) {
			continue;
		}

		$candidates = array(
			'filter_' . $attribute->attribute_name,
			'filter_' . $taxonomy,
			'attribute_' . $taxonomy,
		);
		$value      = '';
		foreach ( $candidates as $key ) {
			$value = function_exists( 'jofme_filter_value' ) ? jofme_filter_value( $key ) : ( isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' !== $value ) {
				break;
			}
		}
		if ( '' === $value ) {
			continue;
		}
		// Woo may pass multiple values comma-separated; use the first.
		$parts = array_filter( array_map( 'sanitize_title', explode( ',', $value ) ) );
		if ( $parts ) {
			$active[ $taxonomy ] = reset( $parts );
		}
	}

	return $active;
}

/**
 * Find a visible variation matching the active swatch filters.
 *
 * @param WC_Product $product Product.
 * @return array|null Variation data from get_available_variations().
 */
function jofme_matching_variation_for_filters( $product ) {
	$active = jofme_active_swatch_filters();
	if ( ! $product || ! $active || ! $product->is_type( 'variable' ) ) {
		return null;
	}

	$best = null;
	foreach ( $product->get_available_variations() as $variation ) {
		if ( isset( $variation['variation_is_visible'] ) && ! $variation['variation_is_visible'] ) {
			continue;
		}
		$attrs = isset( $variation['attributes'] ) && is_array( $variation['attributes'] ) ? $variation['attributes'] : array();
		$match = true;
		foreach ( $active as $taxonomy => $slug ) {
			$key   = 'attribute_' . $taxonomy;
			$value = isset( $attrs[ $key ] ) ? (string) $attrs[ $key ] : '';
			// Empty attribute on a variation means "any".
			if ( '' !== $value && $value !== $slug ) {
				$match = false;
				break;
			}
		}
		if ( ! $match ) {
			continue;
		}
		$best = $variation;
		if ( ! empty( $variation['image_id'] ) || ( ! empty( $variation['image']['src'] ) ) ) {
			return $variation;
		}
	}

	return $best;
}

/**
 * Append active swatch attributes to loop product URLs so the PDP opens preselected.
 *
 * @param string     $link    Product URL.
 * @param WC_Product $product Product.
 * @return string
 */
function jofme_loop_product_link_with_filters( $link, $product ) {
	$active = jofme_active_swatch_filters();
	if ( ! $active || ! $product ) {
		return $link;
	}
	foreach ( $active as $taxonomy => $slug ) {
		$link = add_query_arg( 'attribute_' . $taxonomy, $slug, $link );
	}
	return $link;
}
add_filter( 'woocommerce_loop_product_link', 'jofme_loop_product_link_with_filters', 20, 2 );

/**
 * Swatch markup for a product card (same as archive).
 *
 * @param WC_Product|null $product Product.
 * @return string
 */
function jofme_product_card_swatches_html( $product = null ) {
	if ( ! $product && isset( $GLOBALS['product'] ) ) {
		$product = $GLOBALS['product'];
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$active = function_exists( 'jofme_active_swatch_filters' ) ? jofme_active_swatch_filters() : array();
	$html   = '';

	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->is_taxonomy() || ! jofme_is_swatch_attribute( $attribute->get_name() ) ) {
			continue;
		}

		$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name() );
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}

		$taxonomy = $attribute->get_name();
		$html    .= '<div class="jofme-swatches" aria-label="' . esc_attr( wc_attribute_label( $taxonomy ) ) . '">';
		foreach ( $terms as $term ) {
			$url      = add_query_arg( 'attribute_' . $taxonomy, $term->slug, get_permalink( $product->get_id() ) );
			$selected = isset( $active[ $taxonomy ] ) && $active[ $taxonomy ] === $term->slug;
			$visuals  = jofme_term_swatch_visuals( $taxonomy, $term );
			$html    .= jofme_swatch_html(
				array(
					'tag'      => 'a',
					'name'     => $term->name,
					'color'    => $visuals['color'],
					'image'    => $visuals['image'],
					'url'      => $url,
					'value'    => $term->slug,
					'selected' => $selected,
				)
			);
		}
		$html .= '</div>';
	}

	return $html;
}

/**
 * Color swatches on catalog product cards.
 */
function jofme_card_swatches() {
	global $product;
	echo jofme_product_card_swatches_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with escaped helpers.
}
// Swatch links must follow the main product link, never be nested inside it.
add_action( 'woocommerce_after_shop_loop_item', 'jofme_card_swatches', 7 );

/**
 * Catalog filter control: accordion checklist for every attribute (incl. color).
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $key      Query arg.
 * @param string $label    Visible label.
 */
function jofme_filter_attribute_control( $taxonomy, $key, $label ) {
	if ( function_exists( 'jofme_filter_checklist' ) ) {
		jofme_filter_checklist( $taxonomy, $key, $label );
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
	$terms = function_exists( 'jofme_catalog_filter_terms' )
		? jofme_catalog_filter_terms( $taxonomy )
		: get_terms(
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
		$id      = 'jofme-filter-' . sanitize_html_class( $key . '-' . $term->slug );
		$visuals = jofme_term_swatch_visuals( $taxonomy, $term );
		$input   = '<input id="' . esc_attr( $id ) . '" type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( $current, $term->slug, false ) . '>';
		echo jofme_swatch_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'tag'      => 'label',
				'name'     => $term->name,
				'color'    => $visuals['color'],
				'image'    => $visuals['image'],
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
		$term        = get_term_by( 'slug', $option, $attribute );
		$name        = $term instanceof WP_Term ? $term->name : (string) $option;
		$visuals     = $term instanceof WP_Term ? jofme_term_swatch_visuals( $attribute, $term ) : array( 'color' => '', 'image' => '' );
		$is_selected = ( sanitize_title( $selected ) === sanitize_title( $option ) );

		$swatches .= jofme_swatch_html(
			array(
				'tag'      => 'button',
				'name'     => $name,
				'color'    => $visuals['color'],
				'image'    => $visuals['image'],
				'value'    => $option,
				'selected' => $is_selected,
			)
		);
	}

	$swatches .= '</div>';

	$driver = jofme_is_size_attribute( $attribute ) ? '0' : '1';

	return '<div class="jofme-variation-swatches" data-attribute="' . esc_attr( $attribute ) . '" data-jofme-gallery-driver="' . esc_attr( $driver ) . '">' . $swatches . $html . '</div>';
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'jofme_variation_swatches_html', 20, 2 );
