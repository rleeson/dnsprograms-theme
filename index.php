<?php
/**
 * The main template file.
 *
 * @package DNS Programs
 * @author Ryan Leeson
 */

get_header();
if ( is_search() ) {
	get_template_part( 'content', 'program-table' );
}
else {
	get_template_part( 'content', 'main-list' );
}
get_footer();
