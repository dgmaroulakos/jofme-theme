<?php
/** Header rendering without WordPress. */
define('ABSPATH',__DIR__);
function __($text){return $text;}
function esc_html($text){return htmlspecialchars((string)$text,ENT_QUOTES,'UTF-8');}
function esc_attr($text){return esc_html($text);}function esc_url($text){return esc_html($text);}
function esc_attr_e($text){echo esc_attr($text);}
function get_theme_mod($key,$default){return $default;}
function jofme_get_announcement_text(){return '';}
function jofme_site_branding(){echo '<p class="site-title"><a href="/">Jofme</a></p>';}
function apply_filters($name,$default,$args){return $default;}
function do_action($name){}
function has_nav_menu($name){return true;}
function wp_nav_menu($args){
 $id=$args['menu_id'];$links=array('Home','New in','Shop','Collections','Journal');
 echo '<ul class="menu" id="'.esc_attr($id).'">';foreach($links as $label){echo '<li><a href="/#'.esc_attr(str_replace(' ','-',strtolower($label))).'">'.esc_html($label).'</a></li>';}echo '</ul>';
}
class WooCommerce{}
function wc_get_page_permalink($page){return '/account';}
function jofme_cart_link(){return '<a class="jofme-cart-link" href="/cart" aria-label="Cart"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M4 7h16l1 15H3L4 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/></svg><span class="jofme-cart-count">0</span></a>';}
function get_product_search_form(){echo '<form class="woocommerce-product-search"><input type="search" aria-label="Search products" placeholder="Search jewelry"><button>Search</button></form>';}
function get_search_form(){get_product_search_form();}
function get_template_part($file){require dirname(__DIR__).'/'.$file.'.php';}
ob_start();require dirname(__DIR__).'/template-parts/header/editorial-header.php';$html=ob_get_clean();
if(in_array('--preview',$argv,true)){echo $html;}else{
 foreach(array('primary-menu','jofme-mobile-menu','jofme-mobile-drawer','jofme-header-search','jofme-mobile-close','aria-controls="jofme-mobile-drawer"') as $needle){if(strpos($html,$needle)===false)throw new RuntimeException('Missing header element: '.$needle);}
 if(false!==strpos($html,'jofme-utility-bar'))throw new RuntimeException('Utility bar should be removed');
 if(substr_count($html,'id="primary-menu"')!==1)throw new RuntimeException('Duplicate menu ID');
 $css=file_get_contents(dirname(__DIR__).'/assets/css/main.css');
 if(!preg_match('/\.custom-logo-link img\s*\{[^}]*max-height:\s*100px/s',$css))throw new RuntimeException('Logo max-height should be 100px');
 if(!preg_match('/\.custom-logo-link img\s*\{\s*max-width:\s*90px/s',$css))throw new RuntimeException('Mobile logo max-width should be 90px');
 echo "PASS: editorial header, unique menus, shared search and mobile controls\n";
}
