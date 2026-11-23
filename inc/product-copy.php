<?php
/**
 * Product page copy: cart labels, stock, shipping, related/upsells, Instagram help.
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Default product-copy theme mods.
 *
 * @return array<string,string|int>
 */
function jofme_product_copy_defaults() {
	return array(
		'jofme_add_to_cart_text'       => 'Στο καλάθι',
		'jofme_added_to_cart_text'     => 'Μπήκε στο καλάθι',
		'jofme_free_shipping_text'     => 'Δωρεάν αποστολή σε όλη την Ελλάδα για αγορές άνω των 45€.',
		'jofme_low_stock_text'         => 'Έμειναν μόνο %d',
		'jofme_low_stock_threshold'    => 5,
		'jofme_sold_out_text'          => 'Sold out',
		'jofme_restock_notify_text'    => 'Ενημέρωσέ με όταν ξανάρθει',
		'jofme_upsells_heading'        => 'Ταιριάζει τέλεια με…',
		'jofme_related_heading'        => 'Μπορεί να σου αρέσουν',
		'jofme_styling_help_text'      => 'Δεν είσαι σίγουρη εάν σου πάει; Ρώτησέ μας',
		'jofme_styling_help_url'       => '',
	);
}

/**
 * Get a product-copy string (Customizer → optional WPML).
 *
 * @param string $key Setting key without jofme_ prefix segment handled via map.
 * @return string
 */
function jofme_get_product_copy( $key ) {
	$defaults = jofme_product_copy_defaults();
	$map      = array(
		'add_to_cart'    => array( 'jofme_add_to_cart_text', 'Add to cart button', 'jofme_add_to_cart_text' ),
		'added_to_cart'  => array( 'jofme_added_to_cart_text', 'Added to cart message', 'jofme_added_to_cart_text' ),
		'free_shipping'  => array( 'jofme_free_shipping_text', 'Free shipping line', 'jofme_free_shipping_text' ),
		'low_stock'      => array( 'jofme_low_stock_text', 'Low stock text', 'jofme_low_stock_text' ),
		'sold_out'       => array( 'jofme_sold_out_text', 'Sold out text', 'jofme_sold_out_text' ),
		'restock_notify' => array( 'jofme_restock_notify_text', 'Restock notify text', 'jofme_restock_notify_text' ),
		'upsells'        => array( 'jofme_upsells_heading', 'Upsells heading', 'jofme_upsells_heading' ),
		'related'        => array( 'jofme_related_heading', 'Related products heading', 'jofme_related_heading' ),
		'styling_help'   => array( 'jofme_styling_help_text', 'Styling help text', 'jofme_styling_help_text' ),
	);

	if ( ! isset( $map[ $key ] ) ) {
		return '';
	}

	$mod_key  = $map[ $key ][0];
	$default  = isset( $defaults[ $mod_key ] ) ? (string) $defaults[ $mod_key ] : '';
	$value    = get_theme_mod( $mod_key, $default );
	$value    = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		$value = $default;
	}

	if ( function_exists( 'jofme_translate_mod' ) ) {
		return jofme_translate_mod( $value, $map[ $key ][1], $map[ $key ][2] );
	}

	return $value;
}

/**
 * Low-stock threshold (qty at or below shows the low-stock line).
 *
 * @return int
 */
function jofme_low_stock_threshold() {
	$defaults = jofme_product_copy_defaults();
	return max( 1, min( 50, absint( get_theme_mod( 'jofme_low_stock_threshold', $defaults['jofme_low_stock_threshold'] ) ) ) );
}

/**
 * Instagram / styling-help URL from Customizer.
 *
 * @return string
 */
function jofme_styling_help_url() {
	$url = get_theme_mod( 'jofme_styling_help_url', '' );
	$url = is_string( $url ) ? esc_url_raw( trim( $url ) ) : '';
	return $url;
}

/**
 * Customizer: Product copy section.
 *
 * @param WP_Customize_Manager $manager Manager.
 */
function jofme_product_copy_customize( $manager ) {
	$defaults = jofme_product_copy_defaults();

	$manager->add_section(
		'jofme_product_copy',
		array(
			'title'       => __( 'Product copy', 'jofme' ),
			'description' => __( 'Labels and CTAs on the single product page.', 'jofme' ),
			'panel'       => 'jofme_options',
			'priority'    => 45,
		)
	);

	$fields = array(
		'jofme_add_to_cart_text'    => array( __( 'Add to cart button', 'jofme' ), 'text' ),
		'jofme_added_to_cart_text'  => array( __( 'Added to cart message', 'jofme' ), 'text' ),
		'jofme_free_shipping_text'  => array( __( 'Free shipping line', 'jofme' ), 'textarea' ),
		'jofme_low_stock_text'      => array( __( 'Low stock text (use %d for quantity)', 'jofme' ), 'text' ),
		'jofme_sold_out_text'       => array( __( 'Sold out text', 'jofme' ), 'text' ),
		'jofme_restock_notify_text' => array( __( 'Restock notify button', 'jofme' ), 'text' ),
		'jofme_upsells_heading'     => array( __( 'Upsells / pairs-well heading', 'jofme' ), 'text' ),
		'jofme_related_heading'     => array( __( 'Related products heading', 'jofme' ), 'text' ),
		'jofme_styling_help_text'   => array( __( 'Styling help text', 'jofme' ), 'text' ),
	);

	foreach ( $fields as $id => $config ) {
		$manager->add_setting(
			$id,
			array(
				'default'           => $defaults[ $id ],
				'sanitize_callback' => 'textarea' === $config[1] ? 'sanitize_textarea_field' : 'sanitize_text_field',
			)
		);
		$manager->add_control(
			$id,
			array(
				'label'   => $config[0],
				'section' => 'jofme_product_copy',
				'type'    => $config[1],
			)
		);
	}

	$manager->add_setting(
		'jofme_low_stock_threshold',
		array(
			'default'           => $defaults['jofme_low_stock_threshold'],
			'sanitize_callback' => static function ( $value ) {
				return max( 1, min( 50, absint( $value ) ) );
			},
		)
	);
	$manager->add_control(
		'jofme_low_stock_threshold',
		array(
			'label'       => __( 'Low stock threshold', 'jofme' ),
			'description' => __( 'Show the low-stock line when quantity is at or below this number.', 'jofme' ),
			'section'     => 'jofme_product_copy',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 50,
			),
		)
	);

	$manager->add_setting(
		'jofme_styling_help_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$manager->add_control(
		'jofme_styling_help_url',
		array(
			'label'       => __( 'Styling help link (Instagram DM)', 'jofme' ),
			'description' => __( 'e.g. https://ig.me/m/yourbrand or your Instagram profile URL. Leave empty to hide the CTA.', 'jofme' ),
			'section'     => 'jofme_product_copy',
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'jofme_product_copy_customize', 30 );

/**
 * Single product add-to-cart label.
 *
 * @param string $text Default text.
 * @return string
 */
function jofme_single_add_to_cart_text( $text ) {
	$custom = jofme_get_product_copy( 'add_to_cart' );
	return $custom ? $custom : $text;
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'jofme_single_add_to_cart_text' );

/**
 * Loop add-to-cart label for simple in-stock products.
 *
 * @param string     $text    Default text.
 * @param WC_Product $product Product.
 * @return string
 */
function jofme_loop_add_to_cart_text( $text, $product ) {
	if ( $product instanceof WC_Product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		$custom = jofme_get_product_copy( 'add_to_cart' );
		return $custom ? $custom : $text;
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'jofme_loop_add_to_cart_text', 10, 2 );

/**
 * Simplify the “added to cart” notice.
 *
 * @param string $message  HTML message.
 * @param array  $products Product qty map.
 * @return string
 */
function jofme_add_to_cart_message_html( $message, $products ) {
	$text = jofme_get_product_copy( 'added_to_cart' );
	if ( ! $text ) {
		return $message;
	}

	return sprintf(
		'<a href="%1$s" tabindex="1" class="button wc-forward">%2$s</a> %3$s',
		esc_url( wc_get_cart_url() ),
		esc_html__( 'View cart', 'woocommerce' ),
		esc_html( $text )
	);
}
add_filter( 'wc_add_to_cart_message_html', 'jofme_add_to_cart_message_html', 10, 2 );

/**
 * Availability: sold out + low stock wording.
 *
 * @param array      $availability Availability data.
 * @param WC_Product $product      Product.
 * @return array
 */
function jofme_product_availability( $availability, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $availability;
	}

	if ( ! $product->is_in_stock() ) {
		$sold = jofme_get_product_copy( 'sold_out' );
		if ( $sold ) {
			$availability['availability'] = $sold;
			$availability['class']        = 'out-of-stock';
		}
		return $availability;
	}

	if ( $product->managing_stock() ) {
		$qty = $product->get_stock_quantity();
		if ( null !== $qty && $qty > 0 && $qty <= jofme_low_stock_threshold() ) {
			$tpl = jofme_get_product_copy( 'low_stock' );
			if ( $tpl ) {
				// Support "%d" or a bare "Χ"/"X" placeholder from Customizer.
				if ( false !== strpos( $tpl, '%d' ) || false !== strpos( $tpl, '%s' ) ) {
					$availability['availability'] = sprintf( $tpl, (int) $qty );
				} else {
					$availability['availability'] = preg_replace( '/[ΧXχx]/u', (string) (int) $qty, $tpl, 1 );
				}
				$availability['class'] = trim( ( isset( $availability['class'] ) ? $availability['class'] : '' ) . ' low-stock' );
			}
		}
	}

	return $availability;
}
add_filter( 'woocommerce_get_availability', 'jofme_product_availability', 10, 2 );

/**
 * Related products heading.
 *
 * @param string $heading Default heading.
 * @return string
 */
function jofme_related_products_heading( $heading ) {
	$custom = jofme_get_product_copy( 'related' );
	return $custom ? $custom : $heading;
}
add_filter( 'woocommerce_product_related_products_heading', 'jofme_related_products_heading' );

/**
 * Upsells heading (“pairs well with”).
 *
 * @param string $heading Default heading.
 * @return string
 */
function jofme_upsells_products_heading( $heading ) {
	$custom = jofme_get_product_copy( 'upsells' );
	return $custom ? $custom : $heading;
}
add_filter( 'woocommerce_product_upsells_products_heading', 'jofme_upsells_products_heading' );

/**
 * Free shipping line under the add-to-cart form.
 */
function jofme_render_free_shipping_line() {
	if ( ! is_product() ) {
		return;
	}
	$text = jofme_get_product_copy( 'free_shipping' );
	if ( ! $text ) {
		return;
	}
	echo '<p class="jofme-product-shipping">' . esc_html( $text ) . '</p>';
}
add_action( 'woocommerce_after_add_to_cart_form', 'jofme_render_free_shipping_line', 15 );

/**
 * Styling help → Instagram DM CTA.
 */
function jofme_render_styling_help() {
	if ( ! is_product() ) {
		return;
	}
	$url  = jofme_styling_help_url();
	$text = jofme_get_product_copy( 'styling_help' );
	if ( ! $url || ! $text ) {
		return;
	}
	printf(
		'<p class="jofme-product-styling-help"><a class="jofme-product-styling-help__link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>',
		esc_url( $url ),
		esc_html( $text )
	);
}
add_action( 'woocommerce_after_add_to_cart_form', 'jofme_render_styling_help', 20 );

/**
 * Restock notify form for out-of-stock products.
 */
function jofme_render_restock_notify() {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product instanceof WC_Product || $product->is_in_stock() ) {
		return;
	}

	// Variable products: stock depends on variation; still show a general CTA.
	$label = jofme_get_product_copy( 'restock_notify' );
	if ( ! $label ) {
		return;
	}

	$product_id = $product->get_id();
	$uid        = (string) $product_id;
	?>
	<div class="jofme-restock-notify" data-product-id="<?php echo esc_attr( $uid ); ?>">
		<p class="jofme-restock-notify__title" id="jofme-restock-title-<?php echo esc_attr( $uid ); ?>">
			<?php echo esc_html( $label ); ?>
		</p>
		<form
			id="jofme-restock-form-<?php echo esc_attr( $uid ); ?>"
			class="jofme-restock-notify__form"
			aria-labelledby="jofme-restock-title-<?php echo esc_attr( $uid ); ?>"
			novalidate
		>
			<label class="screen-reader-text" for="jofme-restock-email-<?php echo esc_attr( $uid ); ?>">
				<?php esc_html_e( 'Email', 'jofme' ); ?>
			</label>
			<div class="jofme-restock-notify__row">
				<input
					type="email"
					id="jofme-restock-email-<?php echo esc_attr( $uid ); ?>"
					class="jofme-restock-notify__email"
					name="email"
					required
					autocomplete="email"
					inputmode="email"
					placeholder="<?php echo esc_attr__( 'Το email σου', 'jofme' ); ?>"
				/>
				<button type="submit" class="jofme-restock-notify__submit">
					<span class="jofme-restock-notify__submit-label"><?php echo esc_html__( 'Αποστολή', 'jofme' ); ?></span>
					<span class="jofme-restock-notify__submit-busy" hidden><?php echo esc_html__( 'Αποστολή…', 'jofme' ); ?></span>
				</button>
			</div>
			<p class="jofme-restock-notify__hint"><?php echo esc_html__( 'Θα σου στείλουμε ένα email μόλις ξανάρθει.', 'jofme' ); ?></p>
			<p class="jofme-restock-notify__status" role="status" aria-live="polite" hidden></p>
		</form>
		<div class="jofme-restock-notify__done" hidden>
			<p class="jofme-restock-notify__done-text"></p>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'jofme_render_restock_notify', 35 );

/**
 * Assets for restock notify on single products.
 */
function jofme_product_copy_assets() {
	if ( ! is_product() ) {
		return;
	}

	wp_enqueue_script(
		'jofme-product-copy',
		JOFME_URI . '/assets/js/product-copy.js',
		array(),
		JOFME_VERSION,
		true
	);
	wp_localize_script(
		'jofme-product-copy',
		'jofmeProductCopy',
		array(
			'url'     => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'jofme_restock_notify' ),
			'success' => __( 'Τέλεια — θα σε ενημερώσουμε μόλις ξανάρθει.', 'jofme' ),
			'error'   => __( 'Κάτι πήγε στραβά. Δοκίμασε ξανά.', 'jofme' ),
			'invalid' => __( 'Βάλε ένα έγκυρο email.', 'jofme' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'jofme_product_copy_assets', 30 );

/**
 * AJAX: store restock interest and email the shop admin.
 */
function jofme_restock_notify_ajax() {
	check_ajax_referer( 'jofme_restock_notify', 'nonce' );

	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	if ( ! is_email( $email ) || ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Βάλε ένα έγκυρο email.', 'jofme' ) ), 400 );
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'Κάτι πήγε στραβά. Δοκίμασε ξανά.', 'jofme' ) ), 400 );
	}

	$subscribers = get_post_meta( $product_id, '_jofme_restock_emails', true );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}
	$subscribers[ strtolower( $email ) ] = time();
	update_post_meta( $product_id, '_jofme_restock_emails', $subscribers );

	$admin = get_option( 'admin_email' );
	if ( is_email( $admin ) ) {
		$subject = sprintf(
			/* translators: %s: product name */
			__( '[Jofme] Restock request: %s', 'jofme' ),
			$product->get_name()
		);
		$body = sprintf(
			/* translators: 1: email, 2: product name, 3: product edit URL */
			__( "%1\$s asked to be notified when “%2\$s” is back in stock.\n\nEdit product: %3\$s\n", 'jofme' ),
			$email,
			$product->get_name(),
			admin_url( 'post.php?post=' . $product_id . '&action=edit' )
		);
		wp_mail( $admin, $subject, $body );
	}

	wp_send_json_success(
		array(
			'message' => __( 'Τέλεια — θα σε ενημερώσουμε μόλις ξανάρθει.', 'jofme' ),
		)
	);
}
add_action( 'wp_ajax_jofme_restock_notify', 'jofme_restock_notify_ajax' );
add_action( 'wp_ajax_nopriv_jofme_restock_notify', 'jofme_restock_notify_ajax' );
