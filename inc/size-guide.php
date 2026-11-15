<?php
/**
 * Size guides: PDF import, multiple guides, assigned to product categories.
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Register Size Guide post type.
 */
function jofme_size_guide_register() {
	register_post_type(
		'jofme_size_guide',
		array(
			'labels'              => array(
				'name'               => __( 'Size guides', 'jofme' ),
				'singular_name'      => __( 'Size guide', 'jofme' ),
				'add_new'            => __( 'Add size guide', 'jofme' ),
				'add_new_item'       => __( 'Add size guide', 'jofme' ),
				'edit_item'          => __( 'Edit size guide', 'jofme' ),
				'new_item'           => __( 'New size guide', 'jofme' ),
				'view_item'          => __( 'View size guide', 'jofme' ),
				'search_items'       => __( 'Search size guides', 'jofme' ),
				'not_found'          => __( 'No size guides found', 'jofme' ),
				'not_found_in_trash' => __( 'No size guides found in Trash', 'jofme' ),
				'menu_name'          => __( 'Size guides', 'jofme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=product',
			'menu_position'       => 56,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'exclude_from_search' => true,
		)
	);
}
add_action( 'init', 'jofme_size_guide_register' );

/**
 * Meta boxes: PDF + categories.
 */
function jofme_size_guide_meta_boxes() {
	add_meta_box(
		'jofme_size_guide_pdf',
		__( 'PDF size guide', 'jofme' ),
		'jofme_size_guide_pdf_box',
		'jofme_size_guide',
		'normal',
		'high'
	);
	add_meta_box(
		'jofme_size_guide_cats',
		__( 'Product categories', 'jofme' ),
		'jofme_size_guide_cats_box',
		'jofme_size_guide',
		'side',
		'default'
	);
	add_meta_box(
		'jofme_size_guide_products',
		__( 'Individual products', 'jofme' ),
		'jofme_size_guide_products_box',
		'jofme_size_guide',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'jofme_size_guide_meta_boxes' );

/**
 * PDF upload meta box.
 *
 * @param WP_Post $post Post.
 */
function jofme_size_guide_pdf_box( $post ) {
	wp_nonce_field( 'jofme_save_size_guide', 'jofme_size_guide_nonce' );
	$pdf_id  = absint( get_post_meta( $post->ID, '_jofme_size_guide_pdf_id', true ) );
	$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
	$label   = get_post_meta( $post->ID, '_jofme_size_guide_button', true );
	if ( ! is_string( $label ) ) {
		$label = '';
	}

	echo '<p><label for="jofme_size_guide_button"><strong>' . esc_html__( 'Button label', 'jofme' ) . '</strong></label></p>';
	echo '<p><input type="text" class="widefat" id="jofme_size_guide_button" name="jofme_size_guide_button" value="' . esc_attr( $label ) . '" placeholder="' . esc_attr__( 'Size guide', 'jofme' ) . '"></p>';
	echo '<p class="description">' . esc_html__( 'Shown on product pages. Leave empty to use the guide title.', 'jofme' ) . '</p>';

	echo '<div class="jofme-size-guide-pdf-field">';
	echo '<input type="hidden" id="jofme_size_guide_pdf_id" name="jofme_size_guide_pdf_id" value="' . absint( $pdf_id ) . '">';
	echo '<p><strong>' . esc_html__( 'PDF file', 'jofme' ) . '</strong></p>';
	echo '<p class="jofme-size-guide-pdf-preview"' . ( $pdf_url ? '' : ' hidden' ) . '>';
	echo '<a class="jofme-size-guide-pdf-link" href="' . esc_url( $pdf_url ? $pdf_url : '#' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $pdf_url ? basename( $pdf_url ) : '' ) . '</a>';
	echo '</p>';
	echo '<p>';
	echo '<button type="button" class="button jofme-size-guide-pdf-select">' . esc_html__( 'Upload / import PDF', 'jofme' ) . '</button> ';
	echo '<button type="button" class="button jofme-size-guide-pdf-remove">' . esc_html__( 'Remove PDF', 'jofme' ) . '</button>';
	echo '</p>';
	echo '<p class="description">' . esc_html__( 'Import a PDF size chart. Assign to categories and/or individual products. Multiple guides are supported.', 'jofme' ) . '</p>';
	echo '</div>';
}

/**
 * Category checklist meta box.
 *
 * @param WP_Post $post Post.
 */
function jofme_size_guide_cats_box( $post ) {
	$selected = get_post_meta( $post->ID, '_jofme_size_guide_cats', true );
	if ( ! is_array( $selected ) ) {
		$selected = array();
	}
	$selected = array_map( 'absint', $selected );

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		echo '<p>' . esc_html__( 'No product categories found.', 'jofme' ) . '</p>';
		return;
	}

	echo '<p class="description">' . esc_html__( 'Show this guide on products in the selected categories (including child categories).', 'jofme' ) . '</p>';
	echo '<ul class="jofme-size-guide-cats" style="max-height:280px;overflow:auto;margin:0;padding:.5rem 0 0;">';
	foreach ( $terms as $term ) {
		$checked = in_array( (int) $term->term_id, $selected, true );
		echo '<li><label><input type="checkbox" name="jofme_size_guide_cats[]" value="' . absint( $term->term_id ) . '"' . checked( $checked, true, false ) . '> ' . esc_html( $term->name ) . '</label></li>';
	}
	echo '</ul>';
}

/**
 * Individual products meta box (searchable multi-select).
 *
 * @param WP_Post $post Post.
 */
function jofme_size_guide_products_box( $post ) {
	$selected = get_post_meta( $post->ID, '_jofme_size_guide_products', true );
	if ( ! is_array( $selected ) ) {
		$selected = array();
	}
	$selected = array_values( array_filter( array_map( 'absint', $selected ) ) );

	echo '<p class="description">' . esc_html__( 'Also show this guide on specific products, even if their category is not selected.', 'jofme' ) . '</p>';
	echo '<select class="wc-product-search" multiple="multiple" style="width:100%;" id="jofme_size_guide_products" name="jofme_size_guide_products[]" data-placeholder="' . esc_attr__( 'Search for a product…', 'jofme' ) . '" data-action="woocommerce_json_search_products">';
	foreach ( $selected as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			continue;
		}
		echo '<option value="' . absint( $product_id ) . '" selected="selected">' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
	}
	echo '</select>';
}

/**
 * Save size guide meta.
 *
 * @param int $post_id Post ID.
 */
function jofme_size_guide_save( $post_id ) {
	if ( ! isset( $_POST['jofme_size_guide_nonce'] ) || ! is_string( $_POST['jofme_size_guide_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jofme_size_guide_nonce'] ) ), 'jofme_save_size_guide' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'jofme_size_guide' !== get_post_type( $post_id ) ) {
		return;
	}

	$button = isset( $_POST['jofme_size_guide_button'] ) && is_string( $_POST['jofme_size_guide_button'] )
		? sanitize_text_field( wp_unslash( $_POST['jofme_size_guide_button'] ) )
		: '';
	update_post_meta( $post_id, '_jofme_size_guide_button', $button );

	$pdf_id = isset( $_POST['jofme_size_guide_pdf_id'] ) ? absint( $_POST['jofme_size_guide_pdf_id'] ) : 0;
	if ( $pdf_id && jofme_is_pdf_attachment( $pdf_id ) ) {
		update_post_meta( $post_id, '_jofme_size_guide_pdf_id', $pdf_id );
	} else {
		delete_post_meta( $post_id, '_jofme_size_guide_pdf_id' );
	}

	$cats = array();
	if ( isset( $_POST['jofme_size_guide_cats'] ) && is_array( $_POST['jofme_size_guide_cats'] ) ) {
		foreach ( $_POST['jofme_size_guide_cats'] as $cat_id ) {
			$cat_id = absint( $cat_id );
			if ( $cat_id ) {
				$cats[] = $cat_id;
			}
		}
	}
	$cats = array_values( array_unique( $cats ) );
	update_post_meta( $post_id, '_jofme_size_guide_cats', $cats );

	$products = array();
	if ( isset( $_POST['jofme_size_guide_products'] ) && is_array( $_POST['jofme_size_guide_products'] ) ) {
		foreach ( $_POST['jofme_size_guide_products'] as $product_id ) {
			$product_id = absint( $product_id );
			if ( ! $product_id ) {
				continue;
			}
			$product = wc_get_product( $product_id );
			if ( $product ) {
				// Store parent for variations so the guide applies on the product page.
				$products[] = $product->get_parent_id() ? $product->get_parent_id() : $product_id;
			}
		}
	}
	$products = array_values( array_unique( $products ) );
	update_post_meta( $post_id, '_jofme_size_guide_products', $products );
}
add_action( 'save_post_jofme_size_guide', 'jofme_size_guide_save' );

/**
 * Whether attachment is a PDF.
 *
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function jofme_is_pdf_attachment( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( ! $attachment_id ) {
		return false;
	}
	$mime = get_post_mime_type( $attachment_id );
	return is_string( $mime ) && 'application/pdf' === $mime;
}

/**
 * Admin assets for PDF picker.
 *
 * @param string $hook Hook.
 */
function jofme_size_guide_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'jofme_size_guide' !== $screen->post_type ) {
		return;
	}
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'jofme-size-guide-admin',
		JOFME_URI . '/assets/js/size-guide-admin.js',
		array( 'jquery' ),
		JOFME_VERSION,
		true
	);
	wp_localize_script(
		'jofme-size-guide-admin',
		'jofmeSizeGuideAdmin',
		array(
			'title'  => __( 'Select or upload PDF', 'jofme' ),
			'button' => __( 'Use this PDF', 'jofme' ),
		)
	);
	wp_enqueue_style( 'woocommerce_admin_styles' );
	wp_enqueue_script( 'wc-enhanced-select' );
}
add_action( 'admin_enqueue_scripts', 'jofme_size_guide_admin_assets' );

/**
 * Admin columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function jofme_size_guide_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['jofme_pdf']      = __( 'PDF', 'jofme' );
			$new['jofme_cats']     = __( 'Categories', 'jofme' );
			$new['jofme_products'] = __( 'Products', 'jofme' );
		}
	}
	return $new;
}
add_filter( 'manage_jofme_size_guide_posts_columns', 'jofme_size_guide_columns' );

/**
 * Admin column content.
 *
 * @param string $column Column.
 * @param int    $post_id Post ID.
 */
function jofme_size_guide_column_content( $column, $post_id ) {
	if ( 'jofme_pdf' === $column ) {
		$pdf_id = absint( get_post_meta( $post_id, '_jofme_size_guide_pdf_id', true ) );
		if ( $pdf_id && jofme_is_pdf_attachment( $pdf_id ) ) {
			$url = wp_get_attachment_url( $pdf_id );
			echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View PDF', 'jofme' ) . '</a>' : '—';
		} else {
			echo '—';
		}
		return;
	}
	if ( 'jofme_cats' === $column ) {
		$cats = get_post_meta( $post_id, '_jofme_size_guide_cats', true );
		if ( ! is_array( $cats ) || ! $cats ) {
			echo '—';
			return;
		}
		$names = array();
		foreach ( $cats as $term_id ) {
			$term = get_term( absint( $term_id ), 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$names[] = $term->name;
			}
		}
		echo $names ? esc_html( implode( ', ', $names ) ) : '—';
		return;
	}
	if ( 'jofme_products' === $column ) {
		$ids = get_post_meta( $post_id, '_jofme_size_guide_products', true );
		if ( ! is_array( $ids ) || ! $ids ) {
			echo '—';
			return;
		}
		$names = array();
		foreach ( $ids as $product_id ) {
			$product = wc_get_product( absint( $product_id ) );
			if ( $product ) {
				$names[] = $product->get_name();
			}
		}
		echo $names ? esc_html( implode( ', ', $names ) ) : '—';
	}
}
add_action( 'manage_jofme_size_guide_posts_custom_column', 'jofme_size_guide_column_content', 10, 2 );

/**
 * Category IDs for a product including ancestors.
 *
 * @param WC_Product $product Product.
 * @return int[]
 */
function jofme_product_category_branch_ids( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}
	$ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
	$all = $ids;
	foreach ( $ids as $id ) {
		$all = array_merge( $all, get_ancestors( (int) $id, 'product_cat' ) );
	}
	return array_values( array_unique( array_map( 'absint', $all ) ) );
}

/**
 * Published size guides matching a product’s categories.
 *
 * @param WC_Product|null $product Product.
 * @return WP_Post[]
 */
function jofme_get_size_guides_for_product( $product = null ) {
	if ( ! $product && isset( $GLOBALS['product'] ) ) {
		$product = $GLOBALS['product'];
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return array();
	}

	$branch = jofme_product_category_branch_ids( $product );
	if ( ! $branch ) {
		return array();
	}

	$guides = get_posts(
		array(
			'post_type'              => 'jofme_size_guide',
			'post_status'            => 'publish',
			'posts_per_page'         => 20,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	$product_id = (int) $product->get_id();
	$matched    = array();
	foreach ( $guides as $guide ) {
		$pdf_id = absint( get_post_meta( $guide->ID, '_jofme_size_guide_pdf_id', true ) );
		if ( ! $pdf_id || ! jofme_is_pdf_attachment( $pdf_id ) ) {
			continue;
		}

		$hit = false;

		$products = get_post_meta( $guide->ID, '_jofme_size_guide_products', true );
		if ( is_array( $products ) && $products ) {
			$products = array_map( 'absint', $products );
			if ( in_array( $product_id, $products, true ) ) {
				$hit = true;
			}
		}

		if ( ! $hit ) {
			$cats = get_post_meta( $guide->ID, '_jofme_size_guide_cats', true );
			if ( is_array( $cats ) && $cats ) {
				$cats = array_map( 'absint', $cats );
				if ( array_intersect( $branch, $cats ) ) {
					$hit = true;
				}
			}
		}

		if ( $hit ) {
			$matched[] = $guide;
		}
	}
	return $matched;
}

/**
 * Button label for a guide.
 *
 * @param WP_Post $guide Guide post.
 * @return string
 */
function jofme_size_guide_button_label( $guide ) {
	$custom = get_post_meta( $guide->ID, '_jofme_size_guide_button', true );
	if ( is_string( $custom ) && '' !== trim( $custom ) ) {
		return $custom;
	}
	return $guide->post_title ? $guide->post_title : __( 'Size guide', 'jofme' );
}

/**
 * Front-end assets.
 */
function jofme_size_guide_assets() {
	if ( ! is_singular( 'product' ) || ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product || ! jofme_get_size_guides_for_product( $product ) ) {
		return;
	}
	wp_enqueue_style(
		'jofme-size-guide',
		JOFME_URI . '/assets/css/size-guide.css',
		array( 'jofme-main' ),
		JOFME_VERSION
	);
	wp_enqueue_script(
		'jofme-size-guide',
		JOFME_URI . '/assets/js/size-guide.js',
		array(),
		JOFME_VERSION,
		true
	);
	wp_localize_script(
		'jofme-size-guide',
		'jofmeSizeGuide',
		array(
			'close'    => __( 'Close', 'jofme' ),
			'download' => __( 'Download PDF', 'jofme' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'jofme_size_guide_assets', 40 );

/**
 * Render size guide triggers + dialogs once per product page.
 */
function jofme_size_guide_render() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	global $product;
	$guides = jofme_get_size_guides_for_product( $product );
	if ( ! $guides ) {
		return;
	}

	echo '<div class="jofme-size-guides">';
	foreach ( $guides as $guide ) {
		$pdf_id  = absint( get_post_meta( $guide->ID, '_jofme_size_guide_pdf_id', true ) );
		$pdf_url = wp_get_attachment_url( $pdf_id );
		if ( ! $pdf_url ) {
			continue;
		}
		$label = jofme_size_guide_button_label( $guide );
		$uid   = 'jofme-size-guide-' . (int) $guide->ID;

		echo '<button type="button" class="jofme-size-guide-trigger" data-guide="' . esc_attr( $uid ) . '" aria-haspopup="dialog">';
		echo esc_html( $label );
		echo '</button>';

		echo '<dialog class="jofme-size-guide-dialog" id="' . esc_attr( $uid ) . '">';
		echo '<div class="jofme-size-guide-dialog__inner">';
		echo '<header class="jofme-size-guide-dialog__header">';
		echo '<h2 class="jofme-size-guide-dialog__title">' . esc_html( $guide->post_title ? $guide->post_title : $label ) . '</h2>';
		echo '<button type="button" class="jofme-size-guide-dialog__close" data-close aria-label="' . esc_attr__( 'Close', 'jofme' ) . '">×</button>';
		echo '</header>';
		echo '<div class="jofme-size-guide-dialog__body">';
		echo '<iframe class="jofme-size-guide-dialog__frame" src="' . esc_url( $pdf_url ) . '#view=FitH" title="' . esc_attr( $label ) . '" loading="lazy"></iframe>';
		echo '</div>';
		echo '<footer class="jofme-size-guide-dialog__footer">';
		echo '<a class="jofme-size-guide-dialog__download" href="' . esc_url( $pdf_url ) . '" target="_blank" rel="noopener noreferrer" download>' . esc_html__( 'Download PDF', 'jofme' ) . '</a>';
		echo '</footer>';
		echo '</div></dialog>';
	}
	echo '</div>';
}
add_action( 'woocommerce_before_add_to_cart_button', 'jofme_size_guide_render', 8 );
