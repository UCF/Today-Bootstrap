<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?="\n".header_()."\n"?>
		<?php if(GA_ACCOUNT or CB_UID):?>
		
		<script type="text/javascript">
			var _sf_startpt = (new Date()).getTime();
			<?php if(GA_ACCOUNT):?>
			
			var GA_ACCOUNT  = '<?=GA_ACCOUNT?>';
			var _gaq        = _gaq || [];
			_gaq.push(['_setAccount', GA_ACCOUNT]);
			_gaq.push(['_setDomainName', 'none']);
			_gaq.push(['_setAllowLinker', true]);
			_gaq.push(['_trackPageview']);
			<?php endif;?>
			<?php if(CB_UID):?>
			
			var CB_UID      = '<?=CB_UID?>';
			var CB_DOMAIN   = '<?=CB_DOMAIN?>';
			<?php endif?>
			
		</script>
		<?php endif;?>
		
		<?  $post_type = get_post_type($post->ID);
			if(($stylesheet_id = get_post_meta($post->ID, $post_type.'_stylesheet', True)) !== False
				&& ($stylesheet_url = wp_get_attachment_url($stylesheet_id)) !== False) { ?>
				<link rel='stylesheet' href="<?=$stylesheet_url?>" type='text/css' media='all' />
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
	<body class="<?=body_classes()?>">
		<div class="container">
			<div class="row" id="header">
				<div id="page-title" class="span6">
					<h1><a href="<?=bloginfo('url')?>"><?=bloginfo('name')?></a></h1>
				</div>
				<div id="weather_bug" class="span6">
					<?php $weather = get_weather_data(); ?>
					<div id="wb_date">
						<?=date('l, F j, Y')?>
					</div>
					<a id="wb_more" href="<?=WEATHER_CLICK_URL?>">more weather</a>
					<div id="wb_status_img">
						<img src="<?php bloginfo('stylesheet_directory'); ?>/static/img/weather/WC<?=$weather['img']?>.png" alt="<?=$weather['condition']?>" />
					</div>
					<div id="wb_status_txt">
						<?=$weather['condition']?>, <span><?=$weather['temp']?></span>
					</div>
				</div>
				<hr class="span12" />
			</div>
			<div id="header-menu">
				<?=wp_nav_menu(array(
					'menu' => 'Top Navigation', 
					'container' => 'false', 
					'menu_class' => 'menu '.get_header_styles(),
					'walker' => new Bootstrap_Walker_Nav_Menu()
					));
				?>
				<?=get_search_form()?>
			</div>