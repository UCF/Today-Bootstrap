<!DOCTYPE html>
<html lang="en-US">
	<head>
		<?="\n".header_()."\n"?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!--[if IE]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<?php if ( GA_ACCOUNT ): ?>
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
		<?php endif; ?>

		<?php if ( CB_UID ): ?>
		<script>
		  var CB_UID    = '<?php echo CB_UID; ?>';
		  var CB_DOMAIN = '<?php echo CB_DOMAIN; ?>';
		</script>
		<?php endif; ?>

		<?  $post_type = get_post_type($post->ID);

			if(($stylesheet_id = get_post_meta($post->ID, $post_type.'_stylesheet', True)) !== False
				&& ($stylesheet_url = wp_get_attachment_url($stylesheet_id)) !== False) { ?>
				<link rel='stylesheet' href="<?php echo $stylesheet_url ?>" type='text/css' media='all' />
		<? } ?>

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

		<?php if ( is_single() ):
			echo display_news_schema( $post );
		endif; ?>

	</head>
	<body class="<?php echo today_body_classes()?>">
		<div class="container">
			<div class="row" id="header" role="banner">
				<div id="page-title" class="span7">
					<? if ( is_home() || !is_single() ): ?>
						<h1><?php echo get_header_title() ?></h1>
					<? else: ?>
						<h2><?php echo get_header_title() ?></h2>
					<? endif; ?>
				</div>
				<?php echo esi_include( 'output_weather_data' )?>
				<hr class="span12" />
			</div>
			<nav id="header-menu" role="navigation">
				<div class="ucf-mobile-menu-trigger pull-left">menu</div>
				<?php echo wp_nav_menu( array(
					'menu' => 'Top Navigation',
					'container' => 'false',
					'menu_class' => 'menu '.get_header_styles(),
					'menu_id' => 'navigation',
					'walker' => new Bootstrap_Walker_Nav_Menu()
					) );
				?>
				<?php echo get_search_form()?>
			</nav>
			<?php echo gen_alerts_html()?>
