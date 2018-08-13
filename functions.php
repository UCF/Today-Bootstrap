<?php
require_once('functions/base.php');   			# Base theme functions
require_once('functions/feeds.php');			# Where functions related to feed data live
require_once('custom-taxonomies.php');  		# Where per theme taxonomies are defined
require_once('custom-post-types.php');  		# Where per theme post types are defined
require_once('functions/admin.php');  			# Admin/login functions
require_once('functions/config.php');			# Where per theme settings are registered
require_once('shortcodes.php');         		# Per theme shortcodes

require_once('third-party/wp-rss-media.php');	# Add images and media tag to the RSS feed for the Widget


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
		$custom['section/(?:[^/]+/)?'.$before.'/feed/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag='.$after.'&feed=$matches[1]';
		$custom['section/(?:[^/]+/)?'.$before.'/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag='.$after.'&feed=$matches[1]';
		$custom['category/(?:[^/]+/)?'.$before.'/feed/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag='.$after.'&feed=$matches[1]';
		$custom['category/(?:[^/]+/)?'.$before.'/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag='.$after.'&feed=$matches[1]';
	}
	// Rewrite old category and tag pages
	$custom['category/(?:[^/]+/)?(.+?)/feed/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
	$custom['category/(?:[^/]+/)?(.+?)/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
	$custom['category/(?:[^/]+/)?(.+?)/?$'] = 'index.php?category_name=$matches[1]';

	$custom['section/(?:[^/]+/)?(.+?)/feed/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
	$custom['section/(?:[^/]+/)?(.+?)/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
	$custom['section/(?:[^/]+/)?(.+?)/?$'] = 'index.php?category_name=$matches[1]';

	$custom['tag/(?:[^/]+/)?(.+?)/feed/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag=$matches[1]&feed=$matches[2]';
	$custom['tag/(?:[^/]+/)?(.+?)/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag=$matches[1]&feed=$matches[2]';
	$custom['tag/(?:[^/]+/)?(.+?)/?$'] = 'index.php?tag=$matches[1]';

	$custom['topic/(?:[^/]+/)?(.+?)/feed/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag=$matches[1]&feed=$matches[2]';
	$custom['topic/(?:[^/]+/)?(.+?)/(feed|rdf|rss|rss2|atom|json)/?$'] = 'index.php?tag=$matches[1]&feed=$matches[2]';
	$custom['topic/(?:[^/]+/)?(.+?)/?$'] = 'index.php?tag=$matches[1]';

	return $custom;
}
function rewrite_rules_filter($rules){
	$custom = transition_rules();
	return $custom + $rules;
}
add_filter('rewrite_rules_array', 'rewrite_rules_filter');


/**
 * Display weather data.
 *
 * @return string
 * @author Jo Dickson
 **/
function output_weather_data() {
	return do_shortcode( '[ucf-weather feed="default" layout="today_nav"]' );
}


/**
 * Custom layout for the UCF Weather Shortcode plugin for
 * displaying weather data in the site header.
 */
function ucf_weather_default_today_nav( $data, $output ) {
	if ( !class_exists( 'UCF_Weather_Common' ) ) { return; }

	ob_start();
	$icon = UCF_Weather_Common::get_weather_icon( $data->condition );
?>
	<div class="weather weather-today-nav">
		<span class="weather-date"><?php echo date( 'l, F j, Y' ); ?></span>
		<span class="weather-status">
			<span class="weather-icon <?php echo $icon; ?>" aria-hidden="true"></span>
			<span class="weather-text">
				<span class="weather-temp"><?php echo $data->temp; ?>F</span>
				<span class="weather-condition"><?php echo $data->condition; ?></span>
			</span>
		</span>
	</div>
<?php
	return ob_get_clean();
}

add_filter( 'ucf_weather_default_today_nav', 'ucf_weather_default_today_nav', 10, 2 );


/**
 * Uses Wordpress's built-in embed shortcode to return
 * markup for a video embed by URL.
 * https://wordpress.org/support/topic/call-function-called-by-embed-shortcode-direct
 **/
function get_embed_html( $media_url ) {
	global $wp_embed;
	return $wp_embed->run_shortcode( '[embed]' . $media_url . '[/embed]' );
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

		if ($alerts) {
			$alert_html = '<div class="row" id="alerts"><ul class="span12">';

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


			 	$alert_html .= '<li style="'.implode( ' ', $li_inline_styles ).'" class="hidden '.implode( ' ', $css_clss ).'" id="alert-'.$alert->ID.'" data-post-modified="'.strtotime( $alert->post_modified ).'">
									<span class="msg" style="'.implode(' ', $span_inline_styles).'">
										'.$text.'
										'.$link_html.'
										<a class="close" alt="Close Alert" title="Close Alert" href="#">&times;</a>
									</span>
								</li>';
			}

			$alert_html .= '</ul></div>';
			$alerts_html .= $alert_html."\n";

		}
		return $alerts_html;
}


/**
 * IMG Tag HTML
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
	$img_alttext = get_the_title($post_id);

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

	 	$html = (isset($thumb[0])) ? '<img src="'.$thumb[0].'" '.$element_id.'width="'.$dims['width'].'" height="'.$dims['height'].'" alt="'.$img_alttext.'" />' : '';
	} else if($org_size != 'story_feature') {
		$html = '<img src="'.get_bloginfo('stylesheet_directory').'/static/img/no-photo.png" '.$element_id.' alt="'.$img_alttext.'" />';
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
 * @return object or False
 * @author Chris Conover
 **/
function resolve_posts($atts, $args = Array(), $filters = True, $strip_tags = True)
{
	$home_page 	= (isset($atts['main_page'])) ? True : False;
	$tag		= (isset($atts['tag'])) ? $atts['tag'] : False;
	$category	= (isset($atts['category'])) ? $atts['category'] : False;

	$args = array_merge(Array(	'numberposts' => 1,
								'offset' => 0,
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
	setup_postdata($post);

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


/**
 * Returns a theme option value or NULL if it doesn't exist
 **/
function get_theme_option($key) {
	global $theme_options;
	return isset($theme_options[$key]) ? $theme_options[$key] : NULL;
}


/**
 * Determine the title of the page <h1>, depending on content returned
 *
 * @return string
 **/
function get_header_title( $elem='' ) {
	if ( !$elem ) {
		$elem = ( is_home() || is_front_page() ) ? 'h1' : 'span';
	}
	ob_start();
?>
	<<?php echo $elem; ?> class="site-title">
		<a href="<?php echo get_bloginfo( 'url' ); ?>">
			<img class="site-logo" src="<?php echo THEME_IMG_URL . '/ucftoday4.png'; ?>" alt="<?php echo get_bloginfo( 'name' ); ?>">
		</a>
	</<?php echo $elem; ?>>
<?php
	return ob_get_clean();
}


/**
 * Determine whether the site's expandable nav toggle should be disabled
 * at the -md breakpoint (and force the site's primary navigation to be
 * visible) depending on the current view.
 *
 * @author Jo Dickson
 * @since 2.3.0
 * @return bool
 */
function disable_md_nav_toggle() {
	return is_home() || is_front_page() || is_category() || is_tag();
}


/**
 * Alternative to base.php body_classes() so we can use functions
 * like is_404() and is_home() to determine the current page
 *
 * @return string
 * @author Jo Dickson
 **/
function today_body_classes() {
	global $post;
	$classes = '';

	if ( disable_md_nav_toggle() ) {
		$classes .= 'disable-md-navbar-toggle ';
	}

	if (is_home()) {
		$classes .= 'body-home ';
	}
	elseif (is_404()) {
		$classes .= 'body-404 ';
	}
	elseif (is_search()) {
		$classes .= 'body-search ';
	}
	elseif ($post->post_type == 'photoset') {
		$classes .= 'body-photoset ';
	}
	elseif ( get_page_template_slug( $post ) == 'featured-single-post.php' ) {
		$classes .= 'body-feature ';
	}
	else {
		$classes .= 'body-subpage ';
	}
	return $classes;
}


/**
 * Wrapper for get_posts() that specifies a search param.
 * Accepts a post type slug as an argument, as well as
 * any standard get_posts() arguments.
 *
 * @return array
 * @author Jo Dickson
 **/
function get_posts_search($query='', $post_type='post', $extra_args=array()) {
	$args = array(
		'post_type' => $post_type,
		'numberposts' => -1,
		's'			=> $query,
	);
	if ($extra_args) {
		array_merge($args, $extra_args);
	}
	return get_posts($args);
}


/**
 * Displays social buttons (Facebook, Twitter, G+) for a post.
 * Accepts a post URL and title as arguments.
 *
 * @return string
 * @author Jo Dickson
 **/
function display_social( $url, $title, $layout='default' ) {
	$share_text = 'UCF Today: ' . $title;
	return do_shortcode( '[ucf-social-links layout="'. $layout .'" permalink="'. $url .'" share_text="'. $share_text .'"]' );
}


/**
 * Prevent Wordpress from trying to redirect to a "loose match" post when
 * an invalid URL is requested. WordPress will redirect to 404.php instead.
 *
 * Implemented to prevent some print views from redirecting to random
 * attachments.
 *
 * See http://wordpress.stackexchange.com/questions/3326/301-redirect-instead-of-404-when-url-is-a-prefix-of-a-post-or-page-name
 **/
function no_redirect_on_404($redirect_url) {
    if (is_404()) {
        return false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'no_redirect_on_404');


/**
 * Kill attachment, author, and daily archive pages.
 *
 * http://betterwp.net/wordpress-tips/disable-some-wordpress-pages/
 **/
function kill_unused_templates() {
	global $wp_query, $post;

	if (is_author() || is_attachment() || is_day()) {
		wp_redirect(home_url());
	}

	if (is_feed()) {
		$author = get_query_var('author_name');
		$attachment = get_query_var('attachment');
		$attachment = (empty($attachment)) ? get_query_var('attachment_id') : $attachment;
		$day = get_query_var('day');

		if (!empty($author) || !empty($attachment) || !empty($day)) {
			wp_redirect(home_url());
			$wp_query->is_feed = false;
		}
	}
}
add_action('template_redirect', 'kill_unused_templates');


/**
 * Kill comments on attachments
 **/
function filter_media_comment_status( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'filter_media_comment_status', 10 , 2 );


/**
 * Wrap a statement in a ESI include tag with a specified duration if the
 * enable_esi theme option is enabled.
 **/
function esi_include($statementname, $argset=null, $print_results=false) {
	if (!$statementname) { return null; }

	// Get the statement key
	$statementkey = null;
	foreach (Config::$esi_whitelist as $key=>$function) {
		if ($function['name'] == $statementname) { $statementkey = $key;}
	}
	if (!$statementkey) { return null; }

	// Never include ESI over HTTPS
	$enable_esi = get_theme_option('enable_esi');
	if(!is_null($enable_esi) && $enable_esi === '1' && is_ssl() == false) {
		$argset = ($argset !== null) ? $argset = '&args='.urlencode(base64_encode($argset)) : '';
		$print_results = ($print_results === false) ? '0' : '1'; // whether or not to print results instead of return
		?>
		<esi:include src="<?php echo ESI_INCLUDE_URL?>?print_results=<?=$print_results?>&statement=<?=$statementkey?><?=$argset?>" />
		<?php
	} elseif (array_key_exists($statementkey, Config::$esi_whitelist)) {
		$statementname = Config::$esi_whitelist[$statementkey]['name'];
		$statementargs = Config::$esi_whitelist[$statementkey]['safe_args'];
		// If no safe arguments are defined in the whitelist for this statement,
		// run call_user_func(); otherwise check arguments and run call_user_func_array()
		if (!is_array($statementargs) || $argset == null) {
			if ($print_results) {
				print call_user_func($statementname);
			}
			else {
				return call_user_func($statementname);
			}
		}
		else {
			// Convert argset arrays to strings for easy comparison with our whitelist
			$argset = is_array($argset) ? serialize($argset) : $argset;
			if ($argset !== null && in_array($argset, $statementargs)) {
				$argset = (unserialize($argset) !== false) ? unserialize($argset) : array($argset);

				if ($print_results) {
					print call_user_func_array($statementname, $argset);
				}
				else {
					return call_user_func_array($statementname, $argset);
				}
			}
		}
	}
	else {
		return null;
	}
}

/*
 * Filter based to get all the post within the month/year
 */
function filter_archive_date_range( $where = '' ) {
    $monthYear = _getArchiveMonthYear();

	$where .= " AND post_date <= '" . date('Y-m-t', strtotime($monthYear["year"] . '-' . $monthYear["mon"] . '-1')) .
		"' AND post_date >= '" . date('Y-m-d', strtotime($monthYear["year"] . '-' . $monthYear["mon"] . '-1')) . "'";
	return $where;
}

/*
 * Returns the 'video_url' meta value for a video post type
 * as a protocol-agnostic URL.
 */
function get_video_url($video_ID){
	$video_url = get_post_meta($video_ID, 'video_url', True);
	if (!empty($video_url)) {
		$video_url = preg_replace('/^http(s)?:\/\//', CURRENT_PROTOCOL, $video_url);
	}
	else {
		$video_url = '';
	}
	return $video_url;
}

/*
 * Force protocol-agnostic oembeds
 * http://wordpress.stackexchange.com/a/113550
 */
function protocol_relative_oembed($html) {
    return preg_replace('@src="https?:@', 'src="', $html);
}
add_filter('embed_oembed_html', 'protocol_relative_oembed');

/*
 * Add responsive container to YouTube embeds
 */
function video_embed_html( $html, $url ) {
	if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
		return '<div class="video-container">' . $html . '</div>';
	} else {
		return $html;
	}
}
add_filter( 'embed_oembed_html', 'video_embed_html', 10, 3 );

/*
 * Force an exact crop of an image; bypassing wordpress's default
 * cropping settings which do not upscale small images.
 * http://wordpress.stackexchange.com/a/64953
 */
function image_crop_dimensions($default, $orig_w, $orig_h, $new_w, $new_h, $crop){
    if ( !$crop ) return null; // let the wordpress default function handle this

    $aspect_ratio = $orig_w / $orig_h;
    $size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

    $crop_w = round($new_w / $size_ratio);
    $crop_h = round($new_h / $size_ratio);

    $s_x = floor( ($orig_w - $crop_w) / 2 );
    $s_y = floor( ($orig_h - $crop_h) / 2 );

    return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
}
add_filter('image_resize_dimensions', 'image_crop_dimensions', 10, 6);

/**
 * Add ID attribute to registered University Header script.
 **/
function add_id_to_ucfhb($url) {
    if ( (false !== strpos($url, 'bar/js/university-header.js')) || (false !== strpos($url, 'bar/js/university-header-full.js')) ) {
      remove_filter('clean_url', 'add_id_to_ucfhb', 10, 3);
      return "$url' id='ucfhb-script";
    }
    return $url;
}
add_filter('clean_url', 'add_id_to_ucfhb', 10, 3);

class UCF_Feed_JSON {

	public $feed = 'json';

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_feed( $this->feed, array( $this, 'do_json_feed' ) );
		flush_rewrite_rules();
	}

	public function do_json_feed() {
		load_template( $this->template_json( dirname( __FILE__ ) . '/feed-json.php' ) );
	}

	public function template_json( $template ) {
		return apply_filters( 'feed-json-template-file', $template );
	}
}

$ucf_feed_json = new UCF_Feed_JSON();

function post_get_thumbnail( $object, $field_name, $request ) {
	$image = null;

	if ( $object['featured_media'] ) {
		$image = wp_get_attachment_image_src( $object['featured_media'] );
	} else {
		$attachments = get_attached_media( 'image', $object['id'] );
		if ( $attachments ) {
			foreach( $attachments as $key=>$val ) {
				if ( $image = wp_get_attachment_image_src( $key ) ) {
					break;
				}
			}
		}
	}

	return is_array( $image ) ? $image[0] : null;
}

function add_image_to_post_feed(  ) {
	register_rest_field( 'post', 'thumbnail',
		array(
			'get_callback' => 'post_get_thumbnail',
			'schema'       => null,
		)
	);
}

add_action( 'rest_api_init', 'add_image_to_post_feed' );

function add_tax_query_to_posts_endpoint( $args, $request ) {
	$params = $request->get_params();

	$tax_query = array();

	if ( isset( $params['category_slugs'] ) ) {
		$tax_query[] =
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $params['category_slugs']
			);
	}

	if ( isset( $params['tag_slugs'] ) ) {
		$tax_query[] =
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $params['tag_slugs']
			);
	}

	if ( count( $tax_query ) > 0 ) {
		$args['tax_query'] = $tax_query;
	}

	return $args;
}

add_action( 'rest_post_query', 'add_tax_query_to_posts_endpoint', 2, 10 );

/**
 * Returns an array of post objects related to the passed in post
 * @author Jim Barnes
 * @param WP_Post $post The post object
 * @return array The array of related posts
 */
function get_more_stories( $post ) {
	$primary_tag = get_post_meta( $post->ID, 'primary_tag', TRUE );

	if ( ! $primary_tag ) {
		$tags = wp_get_post_tags($post->ID);
		if ( count( $tags ) > 0 ) {
			$primary_tag = $tags[0];
		} else {
			return array();
		}
	}

	$args = array(
		'tag_id'      => $primary_tag,
		'numberposts' => 8,
		'exclude'     => array( $post->ID )
	);

	$stories = get_posts( $args );

	return $stories;
}

/**
 * Displays more stories based on primary or first tag
 * @author Jim Barnes
 * @param WP_Post $post The post object
 * @return string
 */
function display_more_stories_featured( $post ) {
	$stories = get_more_stories( $post );

	if ( ! is_array( $stories ) || count( $stories ) === 0 ) {
		return '';
	}

	ob_start();
?>
	<div class="related-stories row-fluid">
<?php foreach( $stories as $story ) : ?>
		<?php echo display_related_story( $story ); ?>
<?php endforeach; ?>
	</div>
<?php
	return ob_get_clean();
}

function display_related_story( $story ) {
	$thumbnail = get_the_post_thumbnail_url( $story->ID, 'post-thumbnail', array( 'class' => 'img-responsive' ) );
	$thumbnail = $thumbnail ?: FEED_THUMBNAIL_FALLBACK;
	ob_start();
?>
	<div class="span3 match-height">
	<a class="related-story" href="<?php echo get_permalink( $story->ID ); ?>">
		<div class="related-story-image" style="background-image: url( '<?php echo $thumbnail; ?>' );"></div>
		<p class="h2 related-story-title"><?php echo $story->post_title; ?></p>
	</a>
	</div>
<?php
	return ob_get_clean ();
}

/**
* Replaces RSS description element content with a post's promo field if available.
* If promo field is empty, the content is truncated to 30 words
*
* @author Cadie Brown
* @return string
*/
function update_rss_description_to_promo( $content ) {
	global $post;
	$promo_value = get_post_meta($post->ID, 'promo', true);

	if (has_tag('Main Site Stories') && !empty($promo_value)) {
		return $promo_value;
	} else {
		$parts = explode(' ', $content, 30);
		return implode(' ', array_slice($parts, 0, count($parts) - 1)).'...';
	}
}
add_action('the_excerpt_rss', 'update_rss_description_to_promo');


/**
 * Custom layout for content displayed before social links
 * @author Jo Dickson
 * @since 2.3.0
 * @param array $atts | shortcode attributes
 * @return String
 **/
if ( ! function_exists( 'ucf_social_links_display_affixed_before' ) ) {
	function ucf_social_links_display_affixed_before( $content='', $atts ) {
		ob_start();
	?>
		<aside class="ucf-social-links ucf-social-links-affixed">
	<?php
		return ob_get_clean();
	}
}

add_filter( 'ucf_social_links_display_affixed_before', 'ucf_social_links_display_affixed_before', 10, 2 );


/**
 * Displays 'NewsArticle' schema
 * @author Cadie Brown
 * @param WP_Post $post The post object
 * @return string
 */
 function display_news_schema( $post ) {
	$post_promo = get_post_meta($post->ID, 'promo', true);
	$excerpt = get_excerpt($post);
	$thumbnail = get_the_post_thumbnail_url( $post->ID, 'medium' );
	$thumbnail = $thumbnail ?: FEED_THUMBNAIL_FALLBACK;
	$description = !empty($post_promo) ? $post_promo : $excerpt;
	ob_start();
 ?>
	<script type="application/ld+json">
		{
		"@context": "http://schema.org",
		"@type": "NewsArticle",
		"mainEntityOfPage": {
			"@type": "WebPage",
			"@id": "<?php echo site_url(); ?>"
		},
		"headline": "<?php echo htmlspecialchars( the_title(), ENT_QUOTES ); ?>",
		"image": [
			"<?php echo $thumbnail; ?>"
		],
		"datePublished": "<?php echo get_the_date(DATE_ISO8601); ?>",
		"dateModified": "<?php echo get_the_modified_date(DATE_ISO8601); ?>",
		"author": {
			"@type": "Person",
			"name": "<?php echo htmlspecialchars( get_the_author(), ENT_QUOTES ); ?>"
		},
		"publisher": {
			"@type": "Organization",
			"name": "University of Central Florida",
			"logo": {
				"@type": "ImageObject",
				"url": "<?php echo THEME_IMG_URL; ?>/ucftoday4_small.png"
			}
		},
		"description": "<?php echo htmlspecialchars( $description, ENT_QUOTES ); ?>"
		}
	</script>
<?php
	return ob_get_clean ();
}
