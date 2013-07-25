<?php disallow_direct_load('single.php');?>
<?php get_header(); the_post();?>

<?php
	$use_page = false;
	$page = get_page_by_title('Single');
	if ($page !== null) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>

	<div id="single">
	<? if($use_page == true) { ?>
		<?=apply_filters('the_content', $page->post_content)?>
	<?php 
	} else { ?>
		<div class="row">
			<?=do_shortcode('[single_post css="span7"]')?>
			<div class="span4 offset1" id="sidebar" role="complementary">
				<?=do_shortcode('[single_post_meta css="border-bottom"]')?>
				<?=do_shortcode('[advertisement css="border-bottom" location="Single Right Vertical"]')?>
				<?=do_shortcode('[single_post_more_tag css="border-bottom"]')?>
				<?=do_shortcode('[single_post_more_cat css="border-bottom"]')?>
				<?=do_shortcode('[single_post_topics css="border-bottom"]')?>
				<?=do_shortcode('[single_post_comments css="border-bottom"]')?>
				<?=do_shortcode('[single_post_recommended]')?>
			</div>
		</div>
	<?php 
	}
	?>
	</div>

<?php get_footer();?>