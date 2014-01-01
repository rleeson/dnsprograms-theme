<?php
/** 
 * Main program table row creation
 * 
 * @package DNS Programs
 * @author Ryan Leeson
 */
?>
<main class="bs-masthead" id="content" role="main">
	<div class="container">
   		<h2>Program Listing</h2>
   		<?php 
   			if ( is_author() ) {
				printf( '<p><strong>Author:</strong> %s</p>', get_the_author() );
			}
   		?>
		<table id="program-table" class="table table-striped">
			<thead>
				<tr>
					<th>Title</th>
					<th>Description</th>
					<th>Image</th>
					<th>Ages</th>
					<th>Teacher(s)</th>
					<!--th>Schedule</th -->
				</tr>
			</thead>
			<tbody>
			<?php 
			if ( have_posts() ) {
				while ( have_posts() ) { 
					the_post();
			?>
				<tr>
					<td class="title">
						<?php 
							$link = get_permalink();
							$before = sprintf( '<a href="%s">', $link );
							the_title( $before, '</a>' ); 
							dns_print_post_status();
						?>
					</td>
					<td class="description">
						<?php 
							the_content(); 
							$series = wp_get_post_terms( $post->ID, 'series' );
							
							if ( !empty( $series ) ) {
								printf( '<p class="series-title">Series:</p><ul>' );
								foreach ( $series as $term ) {
									printf( '<li>%s</li>', $term->name );
								}
								printf( '</ul>' );
							}
						?>
					</td>
					<?php 
						dns_program_image_column();		
						dns_taxonomy_list( 'age-ranges', true );
						dns_taxonomy_list( 'teachers', true );
						//dns_program_schedule_column();
					?>
				</tr>
			<?php 
				}
			}
			else {
				get_template_part( 'content', 'none' );
			} 
		?>
			</tbody>
		</table>
		<?php 
			if ( have_posts() ) {
				dns_paging_nav();
			}
		?>	
	</div><!-- .container -->
</main>
