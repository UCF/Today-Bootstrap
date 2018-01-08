<?php
/**
 * Template Name: Featured Post
 * Template Post Type: post
 */
disallow_direct_load( 'featured-single-post.php' );
get_header( 'featured' ); the_post();
?>
<div class="featured-story-header">
	<div class="row-fluid">
		<div class="span5">
			<?php the_post_thumbnail( null, array( 'class' => 'img-responsive' ) ); ?>
		</div>
		<div class="span7">
			<div id="page-title" class="featured-title">
				<h1><?php echo the_title(); ?></h1>
			</div>
			<hr>
			<div id="by-line" class="featured-byline">
				<?php echo do_shortcode( '[single_post_meta css="border-bottom clearfix"]' ) ?>
			</div>
			<div id="deck" class="featured-deck">
				<p class="lead"><?php the_excerpt(); ?></p>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<?php echo gen_alerts_html()?>
<div id="featured-single">
	<?php the_content(); ?>
</div>
<?php get_footer( 'featured' ); ?>
