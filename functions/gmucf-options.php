<?php

/**
 * Adds the GMUCF Email options page if
 * ACF Pro is installed
 */
if ( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'GMUCF Email',
		'menu_title'	=> 'GMUCF Email',
		'menu_slug' 	=> 'gmucf-email',
		'capability'	=> 'editor',
		'icon_url'      => 'dashicons-email-alt',
		'redirect'      => false
	));

}
