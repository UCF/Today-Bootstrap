<?php disallow_direct_load('home.php');?>
<?php get_header(); ?>
<?php
	$use_page = false;
	$page = get_page_by_title('Home');
	if ($page !== null) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>
<div class="row page-content" id="home">
	<? if($use_page == true) { ?>
		<?=apply_filters('the_content', $page->post_content)?>
	<?php 
	} else { ?>
		<div class="span4" id="side-features">
		    <?=do_shortcode('[promos css="border-bottom"]')?>
		    <?=do_shortcode('[ucf_photo link_page_name="Focus" css="border-bottom" front_page="true"]')?>
		    <?=do_shortcode('[external_stories]')?>
		</div>
		<div class="span5 border-both" id="center-features">
		    <?=do_shortcode('[feature css="border-bottom"]')?>
		    <?=do_shortcode('[more_headlines social="1" css="border-bottom" num_posts="4"]')?>
		    <?=do_shortcode('[ucf_video width="380" height="270"]')?>
		</div>
		<div class="span3" id="sidebar">
		    <?=esi_include('display_events', array('h2', 'border-bottom'))?>
		    <?=do_shortcode('[advertisement css="border-bottom" location="Frontpage Right Vertical"]')?>
		    <?=do_shortcode('[resources]')?>
		</div>
	<?php 
	}
	?>
</div>
<?php get_footer();?>