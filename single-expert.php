<?php disallow_direct_load( 'single-expert.php' ); ?>
<?php get_header(); the_post(); ?>

<?php
	$use_page = false;
	$page = get_page_by_title( 'Expert' );
	if ( $page !== null ) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>

	<div id="single">
	<?php if ( $use_page == true ) : ?>
		<?php echo apply_filters( 'the_content', $page->post_content ); ?>
	<?php else : ?>
		<div class="row">
			<?php echo do_shortcode( '[single_post css="span7"]' ); ?>
			<div class="span4 offset1" id="sidebar" role="complementary">
				<?php echo do_shortcode( '[expert_meta css="border-bottom"]' ); ?>
				<?php echo do_shortcode( '[expert_tagged css="border-bottom"]' ); ?>
				<?php echo do_shortcode( '[expert_videos css="border-bottom"]' ); ?>
				<?php echo do_shortcode( '[expert_photos css="border-bottom"]' ); ?>
				<?php echo do_shortcode( '[single_post_recommended]' ); ?>
			</div>
		</div>
	<?php endif; ?>
	</div>

<?php get_footer(); ?>
