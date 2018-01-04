<?php
/**
 * Template Name: Featured Post
 * Template Post Type: post
 */
disallow_direct_load( 'featured-single-post.php' );
get_header( 'featured' ); the_post();
?>
<div id="featured-single">
	<?php the_content(); ?>
</div>
<?php get_footer( 'featured' ); ?>
