<?php
/** Editorial header. @package Jofme */
defined( 'ABSPATH' ) || exit;
$announcement = jofme_get_announcement_text();
if ( $announcement ) : ?>
 <div class="jofme-announcement"><div class="jofme-container"><p><?php echo esc_html( $announcement ); ?></p></div></div>
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
