<?php
/**
 * JSON Feed Template
 **/

$charset = get_option( 'charset' );

if ( have_posts() ) {
	$json = array();

	while ( have_posts() ) {
		the_post();
		$id = (int) $post->ID;

		$single = array(
			'id'        => $id,
			'title'     => get_the_title(),
			'permalink' => get_permalink(),
			'content'   => get_the_content(),
			'excerpt'   => get_the_excerpt(),
			'date'      => get_the_date(DATE_ISO8601),
			'author'    => get_the_author()
		);

		if ( has_post_thumbnail( $id ) ) {
			$single['thumbnail'] = get_the_post_thumbnail( $id, array( 'widget_95', ) );
		}

		$json[] = $single;
	}

	$json = json_encode( $json );

	header("Content-Type: application/json; charset={$charset}");
	header("Access-Control-Allow-Origin: *");
	echo $json;
} else {
	status_header( '404' );
	wp_die( '404 Not Found' );
}

?>