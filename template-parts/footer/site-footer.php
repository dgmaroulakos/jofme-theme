<?php
/**
 * Default site footer.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout      = function_exists( 'jofme_footer_layout' ) ? jofme_footer_layout() : 'widgets';
$has_widgets = is_active_sidebar( 'footer-1' )
	|| is_active_sidebar( 'footer-2' )
	|| is_active_sidebar( 'footer-3' )
	|| is_active_sidebar( 'footer-4' );
$prebuilt    = 'brand_left' === $layout;
$show_main   = $prebuilt || $has_widgets;
$footer_classes = array(
	'site-footer',
	'site-footer--' . $layout,
);
if ( $prebuilt && function_exists( 'jofme_footer_logo_invert' ) && jofme_footer_logo_invert() ) {
	$footer_classes[] = 'site-footer--invert-logo';
}
?>

<footer id="colophon" class="<?php echo esc_attr( implode( ' ', $footer_classes ) ); ?>">
	<?php if ( $show_main ) : ?>
		<div class="site-footer__widgets">
			<?php if ( $prebuilt ) : ?>
				<div class="jofme-container site-footer__grid site-footer__grid--brand-left">
					<?php
					jofme_footer_brand_column();
					jofme_footer_prebuilt_col( 1 );
					jofme_footer_prebuilt_col( 2 );
					jofme_footer_prebuilt_col( 3 );
					?>
				</div>
			<?php else : ?>
				<div class="jofme-container site-footer__grid">
					<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
						<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
							<div class="site-footer__col">
								<?php dynamic_sidebar( 'footer-' . $i ); ?>
							</div>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="site-footer__bottom">
		<div class="jofme-container site-footer__bottom-inner">
			<p class="site-footer__copyright"><?php echo wp_kses_post( jofme_get_footer_copyright() ); ?></p>

			<?php
			// Classic widget footer: socials live in the bottom bar (prebuilt shows them in the brand column).
			if ( ! $prebuilt && function_exists( 'jofme_social_nav' ) ) {
				jofme_social_nav( 'footer' );
			}
			?>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="footer-navigation" aria-label="<?php esc_attr_e( 'Footer', 'jofme' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>
	</div>
</footer>

<button type="button" class="jofme-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'jofme' ); ?>" hidden>
	<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
</button>
