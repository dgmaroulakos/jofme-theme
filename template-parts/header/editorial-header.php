<?php
/** Editorial header with optional utility bar. @package Jofme */
defined( 'ABSPATH' ) || exit;
$announcement = jofme_get_announcement_text();
if ( $announcement ) : ?>
 <div class="jofme-announcement"><div class="jofme-container"><p><?php echo esc_html( $announcement ); ?></p></div></div>
<?php endif;
if ( get_theme_mod( 'jofme_header_utility', true ) ) : ?>
 <div class="jofme-utility-bar"><div class="jofme-container jofme-utility-bar__inner">
  <nav aria-label="<?php esc_attr_e( 'Utility links', 'jofme' ); ?>"><?php wp_nav_menu( array( 'theme_location' => 'utility-left', 'container' => false, 'menu_id' => 'jofme-utility-left', 'depth' => 1, 'fallback_cb' => false ) ); ?></nav>
  <div class="jofme-utility-bar__end">
   <nav aria-label="<?php esc_attr_e( 'Additional links', 'jofme' ); ?>"><?php wp_nav_menu( array( 'theme_location' => 'utility-right', 'container' => false, 'menu_id' => 'jofme-utility-right', 'depth' => 1, 'fallback_cb' => false ) ); ?></nav>
   <?php
   $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 1 ) );
   if ( is_array( $languages ) && count( $languages ) > 1 ) : ?>
    <nav class="jofme-header-languages" aria-label="<?php esc_attr_e( 'Language', 'jofme' ); ?>">
     <?php foreach ( $languages as $language ) : ?>
      <a href="<?php echo esc_url( $language['url'] ); ?>" lang="<?php echo esc_attr( $language['language_code'] ); ?>"<?php if ( ! empty( $language['active'] ) ) { echo ' aria-current="true"'; } ?>><?php echo esc_html( $language['native_name'] ); ?></a>
     <?php endforeach; ?>
    </nav>
   <?php endif;
   do_action( 'jofme_header_utility_end' );
   ?>
  </div>
 </div></div>
<?php endif; ?>
<header id="masthead" class="site-header site-header--editorial">
 <div class="jofme-container site-header__inner">
  <div class="site-header__start">
   <button type="button" class="jofme-menu-toggle" aria-label="<?php esc_attr_e( 'Open menu', 'jofme' ); ?>" aria-controls="jofme-mobile-drawer" aria-expanded="false"><span class="jofme-menu-toggle__bars" aria-hidden="true"></span></button>
   <div class="site-branding"><?php jofme_site_branding(); ?></div>
  </div>
  <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'jofme' ); ?>"><?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu', 'container' => false, 'fallback_cb' => false ) ); ?></nav>
  <div class="site-header__end">
   <?php if ( class_exists( 'WooCommerce' ) ) : ?>
    <a class="jofme-account-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'jofme' ); ?>"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M3 22a9 9 0 0 1 18 0"/></svg></a>
   <?php endif; ?>
   <button type="button" class="jofme-search-toggle" aria-label="<?php esc_attr_e( 'Search', 'jofme' ); ?>" aria-expanded="false" aria-controls="jofme-header-search"><svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="m15 15 7 7"/></svg></button>
   <?php if ( class_exists( 'WooCommerce' ) ) { echo jofme_cart_link(); } ?>
  </div>
 </div>
 <?php get_template_part( 'template-parts/header/panels' ); ?>
</header>
