<?php disallow_direct_load( 'home.php' ); ?>
<?php get_header(); ?>
<?php
	$use_page = false;
	$page = get_page_by_title( 'Home' );
	if ( $page !== null ) {
		$use_page = $page->post_content !== '' ? true : false;
	}
 ?>
<div class="row page-content" id="home">
	<? if( $use_page === true ) { ?>
		<?php echo apply_filters( 'the_content', $page->post_content ) ?>
	<?php
		} else { ?>
			<div class="span4" id="side-features">
			    <?php echo do_shortcode( '[feature css="border-bottom visible-phone"]' ) ?>
				<?php echo do_shortcode( '[ucf_news social="1" css="border-bottom visible-phone" num_posts="4"]' ) ?>
			    <?php echo do_shortcode( '[promos css="border-bottom visible-desktop visible-tablet"]' ) ?>
			    <?php echo do_shortcode( '[ucf_photo link_page_name="Focus" css="border-bottom" front_page="true"]' ) ?>
				<?php echo do_shortcode( '[promos num_posts="2" css="border-bottom visible-phone"]' ) ?>
			    <?php echo do_shortcode( '[external_stories]' ) ?>
			</div>
			<div class="span5 border-both" id="center-features">
			    <?php echo do_shortcode( '[feature css="border-bottom visible-desktop"]' ) ?>
			    <?php echo do_shortcode( '[ucf_news social="1" css="border-bottom visible-desktop visible-tablet" num_posts="4"]' ) ?>
			    <?php echo do_shortcode( '[ucf_video width="380" height="270"]' ) ?>
			</div>
			<div class="span3" id="sidebar">
			    <?php echo esi_include( 'do_shortcode', '[events css="border-bottom"]', true ) ?>
			    <?php echo do_shortcode( '[more_headlines social="1" css="border-bottom" num_posts="4" offset="4"]' ) ?>
			    <?php echo do_shortcode( '[resources]' ) ?>
			</div>
	<?php
		}
	?>
</div>
<?php get_footer(); ?>
