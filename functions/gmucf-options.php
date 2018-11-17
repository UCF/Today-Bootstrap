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
		'capability'	  => 'administrator',
		'icon_url'        => 'dashicons-email-alt',
		'redirect'        => false,
		'updated_message' => 'GMUCF Options Updated'
	));

}

/**
 * Sets the default values for each stories' image, title and
 * description for use in the GMUCF Today emails
 * @since 2.9.0
 * @author Cadie Brown
 * @param Array $story | single array with story data
 * @return Array
 */
function gmucf_replace_story_default_values( $story ) {
	$post_id = $story['gmucf_story'];

	$story['gmucf_story_permalink'] = get_permalink( $post_id );
	$story['gmucf_layout']          = $story['acf_fc_layout'];
	
	if ( ! $story['gmucf_story_image'] ) {
		$story['gmucf_story_image'] = get_the_post_thumbnail_url( $post_id, 'gmucf_top_story' );
	} else {
		$story['gmucf_story_image'] = $story['gmucf_story_image']['sizes']['gmucf_top_story'];
	}

	if ( ! $story['gmucf_story_title'] ) {
		$story['gmucf_story_title'] = get_the_title( $post_id );
	}

	if ( ! $story['gmucf_story_description'] ) {
		$story['gmucf_story_description'] = get_post_meta( $post_id, 'promo', true );
	}

	unset( $story['gmucf_story'] );
	unset( $story['acf_fc_layout'] );

	return $story;
}

/**
 * Sets the default values depending on what kind
 * of layout is being used
 * @since 2.9.0
 * @author Cadie Brown
 * @param Array $stories | gmucf_email_content array from ACF GMUCF Options Page
 * @return Array
 */
function gmucf_stories_default_values( $stories ) {
	foreach ( $stories as $story ) {
		if ( $story['acf_fc_layout'] === 'gmucf_top_story' ) {
			$retval[] = gmucf_replace_story_default_values( $story );
		} elseif ( $story['acf_fc_layout'] === 'gmucf_featured_stories_row' ) {
			// for both featured stories, add an 'acf_fc_layout' field with value of 'gmucf_featured_story' to the beginning of the array
			$story['gmucf_left_featured_story']  = ['acf_fc_layout' => 'gmucf_featured_story'] + $story['gmucf_left_featured_story'];
			$story['gmucf_right_featured_story'] = ['acf_fc_layout' => 'gmucf_featured_story'] + $story['gmucf_right_featured_story'];

			$retval[] = gmucf_replace_story_default_values( $story['gmucf_left_featured_story'] );
			$retval[] = gmucf_replace_story_default_values( $story['gmucf_right_featured_story'] );
		} elseif ( $story['acf_fc_layout'] === 'gmucf_spotlight' ) {
			$story['gmucf_spotlight_image'] = $story['gmucf_spotlight_image']['sizes']['gmucf_top_story'];

			$retval[] = $story;
		} else {
			$retval[] = $story;
		}
	}

	return $retval;
}
