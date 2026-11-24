<?php
/** Gallery product cards and a sculptural single-product layout. @package Jofme */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }
function jofme_gallery_design_customize( $manager ) {
 $position_control = $manager->get_control( 'jofme_product_gallery_layout' );
 if ( $position_control ) { $position_control->description = __( 'Applies to Classic design. Gallery design uses its own image and purchase-panel layout.', 'jofme' ); }
 $manager->add_setting( 'jofme_product_page_layout', array( 'default' => 'classic', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control( 'jofme_product_page_layout', array( 'label' => __( 'Product pages — Design', 'jofme' ), 'section' => 'jofme_layout', 'type' => 'select', 'choices' => array( 'classic' => __( 'Classic', 'jofme' ), 'gallery' => __( 'Gallery (large image & minimal purchase panel)', 'jofme' ) ) ) );
 $manager->add_setting( 'jofme_gallery_display', array( 'default' => 'stacked', 'sanitize_callback' => 'jofme_sanitize_select' ) );
 $manager->add_control(
  'jofme_gallery_display',
  array(
   'label'       => __( 'Gallery design — Image display', 'jofme' ),
   'description' => __( 'Only applies when Product pages — Design is set to Gallery.', 'jofme' ),
   'section'     => 'jofme_layout',
   'type'        => 'select',
   'choices'     => array(
    'stacked'  => __( 'Stacked (scroll)', 'jofme' ),
    'carousel' => __( 'Carousel', 'jofme' ),
   ),
  )
 );
 $manager->add_setting(
  'jofme_gallery_image_bg',
  array(
   'default'           => '',
   'sanitize_callback' => 'jofme_sanitize_gallery_image_bg',
  )
 );
 $manager->add_control(
  new WP_Customize_Color_Control(
   $manager,
   'jofme_gallery_image_bg',
   array(
    'label'       => __( 'Gallery design — Image background', 'jofme' ),
    'description' => __( 'Optional. Leave empty for no background behind product photos.', 'jofme' ),
    'section'     => 'jofme_layout',
   )
  )
 );
}

/**
 * Sanitize optional gallery image background (empty = none).
 *
 * @param mixed $value Raw value.
 * @return string
 */
function jofme_sanitize_gallery_image_bg( $value ) {
 if ( '' === $value || null === $value ) {
  return '';
 }
 $color = sanitize_hex_color( $value );
 return $color ? $color : '';
}
add_action( 'customize_register', 'jofme_gallery_design_customize', 30 );
function jofme_is_gallery_product() { return is_product() && 'gallery' === get_theme_mod( 'jofme_product_page_layout', 'classic' ); }

/**
 * Gallery image display mode for the Gallery product design.
 *
 * @return string stacked|carousel
 */
function jofme_gallery_display_mode() {
 $mode = get_theme_mod( 'jofme_gallery_display', 'stacked' );
 return 'carousel' === $mode ? 'carousel' : 'stacked';
}

function jofme_gallery_product_body( $classes ) {
 if ( jofme_is_gallery_product() ) {
  $classes[] = 'jofme-product-gallery';
  $classes[] = 'jofme-gallery-' . jofme_gallery_display_mode();
 }
 return $classes;
}
add_filter( 'body_class', 'jofme_gallery_product_body', 35 );
function jofme_gallery_design_assets() {
 wp_enqueue_style( 'jofme-gallery-design', JOFME_URI . '/assets/css/gallery-design.css', array( 'jofme-layout', 'jofme-premium' ), JOFME_VERSION );
 $bg = jofme_sanitize_gallery_image_bg( get_theme_mod( 'jofme_gallery_image_bg', '' ) );
 wp_add_inline_style( 'jofme-gallery-design', ':root{--jofme-gallery-image-bg:' . ( $bg ? $bg : 'transparent' ) . ';}' );
 if ( jofme_is_gallery_product() ) {
  wp_enqueue_script( 'jofme-gallery-design', JOFME_URI . '/assets/js/gallery-design.js', array( 'jquery', 'wc-add-to-cart-variation' ), JOFME_VERSION, true );
  wp_localize_script(
   'jofme-gallery-design',
   'jofmeGallery',
   array(
    'image' => __( 'Product image', 'jofme' ),
    'close' => __( 'Close', 'jofme' ),
    'prev'  => __( 'Previous image', 'jofme' ),
    'next'  => __( 'Next image', 'jofme' ),
    /* translators: 1: current image number, 2: total images */
    'of'    => __( '%1$s of %2$s', 'jofme' ),
   )
  );
 }
}
add_action( 'wp_enqueue_scripts', 'jofme_gallery_design_assets', 35 );

/**
 * Expose variation image set to the frontend gallery switcher.
 *
 * Includes the variation featured image plus optional additional variation
 * gallery images (core variation gallery or Additional Variation Images meta).
 *
 * @param array                $data      Variation data.
 * @param WC_Product           $product   Parent product.
 * @param WC_Product_Variation $variation Variation product.
 * @return array
 */
function jofme_available_variation_gallery( $data, $product, $variation ) {
	if ( ! is_array( $data ) || ! $variation instanceof WC_Product_Variation ) {
		return $data;
	}

	$ids  = array();
	$main = (int) $variation->get_image_id();
	if ( $main ) {
		$ids[] = $main;
	}

	foreach ( $variation->get_gallery_image_ids() as $id ) {
		$id = (int) $id;
		if ( $id && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}

	$extra = get_post_meta( $variation->get_id(), '_wc_additional_variation_images', true );
	if ( is_string( $extra ) && '' !== $extra ) {
		foreach ( array_filter( array_map( 'absint', explode( ',', $extra ) ) ) as $id ) {
			if ( $id && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}
	} elseif ( is_array( $extra ) ) {
		foreach ( $extra as $id ) {
			$id = absint( $id );
			if ( $id && ! in_array( $id, $ids, true ) ) {
				$ids[] = $id;
			}
		}
	}

	$images = array();
	foreach ( $ids as $id ) {
		$full = wp_get_attachment_image_src( $id, 'full' );
		if ( ! $full || empty( $full[0] ) ) {
			continue;
		}
		$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
		$images[] = array(
			'id'       => $id,
			'src'      => $full[0],
			'full_src' => $full[0],
			'width'    => (int) $full[1],
			'height'   => (int) $full[2],
			'alt'      => is_string( $alt ) ? $alt : '',
		);
	}

	$data['jofme_gallery'] = $images;
	return $data;
}
add_filter( 'woocommerce_available_variation', 'jofme_available_variation_gallery', 20, 3 );

/**
 * Attachment IDs for the stacked product gallery (featured + gallery, unique).
 *
 * @param WC_Product|null $product Product.
 * @return int[]
 */
function jofme_stacked_gallery_ids( $product = null ) {
 if ( ! $product && isset( $GLOBALS['product'] ) ) {
  $product = $GLOBALS['product'];
 }
 if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
  return array();
 }
 $ids = array();
 $main = (int) $product->get_image_id();
 if ( $main ) {
  $ids[] = $main;
 }
 foreach ( $product->get_gallery_image_ids() as $id ) {
  $id = (int) $id;
  if ( $id && ! in_array( $id, $ids, true ) ) {
   $ids[] = $id;
  }
 }
 return $ids;
}

/**
 * Render the gallery product images (stacked or carousel).
 */
function jofme_product_gallery() {
 if ( 'carousel' === jofme_gallery_display_mode() ) {
  jofme_carousel_product_gallery();
  return;
 }
 jofme_stacked_product_gallery();
}

/**
 * Output an uncropped full-size product image (no srcset of cropped sizes).
 *
 * @param int   $attachment_id Attachment ID.
 * @param array $attr          Extra img attributes.
 */
function jofme_gallery_uncropped_image( $attachment_id, $attr = array() ) {
 $attachment_id = absint( $attachment_id );
 if ( ! $attachment_id ) {
  return;
 }
 $src = wp_get_attachment_image_src( $attachment_id, 'full' );
 if ( ! $src || empty( $src[0] ) ) {
  return;
 }
 $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
 if ( ! is_string( $alt ) ) {
  $alt = '';
 }
 $attr = wp_parse_args(
  $attr,
  array(
   'src'                => $src[0],
   'width'              => (string) (int) $src[1],
   'height'             => (string) (int) $src[2],
   'alt'                => $alt,
   'decoding'           => 'async',
   'data-attachment_id' => (string) $attachment_id,
  )
 );
 // Force original file only — never emit cropped intermediate srcset.
 unset( $attr['srcset'], $attr['sizes'] );

 $html = '<img';
 foreach ( $attr as $name => $value ) {
  if ( '' === $value && 'alt' !== $name ) {
   continue;
  }
  $html .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
 }
 $html .= ' />';
 echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes escaped above.
}

/**
 * Vertical stacked gallery that scrolls beside a sticky purchase panel.
 */
function jofme_stacked_product_gallery() {
 global $product;
 if ( ! $product ) {
  return;
 }

 $ids = jofme_stacked_gallery_ids( $product );
 echo '<div class="images jofme-stacked-gallery">';

 if ( ! $ids ) {
  echo '<figure class="jofme-stacked-gallery__item">' . wp_kses_post( wc_placeholder_img( 'woocommerce_single' ) ) . '</figure>';
  echo '</div>';
  return;
 }

 if ( count( $ids ) > 1 ) {
  echo '<div class="jofme-stacked-gallery__rail"><nav class="jofme-stacked-gallery__dots" aria-label="' . esc_attr__( 'Product images', 'jofme' ) . '">';
  foreach ( $ids as $index => $attachment_id ) {
   $item_id = 'jofme-gallery-image-' . ( $index + 1 );
   echo '<button type="button" class="jofme-stacked-gallery__dot" data-target="' . esc_attr( $item_id ) . '" aria-label="' . esc_attr( sprintf( /* translators: %d image number */ __( 'Go to image %d', 'jofme' ), $index + 1 ) ) . '"' . ( 0 === $index ? ' aria-current="true"' : '' ) . '></button>';
  }
  echo '</nav></div>';
 }

 echo '<div class="jofme-stacked-gallery__track">';
 foreach ( $ids as $index => $attachment_id ) {
  $full    = wp_get_attachment_image_src( $attachment_id, 'full' );
  $href    = $full ? $full[0] : wp_get_attachment_url( $attachment_id );
  $item_id = 'jofme-gallery-image-' . ( $index + 1 );
  echo '<figure class="jofme-stacked-gallery__item" id="' . esc_attr( $item_id ) . '" data-index="' . absint( $index ) . '"' . ( 0 === $index ? ' data-primary="1"' : '' ) . '>';
  if ( $href ) {
   echo '<a class="jofme-gallery-zoom-link" href="' . esc_url( $href ) . '" data-lightbox-index="' . absint( $index ) . '">';
  }
  jofme_gallery_uncropped_image(
   $attachment_id,
   array(
    'class'   => 0 === $index ? 'wp-post-image' : '',
    'loading' => 0 === $index ? 'eager' : 'lazy',
   )
  );
  if ( $href ) {
   echo '</a>';
  }
  echo '</figure>';
 }
 echo '</div></div>';
}

/**
 * Single-slide carousel gallery with arrows and thumbnails.
 */
function jofme_carousel_product_gallery() {
 global $product;
 if ( ! $product ) {
  return;
 }

 $ids = jofme_stacked_gallery_ids( $product );
 echo '<div class="images jofme-carousel-gallery" data-index="0">';

 if ( ! $ids ) {
  echo '<div class="jofme-carousel-gallery__viewport"><figure class="jofme-carousel-gallery__slide is-active" data-primary="1">' . wp_kses_post( wc_placeholder_img( 'woocommerce_single' ) ) . '</figure></div>';
  echo '</div>';
  return;
 }

 $multi = count( $ids ) > 1;
 echo '<div class="jofme-carousel-gallery__viewport">';
 if ( $multi ) {
  echo '<button type="button" class="jofme-carousel-gallery__nav jofme-carousel-gallery__nav--prev" data-dir="-1" aria-label="' . esc_attr__( 'Previous image', 'jofme' ) . '">‹</button>';
 }
 echo '<div class="jofme-carousel-gallery__track">';
 foreach ( $ids as $index => $attachment_id ) {
  $full = wp_get_attachment_image_src( $attachment_id, 'full' );
  $href = $full ? $full[0] : wp_get_attachment_url( $attachment_id );
  echo '<figure class="jofme-carousel-gallery__slide' . ( 0 === $index ? ' is-active' : '' ) . '" data-index="' . absint( $index ) . '"' . ( 0 === $index ? ' data-primary="1"' : '' ) . ( 0 === $index ? '' : ' hidden' ) . '>';
  if ( $href ) {
   echo '<a class="jofme-gallery-zoom-link" href="' . esc_url( $href ) . '" data-lightbox-index="' . absint( $index ) . '">';
  }
  jofme_gallery_uncropped_image(
   $attachment_id,
   array(
    'class'   => 0 === $index ? 'wp-post-image' : '',
    'loading' => 0 === $index ? 'eager' : 'lazy',
   )
  );
  if ( $href ) {
   echo '</a>';
  }
  echo '</figure>';
 }
 echo '</div>';
 if ( $multi ) {
  echo '<button type="button" class="jofme-carousel-gallery__nav jofme-carousel-gallery__nav--next" data-dir="1" aria-label="' . esc_attr__( 'Next image', 'jofme' ) . '">›</button>';
 }
 echo '</div>';

 if ( $multi ) {
  echo '<div class="jofme-carousel-gallery__thumbs" role="list">';
  foreach ( $ids as $index => $attachment_id ) {
   echo '<button type="button" class="jofme-carousel-gallery__thumb' . ( 0 === $index ? ' is-active' : '' ) . '" role="listitem" data-index="' . absint( $index ) . '" aria-label="' . esc_attr( sprintf( /* translators: %d image number */ __( 'Show image %d', 'jofme' ), $index + 1 ) ) . '"' . ( 0 === $index ? ' aria-current="true"' : '' ) . '>';
   echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'alt' => '' ) );
   echo '</button>';
  }
  echo '</div>';
 }

 echo '</div>';
}
function jofme_gallery_card_category() {
 $layout = get_theme_mod( 'jofme_card_layout', 'bestsellers' );
 if ( ! in_array( $layout, array( 'gallery', 'bestsellers' ), true ) ) { return; }
 global $product;
 if ( ! $product ) { return; }
 $terms = get_the_terms( $product->get_id(), 'product_cat' );
 if ( is_wp_error( $terms ) || ! $terms ) { return; }
 echo '<span class="jofme-card-category">' . esc_html( $terms[0]->name ) . '</span>';
}
add_action( 'woocommerce_shop_loop_item_title', 'jofme_gallery_card_category', 5 );
function jofme_gallery_summary_category() {
 global $product;
 if ( $product ) { echo '<div class="jofme-product-category">' . wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ' ) ) . '</div>'; }
}

/**
 * SKU on the gallery product summary (full product_meta is removed).
 * Uses WooCommerce .sku markup so variation select updates the value.
 */
function jofme_gallery_product_sku() {
	global $product;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! function_exists( 'wc_product_sku_enabled' ) || ! wc_product_sku_enabled() ) {
		return;
	}

	$sku = $product->get_sku();
	if ( ! $sku && ! $product->is_type( 'variable' ) ) {
		return;
	}

	echo '<div class="jofme-product-sku">';
	echo '<span class="sku_wrapper">' . esc_html__( 'SKU:', 'woocommerce' ) . ' <span class="sku">' . esc_html( $sku ? $sku : __( 'N/A', 'woocommerce' ) ) . '</span></span>';
	echo '</div>';
}

function jofme_gallery_details() {
 global $product;
 if ( ! $product ) { return; }
 $description = $product->get_description();
 $care        = $product->get_meta( '_jofme_care_instructions' );
 if ( ! $description && ! $care ) { return; }
 echo '<div class="jofme-product-details">';
 if ( $description ) {
  echo '<details open class="jofme-product-details__description"><summary>' . esc_html__( 'Description', 'jofme' ) . '</summary><div>' . wp_kses_post( apply_filters( 'the_content', $description ) ) . '</div></details>';
 }
 if ( $care ) {
  echo '<details class="jofme-product-details__care"><summary>' . esc_html__( 'Care instructions', 'jofme' ) . '</summary><div>' . wp_kses_post( wpautop( $care ) ) . '</div></details>';
 }
 echo '</div>';
}

/**
 * Product title above the gallery on small screens (summary title stays for desktop).
 */
function jofme_gallery_mobile_title() {
 global $product;
 if ( ! $product ) {
  return;
 }
 echo '<h1 class="product_title entry-title jofme-product-title--mobile">' . esc_html( $product->get_name() ) . '</h1>';
}

function jofme_gallery_single_hooks() {
 if ( ! jofme_is_gallery_product() ) { return; }
 add_action( 'woocommerce_before_single_product_summary', 'jofme_gallery_intro_open', 0 );
 add_action( 'woocommerce_after_single_product_summary', 'jofme_gallery_intro_close', 0 );
 remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
 add_action( 'woocommerce_before_single_product_summary', 'jofme_gallery_mobile_title', 15 );
 add_action( 'woocommerce_before_single_product_summary', 'jofme_product_gallery', 20 );
 remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
 remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
 add_action( 'woocommerce_single_product_summary', 'jofme_gallery_summary_category', 4 );
 add_action( 'woocommerce_single_product_summary', 'jofme_gallery_product_sku', 11 );
 add_action( 'woocommerce_single_product_summary', 'jofme_gallery_details', 45 );
 add_filter( 'woocommerce_product_tabs', 'jofme_gallery_product_tabs', 99 );
 add_action( 'woocommerce_before_quantity_input_field', 'jofme_quantity_minus' );
 add_action( 'woocommerce_after_quantity_input_field', 'jofme_quantity_plus' );
}
add_action( 'wp', 'jofme_gallery_single_hooks' );
function jofme_gallery_intro_open() { echo '<div class="jofme-product-intro">'; }
function jofme_gallery_intro_close() { echo '</div>'; }
function jofme_gallery_product_tabs( $tabs ) { unset( $tabs['description'] ); return $tabs; }
function jofme_quantity_minus() { echo '<button type="button" class="jofme-quantity-step" data-step="-1" aria-label="' . esc_attr__( 'Decrease quantity', 'jofme' ) . '">−</button>'; }
function jofme_quantity_plus() { echo '<button type="button" class="jofme-quantity-step" data-step="1" aria-label="' . esc_attr__( 'Increase quantity', 'jofme' ) . '">+</button>'; }
function jofme_product_care_field() {
 woocommerce_wp_textarea_input( array( 'id' => '_jofme_care_instructions', 'label' => __( 'Care instructions', 'jofme' ), 'description' => __( 'Optional care text shown in the Gallery product design.', 'jofme' ), 'desc_tip' => true ) );
}
add_action( 'woocommerce_product_options_general_product_data', 'jofme_product_care_field' );
function jofme_product_care_save( $product ) {
 if ( isset( $_POST['_jofme_care_instructions'] ) && is_string( $_POST['_jofme_care_instructions'] ) ) {
  $product->update_meta_data( '_jofme_care_instructions', wp_kses_post( wp_unslash( $_POST['_jofme_care_instructions'] ) ) );
 }
}
// WooCommerce validates permissions and the product-edit nonce before this hook.
add_action( 'woocommerce_admin_process_product_object', 'jofme_product_care_save' );
