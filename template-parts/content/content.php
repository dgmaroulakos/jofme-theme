<?php
/**
 * Default content loop item.
 *
 * @package Jofme
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'jofme-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="jofme-card__media" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'jofme-blog' ); ?>
		</a>
	<?php endif; ?>

	<header class="entry-header">
		<?php the_title( sprintf( '<h2 class="entry-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
		<div class="entry-meta"><?php jofme_posted_on(); ?></div>
	</header>

	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div>
</article>
