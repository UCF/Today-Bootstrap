<?php
require_once('functions/base.php');   			# Base theme functions
require_once('functions/feeds.php');			# Where functions related to feed data live
require_once('custom-taxonomies.php');  		# Where per theme taxonomies are defined
require_once('custom-post-types.php');  		# Where per theme post types are defined
require_once('functions/admin.php');  			# Admin/login functions
require_once('functions/config.php');			# Where per theme settings are registered
require_once('shortcodes.php');         		# Per theme shortcodes

//Add theme-specific functions here.

/**
 * Rewrite rules for transition from (older) today to (slightly less old) today
 * Retained for backwards compatibility
 *
 * @return array
 * @author Chris Conover
 **/
function transition_rules()
{
	global $wp_rewrite;
	
	$cats = Array(
		'music'                           => 'music',
		'theatre'                         => 'theatre',
		'visual-arts'                     => 'visual-arts',
		'arts-humanities'                 => 'arts-humanities',
		'education'                       => 'education',
		'engineering-computer-science'    => 'engineering-computer-science',
		'graduate-studies'                => 'graduate-studies',
		'health-public-affairs'           => 'health-public-affairs',
		'honors'                          => 'honors',
		'hospitality-managment'           => 'hospitality-management',
		'medicine-colleges'               => 'medicine-colleges',
		'nursing-colleges'                => 'ucf-college-of-nursing',
		'optics-photonics'                => 'optics-photonics',
		'sciences'                        => 'sciences',
		'main-site-stories'               => 'main-site-stories',
		'on-campus'                       => 'on-campus',
		'events'                          => 'events',
		'research'                        => 'research');
	
	$custom = Array();
	
	foreach($cats as $before=>$after) {
		// Rewrite category pages
		$custom['section/(?:[^/]+/)?'.$before.'/?$'] = 'index.php?tag='.$after;
		$custom['category/(?:[^/]+/)?'.$before.'/?$'] = 'index.php?tag='.$after;
		
		// Rewrite feed pages
		$custom['section/(?:[^/]+/)?'.$before.'/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?tag='.$after.'&feed=$matches[1]';
		$custom['section/(?:[^/]+/)?'.$before.'/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?tag='.$after.'&feed=$matches[1]';
		$custom['category/(?:[^/]+/)?'.$before.'/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name='.$after.'&feed=$matches[1]';
		$custom['category/(?:[^/]+/)?'.$before.'/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name='.$after.'&feed=$matches[1]';
	}
	// Rewrite old category and tag pages
	$custom['category/(?:[^/]+/)?(.+?)/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
	$custom['category/(?:[^/]+/)?(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
	$custom['category/(?:[^/]+/)?(.+?)/?$'] = 'index.php?category_name=$matches[1]';	
	
	$custom['tag/(?:[^/]+/)?(.+?)/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?tag=$matches[1]&feed=$matches[2]';
	$custom['tag/(?:[^/]+/)?(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?tag=$matches[1]&feed=$matches[2]';
	$custom['tag/(?:[^/]+/)?(.+?)/?$'] = 'index.php?tag=$matches[1]';
	
	return $custom;
}


/**
 * Pulls, parses and caches the weather.
 *
 * @return array
 * @author Chris Conover, Jo Dickson
 **/
function get_weather_data() {
	$cache_key = 'weather';
	
	// Check if cached weather data already exists
	if(($weather = get_transient($cache_key)) !== False) {
		return $weather;
	} else {
		$weather = array('condition' => 'Fair', 'temp' => '80&#186;', 'img' => '34');
		
		// Set a timeout
		$opts = array('http' => array(
								'method'  => 'GET',
								'timeout' => WEATHER_FETCH_TIMEOUT,
		));
		$context = stream_context_create($opts);
		
		// Grab the weather feed
		$raw_weather = file_get_contents(WEATHER_URL, false, $context);
		if ($raw_weather) {
			$json = json_decode($raw_weather);
			
			$weather['condition'] 	= $json->condition;
			$weather['temp']		= $json->temp;
			$weather['img']			= (string)$json->imgCode;
			
			// The temp, condition and image code should always be set,
			// but in case they're not, we catch them here:
			
			# Catch missing cid
			if (!isset($weather['img']) or !$weather['img']){
				$weather['img'] = '34';
			}
			
			# Catch missing condition
			if (!is_string($weather['condition']) or !$weather['condition']){
				$weather['condition'] = 'Fair';
			}
			
			# Catch missing temp
			if (!isset($weather['temp']) or !$weather['temp']){
				$weather['temp'] = '80&#186;';
			}
		}
		
		// Cache the new weather data
		set_transient($cache_key, $weather, WEATHER_CACHE_DURATION);
		
		return $weather;
	}
}


/**
 * Internal list items of alerts ul
 *
 * @return string
 * @author Chris Conover
 **/
function gen_alerts_html()
{		
		$alerts 		= get_posts(Array('post_type' => 'alert'));
		$hidden_alerts	= Array();
		$alerts_html 	= '';
		
		// Parse hidden alerts from cookie
		if(isset($_COOKIE[ALERT_COOKIE_NAME])) {
			$raw_hidden_alerts = explode(',', htmlspecialchars($_COOKIE[ALERT_COOKIE_NAME]));
			foreach($raw_hidden_alerts as $alert_data) {
				$alert = explode('-', $alert_data);
				if(count($alert) == 2) {
					$hidden_alerts[$alert[0]] = $alert[1]; // post_id -> post_time
				}
			}
		}
		
		foreach($alerts as $alert) {
			
			$text       = get_post_meta($alert->ID, 'alert_text', True);
			$link_text  = get_post_meta($alert->ID, 'alert_link_text', True);
			$link_url   = get_post_meta($alert->ID, 'alert_link_url', True);
			$type       = get_post_meta($alert->ID, 'alert_type', True);
			$bg_color   = get_post_meta($alert->ID, 'alert_bg_color', True);
			$text_color = get_post_meta($alert->ID, 'alert_text_color', True);
			
			$css_clss           = Array($type);
			$li_inline_styles   = Array();
			$span_inline_styles = Array();
			
			$link_html = ($link_text && $link_url) ? "<a href=\"$link_url\">$link_text</a>" : '';
			
			$thumbnail_id = get_post_thumbnail_id($alert->ID);
			if($thumbnail_id != '') {
				$thumbnail = wp_get_attachment_image_src($thumbnail_id, 'alert');
				array_push($li_inline_styles, 'background-image: url('.$thumbnail[0].');');
			}
			
			if($bg_color != '') {
				if(substr($bg_color, 0, 1) != '#') {
					$bg_color = '#'.$bg_color;
				}
				array_push($span_inline_styles, 'background-color: '.$bg_color.';');
			}
			if($text_color != '') {
				if(substr($text_color, 0, 1) != '#') {
					$text_color = '#'.$text_color;
				}
				array_push($span_inline_styles, 'color: '.$text_color.';');
			}
			
			
			// Even if alert is hidden, show it if it's updated
			if(isset($hidden_alerts[$alert->ID]) && strtotime($alert->post_modified) <= $hidden_alerts[$alert->ID]) {
				array_push($css_clss, 'hide');
			}
			
		 	$alert_html = '<li style="'.implode(' ',$li_inline_styles).'" class="'.implode(' ',$css_clss).'" id="alert-'.$alert->ID.'-'.strtotime($alert->post_modified).'">
								<span class="msg" style="'.implode(' ', $span_inline_styles).'">
									'.$text.'
									'.$link_html.'
									<a class="close">Close</a>
								</span>
							</li>';
			$alerts_html .= $alert_html."\n";
		}
		return $alerts_html;
}


/**
 * IMG Tag HTML
 * 
 * TODO: wtf is this?
 *
 * @return string or array
 * @author Chris Conover
 **/
function get_img_html($post_id, $size = 'thumbnail', $options = Array())
{
	global $wpdb, $_wp_additional_image_sizes;
	
	if($size == 'photoset_photo') $size = 'full';
	
	$element_id		= (isset($options['element_id'])) ? $options['element_id'] : '';
	$return_id		= (isset($options['return_id'])) ? $options['return_id'] : False;
	$sent_attach	= (isset($options['sent_attach'])) ? $options['sent_attach'] : False;
	
	$org_size = $size;
	
	if($sent_attach) {
		$attach_id = $post_id;
	} else {
	
		$attach_id 	= get_post_thumbnail_id($post_id);
		
		// Look for image attachments that aren't featured images
		if($attach_id === '') {
			$attachments = get_posts(Array(	'post_type' => 'attachment', 
											'numberposts' => -1,
											'post_status' => null,
											'post_parent' => $post_id));
			foreach($attachments as $attachment) {
				if( substr($attachment->post_mime_type, 0, strlen('image')) === 'image') {
					$attach_id = $attachment->ID;
					break;
				}
			}
		}
	
		// Look for a WordPress image link that is simply present in the story
		// (i.e. it was copied from some other story)
		if($attach_id === '') {
			$post = get_post($post_id);
			preg_match('/src="([^"]+(?:jpg|jpeg|png|gif))"/', $post->post_content, $matches);
			if($matches !== FALSE && count($matches) > 0 && ($img_url = parse_url($matches[1])) !== FALSE && !is_null($img_url['path'])) {
				
				$attach_id = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE guid LIKE '%%%s'", $img_url['path']));
			}
		}
	}
	
	$html = '';
	
	if(in_array($size, Array('story', 'category_feature'))) $size = 'thumbnail';
	
	if($attach_id != '') {
		
		$thumb		= wp_get_attachment_image_src($attach_id, $size);
		$element_id	= ($element_id != '') ? 'id="'.$element_id.'" ': '';
		
		$dims = Array('width' => $thumb[1], 'height' => $thumb[2]);
	
		$org_dims = $_wp_additional_image_sizes[$org_size];
		
		if(!is_null($org_dims) && ($dims['width'] > $org_dims['width'] || $dims['height'] > $org_dims['height'])) {
			$dims['width'] = $org_dims['width'];
			$dims['height'] = $org_dims['height'];
		}
		
	 	$html = (isset($thumb[0])) ? '<img src="'.$thumb[0].'" '.$element_id.'width="'.$dims['width'].'" height="'.$dims['height'].'" />' : '';
	} else if($org_size != 'story_feature') {
		$html = '<img src="'.get_bloginfo('stylesheet_directory').'/static/img/no-photo.png" '.$element_id.'width="95" height="91" />';
	}
	
	if($return_id) {
		return Array('html' => $html, 'attachment_id' => $attach_id);
	} else {
		return $html;
	}
}


/**
 * Wrapper around get_posts to take into account main_page meta, 
 * tag and category attributes.
 * 
 * TODO: wtf is this
 *
 * @return object or False
 * @author Chris Conover
 **/
function resolve_posts($atts, $args = Array(), $filters = True, $strip_tags = True)
{
	$home_page 	= (isset($atts['main_page'])) ? True : False;
	$tag		= (isset($atts['tag'])) ? $atts['tag'] : False;
	$category	= (isset($atts['category'])) ? $atts['category'] : False;
	
	$args = array_merge(Array(	'numberposts' => 1, 
								'post_type' => 'post'), $args);
	
	if($tag !== False) {
		$posts = get_posts(array_merge($args, Array('tag' => $tag)));
	} else if($category !== False) {
		$posts = get_posts(array_merge($args, Array('category_name' => $category)));
	} else if($home_page !== False) {
		$posts = get_posts(array_merge($args, Array('meta_query' => Array(
													Array(	'key' => $post_type.'_main_page',
															'value' => 'on'
													)
												)
										)));
	} 
	if(!isset($posts) || count($posts) == 0) {
		$posts = get_posts($args);
	}
	
	if($filters && $strip_tags) {
		array_walk($posts, create_function('&$p', 'return "";'));
	} else if($filters) {
		array_walk($posts, create_function('$p', 'return apply_filters("the_content", $p->post_content);'));
	} else if($strip_tags) {
		array_walk($posts, create_function('$p', 'return strip_tags("the_content", $p->post_content);'));
	}
	
	if($filters) {
		foreach($posts as $post) {
			$post->post_content = apply_filters('the_content', $post->post_content);
			$post->post_content = str_replace("]]>", "]]&gt;", $post->post_content);
		}
	}
	if($strip_tags) {
		foreach($posts as $post) {
			$post->post_content = strip_tags($post->post_content);
		}
	}
	
	return ($args['numberposts'] == 1) ? (count($posts) > 0) ? $posts[0] : False : $posts;
}


/**
 * Get the excerpt for a post.
 *
 * Attempts to grab an excerpt from stripped post content,
 * selected by a highlighted term, and append ellipses.
 * Falls back to the standard WP get_the_excerpt().
 *
 * @return string
 * @author Chris Conover
 **/
function get_excerpt($post, $hl_term = '')
{
	setup_postdata($post); // TODO: find this function in old theme
	
	if($hl_term != '') {
		$stripped_content = strip_tags($post->post_content);
		if( ($term_loc = stripos($stripped_content, $hl_term)) !== False) {
			// Get the actual term to preserve capitalization
			$hl_term = substr($stripped_content, $term_loc, strlen($hl_term));
			
			// Get an excerpt around the highligh term
			preg_match('/\b(?:[^\s]+\s+){0,20}'.preg_quote($hl_term).'\s?(?:[^\s]+\s+){0,20}/', $stripped_content, $matches);
			$excerpt = strip_tags(trim($matches[0]));
			
			// Place ellipses
			if(substr($post->post_content, 0, strlen($excerpt)) !== $excerpt) $excerpt = '...'.$excerpt;
			if(substr($post->post_content,strlen($excerpt) * -1) !== $excerpt) $excerpt = $excerpt.'...';
			
			return str_replace($hl_term, '<span class="highlight">'.$hl_term.'</span>', $excerpt);
		}
	}
	
	if($post->post_excerpt != '') {
		return strip_tags($post->post_excerpt);
	}
	return strip_tags(get_the_excerpt());
}


/**
 * Strip post content of <a><img></a> and [caption]???[/caption]
 *
 * @return string
 * @author Chris Conover
 **/
function strip_img_caption($content)
{
	$content = preg_replace('/\[caption[^\]]*\][^\[]+\[\/caption\]/', '', $content);	
	return $content;
}
add_filter('the_content', 'strip_img_caption');


/**
 * Check to see if the post has the mainsite tag associated with it
 * before it is edited. Used to check maintsite tag permissions after
 * the post is saved in check_mainsite_tag()
 *
 * @return void
 * @author Chris Conover
 **/
function mainsite_tag_exists($post_id)
{
	global $mainsite_tag_existed;
	
	$mainsite_tag_existed = False;
	$tags = wp_get_post_tags($post_id);
	
	foreach($tags as $tag) {
		if($tag->slug == MAINSITE_TAG_SLUG) {
			$mainsite_tag_existed = True;
		}
	}
}
add_filter('pre_post_update', 'mainsite_tag_exists');


/**
 * Only allow administrators or editors to add or remove the tag that
 * send stories to the main site (ucf.edu) defined by MAINSITE_TAG_SLUG
 *
 * @return void
 * @author Chris Conover
 **/
function check_mainsite_tag($post_id)
{
	global $current_user, $mainsite_tag_existed;
	
	$roles = $current_user->roles;
	
	$mainsite_tag = get_mainsite_tag();
	
	if($mainsite_tag !== FALSE) {
	
		$post_tags = wp_get_post_tags($post_id);
		$mainsite_tag_exists = False;
		foreach($post_tags as $tag) {
			if($tag->slug == MAINSITE_TAG_SLUG) {
				$mainsite_tag_exists = True;
				break;
			}
		}
		
		if(!in_array('administrator', $roles) && !in_array('editor', $roles)) {
			
			// Maintsite tag was added
			if($mainsite_tag_exists && !$mainsite_tag_existed) {
				// Remove it
				
				$new_tags = Array();
				foreach($post_tags as $tag) {
					if($tag->term_id != $mainsite_tag->term_id) {
						array_push($new_tags, $tag->name);
					}
				}
				wp_set_post_tags($post_id, implode(',', $new_tags));	
				wp_die('<div style="background-color:#FF0000;border:1px solid #FF0000;padding:15px;color: #FFF;">Only adminstrators or editors can add the Main Site Stories tag to a post.</div>');
			// Mainsite tag we removed
			} else if(!$mainsite_tag_exists && $mainsite_tag_existed) {
				// Add it
				wp_set_post_tags($post_id, $mainsite_tag->name, True);
				wp_die('<div style="background-color:#FF0000;border:1px solid #FF0000;padding:15px;color: #FFF;">Only adminstrators or editors can remove the Main Site Stories tag from a post.</div>');
			}
		}
	}
}
add_filter('save_post', 'check_mainsite_tag');


/**
 * Lookup the Main Site Stories tag object
 *
 * @return object
 * @author Chris Conover
 **/
function get_mainsite_tag()
{
	return get_term_by('slug', MAINSITE_TAG_SLUG, 'post_tag');
}


/*
 * Force images to be resized on upload based on 
 * Setings > Media > Large Size Restrictions
 *
 */
function media_upload_display_scale_option() {
	$checked = get_user_setting('upload_resize') ? ' checked="true"' : '';
	$a = $end = '';

	if ( current_user_can( 'manage_options' ) ) {
		$a = '<a href="' . esc_url( admin_url( 'options-media.php' ) ) . '" target="_blank">';
		$end = '</a>';
	}
?>
<p class="hide-if-no-js"><label>
<input name="image_resize" type="checkbox" id="image_resize" value="true" checked="checked" />
<?php
	print( __( 'Scale down large images' ) );
?>
</label></p>
<p class="hide-if-no-js">
<?php
	/* translators: %1$s is link start tag, %2$s is link end tag, %3$d is width, %4$d is height*/
	printf( __( 'If your images exceed the large-size dimensions saved in %1$smedia settings%2$s (%3$d &times; %4$d) they will be proportionally scaled down during upload.' ), $a, $end, (int) get_option( 'large_size_w', '1024' ), (int) get_option( 'large_size_h', '1024' ) );
?>
</p>
<?php
}

add_action( 'post-upload-ui', 'media_upload_display_scale_option' );


/*
 * Kill comments on attachments
 */
function filter_media_comment_status( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'filter_media_comment_status', 10 , 2 );


/**
 * Determine the title of the page <h1>, depending on content returned
 **/
function get_header_title() {
	$header_title = '<a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a>';

	global $wp_query;
	$post = $wp_query->queried_object;

	if(!is_search() || !is_home() || !is_404()) {
		if(is_category() || is_tag()) {
			$header_title = $post->name;
		} else if(is_single() && count($cats = wp_get_post_categories($post->ID)) > 0) {
			$header_title = get_cat_name($cats[0]);
		} else if(is_page() || is_single()) {
			if($post->post_type == 'photoset') {
				break;
			} else if($post->post_type == 'expert') {
				$header_title = 'Experts at UCF';
			} else if($post->post_type == 'video') {
				$header_title = 'Videos';
			} else if($post->post_type == 'profile') {
				$header_title = 'Profiles';
			} else {
				$header_title = $post->post_title;
			}	
		}
	}
	return $header_title;
}

?>