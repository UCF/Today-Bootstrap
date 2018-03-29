<?php disallow_direct_load('single-photoset.php');?>
<?php get_header(); the_post();?>

<?php
	$use_page = false;
	$page = get_page_by_title('Photo Set');
	if ($page !== null) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>

	<div class="subpage">
		<article role="main">
			<? if($use_page == true) { ?>
				<?=apply_filters('the_content', $page->post_content)?>
			<?php
			} else { ?>
				<?=do_shortcode('[photo_set]')?>
			<?php
			}
			?>
		</article>
	</div>

<?php get_footer();?>
