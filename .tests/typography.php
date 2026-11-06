<?php
/** Typography choice and CDN contracts without WordPress. */
define('ABSPATH',__DIR__);$mods=array();$styles=array();$inline='';
function add_action(){}function add_filter(){}function __($s){return $s;}
function esc_url_raw($s,$protocols){return preg_match('~^https?://~',$s)?str_replace(array('"',"\n","\r"),'',$s):'';}
function wp_parse_url($url,$component){return parse_url($url,$component);}
function get_theme_mod($key,$default){return isset($GLOBALS['mods'][$key])?$GLOBALS['mods'][$key]:$default;}
function absint($id){return abs((int)$id);}function wp_get_attachment_url($id){return 'https://example.com/font.woff2';}
function wp_json_encode($value,$flags){return json_encode($value,$flags);}
function wp_enqueue_style($name,$url,$deps,$version){$GLOBALS['styles'][$name]=$url;}
function wp_add_inline_style($handle,$css){$GLOBALS['inline']=$css;}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);}
require dirname(__DIR__).'/inc/typography.php';
check(''===jofme_font_url_sanitize('javascript:alert(1)'),'Unsafe protocol');
check(''===jofme_font_url_sanitize('https://example.com/not-font.php'),'Wrong extension');
check('https://example.com/font.woff2?v=2'===jofme_font_url_sanitize('https://example.com/font.woff2?v=2'),'CDN query URL');
jofme_typography_assets();check(!$styles,'No extra CDN for default fonts');
$mods['jofme_font_body']='aeonik-light';jofme_typography_assets();
check(isset($styles['jofme-aeonik-light']),'Supplied CDN registered');check(strpos($inline,'Aeonik Pro Light')!==false,'Font applied to body stack');
$mods['jofme_font_body']='custom-aeonik';$mods['jofme_font_cdn_regular']='https://example.com/font.woff2';jofme_typography_assets();check(strpos($inline,'@font-face')!==false&&strpos($inline,'font-display:swap')!==false,'Custom font-face');
check(jofme_font_stack('invalid','manrope')===jofme_font_stack('manrope','manrope'),'Invalid choice fallback');
echo "PASS: font choices, selected CDN, custom WOFF2, unsafe URL rejection and fallback\n";
