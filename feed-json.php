<?php
/**
 * JSON Feed Template
 **/

$charset = get_option( 'charset' );

$thumb_dimensions = 'widget_95';
$thumb_dimensions_user = $_GET['thumb'];

if ( $thumb_dimensions_user ) {
	if ( in_array( $thumb_dimensions_user, get_intermediate_image_sizes() ) ) {
		$thumb_dimensions = $thumb_dimensions_user;
	}
	else {
		$thumb_dimensions_user = explode( 'x', $thumb_dimensions_user, 2 );
		$thumb_x = intval( $thumb_dimensions_user[0] );
		if ( $thumb_x !== 0 ) {
			if ( count( $thumb_dimensions_user ) == 2 ) {
				$thumb_y = intval( $thumb_dimensions_user[1] );
				if ( $thumb_y !== 0 ) {
					// Both x and y dimensions are valid
					$thumb_dimensions = array( $thumb_x, $thumb_y );
				}
				else {
					// Fall back to square dimensions
					$thumb_dimensions = array( $thumb_x, $thumb_x );
				}
			}
			else {
				// Only a single number was passed--return a square thumbnail
				$thumb_dimensions = array( $thumb_x, $thumb_x );
			}
		}
	}
}

if ( have_posts() ) {
	$json = array();

	while ( have_posts() ) {
		the_post();
		$id = (int) $post->ID;

		$single = array(
			'id'        => $id,
			'title'     => get_the_title(),
			'permalink' => get_permalink(),
			'content'   => strip_shortcodes( get_the_content() ),
			'excerpt'   => get_the_excerpt(),
			'date'      => get_the_date(DATE_ISO8601),
			'author'    => get_the_author()
		);

		if ( has_post_thumbnail( $id ) ) {
			$single['thumbnail'] = get_the_post_thumbnail( $id, $thumb_dimensions );
		}

		if ( !$single['thumbnail'] ) {
			$single['thumbnail'] = '<img src="'. FEED_THUMBNAIL_FALLBACK .'" alt="UCF Today news article thumbnail">';
		}

		$json[] = $single;
	}

	$json = json_encode( $json );

	header("Content-Type: application/json; charset={$charset}");
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET");
	echo $json;
} else {
	$json = json_encode( array() );

	header("Content-Type: application/json; charset={$charset}");
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET");
	echo $json;
}

?>
