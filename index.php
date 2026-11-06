<?php
/**
 * Main template fallback.
 *
 * @package Jofme
 */

get_header();
?>

<div class="jofme-container jofme-layout">
	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<?php if ( have_posts() ) : ?>
				<?php if ( is_home() && ! is_front_page() ) : ?>
					<header class="page-header">
						<h1 class="page-title"><?php single_post_title(); ?></h1>
					</header>
				<?php endif; ?>

				<div class="jofme-posts">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/content', get_post_type() );
					endwhile;
					?>
				</div>

				<?php the_posts_navigation(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
			<?php endif; ?>
		</main>
	</div>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
