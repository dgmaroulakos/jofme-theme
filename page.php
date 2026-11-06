<?php
/**
 * Page template.
 *
 * @package Jofme
 */

get_header();
?>

<div class="jofme-container jofme-layout jofme-layout--page">
	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/content', 'page' );

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</main>
	</div>
	<?php jofme_layout_sidebar(); ?>
</div>

<?php
get_footer();
