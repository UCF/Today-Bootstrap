<?php

/**
 * Adds the GMUCF Email options page if
 * ACF Pro is installed
 */
if ( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	  => 'GMUCF Email',
		'post_id'         => 'gmucf_options',
		'menu_title'	  => 'GMUCF Email',
		'menu_slug' 	  => 'gmucf-email',
		'capability'	  => 'editor',
		'icon_url'        => 'dashicons-email-alt',
		'redirect'        => false,
		'updated_message' => 'GMUCF Options Updated'
	));

}

/**
 * Sets the default values for each stories image, title and
 * description for use in the GMUCF Today emails
 * @since 2.9.0
 * @author Cadie Brown
 * @param Array $stories | Stories array from ACF GMUCF Options Page
 * @return Array
 */
function gmucf_stories_default_values( $stories ) {
	foreach ( $stories as $story ) {
		if ( $story['acf_fc_layout'] == 'gmucf_top_story' || $story['acf_fc_layout'] == 'gmucf_featured_story' ) {
			$post_id = $story['gmucf_story'];

			if ( ! $story['gmucf_story_image'] ) {
				$story['gmucf_story_image'] = get_the_post_thumbnail_url( $post_id, 'gmucf_top_story' );
			}

			if ( ! $story['gmucf_story_title'] ) {
				$story['gmucf_story_title'] = get_the_title( $post_id );
			}

			if ( ! $story['gmucf_story_description'] ) {
				$story['gmucf_story_description'] = get_post_meta( $post_id, 'promo', true );
			}

			$retval[] = $story;
		} else {
			$retval[] = $story;
		}
	}

	return $retval;
}
