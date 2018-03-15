<!DOCTYPE html>
<html lang="en-US">
	<head>
		<?php echo header_(); ?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<?php
		// START google analytics
		if ( GA_ACCOUNT ):
		?>
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', '<?php echo GA_ACCOUNT; ?>', 'auto');

		<?php
		/**
		 * Custom dimensions; must be set before sending pageview hit.
		 * dimension1 = post author
		 **/
		global $post;
		if ( is_single() ):
			$author = get_userdata( $post->post_author );
		?>
		  ga('set', 'dimension1', '<?php echo $author->user_login; ?>');
		<?php endif; ?>

		  ga('send', 'pageview');
		</script>
		<?php
		endif;
		// END google analytics
		?>

		<?php
		// START chartbeat
		if ( CB_UID ):
		?>
		<script>
		  var CB_UID    = '<?php echo CB_UID; ?>';
		  var CB_DOMAIN = '<?php echo CB_DOMAIN; ?>';
		</script>
		<?php
		endif;
		// END chartbeat
		?>

		<?php
		// START custom page stylesheet
		$post_type = get_post_type( $post->ID );

		if (
			( $stylesheet_id = get_post_meta( $post->ID, $post_type.'_stylesheet', True ) ) !== False
			&& ( $stylesheet_url = wp_get_attachment_url( $stylesheet_id ) ) !== False
		):
		?>
			<link rel="stylesheet" href="<?php echo $stylesheet_url; ?>" type="text/css" media="all" />
		<?php
		endif;
		// END custom page stylesheet
		?>

		<script type="text/javascript">
			var PostTypeSearchDataManager = {
				'searches' : [],
				'register' : function(search) {
					this.searches.push(search);
				}
			}
			var PostTypeSearchData = function(column_count, column_width, data) {
				this.column_count = column_count;
				this.column_width = column_width;
				this.data         = data;
			}
		</script>

	</head>
	<body class="<?php echo today_body_classes(); ?>">
		<div class="site-nav-overlay fade" id="nav-overlay"></div>
		<header class="site-header">
			<div class="container site-header-inner">
				<div class="site-header-info">
					<?php echo get_header_title(); ?>
					<div class="site-header-desc"><?php echo bloginfo( 'description' ); ?></div>
				</div>
				<div class="site-header-actions">
					<?php if ( disable_md_nav_toggle() ): ?>
						<?php echo esi_include( 'output_weather_data' ); ?>
					<?php endif; ?>
					<button class="ucf-mobile-menu-trigger" role="button">Sections</button>
				</div>
			</div>
			<nav class="site-nav" id="header-menu" role="navigation">
				<div class="container">
					<button class="close-icon">Close Menu</button>
					<div class="hidden-desktop">
						<?php echo get_header_title( 'span' ); ?>
						<div class="site-header-desc"><?php echo bloginfo( 'description' ); ?></div>
					</div>
					<?php
					echo wp_nav_menu( array(
						'menu' => 'Top Navigation',
						'container' => 'false',
						'menu_class' => 'site-menu',
						'menu_id' => 'header-navigation',
						'walker' => new Bootstrap_Walker_Nav_Menu()
					) );
					?>
					<div class="hidden-desktop">
						<?php echo esi_include( 'output_weather_data' ); ?>
						<?php echo get_search_form(); ?>
					</div>
				</div>
			</nav>
		</header>

		<?php echo gen_alerts_html(); ?>

		<div class="container">
