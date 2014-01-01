<?php
/**
 * Program listing for an individual category
 * Listing is based on multiple groupings, first by Brochure (brochure-editions) taxonomy, 
 * then the Age Ranges (age-ranges) taxonomy.  Within each group, programs are sorted by 
 * custom meta field State Date (dns_start_date) ascending
 * 
 * @package DNS Programs
 * @author Ryan Leeson
 */

global $post;

// Set default page properties
$object = get_queried_object();
$page_name = 'main';
$query_args = array(
	'post_type' 		=> 'post',
	'post_status'		=> 'published',
	'posts_per_page' 	=> -1
);

// Modify query based on page type
if ( is_author() ) {
	$page_name = 'author';
	$query_args[ 'author_name' ] = $object->user_nicename;
}
elseif ( is_category() ) {
	$page_name = 'category';
	$query_args[ 'category_name' ] = esc_attr( $object->slug );
}

// Set properties to build the sort array
$sort_args = array(
	'query_args'	=> $query_args,
	'sort_callback'	=> 'dns_startdate_timestamp'
);

$list_order = dns_double_taxonomy_sort_array( $sort_args );

// Specify the list render properties
$render_args = array ( 'sort_array' => $list_order );
if ( is_home() ) {
	$render_args[ 't1_collapse' ] = false;
}

?>
<main class="bs-masthead container <?php echo esc_attr( $page_name ); ?>-list" id="content" role="main">
<?php 
	// Show an optional Header for specific page types
	if ( is_author() ) {
?> 
	<header class="section-info <?php echo $object->slug; ?>">
		<div class="categories">
   			<h1>Author</h1>
   			<?php printf( '<p>%s</p>', $object->display_name ); ?>
   		</div>
   	</header>
<?php 
	} 
	elseif ( is_category() ) {
?> 
	<header class="section-info <?php echo $object->slug; ?>">
		<div class="categories">
   			<h1>Category</h1>
   			<?php printf( '<p>%s</p>', $object->name ); ?>
   		</div>
   	</header>
<?php 
	} 
	dns_double_taxonomy_sort_render( $render_args ); ?>
</main>