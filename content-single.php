<?php
/**
 * Content view for a single post
 *
 * @package DNS Programs
 * @author Ryan Leeson
 */

global $post;
?>
<article class="single">
	<?php if ( is_single() ) { ?>
		<aside class="section-info">
			<div class="program-locations">
				<h2>Location</h2>
				<?php dns_taxonomy_list( 'program-locations' ); ?>
			</div>
			<div class="age-ranges">
				<h2>Age Range</h2>
				<?php dns_taxonomy_list( 'age-ranges' ); ?>
			</div>
			<div class="brochure-editions">
				<h2>Brochures</h2>
				<?php dns_taxonomy_list( 'brochure-editions' ); ?>
			</div>
			<?php 
				// Series (Optional)
				$series = wp_get_post_terms( $post->ID, 'series' );
				if ( !empty( $series ) ) {
				?>
					<div class="series">
						<h2>Series</h2>
						<?php dns_taxonomy_list( 'series' ); ?>
					</div>
				<?php
				}
			?>
		</aside>
	<?php } ?>
	<header class="title">
	<?php	
		// Name
		printf( '<a href="%s/wp-admin/post.php?post=%s&action=edit">', get_bloginfo( 'url' ), esc_attr( $post->ID ) );
		the_title( '<h1>', '</h1>' );
   		printf( '</a>' );
   		
		$teachers = dns_taxonomy_term_string( 'teachers' );
		printf ( '<h2>with %s</h2>', $teachers );

	?>
	</header>
	<div class="details">
		<aside class="schedule">
			<?php
			dns_program_schedule();
			
			// Prices array, key is the enable meta for each price, with subarray of key to price meta and field label
			$prices = array( 
					'dns_mpc_enable' 	=> array( 'key' => 'dns_mem_price_child', 'label' => 'Member Child' ),
					'dns_pc_enable' 	=> array( 'key' => 'dns_price_child', 'label' => 'Non-Member Child' ),
					'dns_mpa_enable' 	=> array( 'key' => 'dns_mem_price_adult', 'label' => 'Member' ),
					'dns_pa_enable'		=> array( 'key' => 'dns_price_adult', 'label' => 'Non-Member' )
			);
			
			// For each price, if it is enable for the program, retrieve and output the label and price
			foreach ( $prices as $key => $label ) {
				$enabled = get_post_meta( $post->ID, $key, single );
				
				if ( !empty( $enabled ) && $enabled == 'on' ) {
					$format = '<p class="price-field"><span class="price-label">%s</span><span class="price">%s</span></p>';
					printf( $format, $label[ 'label' ], dns_program_price( $label[ 'key' ] ) );
				}
			}
			
			// Program Number
			$program_number = get_post_meta( $post->ID, 'dns_program_number', true );
			printf( '<p class="program-number">#%s</p>', $program_number );
			?>
		</aside>
		<div class="description">
			<?php 
				printf( '<p>%s</p>', $post->post_content ); 
			?>
			<p class="categories">
				<strong>Categories: </strong>
				<?php the_category( ', ' ); ?>
			</p>
			<p class="locations">
				<strong>Locations: </strong>
				<?php echo dns_taxonomy_term_string( 'program-locations' ); ?>
			</p>
			<?php 
				$series = wp_get_post_terms( $post->ID, 'series' );
				if ( !is_single() && !empty( $series ) ) {
					printf( '<p><strong>Series:</strong> %s</p>', $series[0]->name );
				}
				dns_program_image_thumbnail( true ); 
			?>
		</div>
	</div>
</article>