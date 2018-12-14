<?php disallow_direct_load( 'single.php' ); ?>
<?php get_header(); the_post(); ?>

<?php
	$use_page = false;
	$page = get_page_by_title( 'Single' );
	if ( $page !== null ) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>

	<div id="single">
	<?php if ( $use_page == true ) {
		echo apply_filters( 'the_content', $page->post_content );
	} else { ?>
		<div class="row">
			<?php echo do_shortcode( '[single_post css="span7"]' ); ?>
			<div class="span4 offset1" id="sidebar" role="complementary">
				<?php
				echo do_shortcode( '[single_post_meta css="border-bottom clearfix"]' );
				echo do_shortcode( '[more_headlines social="1" css="border-bottom" num_posts="4" offset="4"]' );
				echo do_shortcode( '[single_post_more_tag css="border-bottom"]' );
				echo do_shortcode( '[single_post_more_cat css="border-bottom"]' );
				echo do_shortcode( '[single_post_topics css="border-bottom"]' );
				echo do_shortcode( '[single_post_comments css="border-bottom"]' );
				echo do_shortcode( '[single_post_recommended]' );
				?>
			</div>
		</div>
	<?php } ?>
	</div>

<?php get_footer();?>
