<?php
/**
 * 404 template.
 *
 * @package Jofme
 */

get_header();
?>

<div class="jofme-container">
	<main id="primary" class="site-main jofme-404">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Page not found', 'jofme' ); ?></h1>
		</header>
		<div class="page-content">
			<p><?php esc_html_e( 'It looks like nothing was found at this location. Try a search or return to the shop.', 'jofme' ); ?></p>
			<?php get_search_form(); ?>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<p class="jofme-404__actions">
					<a class="jofme-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
						<?php esc_html_e( 'Continue shopping', 'jofme' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</main>
</div>

<?php
get_footer();
