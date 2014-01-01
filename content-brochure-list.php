<?php
/**
 * Program listing for an individual brochure
 * Listing is based on multiple groupings, first by Location (program-locations) taxonomy, 
 * then the Age Ranges (age-ranges) taxonomy.  Within each group, programs are sorted by 
 * custom meta field State Date (dns_start_date) ascending
 * 
 * @package DNS Programs
 * @author Ryan Leeson
 */

global $post;
$debug = 0;
$brochure = get_queried_object();
$t1_slug = 'program-locations';
$t2_slug = 'age-ranges';

// Query all published programs for the requested brochure edition
$query_args = array(
	'post_type' 		=> 'post',
	'post_status'		=> 'published',
	'tax_query' 		=> array (
		array(
			'taxonomy' 	=> 'brochure-editions',
			'field'		=> 'id',
			'terms'		=> $brochure->term_id		
		)			
	),
	'posts_per_page' 	=> -1
);

$sort_args = array (
	'query_args'	=> $query_args,
	't1_slug'		=> $t1_slug, 
	't2_slug'		=> $t2_slug, 
	'sort_callback'	=> 'dns_startdate_timestamp'
);

$brochure_order = dns_double_taxonomy_sort_array( $sort_args );

if ($debug) {
	print_r( $brochure_order );
}

$render_args = array(
	'sort_array'	=> $brochure_order,
	't1_slug'		=> $t1_slug,
	't2_slug'		=> $t2_slug	
);

?>
<main class="bs-masthead container brochure-list" id="content" role="main">
	<header class="section-info <?php echo $brochure->slug; ?>">
		<div class="brochure-editions">
   			<h1>Brochure</h1>
   			<?php printf( '<p>%s</p>', $brochure->name ); ?>
   		</div>
   	</header>
	<?php dns_double_taxonomy_sort_render( $render_args ); ?>
</main>