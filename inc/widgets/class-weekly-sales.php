<?php
/** Three-column sale campaign widget. @package Jofme */
defined( 'ABSPATH' ) || exit;
class Jofme_Weekly_Sales_Widget extends \Elementor\Widget_Base {
 public function get_name() { return 'jofme-weekly-sales'; }
 public function get_title() { return __( 'Weekly Sales', 'jofme' ); }
 public function get_icon() { return 'eicon-products'; }
 public function get_categories() { return array( 'jofme' ); }
 public function get_keywords() { return array( 'sales', 'jewelry', 'campaign', 'jofme' ); }
 public function get_style_depends() { return array( 'jofme-editorial-widgets' ); }
 protected function register_controls() {
  $this->start_controls_section( 'heading', array( 'label' => __( 'Section heading', 'jofme' ) ) );
  $this->add_control( 'title', array( 'label' => __( 'Title', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Don’t miss this week’s sales', 'dynamic' => array( 'active' => true ) ) );
  $this->add_control( 'subtitle', array( 'label' => __( 'Subtitle', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'A considered selection of beautiful pieces, at special prices.', 'jofme' ) ) );
  $this->end_controls_section();
  $this->start_controls_section( 'product', array( 'label' => __( 'Featured product', 'jofme' ) ) );
  $this->add_control( 'product_id', array( 'label' => __( 'WooCommerce product ID', 'jofme' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 0, 'default' => 0, 'description' => __( 'Leave 0 to show a visible sale product automatically. Find the ID under Products in WordPress. An explicit ID may also display a full-price product without a sale badge.', 'jofme' ) ) );
  $this->add_control( 'product_note', array( 'label' => __( 'Text below product (optional)', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'description' => __( 'Leave empty to use the product short description.', 'jofme' ) ) );
  $this->end_controls_section();
  $this->start_controls_section( 'campaign', array( 'label' => __( 'Central campaign', 'jofme' ) ) );
  $this->add_control( 'campaign_image', array( 'label' => __( 'Campaign photo', 'jofme' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => array( 'active' => true ) ) );
  foreach ( array( 'eyebrow' => array( __( 'Small label', 'jofme' ), __( 'The jewelry edit', 'jofme' ) ), 'campaign_title' => array( __( 'Campaign title', 'jofme' ), __( 'Small details. Lasting impressions.', 'jofme' ) ), 'campaign_text' => array( __( 'Campaign text', 'jofme' ), __( 'Explore everyday treasures and expressive pieces, chosen to celebrate your own style.', 'jofme' ) ), 'button_text' => array( __( 'Link text', 'jofme' ), __( 'Shop the edit', 'jofme' ) ) ) as $id => $data ) {
   $this->add_control( $id, array( 'label' => $data[0], 'type' => 'campaign_text' === $id ? \Elementor\Controls_Manager::TEXTAREA : \Elementor\Controls_Manager::TEXT, 'default' => $data[1], 'dynamic' => array( 'active' => true ) ) );
  }
  $this->add_control( 'button_link', array( 'label' => __( 'Campaign link', 'jofme' ), 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
  $this->end_controls_section();
  $this->start_controls_section( 'detail', array( 'label' => __( 'Right-hand story', 'jofme' ) ) );
  $this->add_control( 'detail_title', array( 'label' => __( 'Title', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Made to feel like you', 'jofme' ) ) );
  $this->add_control( 'detail_text', array( 'label' => __( 'Description', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Thoughtful shapes and luminous finishes bring a personal touch to the everyday.', 'jofme' ) ) );
  $this->add_control( 'detail_image', array( 'label' => __( 'Detail photo', 'jofme' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => array( 'active' => true ) ) );
  $this->end_controls_section();
  $this->start_controls_section( 'style', array( 'label' => __( 'Appearance', 'jofme' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
  $this->add_control( 'text_color', array( 'label' => __( 'Text color', 'jofme' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jofme-sales' => 'color: {{VALUE}};' ) ) );
  $this->add_control( 'campaign_color', array( 'label' => __( 'Campaign text color', 'jofme' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => array( '{{WRAPPER}} .jofme-sales__campaign-content' => 'color: {{VALUE}};' ) ) );
  $this->add_control( 'overlay', array( 'label' => __( 'Photo overlay', 'jofme' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => 'rgba(0,0,0,0.25)', 'selectors' => array( '{{WRAPPER}} .jofme-sales__campaign::after' => 'background: {{VALUE}};' ) ) );
  $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name' => 'heading_typography', 'label' => __( 'Section title typography', 'jofme' ), 'selector' => '{{WRAPPER}} .jofme-sales__heading h2' ) );
  $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name' => 'campaign_typography', 'label' => __( 'Campaign title typography', 'jofme' ), 'selector' => '{{WRAPPER}} .jofme-sales__campaign h3' ) );
  $this->add_responsive_control( 'gap', array( 'label' => __( 'Column gap', 'jofme' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 12, 'max' => 120 ) ), 'selectors' => array( '{{WRAPPER}} .jofme-sales__grid' => 'gap: {{SIZE}}px;' ) ) );
  $this->add_responsive_control( 'campaign_height', array( 'label' => __( 'Campaign minimum height', 'jofme' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 300, 'max' => 1200 ) ), 'selectors' => array( '{{WRAPPER}} .jofme-sales__campaign' => 'min-height: {{SIZE}}px;' ) ) );
  $this->end_controls_section();
 }
 protected function render() {
  $s = $this->get_settings_for_display();
  $product = jofme_editorial_product( absint( $s['product_id'] ) );
  echo '<section class="jofme-sales"><header class="jofme-sales__heading">';
  if ( $s['title'] ) { echo '<h2>' . esc_html( $s['title'] ) . '</h2>'; }
  if ( $s['subtitle'] ) { echo '<p>' . esc_html( $s['subtitle'] ) . '</p>'; }
  echo '</header><div class="jofme-sales__grid' . ( $product ? '' : ' jofme-sales__grid--no-product' ) . '">';
  if ( $product ) {
   $url = $product->get_permalink();
   echo '<article class="jofme-sales__product"><a class="jofme-sales__product-image" href="' . esc_url( $url ) . '">' . wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) );
   if ( $product->is_on_sale() ) { echo '<span class="jofme-sales__badge">' . esc_html__( 'Sale', 'jofme' ) . '</span>'; }
   echo '</a><h3><a href="' . esc_url( $url ) . '">' . esc_html( $product->get_name() ) . '</a></h3><div class="jofme-sales__price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
   $note = $s['product_note'] ? $s['product_note'] : wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 35 );
   if ( $note ) { echo '<p class="jofme-sales__product-note">' . esc_html( $note ) . '</p>'; }
   echo '</article>';
  } elseif ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
   echo '<p class="jofme-widget-notice">' . esc_html__( 'Choose a published, visible WooCommerce product ID, or add products on sale for automatic selection. This message only appears in the editor.', 'jofme' ) . '</p>';
  }
  echo '<div class="jofme-sales__campaign">' . jofme_editorial_image( $s['campaign_image'], 'full', 'jofme-sales__campaign-image' ) . '<div class="jofme-sales__campaign-content">';
  if ( $s['eyebrow'] ) { echo '<p class="jofme-sales__eyebrow">' . esc_html( $s['eyebrow'] ) . '</p>'; }
  if ( $s['campaign_title'] ) { echo '<h3>' . esc_html( $s['campaign_title'] ) . '</h3>'; }
  if ( $s['campaign_text'] ) { echo '<p>' . esc_html( $s['campaign_text'] ) . '</p>'; }
  if ( $s['button_text'] && ! empty( $s['button_link']['url'] ) ) {
   $this->add_link_attributes( 'campaign_link', $s['button_link'] );
   echo '<a class="jofme-editorial-link" ' . $this->get_render_attribute_string( 'campaign_link' ) . '>' . esc_html( $s['button_text'] ) . '</a>';
  }
  echo '</div></div><div class="jofme-sales__detail">';
  if ( $s['detail_title'] ) { echo '<h3>' . esc_html( $s['detail_title'] ) . '</h3>'; }
  if ( $s['detail_text'] ) { echo '<p>' . esc_html( $s['detail_text'] ) . '</p>'; }
  echo jofme_editorial_image( $s['detail_image'] ) . '</div></div></section>';
 }
}
