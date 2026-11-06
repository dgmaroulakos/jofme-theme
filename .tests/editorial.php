<?php
/** Render and safety contracts without requiring a live WP database. */
namespace Elementor {
 class Controls_Manager {
  const TEXT='text', TEXTAREA='textarea', MEDIA='media', WYSIWYG='wysiwyg', URL='url', SELECT='select', TAB_STYLE='style', COLOR='color', DIMENSIONS='dimensions', SLIDER='slider', NUMBER='number', REPEATER='repeater', SWITCHER='switcher';
 }
 class Group_Control_Typography { public static function get_type() { return 'typography'; } }
 class Utils { public static function get_placeholder_image_src() { return '/placeholder.svg'; } }
 class Repeater {
  private $controls=array();
  public function add_control( $id, $args ) { $this->controls[$id]=$args; }
  public function get_controls() { return $this->controls; }
 }
 class Widget_Base {
  public $settings=array(); private $attributes=array();
  public function __construct() { $this->register_controls(); }
  public function start_controls_section( $id, $args ) {} public function end_controls_section() {}
  public function add_control( $id, $args ) { $this->settings[$id]=isset($args['default'])?$args['default']:('media'===$args['type']?array():('repeater'===$args['type']?array():'')); }
  public function add_group_control( $type, $args ) {} public function add_responsive_control( $id, $args ) {}
  public function get_settings_for_display() { return $this->settings; }
  public function get_id() { return 'testwidget'; }
  public function add_link_attributes( $name, $link ) { $this->attributes[$name]='href="'.\esc_url($link['url']).'"'.(!empty($link['is_external'])?' target="_blank" rel="noopener"':''); }
  public function get_render_attribute_string( $name ) { return $this->attributes[$name]; }
  public function output() { ob_start();$this->render();return ob_get_clean(); }
 }
 class Plugin { public static $instance; }
 Plugin::$instance=(object)array('editor'=>new class { public function is_edit_mode(){return !empty($GLOBALS['editor_mode']);} });
}
namespace {
 define('ABSPATH', __DIR__); define('JOFME_DIR', dirname(__DIR__));
 function add_action() {} function add_filter() {}
 function __($text){return $text;} function esc_html__($text){return esc_html($text);}
 function esc_html($text){return htmlspecialchars((string)$text,ENT_QUOTES,'UTF-8');} function esc_attr($text){return esc_html($text);}
 function esc_url($url){return preg_match('~^(https?://|/)~',$url)?esc_attr($url):'';}
 function absint($id){return abs((int)$id);} function get_post_status($id){return $GLOBALS['product_status'];}
 function post_password_required($id){return $GLOBALS['password'];}
 function wp_kses_post($html){return preg_replace('~<script\b[^>]*>.*?</script>~is','',$html);}
 function wp_strip_all_tags($html){return strip_tags($html);} function wp_trim_words($html,$limit){return implode(' ',array_slice(explode(' ',$html),0,$limit));}
 function wp_get_attachment_image($id,$size,$icon,$attributes){return '';}
 function taxonomy_exists($taxonomy){return 'product_cat'===$taxonomy;}
 function is_wp_error($thing){return $thing instanceof \WP_Error;}
 function get_terms($args){return array((object)array('term_id'=>3,'name'=>'Rings','slug'=>'rings'));}
 function get_term($id,$taxonomy){return (object)array('term_id'=>3,'name'=>'Rings','slug'=>'rings');}
 function get_the_terms($id,$taxonomy){return array((object)array('term_id'=>3,'name'=>'Earrings','slug'=>'earrings'));}
 function wc_get_product($id){return isset($GLOBALS['products'][$id])?$GLOBALS['products'][$id]:false;}
 function wc_get_product_ids_on_sale(){return array_keys($GLOBALS['products']);} function wc_get_products($args){return array_values($GLOBALS['products']);}
 class FixtureProduct {
  public $visible=true,$sale=true,$variation=false;
  public function get_id(){return 8;} public function is_type($type){return $this->variation;}
  public function is_visible(){return $this->visible;} public function is_on_sale(){return $this->sale;}
  public function get_permalink(){return '/product/earrings';} public function get_name(){return 'Sculpted gold earrings';}
  public function get_image($size){return '<img src="/product.jpg" alt="Gold earrings">';}
  public function get_price_html(){return '<del>€120</del> <ins>€95</ins>';}
  public function get_short_description(){return 'A warm gold finish, shaped for everyday elegance.';}
  public function get_regular_price(){return '120';} public function get_sale_price(){return '95';}
 }
 class WooCommerce {}
 function jofme_product_sale_badge_html($product,$class='jofme-sale-badge'){return $product&&$product->is_on_sale()?'<span class="'.esc_attr($class).'">-21%</span>':'';}
 function jofme_product_card_media_html($product,$size='woocommerce_thumbnail'){
  return '<span class="jofme-card-media">'.$product->get_image($size).'<img class="jofme-hover-image" src="/hover.jpg" alt=""></span>';
 }
 function verify($truth,$label){if(!$truth){throw new \RuntimeException($label);}}
 $product_status='publish';$password=false;$products=array(8=>new FixtureProduct());
 require JOFME_DIR.'/inc/editorial.php';
 require JOFME_DIR.'/inc/widgets/class-editorial-story.php';require JOFME_DIR.'/inc/widgets/class-weekly-sales.php';require JOFME_DIR.'/inc/widgets/class-bestsellers-tabs.php';
 $story=new Jofme_Editorial_Story_Widget();$story->settings['button_link']=array('url'=>'https://example.com/collection','is_external'=>true);$story->settings['main_image']=array('url'=>'/main.jpg');$story->settings['detail_image']=array('url'=>'/detail.jpg');
 $html=$story->output();verify(strpos($html,'jofme-story--left')!==false,'Story layout');verify(strpos($html,'target="_blank"')!==false,'External link attributes');
 $story->settings['title']='<script>alert(1)</script>';$html=$story->output();verify(strpos($html,'<script>')===false,'Escaped heading');
 $story->settings['image_side']='right';verify(strpos($story->output(),'jofme-story--right')!==false,'Reverse image');
 $sales=new Jofme_Weekly_Sales_Widget();$html=$sales->output();verify(strpos($html,'Sculpted gold earrings')!==false && strpos($html,'€95')!==false,'Real product content');verify(strpos($html,'jofme-sales__badge')!==false,'Sale badge');
 $products[8]->sale=false;$sales->settings['product_id']=8;verify(strpos($sales->output(),'jofme-sales__badge')===false,'No fake sale badge');
 $products[8]->visible=false;verify(false===jofme_editorial_product(8),'Hidden product');$products[8]->visible=true;
 $product_status='draft';verify(false===jofme_editorial_product(8),'Draft product');$product_status='publish';
 $password=true;verify(false===jofme_editorial_product(8),'Password product');$password=false;
 $products=array();verify(strpos($sales->output(),'jofme-sales__grid--no-product')!==false,'Empty catalog layout');verify(strpos($sales->output(),'jofme-widget-notice')===false,'No editor notices on public page');
 $editor_mode=true;verify(strpos($sales->output(),'jofme-widget-notice')!==false,'Editor guidance');
 $products=array(8=>new FixtureProduct());$editor_mode=false;
 $best=new Jofme_Bestsellers_Tabs_Widget();
 $best->settings['tabs']=array(array('tab_label'=>'Rings','category_id'=>'3'),array('tab_label'=>'Earrings','category_id'=>'3'));
 $best->settings['button_link']=array('url'=>'/shop');
 $html=$best->output();
 verify(strpos($html,'Bestsellers for a Reason')!==false,'Bestsellers title');
 verify(strpos($html,'role="tablist"')!==false && strpos($html,'role="tab"')!==false,'Accessible tabs');
 verify(strpos($html,'Sculpted gold earrings')!==false,'Bestsellers product card');
 verify(strpos($html,'jofme-bestsellers__badge')!==false,'Discount badge');
 verify(strpos($html,'jofme-card-media')!==false && strpos($html,'jofme-hover-image')!==false,'Hover image media');
 verify(substr_count($html,'role="tabpanel"')>=2,'Tab panels rendered');
 $best->settings['title']='<img src=x onerror=alert(1)>';
 verify(strpos($best->output(),'<img src=x')===false,'Escaped bestsellers title');
 if(in_array('--preview',$argv,true)) {
  $products=array(8=>new FixtureProduct());$editor_mode=false;$story->settings['title']='Paola Dias';$story->settings['image_side']='left';
  $sales->settings['campaign_image']=array('url'=>'/campaign.jpg');$sales->settings['detail_image']=array('url'=>'/detail.jpg');$sales->settings['button_link']=array('url'=>'/shop');
  echo $sales->output().$story->output().$best->output();
 }else{echo "PASS: widget rendering, links, escaped titles, product pricing and sale badge, private product exclusion, empty state, bestsellers tabs\n";}
}
