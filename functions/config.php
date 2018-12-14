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
	add_theme_support( 'menus' );
	add_theme_support( 'post-thumbnails' );
	// Custom Image Sizes
	add_image_size( 'feature', 417, 343, True );
	add_image_size( 'story', 95, 91, True ); // also used for expert thumb
	add_image_size( 'subpage_feature', 469, 270, True );
	add_image_size( 'category_story', 167, 154, True );
	add_image_size( 'update', 308, 204, True );
	add_image_size( 'profile_img', 295, 367, True );
	add_image_size( 'story_feature', 548, 396, False );
	add_image_size( 'photoset_preview', 590, 443, True );
	add_image_size( 'photoset_thumb', 220, 203, True );
	add_image_size( 'alert', 47, 49, True );
	add_image_size( 'ucf_photo', 300, 230, False );
	add_image_size( 'ucf_photo_subpage', 380, 300, True );
	add_image_size( 'profile_feature', 230, 286, True );
	add_image_size( 'homepage', 66, 66, True );
	add_image_size( 'widget_60', 60, 60, True );
	add_image_size( 'widget_95', 95, 95, True );
	add_image_size( 'gmucf_top_story', 600 );
	add_image_size( 'gmucf_featured_story', 95, 95, True );
	register_nav_menu( 'social-links', __( 'Social Links' ) );

	// Widgets
	register_sidebar( array(
		'name'          => __( 'Primary Aside' ),
		'id'            => 'primary-aside',
		'description'   => 'The primary widget area, most often used as a sidebar.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	) );
	register_sidebar( array(
		'name'          => __( 'Secondary Aside' ),
		'id'            => 'secondary-aside',
		'description'   => 'Left column on the bottom of pages, after flickr images if enabled.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	) );
	register_sidebar( array(
		'name'          => __( '1st Subsidary Aside' ),
		'id'            => '1st-subsidiary-aside',
		'description'   => 'The 1st widget area in the footer.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
	) );
	foreach(Config::$styles as $style){Config::add_css($style);}
	foreach(Config::$scripts as $script){Config::add_script($script);}

	global $timer;
	$timer = Timer::start();

	wp_deregister_script('l10n');
	set_defaults_for_options();
}
add_action( 'after_setup_theme', '__init__' );



# Set theme constants
#define( 'DEBUG', True );                  # Always on
#define( 'DEBUG', False );                 # Always off
define( 'DEBUG', isset( $_GET['debug'] ) ); # Enable via getparameter
define( 'THEME_URL', get_stylesheet_directory_uri() );
define( 'THEME_ADMIN_URL', get_admin_url() );
define( 'THEME_DIR', get_stylesheet_directory() );
define( 'THEME_INCLUDES_DIR', THEME_DIR.'/includes' );
define( 'THEME_STATIC_URL', THEME_URL.'/static' );
define( 'THEME_IMG_URL', THEME_STATIC_URL.'/img' );
define( 'THEME_JS_URL', THEME_STATIC_URL.'/js' );
define( 'THEME_CSS_URL', THEME_STATIC_URL.'/css' );
define( 'THEME_OPTIONS_GROUP', 'settings' );
define( 'THEME_OPTIONS_NAME', 'theme' );
define( 'THEME_OPTIONS_PAGE_TITLE', 'Theme Options' );

$theme_options = get_option(THEME_OPTIONS_NAME);
define( 'GA_ACCOUNT', $theme_options['ga_account'] );
define( 'CB_UID', $theme_options['cb_uid'] );
define( 'CB_DOMAIN', $theme_options['cb_domain'] );

# Announcements
define( 'ANNOUNCE_DEFAULT', 'http://www.ucf.edu/announcements/?include_ongoing=0&output=json' );
define( 'ANNOUNCE_CACHE_DURATION', 60 * 5 ); // seconds

# Alerts
define( 'ALERT_COOKIE_NAME', 'ucf_today_alerts' );

# Mainsite Tag Checker
define( 'MAINSITE_TAG_SLUG', 'main-site-stories' );
$mainsite_tag_existed = False;

# Events
define( 'EVENTS_URL', 'http://events.ucf.edu' );
define( 'EVENTS_CALENDAR_ID', 1 );
define( 'EVENTS_CACHE_DURATION', 60 * 5 ); //seconds

# JSON feed retrieval timeout
define( 'FEED_FETCH_TIMEOUT', 5 ); //seconds

# Feed thumbnails
define( 'FEED_THUMBNAIL_FALLBACK', get_bloginfo( 'stylesheet_directory' ) . '/static/img/no-photo.png' );

# Protocol-agnostic URL schemes aren't supported before WP 3.5,
# so we have to determine the protocol before registering
# any non-relative resources.
define( 'CURRENT_PROTOCOL', is_ssl() ? 'https://' : 'http://' );

# ESI processing
define( 'ESI_INCLUDE_URL', THEME_STATIC_URL.'/esi.php' );

# Feed thumbnail default image
define( 'FEED_THUMBNAIL_FALLBACK', get_bloginfo( 'stylesheet_directory' ) . '/static/img/no-photo.png' );


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
	'ExternalStory'
);

Config::$custom_taxonomies = array(
	'Experts',
	'Groups',
	'Sources'
);

/**
 * Edge Side Includes (ESI) are directives that tell Varnish to include some other
 * content in the page. The primary use for use to assign another cache duration
 * to the "other content".
 * To add an ESI, first add some function and any safe-to-use arguments to the ESI
 * whitelist below, then call that function by referencing its key in the whitelist
 * and any arguments using esi_include($key, $args).
 * Functions that accept/require multiple arguments should be listed here with
 * serialized set(s) of arguments (so that they can be compared as a single string).
 * Example:
 * $key => array(
 * 		'name' => $functionname,
 * 		'safe_args' => array('somearg', 'anotherarg', serialize($arrayofargs),
 * )
 **/
Config::$esi_whitelist = array(
	2 => array(
		'name' => 'do_shortcode',
		'safe_args' => array( '[events]', '[events css="border-bottom"]' ),
	),
);

/**
 * Configure theme settings, see abstract class Field's descendants for
 * available fields. -- functions/base.php
 **/
Config::$theme_settings = array(
	'Analytics' => array(
		new TextField(array(
			'name'        => 'Google WebMaster Verification',
			'id'          => THEME_OPTIONS_NAME . '[gw_verify]',
			'description' => 'Example: <em>9Wsa3fspoaoRE8zx8COo48-GCMdi5Kd-1qFpQTTXSIw</em>',
			'default'     => null,
			'value'       => $theme_options['gw_verify'],
		)),
		new TextField(array(
			'name'        => 'Google Analytics Account',
			'id'          => THEME_OPTIONS_NAME . '[ga_account]',
			'description' => 'Example: <em>UA-9876543-21</em>. Leave blank for development.',
			'default'     => null,
			'value'       => $theme_options['ga_account'],
		)),
		new TextField(array(
			'name'        => 'Chartbeat UID',
			'id'          => THEME_OPTIONS_NAME . '[cb_uid]',
			'description' => 'Example: <em>1842</em>',
			'default'     => null,
			'value'       => $theme_options['cb_uid'],
		)),
		new TextField(array(
			'name'        => 'Chartbeat Domain',
			'id'          => THEME_OPTIONS_NAME . '[cb_domain]',
			'description' => 'Example: <em>some.domain.com</em>',
			'default'     => null,
			'value'       => $theme_options['cb_domain'],
		)),
	),
	'Events' => array(
		new SelectField(array(
			'name'        => 'Events Max Items',
			'id'          => THEME_OPTIONS_NAME . '[events_max_items]',
			'description' => 'Maximum number of events to display whenever outputting event information.',
			'value'       => $theme_options['events_max_items'],
			'default'     => 5,
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
			'id'          => THEME_OPTIONS_NAME . '[events_url]',
			'description' => 'Base URL for the calendar you wish to use. Example: <em>http://events.ucf.edu/mycalendar</em>',
			'value'       => $theme_options['events_url'],
			'default'     => 'http://events.ucf.edu',
		)),
	),
	'Search' => array(
		new RadioField(array(
			'name'        => 'Enable Google Search',
			'id'          => THEME_OPTIONS_NAME . '[enable_google]',
			'description' => 'Enable to use the google search appliance to power the search functionality.',
			'default'     => 0,
			'choices'     => array(
				'On'  => 1,
				'Off' => 0,
			),
			'value'       => $theme_options['enable_google'],
	    )),
		new TextField(array(
			'name'        => 'Search Domain',
			'id'          => THEME_OPTIONS_NAME . '[search_domain]',
			'description' => 'Domain to use for the built-in google search.  Useful for development or if the site needs to search a domain other than the one it occupies. Example: <em>some.domain.com</em>',
			'default'     => null,
			'value'       => $theme_options['search_domain'],
		)),
		new TextField(array(
			'name'        => 'Search Results Per Page',
			'id'          => THEME_OPTIONS_NAME . '[search_per_page]',
			'description' => 'Number of search results to show per page of results',
			'default'     => 10,
			'value'       => $theme_options['search_per_page'],
		)),
	),
	'Social' => array(
		new RadioField(array(
			'name'        => 'Enable OpenGraph',
			'id'          => THEME_OPTIONS_NAME . '[enable_og]',
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
			'id'          => THEME_OPTIONS_NAME . '[fb_admins]',
			'description' => 'Comma seperated facebook usernames or user ids of those responsible for administrating any facebook pages created from pages on this site. Example: <em>592952074, abe.lincoln</em>',
			'default'     => null,
			'value'       => $theme_options['fb_admins'],
		)),
		new TextField(array(
			'name'        => 'Facebook URL',
			'id'          => THEME_OPTIONS_NAME . '[facebook_url]',
			'description' => 'URL to the facebook page you would like to direct visitors to.  Example: <em>https://www.facebook.com/CSBrisketBus</em>',
			'default'     => null,
			'value'       => $theme_options['facebook_url'],
		)),
		new TextField(array(
			'name'        => 'Twitter URL',
			'id'          => THEME_OPTIONS_NAME . '[twitter_url]',
			'description' => 'URL to the twitter user account you would like to direct visitors to.  Example: <em>http://twitter.com/csbrisketbus</em>',
			'value'       => $theme_options['twitter_url'],
		)),
	),
	'Site' => array(
		new TextField(array(
			'name'        => 'Site Subtitle',
			'id'          => THEME_OPTIONS_NAME . '[site_subtitle]',
			'description' => 'Descriptive text to display next to the UCF Today logo in the site header.',
			'default'     => '',
			'value'       => $theme_options['site_subtitle'],
		)),
		new RadioField(array(
			'name' 		  => 'Enable Edge Side Includes (ESI)',
			'id' 		  => THEME_OPTIONS_NAME . '[enable_esi]',
			'description' => 'Replace specified content with Edge Side Includes (ESI) to be processed by Varnish.',
			'default' 	  => 0,
			'choices' 	  => array(
				'On' 	  => 1,
				'Off' 	  => 0,
			),
			'value' => $theme_options['enable_esi'],
		)),
	),
);

Config::$links = array(
	array( 'rel' => 'shortcut icon', 'href' => THEME_IMG_URL.'/favicon.ico', ),
	array( 'rel' => 'alternate', 'type' => 'application/rss+xml', 'href' => get_bloginfo('rss_url'), ),
);


Config::$styles = array(
	array( 'admin' => True, 'src' => THEME_CSS_URL.'/admin.min.css', ),
	CURRENT_PROTOCOL . 'universityheader.ucf.edu/bar/css/bar.css',
	'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'
);

array_push( Config::$styles,
	plugins_url( 'gravityforms/css/forms.css' ),
	THEME_CSS_URL . '/style.min.css',
	get_bloginfo( 'stylesheet_url' )
);

Config::$scripts = array(
	array( 'admin' => True, 'src' => THEME_JS_URL.'/admin.min.js', ),
	CURRENT_PROTOCOL.'universityheader.ucf.edu/bar/js/university-header.js?use-bootstrap-overrides=1',
	array( 'name' => 'autoellipsis',  'src' => THEME_JS_URL.'/jquery.autoellipsis-1.0.10.min.js', ),
	array( 'name' => 'jquery-cookie',  'src' => THEME_JS_URL.'/jquery-cookie.js', ),
	array( 'name' => 'matchheight', 'src' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js' ),
	array( 'name' => 'theme-script', 'src' => THEME_JS_URL.'/script.min.js', ),
);

Config::$metas = array(
	array( 'charset' => 'utf-8', ),
);
if ( $theme_options['gw_verify'] ) {
	Config::$metas[] = array(
		'name'    => 'google-site-verification',
		'content' => htmlentities( $theme_options['gw_verify'] ),
	);
}



function jquery_in_header() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', CURRENT_PROTOCOL.'ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' );
    wp_enqueue_script( 'jquery' );
    wp_register_script( 'jquery-textFit', THEME_JS_URL.'/jquery.textFit.min.js' );
    wp_enqueue_script( 'jquery-textFit' );
}

add_action( 'wp_enqueue_scripts', 'jquery_in_header' );
