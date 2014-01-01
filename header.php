<!DOCTYPE html>
<html>
<head>
	<title><?php bloginfo('name'); ?><?php wp_title('|',true,''); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
</head>
<body>
	<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
		<div class="container">
    		<div class="navbar-header">
      			<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
	        		<span class="sr-only">Toggle navigation</span>
	        		<span class="icon-bar"></span>
	        		<span class="icon-bar"></span>
	        		<span class="icon-bar"></span>
      			</button>
      			<a href="<?php echo get_bloginfo( 'url' ); ?>" class="navbar-brand">DNS Programs</a>
    		</div>
		    <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
		    	<ul class="nav navbar-nav">
			    	<li class="dropdown">
						<a data-toggle="dropdown" href="#">Authors</a>
					  	<ul class="dropdown-menu" role="menu" aria-labelledby="Author List">
					    	<?php wp_list_authors( array( 'hide_empty' => true, 'show_fullname' => true ) ); ?>
					  	</ul>
					</li>
					<li class="dropdown">
						<a data-toggle="dropdown" href="#">Categories</a>
					  	<ul class="dropdown-menu" role="menu" aria-labelledby="Categories">
					    	<?php wp_list_categories( array( 
										'hide_empty' 	=> 0,
										'title_li'		=> '' 
					    			) ); ?>
					  	</ul>
					</li>
					<li class="dropdown">
						<a data-toggle="dropdown" href="#">Editions</a>
					  	<ul class="dropdown-menu" role="menu" aria-labelledby="Brochure Editions">
					    	<?php dns_list_taxonomy_terms( 'brochure-editions' ); ?>
					  	</ul>
					</li>
				</ul>
		      	<ul class="nav navbar-nav navbar-right">
		        	<li>
		          		<a href="<?php echo get_bloginfo( 'url' ); ?>/wp-admin/edit.php">Program Administrator</a>
		        	</li>
		      	</ul>
				<?php get_search_form(); ?>
			</nav>
  		</div>
	</header>