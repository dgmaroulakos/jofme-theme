<?php
/**
 * Front page — Elementor-friendly home.
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
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'jofme-front-page' ); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>

<?php jofme_layout_sidebar(); ?>
</div>
<?php
get_footer();
