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
 * Whether a page ID can be used as the theme wishlist page.
 *
 * Rejects the static front page so the header heart never points at Home
 * (which also made “Edit” open the homepage in Customizer / Elementor).
 *
 * @param int $page_id Page ID.
 * @return bool
 */
function jofme_wishlist_page_is_usable( $page_id ) {
	$page_id = absint( $page_id );
	if ( ! $page_id || 'page' !== get_post_type( $page_id ) || 'publish' !== get_post_status( $page_id ) ) {
		return false;
	}

	if ( 'page' === get_option( 'show_on_front' ) && absint( get_option( 'page_on_front' ) ) === $page_id ) {
		return false;
	}

	return true;
}

/**
 * Persist wishlist page assignment.
 *
 * @param int $page_id Page ID.
 */
function jofme_wishlist_store_page_id( $page_id ) {
	$page_id = absint( $page_id );
	update_option( 'jofme_wishlist_page_id', $page_id );
	set_theme_mod( 'jofme_wishlist_page_id', $page_id );
}

/**
 * Clear an unusable stored wishlist page ID.
 */
function jofme_wishlist_clear_invalid_page_id() {
	$theme_mod = absint( get_theme_mod( 'jofme_wishlist_page_id', 0 ) );
	$option    = absint( get_option( 'jofme_wishlist_page_id', 0 ) );

	if ( $theme_mod && ! jofme_wishlist_page_is_usable( $theme_mod ) ) {
		remove_theme_mod( 'jofme_wishlist_page_id' );
	}
	if ( $option && ! jofme_wishlist_page_is_usable( $option ) ) {
		delete_option( 'jofme_wishlist_page_id' );
	}
}

/**
 * Find an existing wishlist page by slug or shortcode content.
 *
 * @return int
 */
function jofme_wishlist_find_existing_page() {
	$by_slug = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'name'                   => 'wishlist',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( $by_slug && jofme_wishlist_page_is_usable( (int) $by_slug[0] ) ) {
		return (int) $by_slug[0];
	}

	$with_shortcode = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 5,
			's'                      => '[jofme_wishlist]',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	foreach ( (array) $with_shortcode as $candidate ) {
		$candidate = (int) $candidate;
		if ( ! jofme_wishlist_page_is_usable( $candidate ) ) {
			continue;
		}
		$post = get_post( $candidate );
		if ( $post && has_shortcode( (string) $post->post_content, 'jofme_wishlist' ) ) {
			return $candidate;
		}
	}

	return 0;
}

/**
 * Resolved wishlist page ID (Customizer / option).
 *
 * @return int
 */
function jofme_wishlist_page_id() {
	$page_id = absint( get_theme_mod( 'jofme_wishlist_page_id', 0 ) );
	if ( ! jofme_wishlist_page_is_usable( $page_id ) ) {
		$page_id = absint( get_option( 'jofme_wishlist_page_id', 0 ) );
	}
	return jofme_wishlist_page_is_usable( $page_id ) ? $page_id : 0;
}

/**
 * Sanitize Customizer wishlist page assignment.
 *
 * @param mixed $value Raw page ID.
 * @return int
 */
function jofme_sanitize_wishlist_page_id( $value ) {
	$page_id = absint( $value );
	if ( ! $page_id ) {
		return 0;
	}
	return jofme_wishlist_page_is_usable( $page_id ) ? $page_id : 0;
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
	if ( ! $page_id ) {
		$page_id = jofme_wishlist_ensure_page();
	}
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
 * Preferred public slug for the native wishlist page.
 *
 * @return string
 */
function jofme_wishlist_desired_slug() {
	return 'wishlist';
}

/**
 * Ensure the wishlist page has a usable post_name (prefer "wishlist").
 *
 * @param int $page_id Page ID.
 * @return string Current slug.
 */
function jofme_wishlist_ensure_slug( $page_id ) {
	$page_id = absint( $page_id );
	if ( ! $page_id ) {
		return '';
	}

	$desired = jofme_wishlist_desired_slug();
	$current = (string) get_post_field( 'post_name', $page_id );
	if ( $desired === $current ) {
		return $current;
	}

	$conflict = get_page_by_path( $desired );
	if ( $conflict && (int) $conflict->ID !== $page_id ) {
		return $current ? $current : $desired;
	}

	$updated = wp_update_post(
		array(
			'ID'        => $page_id,
			'post_name' => $desired,
		),
		true
	);
	if ( is_wp_error( $updated ) ) {
		return $current;
	}

	$slug = (string) get_post_field( 'post_name', $page_id );
	if ( $slug && $slug !== $current ) {
		update_option( 'jofme_wishlist_flush_rewrites', 1 );
	}

	return $slug ? $slug : $current;
}

/**
 * Relative request path for a page permalink (no home subdirectory prefix).
 *
 * @param int $page_id Page ID.
 * @return string
 */
function jofme_wishlist_page_request_path( $page_id ) {
	$page_id = absint( $page_id );
	if ( ! $page_id ) {
		return '';
	}

	$permalink = get_permalink( $page_id );
	if ( ! $permalink ) {
		return '';
	}

	$path = (string) wp_parse_url( $permalink, PHP_URL_PATH );
	$path = trim( $path, '/' );
	if ( '' === $path ) {
		return '';
	}

	$home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $home_path && 0 === strpos( $path, $home_path . '/' ) ) {
		$path = substr( $path, strlen( $home_path ) + 1 );
	} elseif ( $home_path && $path === $home_path ) {
		return '';
	}

	return trim( $path, '/' );
}

/**
 * Register a top-priority rewrite so /wishlist/ always maps to the page ID.
 *
 * Fixes cases where ?page_id=N works but the pretty slug 404s (stale rules or
 * WooCommerce/product slug conflicts).
 */
function jofme_wishlist_register_rewrite() {
	if ( jofme_wishlist_has_yith() || jofme_wishlist_has_ti() ) {
		return;
	}
	if ( ! get_option( 'permalink_structure' ) ) {
		return;
	}

	$page_id = jofme_wishlist_page_id();
	if ( ! $page_id ) {
		return;
	}

	$paths = array();
	$request_path = jofme_wishlist_page_request_path( $page_id );
	if ( $request_path ) {
		$paths[] = $request_path;
	}

	$slug = (string) get_post_field( 'post_name', $page_id );
	if ( $slug ) {
		$paths[] = $slug;
	}
	$paths[] = jofme_wishlist_desired_slug();
	$paths   = array_values( array_unique( array_filter( $paths ) ) );

	foreach ( $paths as $path ) {
		$regex = '^' . preg_quote( $path, '/' ) . '/?$';
		add_rewrite_rule( $regex, 'index.php?page_id=' . $page_id, 'top' );
	}
}
add_action( 'init', 'jofme_wishlist_register_rewrite', 20 );

/**
 * Create or repair a Wishlist page with the shortcode.
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

	jofme_wishlist_clear_invalid_page_id();

	$page_id = jofme_wishlist_page_id();
	$created = false;
	if ( ! $page_id ) {
		$page_id = jofme_wishlist_find_existing_page();
	}
	if ( ! $page_id ) {
		$inserted = wp_insert_post(
			array(
				'post_title'   => __( 'Wishlist', 'jofme' ),
				'post_name'    => jofme_wishlist_desired_slug(),
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[jofme_wishlist]',
			),
			true
		);
		if ( is_wp_error( $inserted ) || ! $inserted ) {
			return 0;
		}
		$page_id = (int) $inserted;
		$created = true;
	}

	if ( ! jofme_wishlist_page_is_usable( $page_id ) ) {
		return 0;
	}

	jofme_wishlist_store_page_id( $page_id );
	jofme_wishlist_ensure_slug( $page_id );

	if ( $created ) {
		update_option( 'jofme_wishlist_flush_rewrites', 1 );
	}

	return $page_id;
}
add_action( 'after_switch_theme', 'jofme_wishlist_ensure_page' );

/**
 * Keep a usable wishlist page assigned (heals homepage / missing IDs).
 */
function jofme_wishlist_maybe_ensure_page() {
	if ( jofme_wishlist_has_yith() || jofme_wishlist_has_ti() ) {
		return;
	}
	jofme_wishlist_ensure_page();
}
add_action( 'init', 'jofme_wishlist_maybe_ensure_page', 30 );

/**
 * One-time rewrite upgrade when the theme ships slug/rewrite fixes.
 */
function jofme_wishlist_maybe_upgrade_rewrites() {
	$target = '1.7.56';
	if ( get_option( 'jofme_wishlist_rewrite_version' ) === $target ) {
		return;
	}
	if ( ! jofme_wishlist_has_yith() && ! jofme_wishlist_has_ti() ) {
		jofme_wishlist_ensure_page();
		update_option( 'jofme_wishlist_flush_rewrites', 1 );
	}
	update_option( 'jofme_wishlist_rewrite_version', $target );
}
add_action( 'init', 'jofme_wishlist_maybe_upgrade_rewrites', 31 );

/**
 * Flush rewrite rules once after creating/repairing the wishlist page.
 */
function jofme_wishlist_maybe_flush_rewrites() {
	if ( ! get_option( 'jofme_wishlist_flush_rewrites' ) ) {
		return;
	}
	delete_option( 'jofme_wishlist_flush_rewrites' );
	// Hard flush: pretty /wishlist/ was 404 while ?page_id= worked.
	flush_rewrite_rules( true );
}
add_action( 'init', 'jofme_wishlist_maybe_flush_rewrites', 99 );

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
 * Product card layout choices (shared with shop Customizer).
 *
 * @return array<string,string>
 */
function jofme_product_card_layout_choices() {
	return array(
		'bestsellers' => __( 'Bestsellers (tabs card)', 'jofme' ),
		'minimal'     => __( 'Minimal', 'jofme' ),
		'framed'      => __( 'Framed', 'jofme' ),
		'editorial'   => __( 'Editorial', 'jofme' ),
		'gallery'     => __( 'Gallery (minimal product grid)', 'jofme' ),
	);
}

/**
 * Resolved product card layout for the wishlist page.
 *
 * @return string
 */
function jofme_wishlist_card_layout() {
	$allowed = array_keys( jofme_product_card_layout_choices() );
	$choice  = (string) get_theme_mod( 'jofme_wishlist_card_layout', 'inherit' );
	if ( 'inherit' === $choice || '' === $choice ) {
		$shop = (string) get_theme_mod( 'jofme_card_layout', 'bestsellers' );
		return in_array( $shop, $allowed, true ) ? $shop : 'bestsellers';
	}
	return in_array( $choice, $allowed, true ) ? $choice : 'bestsellers';
}

/**
 * Body class on the wishlist page.
 *
 * @param array $classes Classes.
 * @return array
 */
function jofme_wishlist_body_class( $classes ) {
	if ( ! jofme_is_wishlist_page() ) {
		return $classes;
	}

	$classes[] = 'jofme-wishlist-page-view';
	$layout    = jofme_wishlist_card_layout();

	// Replace shop card layout class so wishlist can use its own card style.
	$classes = array_values(
		array_filter(
			$classes,
			static function ( $class ) {
				return 0 !== strpos( (string) $class, 'jofme-card-' );
			}
		)
	);
	$classes[] = 'jofme-card-' . sanitize_html_class( $layout );

	return $classes;
}
add_filter( 'body_class', 'jofme_wishlist_body_class', 25 );

/**
 * Customizer: wishlist page assignment + card layout.
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
			'sanitize_callback' => 'jofme_sanitize_wishlist_page_id',
		)
	);
	$wp_customize->add_control(
		'jofme_wishlist_page_id',
		array(
			'label'       => __( 'Wishlist page', 'jofme' ),
			'description' => __( 'Must be a published page with [jofme_wishlist] (not the homepage). Used for the header heart link. Ignored when YITH or TI Wishlist is active.', 'jofme' ),
			'section'     => 'jofme_wishlist',
			'type'        => 'dropdown-pages',
			'allow_null'  => true,
		)
	);

	$card_choices = array_merge(
		array( 'inherit' => __( 'Same as shop product cards', 'jofme' ) ),
		jofme_product_card_layout_choices()
	);

	$wp_customize->add_setting(
		'jofme_wishlist_card_layout',
		array(
			'default'           => 'inherit',
			'sanitize_callback' => 'jofme_sanitize_select',
		)
	);
	$wp_customize->add_control(
		'jofme_wishlist_card_layout',
		array(
			'label'       => __( 'Wishlist product card', 'jofme' ),
			'description' => __( 'Choose which product card style the wishlist grid uses.', 'jofme' ),
			'section'     => 'jofme_wishlist',
			'type'        => 'select',
			'choices'     => $card_choices,
		)
	);
}
add_action( 'customize_register', 'jofme_wishlist_customize_register', 20 );
