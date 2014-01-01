<?php
/**
 * Single program page view
 *
 * @package DNS Programs
 * @author Ryan Leeson
 */

global $post;
get_header();
?>
	<div class="bs-masthead container" id="content">
		<?php get_template_part( 'content', 'single' ); ?>
	</div>
<?php
get_footer();