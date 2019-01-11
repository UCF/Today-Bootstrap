<?php
/*
	UCF Today RSS Content Widget
	UCF Web Communications
	Fall 2010

	Modifying MediaRSS plugin to create dynamic thumbnail sizes for widget

*/

/*
Plugin Name: MediaRSS
Plugin URI: http://wordpress.org/extend/plugins/mrss/
Description: Adds &lt;media&gt; tags to your feeds.
Version: 1.1.4
Author: Andy Skelton
Author URI: http://andy.wordpress.com/

FriendFeed image thumbnail compatibility modifications by Daniel J. Pritchett
on the advice of Paul Reynolds (http://friendfeed.com/screwtheman). This adds
the first image in the post as the post's own thumbnail.

*/

add_action( 'template_redirect', 'mrss_init' );

function mrss_init() {
	if ( ! is_feed() ) {
		return;
	}

	if ( isset( $_GET['mrss'] ) && $_GET['mrss'] === 'off' )
		return;

	add_action( 'rss2_ns', 'mrss_ns' );

	add_action( 'rss2_item', 'mrss_item', 10, 0 );
}

function mrss_ns() {
	?>xmlns:media="http://search.yahoo.com/mrss/"<?php
}

function mrss_image_size() {
	$upload_dir = wp_upload_dir();
	$metadata_size = image_get_intermediate_size(
	    get_post_thumbnail_id(),
	    'thumbnail'
	);
	$path_inter = $upload_dir[ 'basedir' ] . '/' . $metadata_size[ 'path' ];

	return filesize(
	    $path_inter
	);
}

function mrss_item() {
	global $mrss_gallery_lookup, $post;
	$media = array();

	# Add featured image as an enclosure
	$feat_img_id = 0;
	if ( !is_null( $feat_img_id  = get_post_thumbnail_id( $post->ID ) )
		&& ( $feat_src = wp_get_attachment_image_src( $feat_img_id ) ) !== false
		&& ( $feat_type = get_post_mime_type( $feat_img_id ) ) !== false ) {
			echo '<enclosure url="' . $feat_src[0] . '" type="' . $feat_type . '" length="' . mrss_image_size() . '" />';
	}

	$valid_mime_types = array( 'image/jpg','image/png', 'image/jpeg' );

	$attachments = get_posts(
		array(
			'post_parent'    => $post->ID,
			'post_type'      => 'attachment',
			'post_mime_type' => $valid_mime_types
		)
	);

	# If a featured image is selected from the media library,
	# it isn't tied to the post as a traditional attachment. Rather
	# a  _thumbnail_id entry is inserted into the postmeta table noting
	# the attachment ID of the media post.
	if ( ( $thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true ) ) !== false &&
		$thumbnail_id != '' &&
		!is_null( $thumbnail_post = get_post( $thumbnail_id ) ) &&
		in_array( $thumbnail_post->post_mime_type, $valid_mime_types ) ) {

		$attachments = array_merge( $attachments, array( $thumbnail_post ) );
	}

	foreach ( $attachments as $attachment ) {
		$item = $img = array();

		$src = wp_get_attachment_image_src( $attachment->ID, 'full' );
		if ( !empty( $src[0] ) ) {
			$img['src'] = $src[0];
		}

		$thumbnail = wp_get_attachment_image_src( $id, 'thumbnail' );
		if ( !empty( $thumbnail[0] ) && $thumbnail[0] != $img['src'] ) {
			$img['thumbnail'] = $thumbnail[0];
		}

		$title = get_the_title( $id );
		if ( !empty( $title ) ) {
			$img['title'] = trim( $title );
		}

		$description = get_the_content( $id );
		if ( !empty( $attachment->post_excerpt ) ) {
			$img['description'] = trim( $attachment->post_excerpt );
		}

		/******************************************************************\
			Modified to allow for dynamic thumbnail sizing using TimThumb
		\******************************************************************/

		$item['content']['attr']['url'] = $img['src'];
		$item['content']['attr']['medium'] = 'image';
		if ( isset( $attachment->post_mime_type ) )
			$item['content']['attr']['type'] = $attachment->post_mime_type;
		if ( !empty( $img['title'] ) ) {
			$item['content']['children']['title']['attr']['type'] = 'html';
			$item['content']['children']['title']['children'][] = $img['title'];
		} elseif ( !empty( $img['alt'] ) ) {
			$item['content']['children']['title']['attr']['type'] = 'html';
			$item['content']['children']['title']['children'][] = $img['alt'];
		}
		if ( !empty( $img['description'] ) ) {
			$item['content']['children']['description']['attr']['type'] = 'html';
			$item['content']['children']['description']['children'][] = $img['description'];
		}

		// thumb
		if ( isset( $_GET['thumb'] ) ) {
			if (
				preg_match( '/^(\d+)$/', $_GET['thumb'], $custom_thumb ) || preg_match( '/^(\d+)x(\d+)$/', $_GET['thumb'], $custom_thumb )
			) {
				// thumb is set and dimensions have been scrutinized, use wp_get_attachment_image_src with width/height args
				$image_width  = $custom_thumb[1];
				$image_height = isset( $custom_thumb[2] ) ?  $custom_thumb[2] : $custom_thumb[1];
				$thumbnail_type = array( $image_width, $image_height );
			} else {
				$thumbnail_type = preg_replace( '/[^a-z0-9_]+/i', '', $_GET['thumb'] );
			}

			$wp_attachment_src = wp_get_attachment_image_src( $attachment->ID, $thumbnail_type );
			if ( $wp_attachment_src !== false ) {
				$image_src = $wp_attachment_src[0];
				$image_width = $wp_attachment_src[1];
				$image_height = $wp_attachment_src[2];
			} else {
				$image_src = get_bloginfo( 'stylesheet_directory' ) . '/static/img/no-photo.png';
				$image_width = 95;
				$image_height = 95;
			}
			$item['thumbnail']['attr']['url']    = $image_src;
			$item['thumbnail']['attr']['width']  = $image_width;
			$item['thumbnail']['attr']['height'] = $image_height;
			if ( isset( $attachment->post_mime_type ) ) {
				$item['thumbnail']['attr']['type'] = $attachment->post_mime_type;
			}
		} else {
			if ( !empty( $img['thumbnail'] ) ) {
				$item['thumbnail']['attr']['url'] = $img['thumbnail'];
			}
		}
		$media[] = $item;

	}

	$media = apply_filters( 'mrss_media', $media );

	mrss_print( $media );
}

function mrss_print( $media ) {
	if ( !empty( $media ) ) {

		if ( count( $media ) > 1 ) {
			echo '<media:group>';
		}

		foreach ( $media as $element ) {
			mrss_print_element( $element );
		}

		if ( count( $media ) > 1 ) {
			echo '</media:group>';
		}
	}
	echo "\n";
}

function mrss_print_element( $element, $indent = 2 ) {
	echo "\n";

	foreach ( $element as $name => $data ) {
		echo str_repeat( "\t", $indent ) . "<media:$name";
		if ( !empty( $data['attr'] ) ) {
			foreach ( $data['attr'] as $attr => $value )
				echo " $attr=\"" . esc_attr( ent2ncr( $value ) ) . "\"";
		}
		if ( !empty( $data['children'] ) ) {
			$nl = false;
			echo ">";
			foreach ( $data['children'] as $_name => $_data ) {
				if ( is_int( $_name ) ) {
					echo ent2ncr( esc_html( $_data ) );
				} else {
					$nl = true;
					mrss_print_element( array( $_name => $_data ), $indent + 1 );
				}
			}
			if ( $nl ) {
				echo "\n" . str_repeat( "\t", $indent );
			}
			echo "</media:$name>";
		} else {
			echo " />";
		}
	}
}
?>
