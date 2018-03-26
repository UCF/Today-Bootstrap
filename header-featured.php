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

	</head>
	<body class="<?php echo today_body_classes()?>">
