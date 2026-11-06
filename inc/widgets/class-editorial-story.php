<?php
/** Editorial collection story widget. @package Jofme */
defined( 'ABSPATH' ) || exit;
class Jofme_Editorial_Story_Widget extends \Elementor\Widget_Base {
 public function get_name() { return 'jofme-editorial-story'; }
 public function get_title() { return __( 'Editorial Story (Paola Dias)', 'jofme' ); }
 public function get_icon() { return 'eicon-image-box'; }
 public function get_categories() { return array( 'jofme' ); }
 public function get_keywords() { return array( 'jewelry', 'collection', 'paola', 'story', 'jofme' ); }
 public function get_style_depends() { return array( 'jofme-editorial-widgets' ); }
 protected function register_controls() {
  $this->start_controls_section( 'content', array( 'label' => __( 'Collection story', 'jofme' ) ) );
  $this->add_control( 'title', array( 'label' => __( 'Title', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Paola Dias', 'dynamic' => array( 'active' => true ) ) );
  $this->add_control( 'intro', array( 'label' => __( 'Introduction', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'An intimate expression of modern jewelry, made for the moments that become your story.', 'jofme' ), 'dynamic' => array( 'active' => true ) ) );
  $this->add_control( 'main_image', array( 'label' => __( 'Large editorial image', 'jofme' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => array( 'active' => true ) ) );
  $this->add_control( 'detail_image', array( 'label' => __( 'Portrait / detail image', 'jofme' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'dynamic' => array( 'active' => true ) ) );
  $this->add_control( 'description', array( 'label' => __( 'Text below detail image', 'jofme' ), 'type' => \Elementor\Controls_Manager::WYSIWYG, 'default' => __( 'Discover a collection of expressive shapes and thoughtful details, selected to feel distinctly yours.', 'jofme' ) ) );
  $this->add_control( 'button_text', array( 'label' => __( 'Link text', 'jofme' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore the collection', 'jofme' ) ) );
  $this->add_control( 'button_link', array( 'label' => __( 'Collection link', 'jofme' ), 'type' => \Elementor\Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
  $this->add_control( 'image_side', array( 'label' => __( 'Large image position', 'jofme' ), 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'left', 'options' => array( 'left' => __( 'Left', 'jofme' ), 'right' => __( 'Right', 'jofme' ) ) ) );
  $this->end_controls_section();
  $this->start_controls_section( 'appearance', array( 'label' => __( 'Appearance', 'jofme' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
  $this->add_control( 'background', array( 'label' => __( 'Background', 'jofme' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#f6f2eb', 'selectors' => array( '{{WRAPPER}} .jofme-story__content' => 'background-color: {{VALUE}};' ) ) );
  $this->add_control( 'text_color', array( 'label' => __( 'Text color', 'jofme' ), 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jofme-story__content' => 'color: {{VALUE}};' ) ) );
  $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name' => 'heading_type', 'label' => __( 'Title typography', 'jofme' ), 'selector' => '{{WRAPPER}} .jofme-story__title' ) );
  $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name' => 'body_type', 'label' => __( 'Text typography', 'jofme' ), 'selector' => '{{WRAPPER}} .jofme-story__intro, {{WRAPPER}} .jofme-story__description' ) );
  $this->add_responsive_control( 'content_padding', array( 'label' => __( 'Content padding', 'jofme' ), 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%', 'em' ), 'selectors' => array( '{{WRAPPER}} .jofme-story__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
  $this->add_responsive_control( 'detail_width', array( 'label' => __( 'Detail image width', 'jofme' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => array( 'px', '%' ), 'range' => array( 'px' => array( 'min' => 100, 'max' => 650 ), '%' => array( 'min' => 20, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .jofme-story__detail' => 'width: {{SIZE}}{{UNIT}};' ) ) );
  $this->add_responsive_control( 'image_position', array( 'label' => __( 'Large image focal point', 'jofme' ), 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'center', 'options' => array( 'center' => __( 'Center', 'jofme' ), 'left' => __( 'Left', 'jofme' ), 'right' => __( 'Right', 'jofme' ), 'top' => __( 'Top', 'jofme' ) ), 'selectors' => array( '{{WRAPPER}} .jofme-story__main img' => 'object-position: {{VALUE}};' ) ) );
  $this->end_controls_section();
 }
 protected function render() {
  $s = $this->get_settings_for_display();
  $side = 'right' === $s['image_side'] ? 'right' : 'left';
  echo '<section class="jofme-story jofme-story--' . esc_attr( $side ) . '"><div class="jofme-story__main">' . jofme_editorial_image( $s['main_image'], 'full' ) . '</div><div class="jofme-story__content">';
  if ( $s['title'] ) { echo '<h2 class="jofme-story__title">' . esc_html( $s['title'] ) . '</h2>'; }
  if ( $s['intro'] ) { echo '<p class="jofme-story__intro">' . esc_html( $s['intro'] ) . '</p>'; }
  echo '<div class="jofme-story__detail">' . jofme_editorial_image( $s['detail_image'] ) . '</div>';
  if ( $s['description'] ) { echo '<div class="jofme-story__description">' . wp_kses_post( $s['description'] ) . '</div>'; }
  if ( $s['button_text'] && ! empty( $s['button_link']['url'] ) ) {
   $this->add_link_attributes( 'collection_link', $s['button_link'] );
   echo '<a class="jofme-editorial-link" ' . $this->get_render_attribute_string( 'collection_link' ) . '>' . esc_html( $s['button_text'] ) . '</a>';
  }
  echo '</div></section>';
 }
}
