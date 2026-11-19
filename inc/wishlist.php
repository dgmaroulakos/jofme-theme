<?php
/**
 * Product card wishlist heart + wishlist page (localStorage + YITH/TI bridges).
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether YITH Wishlist is available.
 *
 * @return bool
 */
function jofme_wishlist_has_yith() {
	return function_exists( 'YITH_WCWL' ) || shortcode_exists( 'yith_wcwl_add_to_wishlist' );
}

/**
 * Whether TI WooCommerce Wishlist is available.
 *
 * @return bool
 */
function jofme_wishlist_has_ti() {
	return function_exists( 'tinv_url_wishlist_default' ) || class_exists( 'TInvWL_Public_AddToWishlist' );
}

/**
 * Resolved wishlist page ID (Customizer / option / plugin).
 *
 * @return int
 */
function jofme_wishlist_page_id() {
	$page_id = absint( get_theme_mod( 'jofme_wishlist_page_id', 0 ) );
	if ( ! $page_id ) {
		$page_id = absint( get_option( 'jofme_wishlist_page_id', 0 ) );
	}
	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return $page_id;
	}
	return 0;
}

/**
 * Public wishlist page URL.
 *
 * @return string
 */
function jofme_wishlist_page_url() {
	if ( jofme_wishlist_has_yith() && function_exists( 'YITH_WCWL' ) ) {
		$yith = YITH_WCWL();
		if ( is_object( $yith ) && method_exists( $yith, 'get_wishlist_url' ) ) {
			$url = $yith->get_wishlist_url();
			if ( $url ) {
				return (string) $url;
			}
		}
	}

	if ( jofme_wishlist_has_ti() && function_exists( 'tinv_url_wishlist_default' ) ) {
		$url = tinv_url_wishlist_default();
		if ( $url ) {
			return (string) $url;
		}
	}

	$page_id = jofme_wishlist_page_id();
	return $page_id ? (string) get_permalink( $page_id ) : '';
}

/**
 * Whether the current view is the theme wishlist page.
 *
 * @return bool
 */
function jofme_is_wishlist_page() {
	$page_id = jofme_wishlist_page_id();
	return $page_id && is_page( $page_id );
}

/**
 * Create a Wishlist page with the shortcode when missing.
 *
 * @return int Page ID or 0.
 */
function jofme_wishlist_ensure_page() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return 0;
	}

	if ( jofme_wishlist_has_yith() || jofme_wishlist_has_ti() ) {
		return jofme_wishlist_page_id();
	}

	$page_id = jofme_wishlist_page_id();
	if ( $page_id ) {
		return $page_id;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'name'           => 'wishlist',
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		$page_id = (int) $existing[0];
	} else {
		$page_id = (int) wp_insert_post(
			array(
				'post_title'   => __( 'Wishlist', 'jofme' ),
				'post_name'    => 'wishlist',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[jofme_wishlist]',
			),
			true
		);
		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}
	}

	update_option( 'jofme_wishlist_page_id', $page_id );
	set_theme_mod( 'jofme_wishlist_page_id', $page_id );

	return $page_id;
}
add_action( 'after_switch_theme', 'jofme_wishlist_ensure_page' );

/**
 * Ensure a wishlist page exists once after update (non-destructive).
 */
function jofme_wishlist_maybe_ensure_page() {
	if ( get_option( 'jofme_wishlist_page_bootstrapped' ) ) {
		return;
	}
	if ( jofme_wishlist_ensure_page() ) {
		update_option( 'jofme_wishlist_page_bootstrapped', 1 );
	}
}
add_action( 'init', 'jofme_wishlist_maybe_ensure_page', 30 );

/**
 * Shared heart icon SVG for wishlist controls.
 *
 * @param int $size Pixel size.
 * @return string
 */
function jofme_wishlist_heart_svg( $size = 20 ) {
	$size = max( 16, absint( $size ) );
	return sprintf(
		'<svg class="jofme-wishlist__icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path class="jofme-wishlist__path" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		$size
	);
}

/**
 * Render wishlist control markup for a product.
 *
 * @param WC_Product|null $product Product.
 * @return string
 */
function jofme_wishlist_button_html( $product = null ) {
	if ( ! $product && isset( $GLOBALS['product'] ) ) {
		$product = $GLOBALS['product'];
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$product_id = (int) $product->get_id();
	if ( ! $product_id ) {
		return '';
	}

	if ( jofme_wishlist_has_yith() && shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) {
		return '<div class="jofme-wishlist jofme-wishlist--plugin">' . do_shortcode( '[yith_wcwl_add_to_wishlist product_id="' . $product_id . '"]' ) . '</div>';
	}

	if ( jofme_wishlist_has_ti() && function_exists( 'tinv_wishlist_button' ) ) {
		ob_start();
		echo '<div class="jofme-wishlist jofme-wishlist--plugin">';
		tinv_wishlist_button();
		echo '</div>';
		return (string) ob_get_clean();
	}

	$label_add    = __( 'Add to wishlist', 'jofme' );
	$label_remove = __( 'Remove from wishlist', 'jofme' );

	return sprintf(
		'<button type="button" class="jofme-wishlist" data-jofme-wishlist data-product-id="%1$d" aria-pressed="false" aria-label="%2$s" data-label-add="%2$s" data-label-remove="%3$s">%4$s</button>',
		$product_id,
		esc_attr( $label_add ),
		esc_attr( $label_remove ),
		jofme_wishlist_heart_svg( 20 )
	);
}

/**
 * Header wishlist link with count badge.
 *
 * @return string
 */
function jofme_wishlist_link() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$url = jofme_wishlist_page_url();
	if ( ! $url ) {
		return '';
	}

	ob_start();
	?>
	<a class="jofme-wishlist-link" href="<?php echo esc_url( $url ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'jofme' ); ?>">
		<span class="jofme-wishlist-link__icon" aria-hidden="true"><?php echo jofme_wishlist_heart_svg( 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG helper. ?></span>
		<span class="jofme-wishlist-count is-empty" data-jofme-wishlist-count>0</span>
	</a>
	<?php
	return (string) ob_get_clean();
}

/**
 * Native wishlist page shortcode markup.
 *
 * @return string
 */
function jofme_wishlist_shortcode() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	if ( jofme_wishlist_has_yith() && shortcode_exists( 'yith_wcwl_wishlist' ) ) {
		return '<div class="jofme-wishlist-page jofme-wishlist-page--plugin">' . do_shortcode( '[yith_wcwl_wishlist]' ) . '</div>';
	}

	if ( jofme_wishlist_has_ti() && shortcode_exists( 'ti_wishlistsview' ) ) {
		return '<div class="jofme-wishlist-page jofme-wishlist-page--plugin">' . do_shortcode( '[ti_wishlistsview]' ) . '</div>';
	}

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

	ob_start();
	?>
	<div class="jofme-wishlist-page" data-jofme-wishlist-page>
		<header class="jofme-wishlist-page__header">
			<p class="jofme-eyebrow"><?php echo esc_html__( 'Saved for later', 'jofme' ); ?></p>
			<h1 class="jofme-wishlist-page__title"><?php echo esc_html__( 'Wishlist', 'jofme' ); ?></h1>
			<p class="jofme-wishlist-page__count" data-jofme-wishlist-page-count hidden></p>
		</header>
		<div class="jofme-wishlist-page__status" data-jofme-wishlist-status role="status" aria-live="polite">
			<?php echo esc_html__( 'Loading your wishlist…', 'jofme' ); ?>
		</div>
		<div class="jofme-wishlist-page__empty" data-jofme-wishlist-empty hidden>
			<p><?php echo esc_html__( 'Your wishlist is empty.', 'jofme' ); ?></p>
			<?php if ( $shop_url ) : ?>
				<p><a class="button" href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Continue shopping', 'jofme' ); ?></a></p>
			<?php endif; ?>
		</div>
		<div class="jofme-wishlist-page__grid woocommerce" data-jofme-wishlist-grid hidden></div>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'jofme_wishlist', 'jofme_wishlist_shortcode' );

/**
 * Render product loop HTML for wishlist IDs.
 *
 * @param int[] $ids Product IDs.
 * @return string
 */
function jofme_wishlist_products_html( $ids ) {
	$ids = array_values(
		array_filter(
			array_map( 'absint', (array) $ids ),
			static function ( $id ) {
				return $id > 0;
			}
		)
	);
	$ids = array_slice( array_unique( $ids ), 0, 48 );
	if ( ! $ids ) {
		return '';
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => count( $ids ),
			'post__in'               => $ids,
			'orderby'                => 'post__in',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	woocommerce_product_loop_start();
	while ( $query->have_posts() ) {
		$query->the_post();
		wc_get_template_part( 'content', 'product' );
	}
	woocommerce_product_loop_end();
	wp_reset_postdata();

	return (string) ob_get_clean();
}

/**
 * AJAX: load wishlist product cards by ID list.
 */
function jofme_wishlist_ajax_products() {
	check_ajax_referer( 'jofme_wishlist', 'nonce' );

	$ids = array();
	if ( isset( $_POST['ids'] ) ) {
		$raw = wp_unslash( $_POST['ids'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$ids     = is_array( $decoded ) ? $decoded : preg_split( '/[,\s]+/', $raw );
		} elseif ( is_array( $raw ) ) {
			$ids = $raw;
		}
	}

	$clean = array_values(
		array_filter(
			array_map( 'absint', (array) $ids ),
			static function ( $id ) {
				return $id > 0;
			}
		)
	);
	$html = jofme_wishlist_products_html( $clean );
	wp_send_json_success(
		array(
			'html'  => $html,
			'count' => $html ? count( $clean ) : 0,
		)
	);
}
add_action( 'wp_ajax_jofme_wishlist_products', 'jofme_wishlist_ajax_products' );
add_action( 'wp_ajax_nopriv_jofme_wishlist_products', 'jofme_wishlist_ajax_products' );

/**
 * Enqueue wishlist assets on shop-like views and the wishlist page.
 */
function jofme_wishlist_assets() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	wp_enqueue_style(
		'jofme-wishlist',
		JOFME_URI . '/assets/css/wishlist.css',
		array( 'jofme-premium' ),
		JOFME_VERSION
	);

	if ( jofme_wishlist_has_yith() || jofme_wishlist_has_ti() ) {
		return;
	}

	wp_enqueue_script(
		'jofme-wishlist',
		JOFME_URI . '/assets/js/wishlist.js',
		array(),
		JOFME_VERSION,
		true
	);
	wp_localize_script(
		'jofme-wishlist',
		'jofmeWishlist',
		array(
			'storageKey' => 'jofme_wishlist_ids',
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'jofme_wishlist' ),
			'pageUrl'    => jofme_wishlist_page_url(),
			'i18n'       => array(
				'add'      => __( 'Add to wishlist', 'jofme' ),
				'remove'   => __( 'Remove from wishlist', 'jofme' ),
				'loading'  => __( 'Loading your wishlist…', 'jofme' ),
				'empty'    => __( 'Your wishlist is empty.', 'jofme' ),
				'error'    => __( 'Unable to load your wishlist. Please try again.', 'jofme' ),
				'items'    => __( '%d items', 'jofme' ),
				'item'     => __( '%d item', 'jofme' ),
				'wishlist' => __( 'Wishlist', 'jofme' ),
			),
		)
	);

	if ( jofme_is_wishlist_page() ) {
		wp_enqueue_script( 'wc-add-to-cart' );
	}
}
add_action( 'wp_enqueue_scripts', 'jofme_wishlist_assets', 36 );

/**
 * Body class on the wishlist page.
 *
 * @param array $classes Classes.
 * @return array
 */
function jofme_wishlist_body_class( $classes ) {
	if ( jofme_is_wishlist_page() ) {
		$classes[] = 'jofme-wishlist-page-view';
		$layout    = get_theme_mod( 'jofme_card_layout', 'bestsellers' );
		$classes[] = 'jofme-card-' . sanitize_html_class( $layout );
	}
	return $classes;
}
add_filter( 'body_class', 'jofme_wishlist_body_class' );

/**
 * Customizer: wishlist page assignment.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function jofme_wishlist_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'jofme_wishlist',
		array(
			'title'       => __( 'Wishlist', 'jofme' ),
			'description' => __( 'Assign a page that contains the [jofme_wishlist] shortcode. A Wishlist page is created automatically when the theme is activated.', 'jofme' ),
			'panel'       => 'jofme_options',
		)
	);

	$wp_customize->add_setting(
		'jofme_wishlist_page_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'jofme_wishlist_page_id',
		array(
			'label'       => __( 'Wishlist page', 'jofme' ),
			'description' => __( 'Used for the header heart link and the native wishlist grid. Ignored when YITH or TI Wishlist is active.', 'jofme' ),
			'section'     => 'jofme_wishlist',
			'type'        => 'dropdown-pages',
		)
	);
}
add_action( 'customize_register', 'jofme_wishlist_customize_register', 20 );
