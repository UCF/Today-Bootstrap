<?php disallow_direct_load('tag.php');?>
<?php get_header();?>
<?php echo gen_alerts_html(); ?>

<?php
	$use_page = false;
	$page = get_page_by_title('Tag');
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
			</div>
			<hr class="span12" /></div>
		<div class="row">
			<div class="span5 border-right">
			    <?=do_shortcode('[ucf_news social="0"]')?>
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
