<?php
/**
 * Modify the main query to include draft and pending programs
 */
function dns_query_all_status( $query ) {
	if ( !is_admin() && $query->is_main_query() ) {
		$query->set( 'post_status', array( 'draft', 'pending', 'publish' ) );
		$query->set( 'post_type', 'post' );
	}
}
add_action( 'pre_get_posts', 'dns_query_all_status' );

/**
 * Prints a formatted label for Draft and Pending posts
 */
function dns_print_post_status() {
	global $post;
	
	if ( $post->post_status == 'draft' || $post->post_status == 'pending' ) {
		printf( '<p><strong>%s</strong></p>', ucfirst( $post->post_status ) );
	}
}

/**
 * Print the requested program price, or free if empty
 * @param string $key Post meta key for the price
 */
function dns_program_price( $key ) {
	global $post;
	
	$price	= get_post_meta( $post->ID, $key, true );
	if ( empty( $price ) ) {
		return 'Free';
	}
	else {
		return sprintf( '$%01.0f', $price );
	}
}

/**
 * Generate a set of list items for all terms in a given taxonomy
 */
function dns_list_taxonomy_terms( $taxonomy ) {
	if ( empty( $taxonomy ) ) {
		return;
	}
	$terms = get_terms( $taxonomy );
	
	if ( !empty( $terms ) ) {
		foreach( $terms as $term ) {
			printf( '<li><a href="%s" title="Programs for %s">%s</a></li>', get_term_link( $term ), $term->name, $term->name );
		}
	}
}

/**
 * Display the post thumbnail if there is one and link to larger modal version. Loop only.
 */
function dns_program_image_column() {
	printf( '<td class="program-image">' );
	dns_program_image_thumbnail();
	printf( '</td>' );
}

/**
 * Display the post thumbnail with optional url.  Thumbnail wrapped with link to open modal with larger version. Loop only.
 * @param boolean $show_url Optionally display the image link before the thumbnail
 */
function dns_program_image_thumbnail( $show_url = false ) {
	global $post;
	if ( has_post_thumbnail()) {
		$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
		$url = $large_image_url[0];
		if ( $show_url ) {
			$file_path = explode( '/', $url );
			$file = array_pop( $file_path );
			printf( '<p class="image-url"><strong>Image URL: </strong><a href="%s">%s</a></p>', $url, $url );
			printf( '<p class="image-url"><strong>iMIS Image Page: </strong><span class="imis-path">images/Events/%s</span></p>', $file );
		}
		printf( '<a href="#image-modal" data-image-url="%s" title="%s" data-toggle="modal" class="modal-link">%s</a>',
			$url, the_title_attribute( 'echo=0' ), get_the_post_thumbnail( $post->ID, 'thumbnail' ) );
	}
}

/**
 * Given a taxonomy name, outputs a list of all
 * terms associated with the current post.  Loop only.
 * 
 * @param string $name Taxonomy name
 * @param bool $table Output table cell wrapper if true
 */
function dns_taxonomy_list( $name, $table = false ) {
	global $post;
	$terms = wp_get_post_terms( $post->ID, $name );
	
	if ( $table ) {
		printf( '<td class="%s-list">', $name );
	}
	printf( '<ul class="%s-list">', $name );
	if ( !empty( $terms ) ) {
		foreach ( $terms as $term ) {
			printf( '<li>%s</li>', $term->name );
		}
	}
	printf( '</ul>' );
	if ( $table ) {
		printf( '</td>' );
	}
}

/**
 * Takes the set of taxonomy terms associated with a post and returns or prints them combined by a seperator.
 * 
 * @param string $name Taxonomy name
 * @param string $separator Set of characters to place between each term in the string, defaults to ', '
 * @param boolean $echo Whether to print (true) the string
 * @return null|string Combined string of terms
 */
function dns_taxonomy_term_string( $name, $separator = ', ', $echo = false ) {
	global $post;
	$output = '';
	$terms = wp_get_post_terms( $post->ID, $name );
	
	// If there are terms for this taxonomy associated with the post string them together
	if ( !empty( $terms ) ) {
		$term_names = array();
		foreach ( $terms as $term ) {
			$term_names[] = $term->name;
		}
		$output = esc_html( implode( $separator, $term_names ) );
	}
	
	// Print if echo is set
	if ( $echo ) {
		printf( $output );
	}
	return $output;
}

/**
 * Using supplied WP_Query arguments, creates a two-dimensional sort array for a set of queried posts
 * Results are grouped by the taxonomy of $t1_slug, then $t2_slug, only uses first return taxonomy match for a program.
 * Each post is represented in a third array level using $key => $value of Post ID => $sort_callback return
 * If $sort_callback has no return, the post is not added to the array. Loop only.
 * 
 * Arguments are:
 * 	query_args = (Required) Argument array passed to WP_Query
 *  t1_slug = (Optional) First taxonomy group, default 'brochure-editions'
 *  t2_slug = (Optional) Second taxonomy group, default 'age-ranges'
 *  sort_callback = (Required) Callback function which generates a sort value for a given post
 * 
 * @param array $sort_args Properties to make the sort array
 * @return null|array 2D sorted array of post IDs grouped and sorted by supplied taxonomies
 */
function dns_double_taxonomy_sort_array( $sort_args ) {
	global $post;

	$defaults = array(
			'query_args'	=> array(),
			't1_slug'		=> 'brochure-editions',
			't2_slug'		=> 'age-ranges',
			'sort_callback'	=> '',
	);
	$args = wp_parse_args( $sort_args, $defaults );
	extract( $args, EXTR_SKIP );
	
	if ( empty( $sort_callback ) ) {
		return null;
	}
	
	$sort_query = new WP_Query( $query_args );
	$sort_array = array();
	$t1 = get_terms( $t1_slug );
	$t2 = get_terms( $t2_slug );
	
	foreach ( $t1 as $term1 ) {
		foreach( $t2 as $term2 ) {
			$sort_array[ $term1->name ][ $term2->name ] = array();
		}
	}
	
	// Run the loop 
	if( $sort_query->have_posts() ) {
		while( $sort_query->have_posts() ) {
			$sort_query->the_post();
			
			$t1_list = wp_get_post_terms( $post->ID, $t1_slug );
			$t2_list = wp_get_post_terms( $post->ID, $t2_slug );
			$t1_term = $t1_list[0]->name;
			$t2_term = $t2_list[0]->name;
			$sort_value = call_user_func( $sort_callback );
			
			// If any of the fields are missing skip adding the program
			if ( empty( $t1_term ) || empty( $t2_term ) || empty( $sort_value ) ) {
				continue;
			}
			$sort_array[ $t1_term ][ $t2_term ][$post->ID] = $sort_value;
		}
	}
	$output = array_filter( $sort_array, 'array_filter' );
	return $output;
}

/**
 * Display a collapsable listing of all programs grouped within taxonomy $t1_slug, 
 * then taxonomy $t2_slug, using an ordered array generated by dns_double_taxonomy_sort_array. Loop only.
 * 
 * Arguments:
 *  sort_array = (Required) 2D array of post IDs generated by _taxonomy_sort_array function
 *  t1_collapse = (Optional) First taxonomy group collapse state, false starts open
 *  t2_collapse = (Optional) Second taxonomy group collapse state, false starts open
 *  t1_slug = (Optional) First taxonomy group, default 'brochure-editions'
 *  t2_slug = (Optional) Second taxonomy group, default 'age-ranges'
 * 
 * @param array $sort_args Render properties array
 * @return boolean If no posts are provided, false is returned
 */
function dns_double_taxonomy_sort_render( $sort_args ) {
	global $post; 
	
	$defaults = array(
		'sort_array'	=> array(),
		't1_slug'		=> 'brochure-editions',
		't2_slug'		=> 'age-ranges',
		't1_collapse'	=> true,
		't2_collapse'	=> true	
	);
	$args = wp_parse_args( $sort_args, $defaults );
	extract( $args, EXTR_SKIP );
	
	if ( empty( $sort_array ) ) {
		return false;
	}
	
	$t1_name = get_taxonomy( $t1_slug );
	$t2_name = get_taxonomy( $t2_slug );
	
	// Loop through the sort array, creating a section header for each top level taxonomy item
	foreach( $sort_array as $term1 => $t2_array ) {
		?>
		<section>
			<header class="section-info">
				<div class="<?php echo esc_attr( $t1_slug )?>">
					<a data-toggle="collapse" href="#<?php echo sanitize_key( $term1 ); ?>-container">
						<h2><?php echo $t1_name->labels->singular_name; ?></h2>
						<?php printf( '<p>%s</p>', $term1 ); ?>
					</a>
				</div>
			</header>
			<div id="<?php echo sanitize_key( $term1 ); ?>-container" <?php if ( $t1_collapse ) { echo 'class="collapse"'; } ?>>
			<?php 
			// Loop through all second level taxonomy items, creating a header for each
			foreach( $t2_array as $term2 => $posts ) {
				// Skip an item with no posts
				if ( count( $posts ) == 0 ) { 
					continue; 
				}
				?>	
				<div>
					<div class="section-info">
						<div class="<?php echo esc_attr( $t2_slug ); ?>">
							<a data-toggle="collapse" href="#<?php echo sanitize_key( $term1 ) . '-' . sanitize_key( $term2 ); ?>-container">
								<h3><?php echo $t2_name->labels->singular_name; ?></h3>
								<?php printf( '<p>%s</p>', $term2 ); ?>
							</a>
						</div>
					</div>
					<div id="<?php echo sanitize_key( $term1 ) . '-' . sanitize_key( $term2 ); ?>-container" <?php if ( $t2_collapse ) { echo 'class="collapse"'; } ?>>
						<?php 
						// Sort the programs by timestamp
						asort( $posts );
						
						// Assign each program them to the post globabl and render with the program template
						foreach( $posts as $pid => $sort_value ) {
							$post = get_post( $pid );
							setup_postdata( $post );
							get_template_part( 'content', 'single' );
						}
						?>
					</div>
				</div>
				<?php 
			}
			?>
			</div>
		</section>
		<?php
	}
	return true;
}

/** 
 * Retrieve the start timestamp for a program. Loop only.
 * 
 * @return int Start Timestamp
 */
function dns_startdate_timestamp() {
	global $post;
	$start_timestamp = sprintf( '%s %s', get_post_meta( $post->ID, 'dns_start_date', true ),
			get_post_meta( $post->ID, 'dns_start_time', true ) );
	return strtotime( $start_timestamp );
}

/**
 * Basic theme settings and deafult WP function resets
 */
function dns_theme_defaults() {
	// Hide the administration bar when logged in
	add_filter( 'show_admin_bar', '__return_false' );
	remove_action( 'wp_head', '_admin_bar_bump_cb' );
	
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 80, 80 );
}
add_filter( 'init', 'dns_theme_defaults' );

/**
 * Displays navigation to next/previous set of programs when applicable.
 *
 * @since DNS Programs 1.0
 *
 * @return void
 */

function dns_paging_nav() {
	global $wp_query;

	// Disable pagination with only one page.
	if ( $wp_query->max_num_pages < 2 )
		return;
	?>
	<nav class="navbar navbar-inverse navbar-fixed-bottom" role="navigation">
		<div class="container">
			<?php if ( get_next_posts_link() ) : ?>
				<span class="nav-previous">
					<?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older programs', 'dnsprograms' ) ); ?>
				</span>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
				<span class="nav-next">
					<?php previous_posts_link( __( 'Newer programs <span class="meta-nav">&rarr;</span>', 'dnsprograms' ) ); ?>
				</span>
			<?php endif; ?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}

/**
 * Register the theme CSS and JS files
 */
function dns_theme_scripts() {
	wp_enqueue_style( 'dns-styles', get_template_directory_uri() . '/css/dnsprograms.css' );
	wp_enqueue_script( 'dns-bootstrap', get_template_directory_uri() . '/js/bootstrap.js', array( 'jquery' ), '3.0', true );
	wp_enqueue_script( 'dns-scripts', get_template_directory_uri() . '/js/dnsprograms.js', array( 'jquery', 'dns-bootstrap' ), 
		'1.0', true );
}
add_filter( 'wp_enqueue_scripts', 'dns_theme_scripts' );

function dns_program_schedule() { 
	global $post;
	
	$settings = array(
		'dns_start_date'			=> '',
		'dns_end_date'				=> '',
		'dns_start_time'			=> '',
		'dns_end_time'				=> '',
		'dns_frequency'				=> 0,
		'dns_days_week'				=> array(),
		'dns_day_month'				=> 0
	);
	foreach( $settings as $key => $value ) {
		$post_meta = get_post_meta( $post->ID, $key, true );
		if ( !empty( $post_meta ) ) {
			$settings[ $key ] = $post_meta;
		}
	}
	
	if ( empty( $settings[ 'dns_start_date' ] ) || $setting[ 'dns_end_date' ] ) {
		printf( '<p>Date/Times to be determined</p>' );
		return;
	}
	
	$time_start = strtotime( $settings[ 'dns_start_date' ] );
	$time_end = strtotime( $settings[ 'dns_end_date' ] );

	switch( $settings[ 'dns_frequency' ] ) {
		case 0:
			$dow_start = date( 'D n/j', $time_start );
			$dow_end = date( 'D n/j', $time_end );
			printf( '<p>%s', $dow_start );
			if ( $time_start != $time_end ) {
				printf( ' - %s', $dow_end );
			}
			printf( '</p>');
			break;
		case 1:
			printf( '<p>Weekly</p>' );
			$dow_start = date( 'D n/j', $time_start );
			$dow_end = date( 'D n/j', $time_end );
			
			printf( '<p>from %s &ndash; %s</p>', $dow_start, $dow_end );
			break;
		case 2:
			printf( '<p>Monthly</p>' );
			$dow_start = date( 'M', $time_start );
			$dow_end = date( 'M', $time_end );
			printf( '<p>%s - %s', $dow_start, $dow_end );
			
			if ( $settings[ 'dns_day_month'] > 0 && $settings[ 'dns_day_month'] < 32 ) {
				$date_test = mktime( 0, 0 ,0, date( 'm' ), $settings[ 'dns_day_month' ], date( 'Y' ) );	
				$day_month = date( 'jS', $date_test );
				printf( ' on the %s</p>', $day_month );
			}
			else {
				printf( ', day to be determined</p>' );
			}
			break;
		default:
			printf( '<p>No schedule</p>' );
			break;
	}
	if ( !empty( $settings[ 'dns_start_time' ] ) && !empty( $settings[ 'dns_end_time' ] ) ) {
		$start_time = date( 'g:i A', strtotime( $settings[ 'dns_start_time' ] ) );
		$end_time = date( 'g:i A', strtotime( $settings[ 'dns_end_time' ] ) ) ;
		printf( '<p class="program-time">%s &ndash; %s</p>', $start_time, $end_time );
	}
	else {
		printf( '<p class="program-time">Times to be announced</p>' );
	}
}
