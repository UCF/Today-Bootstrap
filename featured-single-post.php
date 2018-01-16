<?php
/**
 * Template Name: Featured Post
 * Template Post Type: post
 */
disallow_direct_load( 'featured-single-post.php' );
get_header( 'featured' ); the_post();

$subtitle = get_post_meta( $post->ID, 'subtitle', TRUE );

?>
<div class="featured-story-header">
	<div class="row-fluid">
		<div class="span5">
			<?php the_post_thumbnail( null, array( 'class' => 'img-responsive' ) ); ?>
		</div>
		<div class="span7">
			<div id="page-title" class="featured-title">
				<h1><?php echo the_title(); ?></h1>
				<?php if ( $subtitle ) : ?>
				<p id="subtitle"><em><?php echo $subtitle; ?></em></p>
				<?php endif; ?>
				<div id="deck" class="featured-deck">
					<p class="lead"><?php the_excerpt(); ?></p>
				</div>
			</div>
			<hr>
			<div id="by-line" class="featured-byline">
				<?php echo do_shortcode( '[single_post_meta css="clearfix"]' ); ?>
			</div>
		</div>
	</div>
</div>
<article class="featured-article">
	<div class="container">
		<?php echo gen_alerts_html()?>
		<?php the_content(); ?>
	</div>
</article>
<div class="container">
	<aside class="related-stories">
		<h2 class="text-center">Related Stories</h2>
		<?php echo display_more_stories_featured( $post ); ?>
	</aside>
<?php get_footer( 'featured' ); ?>
