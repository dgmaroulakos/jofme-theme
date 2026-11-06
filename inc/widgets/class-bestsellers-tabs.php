<?php
/**
 * Bestsellers product tabs Elementor widget.
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tabbed WooCommerce product showcase (Shinao-style bestsellers).
 */
class Jofme_Bestsellers_Tabs_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'jofme-bestsellers-tabs';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return __( 'Bestsellers Tabs', 'jofme' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-tabs';
	}

	/**
	 * @return array
	 */
	public function get_categories() {
		return array( 'jofme' );
	}

	/**
	 * @return array
	 */
	public function get_keywords() {
		return array( 'bestsellers', 'tabs', 'products', 'woocommerce', 'jewelry', 'jofme' );
	}

	/**
	 * @return array
	 */
	public function get_style_depends() {
		return array( 'jofme-bestsellers-tabs' );
	}

	/**
	 * @return array
	 */
	public function get_script_depends() {
		return array( 'jofme-bestsellers-tabs' );
	}

	/**
	 * Product category choices for the repeater.
	 *
	 * @return array
	 */
	protected function get_category_options() {
		$options = array( '' => __( '— Select category —', 'jofme' ) );
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $options;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return $options;
		}
		foreach ( $terms as $term ) {
			$options[ (string) $term->term_id ] = $term->name;
		}
		return $options;
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content',
			array(
				'label' => __( 'Section', 'jofme' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Bestsellers for a Reason', 'jofme' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'show_all_tab',
			array(
				'label'        => __( 'Show “All” tab', 'jofme' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'jofme' ),
				'label_off'    => __( 'No', 'jofme' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'all_label',
			array(
				'label'     => __( '“All” tab label', 'jofme' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'All', 'jofme' ),
				'condition' => array(
					'show_all_tab' => 'yes',
				),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'tab_label',
			array(
				'label'   => __( 'Tab label', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Rings', 'jofme' ),
				'dynamic' => array( 'active' => true ),
			)
		);
		$repeater->add_control(
			'category_id',
			array(
				'label'   => __( 'Product category', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_category_options(),
				'default' => '',
			)
		);

		$this->add_control(
			'tabs',
			array(
				'label'       => __( 'Category tabs', 'jofme' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'tab_label' => __( 'Bracelets', 'jofme' ) ),
					array( 'tab_label' => __( 'Earrings', 'jofme' ) ),
					array( 'tab_label' => __( 'Necklaces', 'jofme' ) ),
					array( 'tab_label' => __( 'Rings', 'jofme' ) ),
				),
				'title_field' => '{{{ tab_label }}}',
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Products per tab', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 24,
				'default' => 8,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'options' => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'popularity',
				'options' => array(
					'popularity' => __( 'Popularity', 'jofme' ),
					'date'       => __( 'Latest', 'jofme' ),
					'menu_order' => __( 'Menu order', 'jofme' ),
					'title'      => __( 'Title', 'jofme' ),
					'price'      => __( 'Price', 'jofme' ),
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => __( 'Footer link text', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'View all', 'jofme' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'button_link',
			array(
				'label'   => __( 'Footer link', 'jofme' ),
				'type'    => \Elementor\Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style',
			array(
				'label' => __( 'Appearance', 'jofme' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text color', 'jofme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .jofme-bestsellers' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tab_active_color',
			array(
				'label'     => __( 'Active tab color', 'jofme' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .jofme-bestsellers__tab[aria-selected="true"]' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'label'    => __( 'Title typography', 'jofme' ),
				'selector' => '{{WRAPPER}} .jofme-bestsellers__title',
			)
		);

		$this->add_responsive_control(
			'section_padding',
			array(
				'label'      => __( 'Section padding', 'jofme' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .jofme-bestsellers' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Query visible products for a tab.
	 *
	 * @param int    $category_id Category term ID, or 0 for all.
	 * @param int    $limit       Product limit.
	 * @param string $orderby     Orderby key.
	 * @return WC_Product[]
	 */
	protected function query_products( $category_id, $limit, $orderby ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$args = array(
			'status'   => 'publish',
			'limit'    => max( 1, min( 24, absint( $limit ) ) ),
			'orderby'  => in_array( $orderby, array( 'popularity', 'date', 'menu_order', 'title', 'price' ), true ) ? $orderby : 'popularity',
			'order'    => 'DESC',
			'return'   => 'objects',
			'category' => array(),
		);

		if ( $category_id ) {
			$term = get_term( $category_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$args['category'] = array( $term->slug );
			}
		}

		$products = wc_get_products( $args );
		$visible  = array();
		foreach ( $products as $product ) {
			if (
				$product
				&& ! $product->is_type( 'variation' )
				&& 'publish' === get_post_status( $product->get_id() )
				&& ! post_password_required( $product->get_id() )
				&& $product->is_visible()
			) {
				$visible[] = $product;
			}
		}
		return $visible;
	}

	/**
	 * Discount percent badge markup.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	protected function sale_badge( $product ) {
		if ( ! $product->is_on_sale() ) {
			return '';
		}
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
			return '<span class="jofme-bestsellers__badge">' . esc_html__( 'Sale', 'jofme' ) . '</span>';
		}
		$percent = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
		return '<span class="jofme-bestsellers__badge">-' . absint( $percent ) . '%</span>';
	}

	/**
	 * Render one product card.
	 *
	 * @param WC_Product $product Product.
	 */
	protected function render_product( $product ) {
		$url      = $product->get_permalink();
		$terms    = get_the_terms( $product->get_id(), 'product_cat' );
		$category = ( ! is_wp_error( $terms ) && ! empty( $terms[0] ) ) ? $terms[0]->name : '';

		echo '<article class="jofme-bestsellers__card">';
		echo '<a class="jofme-bestsellers__media" href="' . esc_url( $url ) . '">';
		echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) );
		echo $this->sale_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
		echo '</a><div class="jofme-bestsellers__meta">';
		if ( $category ) {
			echo '<p class="jofme-bestsellers__category">' . esc_html( $category ) . '</p>';
		}
		echo '<h3 class="jofme-bestsellers__name"><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
		echo '<div class="jofme-bestsellers__price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
		echo '</div></article>';
	}

	/**
	 * Build tab definitions from settings.
	 *
	 * @param array $settings Settings.
	 * @return array<int,array{id:string,label:string,category:int}>
	 */
	protected function build_tabs( $settings ) {
		$tabs = array();

		if ( 'yes' === ( $settings['show_all_tab'] ?? 'yes' ) ) {
			$tabs[] = array(
				'id'       => 'all',
				'label'    => $settings['all_label'] ? $settings['all_label'] : __( 'All', 'jofme' ),
				'category' => 0,
			);
		}

		foreach ( (array) ( $settings['tabs'] ?? array() ) as $index => $tab ) {
			$label = isset( $tab['tab_label'] ) ? trim( (string) $tab['tab_label'] ) : '';
			if ( '' === $label ) {
				continue;
			}
			$tabs[] = array(
				'id'       => 'tab-' . ( $index + 1 ),
				'label'    => $label,
				'category' => isset( $tab['category_id'] ) ? absint( $tab['category_id'] ) : 0,
			);
		}

		return $tabs;
	}

	/**
	 * Frontend render.
	 */
	protected function render() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="jofme-widget-notice">' . esc_html__( 'WooCommerce is required for the Bestsellers Tabs widget.', 'jofme' ) . '</p>';
			}
			return;
		}

		$settings = $this->get_settings_for_display();
		$tabs     = $this->build_tabs( $settings );
		$uid      = 'jofme-bestsellers-' . $this->get_id();
		$columns  = in_array( (string) $settings['columns'], array( '2', '3', '4' ), true ) ? (string) $settings['columns'] : '4';
		$limit    = isset( $settings['limit'] ) ? absint( $settings['limit'] ) : 8;
		$orderby  = isset( $settings['orderby'] ) ? (string) $settings['orderby'] : 'popularity';

		if ( ! $tabs ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="jofme-widget-notice">' . esc_html__( 'Add at least one tab, or enable the “All” tab.', 'jofme' ) . '</p>';
			}
			return;
		}

		echo '<section class="jofme-bestsellers" data-jofme-bestsellers>';
		echo '<header class="jofme-bestsellers__header">';
		if ( ! empty( $settings['title'] ) ) {
			echo '<h2 class="jofme-bestsellers__title" id="' . esc_attr( $uid . '-title' ) . '">' . esc_html( $settings['title'] ) . '</h2>';
		}
		echo '<div class="jofme-bestsellers__tabs" role="tablist" aria-labelledby="' . esc_attr( $uid . '-title' ) . '">';

		foreach ( $tabs as $index => $tab ) {
			$selected = 0 === $index ? 'true' : 'false';
			echo '<button type="button" class="jofme-bestsellers__tab" role="tab" id="' . esc_attr( $uid . '-' . $tab['id'] ) . '" aria-controls="' . esc_attr( $uid . '-panel-' . $tab['id'] ) . '" aria-selected="' . esc_attr( $selected ) . '" tabindex="' . ( 0 === $index ? '0' : '-1' ) . '" data-tab="' . esc_attr( $tab['id'] ) . '">' . esc_html( $tab['label'] ) . '</button>';
		}
		echo '</div></header>';

		$any_products = false;
		foreach ( $tabs as $index => $tab ) {
			$products = $this->query_products( $tab['category'], $limit, $orderby );
			$hidden   = 0 === $index ? '' : ' hidden';
			echo '<div class="jofme-bestsellers__panel" role="tabpanel" id="' . esc_attr( $uid . '-panel-' . $tab['id'] ) . '" aria-labelledby="' . esc_attr( $uid . '-' . $tab['id'] ) . '"' . $hidden . '>';
			if ( $products ) {
				$any_products = true;
				echo '<div class="jofme-bestsellers__grid jofme-bestsellers__grid--' . esc_attr( $columns ) . '">';
				foreach ( $products as $product ) {
					$this->render_product( $product );
				}
				echo '</div>';
			} elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="jofme-widget-notice">' . esc_html__( 'No visible products found for this tab. Assign a product category or add published products.', 'jofme' ) . '</p>';
			}
			echo '</div>';
		}

		if ( ! empty( $settings['button_text'] ) && ! empty( $settings['button_link']['url'] ) ) {
			$this->add_link_attributes( 'bestsellers_link', $settings['button_link'] );
			echo '<div class="jofme-bestsellers__footer"><a class="jofme-editorial-link" ' . $this->get_render_attribute_string( 'bestsellers_link' ) . '>' . esc_html( $settings['button_text'] ) . '</a></div>';
		}

		if ( ! $any_products && ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			// Keep markup for tabs, but avoid an empty public section looking broken.
		}

		echo '</section>';
	}
}
