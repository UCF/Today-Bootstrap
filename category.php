<?php disallow_direct_load( 'category.php' );?>
<?php get_header();?>

<?php
	$use_page = false;
	$page = get_page_by_title( 'Category' );
	if ($page !== null) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>
	<div class="subpage">
	<? if($use_page == true) { ?>
		<? echo apply_filters( 'the_content', $page->post_content ) ?>
	<?php
	} else { ?>
		<div class="row">
			<div class="span9 border-right">
				<h1 class="term-heading"><?php single_cat_title(); ?></h1>
				<?php echo do_shortcode( '[feature css="border-bottom"]' )?>
				<?php echo do_shortcode( '[subpage_features]' )?>
			</div>
			<div class="span3" id="sidebar">
				<?php echo esi_include( 'do_shortcode', '[events]', true )?>
				<?php echo do_shortcode( '[more_headlines social="1" css="border-bottom" num_posts="4" offset="4"]' )?>
			</div>
		<div class="row">
			<div class="span5 border-right">
				<?php echo do_shortcode( '[ucf_news social="0"]' )?>
			</div>
			<div class="span7">
				<?php echo do_shortcode( '[ucf_video width="540"]' )?>
			</div>
		</div>
	<?php
	}
	?>
	</div>
<?php get_footer(); ?>
