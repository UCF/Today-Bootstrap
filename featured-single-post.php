<?php
/**
 * Template Name: Featured Post
 * Template Post Type: Post
 */
disallow_direct_load( 'featured-single-post.php' );
get_header( 'featured' ); the_post();

$title = $post->post_title;
$subtitle = get_post_meta( $post->ID, 'subtitle', TRUE );
$deck = get_post_meta( $post->ID, 'deck', TRUE );
$source = get_post_meta( $post->ID, 'source', TRUE);
$img_attach = get_img_html($post->ID, 'story_feature', Array('return_id' => True));

if($img_attach['attachment_id'] != '') {
	$attachment = get_post($img_attach['attachment_id']);
}

$video_url = get_video_url($post->ID);

?>

<div id="feature-story">
	<div class="row-fluid feature-above-fold">
		<div class="span6">
			<div class="feature-heading-content">
				<h2><?php echo get_header_title() ?></h2>
				<h1><?php echo the_title(); ?></h1>
				<?php if ( $subtitle ) : ?>
					<p id="subtitle"><?php echo $subtitle; ?></p>
				<?php endif; ?>
				<?php if ( $deck ) : ?>
					<p id="deck"><?php echo $deck; ?></p>
				<?php endif; ?>
				<?php echo do_shortcode( '[feature_post_meta css="clearfix"]' ); ?>
			</div>
		</div>
		<?php if($video_url != '') : ?>
			<div class="span6">
				<div class="feature-video-container">
					<?php echo $wp_embed->run_shortcode( '[embed width="550" height="500"]'.$video_url.'[/embed]' ); ?>
				</div>
			</div>
		<?php else : ?>
			<div class="feature-post-feature-img span6" style="background-image: url('<?php the_post_thumbnail_url(); ?>');">
				<?php the_post_thumbnail( null, array( 'class' => 'img-responsive' ) ); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="container">
		<?php echo gen_alerts_html(); ?>
	</div>
	<article class="feature-story">
		<div class="container">
			<div class="row">
				<div class="span10 offset1">
					<?php echo display_feature_social(get_permalink($post->ID), $title, $deck); ?>
					<div id="content">
						<?php the_content(); ?>
					</div>
					<?php if ( $source ) : ?>
						<p id="source"><?php echo $source; ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</article>
	<div class="container">
		<aside class="related-stories">
			<h2 class="text-center">Related Stories</h2>
			<?php echo display_more_stories_featured( $post ); ?>
		</aside>
<?php get_footer( 'featured' ); ?>
