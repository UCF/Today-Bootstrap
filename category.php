<?php disallow_direct_load('category.php');?>
<?php get_header();?>
<?php
	$use_page = false;
	$page = get_page_by_title('Category');
	if ($page !== null) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>
	<div class="subpage">
	<? if($use_page == true) { ?>
		<?=apply_filters('the_content', $page->post_content)?>
	<?php 
	} else { ?>
		<div class="row">
			<div class="span9 border-right">
			    <?=do_shortcode('[feature css="border-bottom"]')?>
			    <?=do_shortcode('[subpage_features]')?>
			</div>
			<div class="span3" id="sidebar">
			    <?=esi_include('do_shortcode', '[events]', true)?>
			    <?=do_shortcode('[advertisement css="border-top" location="Category Right Vertical"]')?>
			</div>
			<hr class="span12" /></div>
		<div class="row">
			<div class="span5 border-right">
			    <?=do_shortcode('[more_headlines social="0"]')?>
			</div>
			<div class="span7">
			    <?=do_shortcode('[ucf_video width="540"]')?>
			</div>
		</div>
	<?php 
	}
	?>
	</div>
<?php get_footer();?>