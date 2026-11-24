<?php
/**
 * Header topbar: announcement + desktop social icons.
 *
 * @package Jofme
 */

defined( 'ABSPATH' ) || exit;

$announcement = jofme_get_announcement_text();
$show_social  = function_exists( 'jofme_social_show_in' ) && jofme_social_show_in( 'header' );

if ( ! $announcement && ! $show_social ) {
	return;
}

$classes = array( 'jofme-topbar' );
if ( $announcement ) {
	$classes[] = 'jofme-topbar--has-text';
}
if ( $show_social ) {
	$classes[] = 'jofme-topbar--has-social';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" role="region" aria-label="<?php esc_attr_e( 'Top bar', 'jofme' ); ?>">
	<div class="jofme-container jofme-topbar__inner">
		<?php if ( $show_social ) : ?>
			<div class="jofme-topbar__social">
				<?php jofme_social_nav( 'header' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $announcement ) : ?>
			<p class="jofme-topbar__text"><?php echo esc_html( $announcement ); ?></p>
		<?php endif; ?>
	</div>
</div>
