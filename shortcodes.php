<?php
/** GENERIC THEME SHORTCODES **/

/**
 * Post search
 *
 * @return string
 * @author Chris Conover
 **/
function sc_post_type_search($params=array(), $content='') {
	$defaults = array(
		'post_type_name'         => 'post',
		'taxonomy'               => 'category',
		'show_empty_sections'    => false,
		'non_alpha_section_name' => 'Other',
		'column_width'           => 'span4',
		'column_count'           => '3',
		'order_by'               => 'title',
		'order'                  => 'ASC',
		'show_sorting'           => True,
		'default_sorting'        => 'term',
		'show_sorting'           => True
	);

	$params = ($params === '') ? $defaults : array_merge($defaults, $params);

	$params['show_empty_sections'] = (bool)$params['show_empty_sections'];
	$params['column_count']        = is_numeric($params['column_count']) ? (int)$params['column_count'] : $defaults['column_count'];
	$params['show_sorting']        = (bool)$params['show_sorting'];

	if(!in_array($params['default_sorting'], array('term', 'alpha'))) {
		$params['default_sorting'] = $default['default_sorting'];
	}

	// Resolve the post type class
	if(is_null($post_type_class = get_custom_post_type($params['post_type_name']))) {
		return '<p>Invalid post type.</p>';
	}
	$post_type = new $post_type_class;

	// Set default search text if the user didn't
	if(!isset($params['default_search_text'])) {
		$params['default_search_text'] = 'Find a '.$post_type->singular_name;
	}

	// Register if the search data with the JS PostTypeSearchDataManager
	// Format is array(post->ID=>terms) where terms include the post title
	// as well as all associated tag names
	$search_data = array();
	foreach(get_posts(array('numberposts' => -1, 'post_type' => $params['post_type_name'])) as $post) {
		$search_data[$post->ID] = array($post->post_title);
		foreach(wp_get_object_terms($post->ID, 'post_tag') as $term) {
			$search_data[$post->ID][] = $term->name;
		}
	}
	?>
	<script type="text/javascript">
		if(typeof PostTypeSearchDataManager != 'undefined') {
			PostTypeSearchDataManager.register(new PostTypeSearchData(
				<?=json_encode($params['column_count'])?>,
				<?=json_encode($params['column_width'])?>,
				<?=json_encode($search_data)?>
			));
		}
	</script>
	<?

	// Split up this post type's posts by term
	$by_term = array();
	foreach(get_terms($params['taxonomy']) as $term) {
		$posts = get_posts(array(
			'numberposts' => -1,
			'post_type'   => $params['post_type_name'],
			'tax_query'   => array(
				array(
					'taxonomy' => $params['taxonomy'],
					'field'    => 'id',
					'terms'    => $term->term_id
				)
			),
			'orderby'     => $params['order_by'],
			'order'       => $params['order']
		));

		if(count($posts) == 0 && $params['show_empty_sections']) {
			$by_term[$term->name] = array();
		} else {
			$by_term[$term->name] = $posts;
		}
	}

	// Split up this post type's posts by the first alpha character
	$by_alpha = array();
	$by_alpha_posts = get_posts(array(
		'numberposts' => -1,
		'post_type'   => $params['post_type_name'],
		'orderby'     => 'title',
		'order'       => 'alpha'
	));
	foreach($by_alpha_posts as $post) {
		if(preg_match('/([a-zA-Z])/', $post->post_title, $matches) == 1) {
			$by_alpha[strtoupper($matches[1])][] = $post;
		} else {
			$by_alpha[$params['non_alpha_section_name']][] = $post;
		}
	}
	ksort($by_alpha);

	if($params['show_empty_sections']) {
		foreach(range('a', 'z') as $letter) {
			if(!isset($by_alpha[strtoupper($letter)])) {
				$by_alpha[strtoupper($letter)] = array();
			}
		}
	}

	$sections = array(
		'post-type-search-term'  => $by_term,
		'post-type-search-alpha' => $by_alpha,
	);

	ob_start();
	?>
	<div class="post-type-search">
		<div class="post-type-search-header">
			<form class="post-type-search-form" action="." method="get">
				<label style="display:none;">Search</label>
				<input type="text" class="span3" placeholder="<?=$params['default_search_text']?>" />
			</form>
		</div>
		<div class="post-type-search-results "></div>
		<? if($params['show_sorting']) { ?>
		<div class="btn-group post-type-search-sorting">
			<button class="btn<?if($params['default_sorting'] == 'term') echo ' active';?>"><i class="icon-list-alt"></i></button>
			<button class="btn<?if($params['default_sorting'] == 'alpha') echo ' active';?>"><i class="icon-font"></i></button>
		</div>
		<? } ?>
	<?

	foreach($sections as $id => $section) {
		$hide = false;
		switch($id) {
			case 'post-type-search-alpha':
				if($params['default_sorting'] == 'term') {
					$hide = True;
				}
				break;
			case 'post-type-search-term':
				if($params['default_sorting'] == 'alpha') {
					$hide = True;
				}
				break;
		}
		?>
		<div class="<?=$id?>"<? if($hide) echo ' style="display:none;"'; ?>>
			<? foreach($section as $section_title => $section_posts) { ?>
				<? if(count($section_posts) > 0 || $params['show_empty_sections']) { ?>
					<div>
						<h3><?=esc_html($section_title)?></h3>
						<div class="row">
							<? if(count($section_posts) > 0) { ?>
								<? $posts_per_column = ceil(count($section_posts) / $params['column_count']); ?>
								<? foreach(range(0, $params['column_count'] - 1) as $column_index) { ?>
									<? $start = $column_index * $posts_per_column; ?>
									<? $end   = $start + $posts_per_column; ?>
									<? if(count($section_posts) > $start) { ?>
									<div class="<?=$params['column_width']?>">
										<ul>
										<? foreach(array_slice($section_posts, $start, $end) as $post) { ?>
											<li data-post-id="<?=$post->ID?>"><?=$post_type->toHTML($post)?></li>
										<? } ?>
										</ul>
									</div>
									<? } ?>
								<? } ?>
							<? } ?>
						</div>
					</div>
				<? } ?>
			<? } ?>
		</div>
		<?
	}
	?> </div> <?
	return ob_get_clean();
}
add_shortcode('post-type-search', 'sc_post_type_search');


function sc_search_form() {
	ob_start();
	get_search_form();
	return ob_get_clean();
}
add_shortcode('search_form', 'sc_search_form');



/** TODAY SHORTCODES **/

/**
 * Generate home page feature story html
 *
 * @return string
 * @author Chris Conover
 **/
function sc_feature($atts = Array(), $id_only = False)
{
	global $wp_embed;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	if(is_front_page()) {

		$feature = resolve_posts(	Array(),
			Array(
				'numberposts' => 1,
				'meta_query' => Array(
					Array(
						'key' => 'display_type',
						'value' => 'featured'))));

		if($feature !== False) {
			$feature_media_attachment = get_img_html($feature->ID, 'feature', array('return_id' => true));
			$attachment_url = wp_get_attachment_image_src($feature_media_attachment['attachment_id'], 'feature');
			if (!$attachment_url) {
				$attachment_url = array(0 => THEME_IMG_URL.'/no-photo.png');
			}
			$feature_media = '<a href="'.get_permalink($feature->ID).'">'.get_img_html($feature->ID, 'feature').'</a>';

			ob_start();
			?>
			<div class="<?php echo $css?>" id="feature">
				<h2 class="indent">Featured Article</h2>
				<div class="thumb cropped" style="background-image: url('<?php echo $attachment_url[0]?>');">
					<?php echo $feature_media; ?>
				</div>
				<h2 class="feature-title"><a href="<?php echo get_permalink($feature->ID)?>"><?php echo $feature->post_title?></a></h2>
			</div>
			<?
			return ob_get_clean();
		}
	} else if(is_category() || is_tag() || is_page()) {

		global $wp_query;

		if(is_category()) {
			$resolve_atts = Array('category' => $wp_query->queried_object->slug);
		} else if(is_tag()) {
			$resolve_atts = Array('tag' => $wp_query->queried_object->slug);
		} else {
			$resolve_atts = Array();
		}

		$top_feature = resolve_posts(	$resolve_atts,
										Array('numberposts' => 1));
		if($id_only) return $top_feature->ID;

		$feature_media_attachment = get_img_html($top_feature->ID, 'subpage_feature', array('return_id' => true));
		$attachment_url = wp_get_attachment_image_src($feature_media_attachment['attachment_id'], 'subpage_feature');
		if (!$attachment_url) {
			$attachment_url = array(0 => THEME_IMG_URL.'/no-photo.png');
		}
		$feature_media = '<a href="'.get_permalink($feature->ID).'">'.get_img_html($feature->ID, 'feature').'</a>';

		ob_start();

		?>
		<div class="<?php echo $css ?>" id="feature">
			<div class="row">
				<div class="span5" style="background-image: url('<?php echo $attachment_url[0]?>');">
					<?php echo $feature_media; ?>
				</div>
				<div class="span4">
					<h2 class="feature-cat-title"><a href="<?php echo get_permalink( $top_feature->ID ) ?>"><?php echo $top_feature->post_title ?></a></h2>
					<p class="story-blurb">
						<?php echo get_excerpt( $top_feature )?>
					</p>
					<?php echo display_social( get_permalink( $top_feature->ID ), $top_feature->post_title )?>
				</div>
			</div>
		</div>
		<?
		return ob_get_clean();
	}
}
add_shortcode('feature', 'sc_feature');


/**
 * Generate more headlines html
 *
 * @return string
 * @author Chris Conover
 **/
function sc_ucf_news( $atts = Array() )
{
	$css = ( isset($atts['css']) ) ? $atts['css'] : '';

	$social		= ( isset( $atts['social'] ) ) ? $atts['social'] : True;
	$header		= ( isset( $atts['header'] ) ) ? $atts['header'] : True;
	$num_posts	= ( isset( $atts['num_posts'] ) && is_numeric( $atts['num_posts'] ) ) ? (int)$atts['num_posts'] : 3;

	$resolve_params = Array();

	if( is_front_page() ) {
		/*
			It's currently not possible to select posts that aren't
			marked as promotional or featured using meta_query. I think this
			is because display_option is a part of the post meta and isn't
			actually set on all posts, just those specified explicitly. That
			and there is non way to select empty values without dropping into
			SQL.

			Because of that, create an excluded_posts list containing all the
			post_ids of of the featured and promotional posts. This list should
			be a relatively small which will keep the processing time down.
		*/
		$excluded_posts = Array();
		$promos = get_posts(Array(
			'numberposts' => 3,
			'meta_query' => Array(
				Array(
					'key' => 'display_type',
					'value' => 'promotional',
					'compare' => '='))));
		$features = get_posts(Array(
			'numberposts' => 1,
			'meta_query' => Array(
				Array(
					'key' => 'display_type',
					'value' => 'featured',
					'compare' => '='))));
		foreach( array_merge( $promos,$features ) as $_post ) {
			array_push( $excluded_posts, $_post->ID );
		}

		$resolve_params['exclude'] = $excluded_posts;
	} else if( is_category() ) {
		global $wp_query;
		$atts['category'] = $wp_query->queried_object->slug;
		$atts['category_title'] = $wp_query->queried_object->cat_name;
	} else if( is_tag() ) {
		global $wp_query;
		$atts['tag'] = $wp_query->queried_object->slug;
	} else if( is_page() ) {
		global $wp_query;
		$atts['tag'] = str_replace( ' ', '', strtolower( $wp_query->queried_object->post_title ) );
	}

	# Category and tag pages have a top story. Don't allow the top story
	# to also show up in the More Headlines sections below it.
	if(!isset($resolve_params['exclude']) && (is_category() || is_tag())) {
		$resolve_params['exclude'] = array_merge(Array(sc_feature(Array(), True)), sc_subpage_features(Array(), True));
	}

	$headlines = resolve_posts( $atts, array_merge( Array( 'numberposts' => $num_posts ), $resolve_params ) );

	ob_start();
	?>
		<div class="<?php echo $css ?>" id="ucf_news">
			<?php echo ( $header ) ? '<h2>UCF ' . $atts['category_title'] . ' News</h2>' : ''?>
			<ul class="story-list">
	<?
	$count = 0;
	foreach($headlines as $headline) {
		$thumb_html = get_img_html($headline->ID, 'story');
		?>
				<li class="clearfix<?php echo ( ( $count + 1 ) == count( $headlines ) ? ' last' : '' ) ?>">
					<a href="<?php echo get_permalink( $headline->ID ) ?>">
						<div class="story-media">
							<div class="thumb">
								<?php echo $thumb_html ?>
							</div>
						</div>
						<div class="content">
							<h3><?php echo $headline->post_title ?></h3>
							<p class="story-blurb">
								<?php echo get_excerpt( $headline ) ?>
							</p>
						</div>
					</a>
				</li>
		<?
		$count++;
	}
	?>
			</ul>
		</div>
	<?
	$html = ob_get_contents();
	ob_end_clean();
	return $html;

}
add_shortcode( 'ucf_news', 'sc_ucf_news' );


/**
 * Generate more headlines html
 *
 * @return string
 * @author RJ Bruneel
 **/
function sc_more_headlines( $atts = Array() )
{
	$css = ( isset( $atts['css'] ) ) ? $atts['css'] : '';

	$social		= ( isset( $atts['social']) ) ? $atts['social'] : True;
	$header		= ( isset( $atts['header']) ) ? $atts['header'] : True;
	$num_posts	= ( isset( $atts['num_posts'] ) && is_numeric($atts['num_posts'] ) ) ? (int)$atts['num_posts'] : 3;
	$offset	    = ( isset( $atts['offset'] ) && is_numeric($atts['offset'] ) ) ? (int)$atts['offset'] : 3;

	$resolve_params = Array();

	if( is_front_page() ) {
		/*
			It's currently not possible to select posts that aren't
			marked as promotional or featured using meta_query. I think this
			is because display_option is a part of the post meta and isn't
			actually set on all posts, just those specified explicitly. That
			and there is non way to select empty values without dropping into
			SQL.

			Because of that, create an excluded_posts list containing all the
			post_ids of of the featured and promotional posts. This list should
			be a relatively small which will keep the processing time down.
		*/
		$excluded_posts = Array();
		$promos = get_posts( Array(
			'numberposts' => 3,
			'meta_query' => Array(
				Array(
					'key' => 'display_type',
					'value' => 'promotional',
					'compare' => '=' ) ) ) );
		$features = get_posts(Array(
			'numberposts' => 1,
			'meta_query' => Array(
				Array(
					'key' => 'display_type',
					'value' => 'featured',
					'compare' => '='))));
		foreach( array_merge( $promos,$features ) as $_post ) {
			array_push( $excluded_posts, $_post->ID );
		}

		$resolve_params['exclude'] = $excluded_posts;
	} else if( is_category() ) {
		global $wp_query;
		$atts['category'] = $wp_query->queried_object->slug;
		$atts['category_title'] = $wp_query->queried_object->cat_name;
	} else if( is_tag() ) {
		global $wp_query;
		$atts['tag'] = $wp_query->queried_object->slug;
	} else if( is_page() ) {
		global $wp_query;
		$atts['tag'] = str_replace( ' ', '', strtolower( $wp_query->queried_object->post_title ) );
	}

	# Category and tag pages have a top story. Don't allow the top story
	# to also show up in the More Headlines sections below it.
	if( !isset( $resolve_params['exclude'] ) && ( is_category() || is_tag() ) ) {
		$resolve_params['exclude'] = array_merge (Array( sc_feature( Array(), True ) ), sc_subpage_features( Array(), True ) );
	}

	$headlines = resolve_posts( $atts, array_merge( Array( 'numberposts' => $num_posts, 'offset' => $offset ), $resolve_params ) );

	ob_start();
	?>
		<div class="<?php echo $css?>" id="more_headlines">
			<?php echo ( $header ) ? '<h2>More  ' . $atts['category_title'] . ' Headlines</h2>' : ''?>
			<ul class="story-list">
	<?
	$count = 0;
	foreach($headlines as $headline) {
		$thumb_html = get_img_html($headline->ID, 'story');
		?>
			<li class="clearfix<?php echo ( ( $count + 1 ) == count( $headlines ) ? ' last' : '' ) ?>">
				<strong><a href="<?php echo get_permalink( $headline->ID ) ?>"><?php echo $headline->post_title ?></a></strong>
			</li>
		<?
		$count++;
	}
	?>
			</ul>
		</div>
	<?
	$html = ob_get_contents();
	ob_end_clean();
	return $html;

}
add_shortcode( 'more_headlines', 'sc_more_headlines' );


/**
 * Generate UCF photo section
 *
 * @return string
 * @author Chris Conover
 **/
function sc_ucf_photo($atts = Array())
{
	$css = ( isset( $atts['css'] ) ) ? $atts['css'] : '';
	$front_page = isset( $atts['front_page'] ) ? True : False;

	$link_page_name = ( isset( $atts['link_page_name'] ) ) ? $atts['link_page_name'] : 'Photos';

	$photosets = resolve_posts( $atts, Array( 'post_type' => 'photoset', 'numberposts' => 4 ) );

	if(count($photosets) > 0) {
		$first = True;
		ob_start();
		?>

			<div class="<?php echo $css ?>" id="ucf_photo">
				<div class="clearfix">
					<h2 class="listing">
						<?php echo $link_page_name ?>
					</h2>
					<a href="<?php echo get_page_link( get_page_by_title( $link_page_name )->ID )?>" class="listing" title="View more photo sets" alt="View more photo sets">
						More &raquo;
					</a>
				</div>
		<?
		foreach( $photosets as $photoset ) {
			if($first) {
				$first = False;

				if($front_page) {

					$image_html = get_img_html( $photoset->ID, 'ucf_photo' );//wp_get_attachment_image_src($first_image->ID, 'ucf_photo');
				} else {
					$image_html = get_img_html( $photoset->ID, 'ucf_photo_subpage' );
					wp_get_attachment_image_src( $first_image->ID, 'ucf_photo_subpage') ;
				}

				?>
					<a href="<?php echo get_permalink( $photoset->ID ) ?>">
						<?php echo $image_html ?>
					</a>
					<h3 class="clear"><a href="<?php echo get_permalink( $photoset->ID ) ?>"><?php echo $photoset->post_title ?></a></h3>
					<p class="story-blurb"><?php echo get_excerpt( $photoset ) ?></p>
				<ul>
				<?

			}
		}

		?>
			</ul>
		</div><?
		return ob_get_clean();
	}
}
add_shortcode( 'ucf_photo', 'sc_ucf_photo' );


/**
 * Generate UCF video section
 *
 * @return string
 * @author Chris Conover
 **/
function sc_ucf_video($atts = Array())
{
	global $wp_query, $wp_embed;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$height	= (isset($atts['height'])) ? $atts['height'] : '';
	$width	= (isset($atts['width'])) ? $atts['width'] : 400;

	if(is_category()) $atts['category'] = $wp_query->queried_object->slug;
	if(is_tag()) $atts['tag'] = $wp_query->queried_object->slug;

	if(is_front_page()) {
		$video =  resolve_posts($atts, Array('post_type' => 'video', 'meta_key' => 'video_main_page', 'meta_value' => 'on'));
	} else {
		$video =  resolve_posts($atts, Array('post_type' => 'video'));
	}

	if($video !== False) {
		$video_url = get_video_url($video->ID);
		if($video_url != '') {
			$embed_string = '[embed width="'.$width.'" '.($height != '' ? 'height="'.$height.'"' : '').']'.$video_url.'[/embed]';
			ob_start();
			?>
			<div class="<?=$css?>" id="ucf_video">
				<h2 class="listing">Watch Video</h2><a href="<?=get_page_link(get_page_by_title('Videos')->ID)?>" class="listing">More &raquo;</a>
					<?php echo $wp_embed->run_shortcode($embed_string); ?>
				<h4><?=$video->post_title?></h4>
				<p><?=$video->post_content?></p>
			</div>
			<?
			return ob_get_clean();
		}
	}
}
add_shortcode('ucf_video', 'sc_ucf_video');


/**
 * Generate resource section
 *
 * @return string
 * @author Chris Conover
 **/
function sc_resources($atts = Array())
{
	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$args = Array('menu' => 'Resources', 'container' => '', 'menu_id'=> '');

	ob_start();
	?>
	<div class="<?=$css?>" id="resources">
		<h2>Resources</h2>
		<?=wp_nav_menu($args)?>
	</div>
	<?
	$html = ob_get_contents(); ob_end_clean();
	return $html;
}
add_shortcode('resources', 'sc_resources');


/**
 * Generate event section
 *
 * @return string
 * @author Chris Conover
 **/
function sc_events($atts = Array())
{
	$css 	= (isset($atts['css'])) ? $atts['css'] : '';
	$header = (isset($atts['header'])) ? $atts['header'] : 'h2';

	ob_start();
	print display_events($header, $css);
	return ob_get_clean();
}
add_shortcode('events', 'sc_events');


/**
 * Internal list items of promo ul
 *
 * @return string
 * @author Chris Conover
 **/
function sc_promos($atts = Array())
{
	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$promos = resolve_posts($atts,	Array(	'numberposts' => 3,
						 					'meta_query' => Array(
																Array(	'key' => 'display_type',
																		'value' => 'promotional'
																)
															)
									));
	if(count($promos) > 0) {
		ob_start();
		?>
			<div class="<?=$css?>" id="promos">
				<h2 class="indent">Promos</h2>
				<ul class="story-list">
		<?
		$count = 0;
		foreach($promos as $promo) {
			?>
			<li<?=(($count + 1) == count($promos) ? ' class="last"' : '')?>>
				<h3><a href="<?=get_permalink($promo->ID)?>"><?=$promo->post_title?></a></h3>
				<p class="story-blurb">
					<?=get_excerpt($promo)?>
				</p>
			</li>
			<?
			$count++;
		}
		?>
				</ul>
			</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('promos', 'sc_promos');


/**
 * Generate expert html
 *
 * @return string
 * @author Chris Conover
 **/
function sc_expert_short($atts = Array())
{
	$css = (isset($atts['css'])) ? $atts['css'] : '';
	$expert = resolve_posts($atts, Array('numberposts' => 1, 'post_type' => 'expert'));

	if($expert !== False) {
		ob_start();
		?>
		<div class="clearfix <?=$css?>" id="expert_short">
			<h3>Experts at UCF</h3>
			<a href="<?=get_permalink($expert->ID)?>">
			  <?=get_img_html($expert->ID, 'story')?>
			</a>
			<h4>
				<a href="<?=get_permalink($expert->ID)?>"><?=get_post_meta($expert->ID, 'expert_name', True)?></a>
			</h4>
			<p class="title"><?=get_post_meta($expert->ID, 'expert_title', True)?></p>
			<p class="story-blurb bio">
				<?=$expert->post_content?>
			</p>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('expert_short', 'sc_expert_short');


/**
 * Generate profile html
 *
 * @return string
 * @author Chris Conover
 **/
function sc_profile($atts = Array())
{
	$css = (isset($atts['css'])) ? $atts['css'] : '';
	$profile = resolve_posts($atts, Array('post_type' => 'profile'));

	if($profile !== False) {
		ob_start();
		?>
		<div class="<?=$css?>" id="profile">
			<a href="<?=get_permalink($profile->ID)?>">
				<h3>Get to Know: <span class="orange"><?=$profile->post_title?></span></h3>
				<?=get_img_html($profile->ID, 'profile_img')?>
				<p>
					<?=get_post_meta($profile->ID, 'profile_bio', True)?>
				</p>
			</a>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('profile', 'sc_profile');


/**
 * Subpage features
 *
 * @return string
 * @author Chris Conover
 **/
function sc_subpage_features($atts = Array(), $id_only = False)
{
	global $wp_query;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	if(is_category()) {
		$resolve_atts = Array('category' => $wp_query->queried_object->slug);
	} else if(is_tag()) {
		$resolve_atts = Array('tag' => $wp_query->queried_object->slug);
	}

	$features = resolve_posts(	$resolve_atts,
								Array( 'numberposts' => 3,
										'exclude' => Array( sc_feature( Array(), True ) ) ) );

	if( $id_only ) {
		return array_map(create_function('$p', 'return $p->ID;'), $features);
	}
	if(count( $features ) > 0) {
		ob_start();
		?>
		<div class="row <?php echo $css ?>" id="features">
			<!-- Features -->
			<div class="story-list">
				<?
				for( $i = 0; $i < count( $features );$i++ ) {
					$feature = $features[$i];
				?>
				<div class="span3">
					<div>
						<a href="<?php echo get_permalink( $feature->ID )?>">
							<?php echo get_img_html( $feature->ID, 'category_story' )?>
						</a>
					</div>
					<h3><a href="<?php echo get_permalink( $feature->ID ) ?>"><?php echo $feature->post_title ?></a></h3>
					<p class="story-blurb">
						<?php echo get_excerpt( $feature )?>
					</p>
				</div>
				<? } ?>
			</div>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode( 'subpage_features', 'sc_subpage_features' );


/**
 * Update
 *
 * @return string
 * @author Chris Conover
 **/
function sc_update($atts = Array())
{
	global $wp_query;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$update = resolve_posts(	Array(	'tag' => $wp_query->queried_object->slug),
								Array(	'post_type' => 'update',
										'numberposts' => 1),
								False,
								False);
	if($update !== False) {
		ob_start();
		?>
		<div class="<?=$css?>" id="update">
			<h3>Update</h3>
			<div>
				<h4><?=$update->post_title?></h4>
				<?=get_img_html($update->ID, 'update')?>
				<?=$update->post_content?>
			</div>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('update', 'sc_update');


/**
 * External UCF stories
 *
 * @return string
 * @author Chris Conover
 **/
function sc_external_stories( $atts = array() ) {
	$css = ( isset( $atts['css'] ) ) ? $atts['css'] : '';
	$heading = ( isset( $atts['heading'] ) ) ? $atts['heading'] : 'UCF in the News';
	$links_per_page = ( isset( $atts['links_per_page'] ) && is_numeric( $atts['links_per_page'] ) ) ? intval( $atts['links_per_page'] ) : 4;
	$linked_page_name = ( isset( $atts['linked_page_name'] ) ) ? $atts['linked_page_name'] : 'UCF in the News';
	$show_description = ( isset( $atts['show_description'] ) ) ? filter_var( $atts['show_description'], FILTER_VALIDATE_BOOLEAN ) : false;

	$stories = resolve_posts(	Array(	'tag' => $wp_query->queried_object->slug),
								Array(	'post_type' => 'externalstory',
										'numberposts' => $links_per_page ) );
	ob_start();
	if ( count( $stories ) > 0 ) : ?>
		<div class="<?php echo $css; ?>" id="external_stories">
			<h2><?php echo $heading; ?></h2>
			<ul class="story-list">
				<?php
				foreach ( $stories as $story ) {
					echo display_external_stories_list_item( $story->ID, $show_description );
				}
				?>
			</ul>
			<?php
			$linked_page_name_id = get_page_by_title( $linked_page_name )->ID;
			if ( $linked_page_name_id ) :
			?>
				<a href="<?php echo get_page_link( $linked_page_name_id ); ?>" class="external-stories-view-all">View All &raquo;</a>
				<div class="clearfix"></div>
			<?php endif; ?>
		</div>
	<?php
	endif;
	return ob_get_clean();
}
add_shortcode('external_stories', 'sc_external_stories');


/**
 * List all external UCF stories
 *
 * @return string
 * @author Cadie Brown
 **/
function sc_all_external_stories( $atts = array() ) {
	$css = ( isset( $atts['css'] ) ) ? $atts['css'] : '';
	$links_per_page = ( isset( $atts['links_per_page'] ) && is_numeric( $atts['links_per_page'] ) ) ? intval( $atts['links_per_page'] ) : 25;
	$show_description = ( isset( $atts['show_description'] ) ) ? filter_var( $atts['show_description'], FILTER_VALIDATE_BOOLEAN ) : false;

	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$external_stories_args = array(
		'posts_per_page' => $links_per_page,
		'post_type' => 'externalstory',
		'paged' => $paged
	);
	$external_stories_query = new WP_Query( $external_stories_args );

	ob_start();
	if ( $external_stories_query->have_posts() ) : ?>
		<div class="<?php echo $css; ?>" id="all_external_stories">
			<ul class="story-list">
			<?php
			while ( $external_stories_query->have_posts() ) : $external_stories_query->the_post();
				echo display_external_stories_list_item( get_the_ID(), $show_description );
			endwhile;
			?>
			</ul>
			<nav aria-label="UCF in the News external stories navigation">
				<ul class="pagination">
					<li class="previous"><?php previous_posts_link('&laquo; Previous'); ?></li>
					<li class="next"><?php next_posts_link( 'Next &raquo;', $external_stories_query->max_num_pages ); ?></li>
				</ul>
			</nav>
		</div>
	<?php
		wp_reset_postdata();
	endif;
	return ob_get_clean();
}
add_shortcode('all_external_stories', 'sc_all_external_stories');


/**
 * Announcements
 *
 * @return string
 * @author Chris Conover
 **/
function sc_announcements($atts)
{
	$css 	= (isset($atts['css'])) ? $atts['css'] : '';
	$header = (isset($atts['header'])) ? $atts['header'] : 'h3';
	$param = ($atts['param'] == 'role' || $atts['param'] == 'keyword' || $atts['param'] == 'time') ? $atts['param'] : 'role';
	$value = $atts['value'] !== null ? $value : 'all';

	return display_announcements($param, $value, $header, $css);
}
add_shortcode('announcements', 'sc_announcements');


/**
 * Single post. Handles expert posts as well
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post($atts = Array())
{
	global $post, $wp_embed;

	$expert = (get_post_type($post->ID) == 'expert') ? True : False;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	if($expert) {
		$title = get_post_meta($post->ID, 'expert_name', True).', '.get_post_meta($post->ID, 'expert_title', True);
		$subtitle = get_post_meta($post->ID, 'expert_association', True);
	} else {
		$title = $post->post_title;
		$subtitle = get_post_meta($post->ID, 'subtitle', True);
	}
	$subtitle = ($subtitle == '') ? '' : '<p id="subtitle">'.$subtitle.'</p>';

	$img_attach = get_img_html($post->ID, 'story_feature', Array('return_id' => True));

	if($img_attach['attachment_id'] != '') {
		$attachment = get_post($img_attach['attachment_id']);
	}

	$comment_form_args = Array(	'fields' => Array(	'<label for="share_name">Name</label><input type="text" id="share_name" name="author" />',
													'<label for="share_email">Email</label>
													<input type="text" id="share_email" name="email" />'),
								'comment_field' => '<label for="share_comment">Your Comment</label><textarea id="share_comment" name="comment"></textarea>',
								'comment_notes_after' => '',
								'comment_notes_before' => '',
								'title_reply' => 'Share Your Thoughts',
						);


	$content = $post->post_content;
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);

	$video_url = get_video_url($post->ID);

	ob_start();
	?>
	<div<?php if ( $css ) { ?> class="<?=$css?>" <?php } ?>>
		<article role="main">
			<h1><?=$title?></h1>
			<?=$subtitle?>
			<? if($video_url != '') { ?>
				<?php echo $wp_embed->run_shortcode( '[embed width="550" height="500"]'.$video_url.'[/embed]' ); ?>
			<? } else { ?>
			<div id="story_feat_img">
				<?=$img_attach['html']?>
			</div>
			<? } ?>
			<p id="caption"><?=( isset( $attachment ) ) ? $attachment->post_excerpt: ''?></p>
			<div id="content">
				<?=strip_tags( $content, '<p><a><ol><ul><li><em><strong><img><blockquote><div>' )?>
			</div>
			<?php echo do_shortcode( '[author_bio]' ); ?>
			<div id="share" role="form">
				<?=comment_form( $comment_form_args, $post->ID )?>
			</div>
		</article>
	</div>
	<?
	$html = ob_get_contents(); ob_end_clean();
	return $html;
}
add_shortcode('single_post', 'sc_single_post');

/**
 * Returns author bio markup for both
 * single and featured posts
 *
 * @return string
 * @author Cadie Brown
 **/
function sc_author_bio( $atts = array() ) {
	global $post;

	$css = ( isset($atts['css']) ) ? $atts['css'] : '';

	$author_title = get_post_meta( $post->ID, 'author_title', True );

	$author_byline = get_post_meta( $post->ID, 'author_byline', True );
	$author_byline = ( $author_byline != '' ) ? $author_byline : get_the_author();

	$author_bio = get_post_meta( $post->ID, 'author_bio', True );

	$featured_post_bio = is_page_template( 'featured-single-post.php' ) ? ' featured-author-bio' : '';

	$classes = $css.$featured_post_bio;

	ob_start();
	if ($author_bio != '') :
	?>
		<hr>
		<div class="author-bio <?php echo $classes; ?>">
			<p class="author-byline"><?php echo $author_byline; ?></p>
			<p class="author-title"><?php echo $author_title; ?></p>
			<p class="author-bio"><?php echo $author_bio; ?></p>
		</div>
	<?php
	endif;
	return ob_get_clean();
}
add_shortcode( author_bio, sc_author_bio );


/**
 * Single post meta. Also handles expert.
 *
 * @return string.
 * @author Chris Conover
 **/
function sc_single_post_meta($atts = Array())
{
	global $post;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$author_title = get_post_meta($post->ID, 'author_title', True);
	$author_title = ($author_title != '') ? '<p id="author_title">'.$author_title.'</p>' : '';

	$source = apply_filters('the_content', get_post_meta($post->ID, 'source', True));
	$source = ($source != '') ? '<div id="source">'.$source.'</div>' : '';

	$byline = get_post_meta($post->ID, 'author_byline', True);
	$byline = ($byline != '') ? $byline : get_the_author();

	ob_start()?>
	<div class="<?=$css?>" id="meta">
		<div>
			<p id="byline">By <?=$byline?></p>
			<?=$author_title?>
			<p><?=date('l, F j, Y', strtotime($post->post_date))?></p>
			<?=$source?>
			<? if(function_exists('wp_print')) {?>
				<div id="print">
					<a href="?print=1" rel="nofollow" target="_blank">Print this Article</a>
				</div>
			<? } ?>
		</div>
		<?php echo display_social( get_permalink( $post->ID ), $post->post_title, 'affixed' ); ?>
	</div>
	<?
	$html = ob_get_contents(); ob_end_clean();
	return $html;
}
add_shortcode('single_post_meta', 'sc_single_post_meta');


/**
 * Single post "More about" tag
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post_more_tag($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$primary_tag_id = get_post_meta($post->ID, 'primary_tag', True);

	if($primary_tag_id == '') {
		$tags = wp_get_post_tags($post->ID);
		if(count($tags) > 0) {
			$primary_tag = $tags[0];
		} else {
			return '';
		}
	} else {
		$primary_tag = get_tag($primary_tag_id);
	}
	$primary_tag_posts = resolve_posts(	Array(	'tag' => $primary_tag->slug),
										Array(	'numberposts' => 3,
												'exclude' => Array($post->ID))
									);
	if(count($primary_tag_posts) > 0) {
		ob_start();
		?>
		<div class="link_list <?=$css?>">
			<h3>More about <?=$primary_tag->name?></h3>
			<ul class="story-list">
				<? foreach($primary_tag_posts as $tag_post) {?>
				<li>
					<a href="<?=get_permalink($tag_post->ID)?>"><?=$tag_post->post_title?></a>
				</li>
				<? } ?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('single_post_more_tag', 'sc_single_post_more_tag');


/**
 * Single post "More about" tag
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post_more_cat($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$cats = wp_get_post_categories($post->ID);

	if(count($cats) > 0) {
		$cat = get_category($cats[0]);
		$cat_posts = resolve_posts(	Array(	'category' => $cat->slug),
									Array(	'numberposts' => 3,
											'exclude' => Array($post->ID))
								);

		ob_start();
		?>
		<div class="link_list <?=$css?>">
			<h3>More about <?=$cat->name?></h3>
			<ul class="story-list">
				<? foreach($cat_posts as $cat_post) {?>
				<li>
					<a href="<?=get_permalink($cat_post->ID)?>"><?=$cat_post->post_title?></a>
				</li>
				<? } ?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('single_post_more_cat', 'sc_single_post_more_cat');


/**
 * Single post comments
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post_comments($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$comments = get_comments(Array(	'post_id' => $post->ID,
									'status' => 'approve',
									'number' => 3,
									'type' => ''
								)
							);

	if(count($comments) > 0) {
		ob_start();
		?>
		<div class="<?=$css?>" id="comments">
			<h3>Comments</h3>
			<ul class="comment-list">
				<? foreach($comments as $comment) {?>
				<li>
					<p class="meta"><?=$comment->comment_author?>, <?=$comment->comment_date?></p>
					<p class="content">
						<?=$comment->comment_content?>
					</p>
				</li>
				<? } ?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('single_post_comments', 'sc_single_post_comments');


/**
 * Single post topics
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post_topics($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$tags = wp_get_post_tags($post->ID);

	// Never display Main Site Tag in More Topics section
	$mainsite_tag = get_mainsite_tag();

	if(count($tags) > 0) {
		ob_start();
		?>
		<div class="link_list <?=$css?>" id="more_tags">
			<h3>More Topics</h3>
			<ul class="term-list clearfix">
				<?	for($i = 0; $i < count($tags);$i++) {
						$tag = $tags[$i];
						if($tag->term_id != $mainsite_tag->term_id) {
				?>
				<li>
					<a href="<?=get_tag_link($tag->term_id)?>">
						<?=$tag->name?><?=(($i + 1) != count($tags)) ? ',' : ''?>
					</a>
				</li>
				<?
					}
				} ?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('single_post_topics', 'sc_single_post_topics');


/**
 * Single post recommendations. Do not display for expert
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post_recommended($atts = Array())
{
	global $post;
	if(get_post_type($post->ID) == 'expert') return '';

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	ob_start();
	?>
	<div class="<?=$css?>" id="recommended">
		<h3>Recommended Stories</h3>
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		<div class="fb-recommendations" data-site="today.ucf.edu" data-width="310" data-height="480" data-header="false" font="" border_color="#FFF"></div>
	</div>
	<?
	$html = ob_get_contents(); ob_end_clean();
	return $html;
}
add_shortcode('single_post_recommended', 'sc_single_post_recommended');


/**
 * Single post related experts
 *
 * @return string
 * @author Chris Conover
 **/
function sc_single_post_related_experts($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$experts = wp_get_object_terms($post->ID, 'experts');

	ob_start();
	foreach($experts as $expert) {
		$stories = resolve_posts(	Array(),
									Array(	'numberposts' => 5,
											'exclude'     => Array($post->ID),
											'tax_query'   => Array(
																Array(	'taxonomy' => 'experts',
																		'field'    => 'slug',
																		'terms'    => $expert->slug))));
		if(count($stories) > 0) {
			?>
			<div class="link_list <?=$css?>" id="related_experts">
				<h3>More About <?=$expert->name?></h3>
				<ul class="story-list">
			<?
				foreach($stories as $story) { ?>
					<li><a href="<?=get_permalink($story->ID)?>"><?=$story->post_title?></a></li>
			<? } ?>
				</ul>
			</div>
			<?
		}
	}
	return ob_get_clean();
}

add_shortcode('single_post_related_experts', 'sc_single_post_related_experts');


/**
 * Expert meta
 *
 * @return string
 * @author Chris Conover
 **/
function sc_expert_meta($atts = Array())
{
	global $post;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$email  = get_post_meta($post->ID, 'expert_email', True);
	$phones = explode(',', get_post_meta($post->ID, 'expert_phone', True));

	$img_html = '';
	$img_details = get_img_html($post->ID, 'full', Array('return_id' => True));
	if($img_details['attachment_id'] != '') {
		$img_src = wp_get_attachment_image_src($img_details['attachment_id'], 'full');
		if($img_src != False) {
			$img_html = '<p><a href="'.$img_src[0].'" target="_blank">Download Profile Image</a></p>';
		}
	}

	ob_start();
	?>
	<div class="<?=$css?>" id="expert_meta">
		<h3>Contact Information</h3>
		<? if($email != '') {?>
		<p><a href="mailto:<?=$email?>"><?=$email?></a></p>
		<? } ?>
		<? if(count($phones) > 0) {?>
		<ul>
			<? foreach($phones as $phone ) {?>
			<li><?=$phone?></li>
			<? } ?>
		</ul>
		<?}?>
		<?=$img_html?>
	</div>
	<?
	$html = ob_get_contents(); ob_end_clean();
	return $html;
}
add_shortcode('expert_meta', 'sc_expert_meta');


/**
 * More expert stories
 *
 * @return string
 * @author Chris Conover
 **/
function sc_expert_tagged($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$term = get_term_by('name', get_post_meta($post->ID, 'expert_name', True), 'experts');

	$stories = resolve_posts(Array(),Array(	'numberposts' => 5,
											'tax_query' => Array(
																Array(
																		'taxonomy' => 'experts',
																		'field' => 'slug',
																		'terms' => $term->slug
																	)
															)
										)
							);
	if(count($stories) > 0) {
		ob_start();
		?>
		<div class="link_list <?=$css?>">
			<h3>More about <?=get_post_meta($post->ID, 'expert_name', True)?></h3>
			<ul class="story-list">
				<? foreach($stories as $story) {?>
				<li><a href="<?=get_permalink($story->ID)?>"><?=$story->post_title?></a></li>
				<?}?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('expert_tagged', 'sc_expert_tagged');


/**
 * More expert videos
 *
 * @return string
 * @author Chris Conover
 **/
function sc_expert_videos($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$term = get_term_by('name', get_post_meta($post->ID, 'expert_name', True), 'experts');

	$videos = resolve_posts(Array(),Array(	'post_type' => 'video',
											'numberposts' => 5,
											'tax_query' => Array(
																Array(
																		'taxonomy' => 'experts',
																		'field' => 'slug',
																		'terms' => $term->slug
																	)
															)
										)
							);
	$video_page = get_page_by_title('Videos');

	if(count($videos) > 0) {
		ob_start();
		?>
		<div class="link_list <?=$css?>">
			<h3>Videos about <?=get_post_meta($post->ID, 'expert_name', True)?></h3>
			<ul class="video-list">
				<? foreach($videos as $video) {?>
				<li><a href="<?=get_permalink($video->ID)?>"><?=$video->post_title?></a></li>
				<?}?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('expert_videos', 'sc_expert_videos');


/**
 * More expert photosets
 *
 * @return string
 * @author Chris Conover
 **/
function sc_expert_photos($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$term = get_term_by('name', get_post_meta($post->ID, 'expert_name', True), 'experts');

	$photosets = resolve_posts(Array(),Array(	'post_type' => 'photoset',
												'numberposts' => 5,
												'tax_query' => Array(
																Array(
																		'taxonomy' => 'experts',
																		'field' => 'slug',
																		'terms' => $term->slug
																	)
															)
										)
							);

	if(count($photosets) > 0) {
		ob_start();
		?>
		<div class="link_list <?=$css?>">
			<h3>Photos about <?=get_post_meta($post->ID, 'expert_name', True)?></h3>
			<ul class="photoset-list">
				<? foreach($photosets as $photoset) {?>
				<li><a href="<?=get_permalink($photoset->ID)?>"><?=$photoset->post_title?></a></li>
				<?}?>
			</ul>
		</div>
		<?
		$html = ob_get_contents(); ob_end_clean();
		return $html;
	}
}
add_shortcode('expert_photos', 'sc_expert_photos');


/**
 * Feature post meta.
 *
 * @return string.
 * @author Cadie Brown
 **/
function sc_feature_post_meta($atts = Array())
{
	global $post;

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$byline = get_post_meta($post->ID, 'author_byline', True);
	$byline = ($byline != '') ? $byline : get_the_author();

	ob_start(); ?>
	<div class="<?php echo $css; ?>" id="meta">
		<div>
			<p id="byline-date">By <?php echo $byline; ?> <span class="hidden-mobile">|</span><br class="visible-mobile"> <?php echo date('F j, Y', strtotime($post->post_date)); ?></p>
		</div>
	</div>
	<?php
	$html = ob_get_clean();
	return $html;
}
add_shortcode('feature_post_meta', 'sc_feature_post_meta');


/**
 * Photo set
 *
 * @return string
 * @author Chris Conover
 **/
function sc_photo_set($atts = Array())
{
	global $post;

	$css	= (isset($atts['css'])) ? $atts['css'] : '';

	$images = resolve_posts(Array(), Array(	'post_type' => 'attachment',
											'post_parent' => $post->ID,
											'numberposts' => -1,
											'orderby' => 'menu_order',
											'order' => 'ASC'
											));

	ob_start();
	?>
	<div id="photoset" class="<?=$css?>">
		<div class="row">
			<h3 class="span8"><?=$post->post_title?></h3>
			<div class="span4">
				<?=display_social(get_permalink($post->ID), $post->post_title)?>
			</div>
		</div>
		<p><?=$post->post_content?> <strong>(<?=count($images)?> photos total)</strong></p>
		<ul class="photoset-list">
			<? for($i = 1; $i <= count($images);$i++) {
				$image_obj = $images[$i - 1];
				$text = '';
				// caption -> post_excerpt
				// description -> post_content
				if($image_obj->post_excerpt != '') {
					$text = $image_obj->post_excerpt;
				} else if($image_obj->post_content != '') {
					$text = $image_obj->post_content;
				}
				?>
			<li class="image" id="photo-<?=$i?>">
				<?=get_img_html($image_obj->ID, 'photoset_photo', Array('sent_attach' => True))?>
				<p class="clearfix"><span><?=$i?></span><?=$text?></p>
			</li>
		<? } ?>
		</ul>
	</div>
	<?
	$html = ob_get_contents(); ob_end_clean();
	return $html;
}
add_shortcode('photo_set', 'sc_photo_set');


/**
 * Photo set listing
 *
 * @return string
 * @author Chris Conover
 **/
function sc_photo_sets($atts = Array())
{

	$css = (isset($atts['css'])) ? $atts['css'] : '';

	$photo_sets = resolve_posts(Array(), Array(	'post_type' => 'photoset',
												'numberposts' => -1));
	$first = True;
	ob_start();?>
	<div id="photo-sets" class="<?=$css?>">
	<?
	$count = 0;
	foreach($photo_sets as $photo_set) {

		$image_id = 0;
		if( ($image_id = get_post_meta($photo_set->ID, '_thumbnail_id', True)) == '') {
			$image = resolve_posts(Array(), Array(
				'post_type'   => 'attachment',
				'post_parent' => $photo_set->ID,
				'numberposts' => 1,
				'order'       => 'DESC'));
			if($image !== False) {
				$image_id = $image->ID;
			}
		}

		if($first) { ?>
			<div class="row">
				<div class="span8">
					<a href="<?=get_permalink($photo_set->ID)?>">
						<?=get_img_html($image_id, 'photoset_preview', Array('sent_attach' => True))?>
					</a>
				</div>
				<div class="span4 last">
					<h3><a href="<?=get_permalink($photo_set->ID)?>"><?=$photo_set->post_title?></a></h3>
					<p><?=$photo_set->post_content?></p>
				</div>
				<hr class="span12" />
			</div>
			<div class="row photoset-list">
		<? } else {	?>
			<?php if (($count % 4) == 0 && $count !== 0) { ?>
			</div>
			<div class="row photoset-list">
			<?php } ?>
				<div class="span3 <?=$css_class?>">
					<a href="<?=get_permalink($photo_set->ID)?>">
						<?=get_img_html($image_id, 'photoset_thumb', Array('sent_attach' => True))?>
					</a>
					<h3><a href="<?=get_permalink($photo_set->ID)?>"><?=$photo_set->post_title?></a></h3>
				</div>
		<? $count++;
		}
		$first = false;
		?>
	<? } ?>
		</div>
	</div>
	<?
	return ob_get_clean();
}
add_shortcode('photo_sets', 'sc_photo_sets');


/**
 * Video listing
 *
 * @return string
 * @author Chris Conover
 **/
function sc_videos($atts = Array())
{
	global $wp_embed;

	$css            = (isset($atts['css'])) ? $atts['css'] : '';
	$specific_video = (isset($atts['specific_video'])) ? $atts['specific_video'] : False;

	$video = null;


	if($specific_video !== False) {
		$specific_video = get_post($specific_video);
		if($specific_video != False) {
			$videos = resolve_posts(Array(), Array( 'post_type' => 'video',
													'numberposts' => -1,
													'exclude' => Array($specific_video->ID)));
			$videos = array_merge(Array($specific_video), $videos);
		}
	} else {
		$videos = resolve_posts(Array(), Array( 'post_type' => 'video',
												'numberposts' => -1,
												'exclude' => Array($specific_video->ID)));
	}

	$first = True;
	$count = 0;
	ob_start();?>
	<?php if ($css !== '') { ?><div class="<?=$css?>"><?php } ?>
	<?
	foreach($videos as $video) {
		$video_url = get_video_url($video->ID);
		if($video_url != '') {
			if($first) {
				$first = false;
				$embed_string = '[embed width="590" height="430"]'.$video_url.'[/embed]';
				?>
				<div class="row">
					<div class="feature span8">
						<?php echo $wp_embed->run_shortcode($embed_string); ?>
					</div>
					<div class="span4">
						<h3><?=$video->post_title?></h3>
						<p><?=$video->post_content?></p>
					</div>
					<hr class="span12" />
				</div>
				<div class="row video-list thumbnails">
			<? } else {
				if(strpos($video_url, 'youtube.com')) {
					preg_match('/v=(?<video_id>[^&]+)&?/', get_post_meta($video->ID, 'video_url', True), $matches);
					if(isset($matches['video_id'])) {
						$video_id = $matches['video_id'];
					?>
					<?php if (($count % 3) == 0 && $count !== 0) { ?>
				</div>
				<div class="row video-list thumbnails">
					<?php } ?>
					<div class="span4" id="video-<?=$count?>">
						<a class="thumbnail" href="<?=get_permalink($video->ID)?>">
							<img src="//i1.ytimg.com/vi/<?=$matches['video_id']?>/hqdefault.jpg" alt="Video: <?=$video->post_title?>" />
							<h3><?=$video->post_title?></h3>
						</a>
					</div>
				<? $count++;
					}
				}
			}
		}
	} ?>
	</div>
	<?php if ($css !== '') { ?></div><?php } ?>
	<?
	return ob_get_clean();
}
add_shortcode('videos', 'sc_videos');


/**
 * Special profile feature
 *
 * @return string
 * @author Chris Conover
 **/
function sc_profile_feature($atts = Array())
{
	$count = 0;
	$css = (isset($atts['css'])) ? $atts['css'] : '';

	if(isset($atts['group'])) {
		$group_name = $atts['group'];
		if( ($group = get_term_by('name', $group_name, 'groups')) !== False) {
			$profiles = get_posts(Array('numberposts' => 4, 'post_type' => 'profile', 'group' => $group->slug));
			ob_start();?>
			<div id="profile-feature" class="<?=$css?>">
				<div class="row">
					<div class="span12">
						<h3><span class="orange">Special Feature:</span> <?=$group->name?></h3>
					</div>
				</div>
				<div class="row profile-list">
					<?php if (($count % 4) == 0 && $count !== 0) { ?>
				</div>
				<div class="row profile-list">
					<?php }
						foreach($profiles as $profile) { ?>
							<div class="span3">
								<a href="<?=get_permalink($profile->ID)?>">
									<?=get_the_post_thumbnail($profile->ID, 'profile_feature')?>
									<h4><?=$profile->post_title?></h4>
									<strong><?=get_post_meta($profile->ID, 'profile_jobtitle', True)?></strong>
								</a>
							</div>
					<?php
							$count++;
						}
					?>
				</div>
			</div>
		<?
		}
		return ob_get_clean();
	}
}
add_shortcode('profile_feature', 'sc_profile_feature');


/**
 * Retrieve article for archive display
 *
 * @return string
 * @author Brandon Groves
 **/
function sc_archive_articles($attrs) {
    $css = (isset($atts['css'])) ? $atts['css'] : '';
    $articles = array();
    $monthYear = _getArchiveMonthYear();
    $month = $monthYear['mon'];
    $year = $monthYear['year'];

    $args = array(
        'post_type'        => 'post',
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'suppress_filters' => false,
    );

    add_filter( 'posts_where', 'filter_archive_date_range' );
    $articles = get_posts($args);
    remove_filter( 'posts_where', 'filter_archive_date_range' );

    ob_start();
    ?>
    <h2 class='month_year'><?=date('F Y', strtotime($year . '-' . $month . '-1')); ?></h2>
    <?
    if (count($articles) > 0) {
        ?>

        <div class="<?=$css?>" id="archives">
            <!-- Features -->
            <ul>
        <?
            for($i = 0; $i < count($articles);$i++) {
                $article = $articles[$i];
                $class = '';
                if($i == 0) {$class = 'first';}
                if(($i + 1) == count($articles)) {$class = 'last';}
        ?>
                <li<?=($class != '') ? ' class="'.$class.' clearfix" ':' class="clearfix"'?>>
                    <div class="thumbnail">
                        <a href="<?=get_permalink($article->ID)?>">
                            <?=get_img_html($article->ID, 'story')?>
                        </a>

                    </div>
                    <h3><a href="<?=get_permalink($article->ID)?>"><?=$article->post_title?></a></h3>
                    <p class="date"><?=$article->post_date; ?></p>
                    <p class="ellipse">
                        <?=get_excerpt($article)?>
                    </p>
                </li>
            <? } ?>
            </ul>
        </div>
    <?
    }

    # no need to unset the last / in the url since it isn't a number
    $url_path = explode( '/', $_SERVER['REQUEST_URI'] );
    if (is_numeric($url_path[count($url_path) - 2])) {
        unset($url_path[count($url_path) - 2]);
    }
    $url = implode('/', $url_path);

    $archive_year_month = date( 'Ym', strtotime( $year . '-' . $month . '-1') );

    $args = array(
        'post_type'        => 'post',
        'numberposts'      => 1,
        'orderby'          => 'date',
        'order'            => 'ASC',
        'suppress_filters' => false,
    );

    $oldest_post_date = date_parse ( array_shift( get_posts( $args ) )->post_date );
    $oldest_year_month = date( 'Ym', strtotime( $oldest_post_date["year"] . '-' . $oldest_post_date["month"] . '-1' ) );

    if( $oldest_year_month < $archive_year_month ) {
        ?>
        <div class="previous"><a href="<?=$url . date('Ym', strtotime($year . '-' . $month . '-1 -1 month')) . '/' ?>">Previous Month</a></div>
        <?php
    }

    $today = getdate();
    if ($today['mon'] == $month + 1 && $today['year'] == $year) {
    	?>
        <div class="next"><a href="<?=$url ?>">Next Month</a></div>
        <?php
    } elseif ($today['mon'] <> $month || $today['year'] <> $year) {
        ?>
        <div class="next"><a href="<?=$url . date('Ym', strtotime($year . '-' . $month . '-1 +1 month')) . '/' ?>">Next Month</a></div>
        <?php
    }

    return ob_get_clean();
}
add_shortcode('sc-archive-articles', 'sc_archive_articles');

function sc_callout( $atts, $content='' ) {
	$atts = shortcode_atts(
		array(
			'background' => '#000',
			'color'      => '#fff',
			'container'  => True,
			'class'      => null
		),
		$atts
	);

	$background = $atts['background'];
	$color = $atts['color'];
	$container = filter_var( $atts['container'], FILTER_VALIDATE_BOOLEAN );
	$class = $atts['class'];

	ob_start();
?>
	</div><!-- end .container -->
	<div class="well" style="background-color: <?php echo $background; ?>; color: <?php echo $color; ?>;">
	<?php if ( $container ) : ?><div class="container"><?php endif; ?>
		<?php echo $content; ?>
	<?php if ( $container ) : ?></div><!-- end .container --><?php endif; ?>
	</div>
	<div class="container"><!-- Reopen content .container -->
<?php
	return ob_get_clean();

}

add_shortcode( 'callout', 'sc_callout' );

?>
