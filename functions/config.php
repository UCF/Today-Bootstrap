<?php

/**
 * Responsible for running code that needs to be executed as wordpress is
 * initializing.  Good place to register scripts, stylesheets, theme elements,
 * etc.
 * 
 * @return void
 * @author Jared Lang
 **/
function __init__(){
	add_theme_support('menus');
	add_theme_support('post-thumbnails');
	// Custom Image Sizes
	add_image_size('feature', 417, 343, True);
	add_image_size('story', 95, 91, True); // also used for expert thumb
	add_image_size('subpage_feature', 469, 270, True);
	add_image_size('category_story', 167, 154, True);
	add_image_size('update', 308, 204, True);
	add_image_size('profile_img', 295, 367, True);
	add_image_size('story_feature', 548, 396, False);
	add_image_size('photoset_preview', 590, 443, True); // unknown
	add_image_size('alert', 47, 49, True);
	add_image_size('ucf_photo', 300, 230, False);
	add_image_size('ucf_photo_subpage', 380, 300, True);
	add_image_size('profile_feature', 230, 286, True);
	add_image_size('homepage', 66, 66, True);
	// Widgets
	register_sidebar(array(
		'name'          => __('Primary Aside'),
		'id'            => 'primary-aside',
		'description'   => 'The primary widget area, most often used as a sidebar.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	));
	register_sidebar(array(
		'name'          => __('Secondary Aside'),
		'id'            => 'secondary-aside',
		'description'   => 'Left column on the bottom of pages, after flickr images if enabled.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	));
	register_sidebar(array(
		'name'          => __('1st Subsidary Aside'),
		'id'            => '1st-subsidiary-aside',
		'description'   => 'The 1st widget area in the footer.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	));
	register_sidebar(array(
		'name'          => __('2nd Subsidary Aside'),
		'id'            => '2nd-subsidiary-aside',
		'description'   => 'The 2nd widget area in the footer.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	));
	register_sidebar(array(
		'name'          => __('3rd Subsidary Aside'),
		'id'            => '3rd-subsidiary-aside',
		'description'   => 'The 3rd widget area in the footer.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	));
	foreach(Config::$styles as $style){Config::add_css($style);}
	foreach(Config::$scripts as $script){Config::add_script($script);}
	
	global $timer;
	$timer = Timer::start();
	
	wp_deregister_script('l10n');
	set_defaults_for_options();
}
add_action('after_setup_theme', '__init__');



# Set theme constants
#define('DEBUG', True);                  # Always on
#define('DEBUG', False);                 # Always off
define('DEBUG', isset($_GET['debug'])); # Enable via get parameter
define('THEME_URL', get_bloginfo('stylesheet_directory'));
define('THEME_ADMIN_URL', get_admin_url());
define('THEME_DIR', get_stylesheet_directory());
define('THEME_INCLUDES_DIR', THEME_DIR.'/includes');
define('THEME_STATIC_URL', THEME_URL.'/static');
define('THEME_IMG_URL', THEME_STATIC_URL.'/img');
define('THEME_JS_URL', THEME_STATIC_URL.'/js');
define('THEME_CSS_URL', THEME_STATIC_URL.'/css');
define('THEME_OPTIONS_GROUP', 'settings');
define('THEME_OPTIONS_NAME', 'theme');
define('THEME_OPTIONS_PAGE_TITLE', 'Theme Options');

$theme_options = get_option(THEME_OPTIONS_NAME);
define('GA_ACCOUNT', $theme_options['ga_account']);
define('CB_UID', $theme_options['cb_uid']);
define('CB_DOMAIN', $theme_options['cb_domain']);

# Weather
define('WEATHER_URL', 'http://weather.smca.ucf.edu/');
define('WEATHER_CLICK_URL', 'http://www.weather.com/weather/today/Orlando+FL+32816');
define('WEATHER_CACHE_DURATION', 60 * 5); //seconds
define('WEATHER_FETCH_TIMEOUT', 8); //seconds

# Announcemets
define('ANNOUNCE_DEFAULT', 'http://www.ucf.edu/feeds/announcement/');
define('ANNOUNCE_STUDENT', 'http://www.ucf.edu/feeds/announcement/student.php');
define('ANNOUNCE_STAFF', 'http://www.ucf.edu/feeds/announcement/staff.php');
define('ANNOUNCE_ALUMNI', 'http://www.ucf.edu/feeds/announcement/alumni.php');
define('ANNOUNCE_PUBLIC', 'http://www.ucf.edu/feeds/announcement/public.php');
define('ANNOUNCE_CACHE_DURATION', 60 * 2); // seconds

define('ALERT_COOKIE_NAME', 'ucf_today_alerts');

# Mainsite Tag Checker
define('MAINSITE_TAG_SLUG', 'main-site-stories');
$mainsite_tag_existed = False;

define('EVENTS_URL', 'http://events.ucf.edu');
define('EVENTS_CALENDAR_ID', 1);
define('EVENTS_CACHE_DURATION', 60 * 5); //seconds
define('EVENTS_HTTP_TIMEOUT', 1); //seconds



/**
 * Set config values including meta tags, registered custom post types, styles,
 * scripts, and any other statically defined assets that belong in the Config
 * object.
 **/
Config::$custom_post_types = array(
	'Alert',
	'Post',
	'Expert',
	'PhotoSet',
	'Video',
	'Profile',
	'Update',
	'ExternalStory'
);

Config::$custom_taxonomies = array(
	'Experts',
	'Groups'
);

Config::$body_classes = array('default',);

/**
 * Configure theme settings, see abstract class Field's descendants for
 * available fields. -- functions/base.php
 **/
Config::$theme_settings = array(
	'Analytics' => array(
		new TextField(array(
			'name'        => 'Google WebMaster Verification',
			'id'          => THEME_OPTIONS_NAME.'[gw_verify]',
			'description' => 'Example: <em>9Wsa3fspoaoRE8zx8COo48-GCMdi5Kd-1qFpQTTXSIw</em>',
			'default'     => null,
			'value'       => $theme_options['gw_verify'],
		)),
		new TextField(array(
			'name'        => 'Google Analytics Account',
			'id'          => THEME_OPTIONS_NAME.'[ga_account]',
			'description' => 'Example: <em>UA-9876543-21</em>. Leave blank for development.',
			'default'     => null,
			'value'       => $theme_options['ga_account'],
		)),
	),
	'Events' => array(
		new RadioField(array(
			'name'        => 'Enable Events Below the Fold',
			'id'          => THEME_OPTIONS_NAME.'[enable_events]',
			'description' => 'Display events in the bottom page content, appearing on most pages.',
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['enable_events'],
		)),
		new RadioField(array(
			'name'        => 'Enable Events on Search Page',
			'id'          => THEME_OPTIONS_NAME.'[enable_search_events]',
			'description' => 'Display events on the search results page.',
			'value'       => $theme_options['enable_search_events'],
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
		)),
		new SelectField(array(
			'name'        => 'Events Max Items',
			'id'          => THEME_OPTIONS_NAME.'[events_max_items]',
			'description' => 'Maximum number of events to display whenever outputting event information.',
			'value'       => $theme_options['events_max_items'],
			'default'     => 4,
			'choices'     => array(
				'1' => 1,
				'2' => 2,
				'3' => 3,
				'4' => 4,
				'5' => 5,
			),
		)),
		new TextField(array(
			'name'        => 'Events Calendar URL',
			'id'          => THEME_OPTIONS_NAME.'[events_url]',
			'description' => 'Base URL for the calendar you wish to use. Example: <em>http://events.ucf.edu/mycalendar</em>',
			'value'       => $theme_options['events_url'],
			'default'     => 'http://events.ucf.edu',
		)),
	),
	'News' => array(
		new RadioField(array(
			'name'        => 'Enable News Below the Fold',
			'id'          => THEME_OPTIONS_NAME.'[enable_news]',
			'description' => 'Display UCF Today news in the bottom page content, appearing on most pages.',
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['enable_news'],
		)),
		new SelectField(array(
			'name'        => 'News Max Items',
			'id'          => THEME_OPTIONS_NAME.'[news_max_items]',
			'description' => 'Maximum number of articles to display when outputting news information.',
			'value'       => $theme_options['news_max_items'],
			'default'     => 2,
			'choices'     => array(
				'1' => 1,
				'2' => 2,
				'3' => 3,
				'4' => 4,
				'5' => 5,
			),
		)),
		new TextField(array(
			'name'        => 'News Feed',
			'id'          => THEME_OPTIONS_NAME.'[news_url]',
			'description' => 'Use the following URL for the news RSS feed <br />Example: <em>http://today.ucf.edu/feed/</em>',
			'value'       => $theme_options['news_url'],
			'default'     => 'http://today.ucf.edu/feed/',
		)),
	),
	'Search' => array(
		new RadioField(array(
			'name'        => 'Enable Google Search',
			'id'          => THEME_OPTIONS_NAME.'[enable_google]',
			'description' => 'Enable to use the google search appliance to power the search functionality.',
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['enable_google'],
	    )),
		new TextField(array(
			'name'        => 'Search Domain',
			'id'          => THEME_OPTIONS_NAME.'[search_domain]',
			'description' => 'Domain to use for the built-in google search.  Useful for development or if the site needs to search a domain other than the one it occupies. Example: <em>some.domain.com</em>',
			'default'     => null,
			'value'       => $theme_options['search_domain'],
		)),
		new TextField(array(
			'name'        => 'Search Results Per Page',
			'id'          => THEME_OPTIONS_NAME.'[search_per_page]',
			'description' => 'Number of search results to show per page of results',
			'default'     => 10,
			'value'       => $theme_options['search_per_page'],
		)),
	),
	'Site' => array(
		new TextField(array(
			'name'        => 'Contact Email',
			'id'          => THEME_OPTIONS_NAME.'[site_contact]',
			'description' => 'Contact email address that visitors to your site can use to contact you.',
			'value'       => $theme_options['site_contact'],
		)),
		new TextField(array(
			'name'        => 'Organization Name',
			'id'          => THEME_OPTIONS_NAME.'[organization_name]',
			'description' => 'Your organization\'s name',
			'value'       => $theme_options['organization_name'],
		)),
		new SelectField(array(
			'name'        => 'Home Image',
			'id'          => THEME_OPTIONS_NAME.'[site_image]',
			'description' => 'Image to feature on the homepage.  Select any image uploaded to the <a href="'.get_admin_url().'upload.php">media gallery</a> or <a href="'.get_admin_url().'media-new.php">upload a new image</a>.',
			'choices'     => get_image_choices(),
			'value'       => $theme_options['site_image'],
		)),
		new TextareaField(array(
			'name'        => 'Site Description',
			'id'          => THEME_OPTIONS_NAME.'[site_description]',
			'description' => 'A quick description of your organization and its role.',
			'default'     => 'This is the site\'s default description, change or remove it on the <a href="'.get_admin_url().'admin.php?page=theme-options#site">theme options page</a> in the admin site.',
			'value'       => $theme_options['site_description'],
		)),
	),
	'Social' => array(
		new RadioField(array(
			'name'        => 'Enable OpenGraph',
			'id'          => THEME_OPTIONS_NAME.'[enable_og]',
			'description' => 'Turn on the opengraph meta information used by Facebook.',
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['enable_og'],
	    )),
		new TextField(array(
			'name'        => 'Facebook Admins',
			'id'          => THEME_OPTIONS_NAME.'[fb_admins]',
			'description' => 'Comma seperated facebook usernames or user ids of those responsible for administrating any facebook pages created from pages on this site. Example: <em>592952074, abe.lincoln</em>',
			'default'     => null,
			'value'       => $theme_options['fb_admins'],
		)),
		new TextField(array(
			'name'        => 'Facebook URL',
			'id'          => THEME_OPTIONS_NAME.'[facebook_url]',
			'description' => 'URL to the facebook page you would like to direct visitors to.  Example: <em>https://www.facebook.com/CSBrisketBus</em>',
			'default'     => null,
			'value'       => $theme_options['facebook_url'],
		)),
		new TextField(array(
			'name'        => 'Twitter URL',
			'id'          => THEME_OPTIONS_NAME.'[twitter_url]',
			'description' => 'URL to the twitter user account you would like to direct visitors to.  Example: <em>http://twitter.com/csbrisketbus</em>',
			'value'       => $theme_options['twitter_url'],
		)),
		new RadioField(array(
			'name'        => 'Enable Flickr',
			'id'          => THEME_OPTIONS_NAME.'[enable_flickr]',
			'description' => 'Automatically display flickr images throughout the site',
			'default'     => 1,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['enable_flickr'],
		)),
		new TextField(array(
			'name'        => 'Flickr Photostream ID',
			'id'          => THEME_OPTIONS_NAME.'[flickr_id]',
			'description' => 'ID of the flickr photostream you would like to show pictures from.  Example: <em>65412398@N05</em>',
			'default'     => '36226710@N08',
			'value'       => $theme_options['flickr_id'],
		)),
		new SelectField(array(
			'name'        => 'Flickr Max Images',
			'id'          => THEME_OPTIONS_NAME.'[flickr_max_items]',
			'description' => 'Maximum number of flickr images to display',
			'value'       => $theme_options['flickr_max_items'],
			'default'     => 12,
			'choices'     => array(
				'6'  => 6,
				'12' => 12,
				'18' => 18,
			),
		)),
	),
);

Config::$links = array(
	array('rel' => 'shortcut icon', 'href' => THEME_IMG_URL.'/favicon.ico',),
	array('rel' => 'alternate', 'type' => 'application/rss+xml', 'href' => get_bloginfo('rss_url'),),
);


Config::$styles = array(
	array('admin' => True, 'src' => THEME_CSS_URL.'/admin.css',),
	'http://universityheader.ucf.edu/bar/css/bar.css',
	THEME_STATIC_URL.'/bootstrap/bootstrap/css/bootstrap.css',
);

array_push(Config::$styles,	
	plugins_url( 'gravityforms/css/forms.css' ),
	THEME_CSS_URL.'/webcom-base.css', 
	get_bloginfo('stylesheet_url')
);

Config::$scripts = array(
	array('admin' => True, 'src' => THEME_JS_URL.'/admin.js',),
	'http://universityheader.ucf.edu/bar/js/university-header.js',
	THEME_STATIC_URL.'/bootstrap/bootstrap/js/bootstrap.js',
	array('name' => 'base-script',  'src' => THEME_JS_URL.'/webcom-base.js',),
	array('name' => 'ellipsizer',  'src' => THEME_JS_URL.'/ellipsizer.js',),
	array('name' => 'theme-script', 'src' => THEME_JS_URL.'/script.js',),
);

Config::$metas = array(
	array('charset' => 'utf-8',),
);
if ($theme_options['gw_verify']){
	Config::$metas[] = array(
		'name'    => 'google-site-verification',
		'content' => htmlentities($theme_options['gw_verify']),
	);
}



function jquery_in_header() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://code.jquery.com/jquery-1.7.1.min.js');
    wp_enqueue_script( 'jquery' );
}    
 
add_action('wp_enqueue_scripts', 'jquery_in_header');