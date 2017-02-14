<?php
/**
 * Export WordPress post data to CSV
 * Based on <http://stackoverflow.com/a/3474698> and <http://ran.ge/2009/10/27/howto-create-stream-csv-php/>
 */

/**
 * Export fields: array of strings representing $post properties to output to csv
 */
$export_fields = array(
  'post_title',
  'post_date'
);

/**
 * Export query parameters for WP_Query
 * @link http://codex.wordpress.org/Function_Reference/WP_Query WP_Query parameter reference
 */
$export_query = array(
  'posts_per_page' => -1,
  'post_status' => 'publish',
  'post_type' => 'any',
);

/**
 * Export query pulls data from relevant custom post types unto an array and exported as a csv
 **/
// Posts query
$posts = new WP_Query( $export_query );
$posts = $posts->posts;

// Output file stream
$output_filename = 'export_' . strftime( '%Y-%m-%d' )  . '.csv';
$output_handle = @fopen( $output_filename, 'w' );
header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header( 'Content-Description: File Transfer' );
header( 'Content-type: text/csv' );
header( 'Content-Disposition: attachment; filename=' . $output_filename );
header( 'Expires: 0' );
header( 'Pragma: public' );

// Output data to file
foreach ( $posts as $post ) {
  // Get post permalink
  $post_content = '';
  switch ( $post->post_type ) {
    case 'alert':
    case 'expert':
    case 'nav_menu_item':
    case 'page':
    case 'profile':
    case 'revision':
      break;
    case 'externalstory':
    case 'photoset':
    case 'post':
    case 'ucf-in-photos':
    case 'video':
      $permalink = get_permalink( $post->ID );
      // Get post content
      $post_content =  apply_filters( 'the_content', $post->post_content );
      break;
    case 'attachment':
      $permalink = get_attachment_link( $post->ID );
      break;
    default:
      $permalink = get_post_permalink( $post->ID );
      break;
  }
  // Build export array
  $post_export = array( $permalink );
  foreach ( $export_fields as $export_field ) {
    $post_export[] = $post->$export_field;
  }

// Add post content
  $post_export[] = $post_content;

  // Add row to file
  fputcsv( $output_handle, $post_export );
}
// Close output file stream
fclose( $output_handle );
exit;
?>