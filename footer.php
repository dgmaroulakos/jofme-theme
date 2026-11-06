<?php
/**
 * Footer template.
 *
 * @package Jofme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<?php
	if ( jofme_has_elementor_footer() && jofme_elementor_do_location( 'footer' ) ) {
		// Elementor Theme Builder footer.
	} else {
		get_template_part( 'template-parts/footer/site', 'footer' );
	}
	?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
