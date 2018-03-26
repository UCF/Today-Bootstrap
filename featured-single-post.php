<?php
/**
 * Template Name: Featured Post
 * Template Post Type: Post
 */
disallow_direct_load( 'featured-single-post.php' );
get_header(); the_post();

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
	<div class="container">
		<div class="row">
			<div class="span10 offset1">
				<div class="feature-headlines">
					<h2><?php echo get_header_title() ?></h2>
					<h1><?php echo the_title(); ?></h1>
					<?php if ( $subtitle ) : ?>
					<h3 class="subtitle"><?php echo $subtitle; ?></h3>
					<?php endif; ?>
					<?php echo do_shortcode( '[feature_post_meta css="clearfix"]' ); ?>
				</div>
				<div class="feature-story-image">
					<?php the_post_thumbnail(); ?>
					<p class="caption"><?php echo get_post(get_post_thumbnail_id())->post_excerpt; ?></p>
				</div>
				<?php if ( $deck ) : ?>
				<p class="deck"><?php echo $deck; ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<article class="feature-story">
		<div class="container">
			<div class="row">
				<div class="span10 offset1">
					<?php if($video_url != '') : ?>
						<div class="video-container">
							<?=$wp_embed->run_shortcode( '[embed width="550" height="500"]'.$video_url.'[/embed]' )?>
						</div>
					<?php endif ?>
					<div id="content">
						<?php the_content(); ?>
					</div>
					<div id="source">
						<p><?php echo $source; ?></p>
					</div>
					<?php echo display_social( get_permalink( $post->ID ), $title, 'affixed' ); ?>
				</div>
			</div>
		</div>
	</article>
	<div class="container">
		<aside class="related-stories">
			<h2 class="text-center">Related Stories</h2>
			<?php echo display_more_stories_featured( $post ); ?>
			<div class="clearfix"></div>
		</aside>
<?php get_footer( 'featured' ); ?>
