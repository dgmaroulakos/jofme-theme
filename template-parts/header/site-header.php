<?php
/**
 * Default site header.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$announcement = jofme_get_announcement_text();
$layout       = get_theme_mod( 'jofme_header_layout', 'classic' );
?>

<?php if ( $announcement ) : ?>
	<div class="jofme-announcement" role="region" aria-label="<?php esc_attr_e( 'Announcement', 'jofme' ); ?>">
		<div class="jofme-container">
			<p><?php echo esc_html( $announcement ); ?></p>
		</div>
	</div>
<?php endif; ?>

<header id="masthead" class="site-header site-header--<?php echo esc_attr( $layout ); ?>">
	<div class="jofme-container site-header__inner">
		<div class="site-header__start">
			<button type="button" class="jofme-menu-toggle" aria-controls="primary-menu" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'jofme' ); ?></span>
				<span class="jofme-menu-toggle__bars" aria-hidden="true"></span>
			</button>

			<?php if ( 'centered' !== $layout ) : ?>
				<div class="site-branding">
					<?php jofme_site_branding(); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( 'centered' === $layout ) : ?>
			<nav id="site-navigation-left" class="main-navigation main-navigation--split" aria-label="<?php esc_attr_e( 'Primary Left', 'jofme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
			<div class="site-branding site-branding--center">
				<?php jofme_site_branding(); ?>
			</div>
		<?php else : ?>
			<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'jofme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="site-header__end">
			<button type="button" class="jofme-search-toggle" aria-expanded="false" aria-controls="jofme-header-search">
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'jofme' ); ?></span>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.4"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
			</button>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="jofme-account-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'jofme' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.4"/><path d="M5 19c1.5-3.5 4-5 7-5s5.5 1.5 7 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
				</a>
				<?php echo jofme_cart_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	</div>

	<div id="jofme-header-search" class="jofme-header-search" hidden>
		<div class="jofme-container">
			<?php
			if ( class_exists( 'WooCommerce' ) ) {
				get_product_search_form();
			} else {
				get_search_form();
			}
			?>
		</div>
	</div>

	<div class="jofme-mobile-drawer" id="jofme-mobile-drawer" hidden>
		<nav class="jofme-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile', 'jofme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</header>
