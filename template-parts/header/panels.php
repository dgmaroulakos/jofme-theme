<?php
/** Shared search and mobile navigation panels. @package Jofme */
defined( 'ABSPATH' ) || exit;
?>

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
		<nav tabindex="-1" class="jofme-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile', 'jofme' ); ?>">
			<button type="button" class="jofme-mobile-close" aria-label="<?php esc_attr_e( 'Close menu', 'jofme' ); ?>">×</button>
			<?php
			wp_nav_menu(
				array(
					'menu_id'        => 'jofme-mobile-menu',
					'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>