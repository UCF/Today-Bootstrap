<?php disallow_direct_load('single-video.php');?>
<?php get_header(); the_post();?>

<?php
	$use_page = false;
	$page = get_page_by_title('Video');
	if ($page !== null) {
		$use_page = $page->post_content !== '' ? true : false;
	}
?>

	<div id="videos">
		<article role="main">
			<? if($use_page == true) { ?>
				<?=apply_filters('the_content', $page->post_content)?>
			<?php
			} else { ?>
				<?=do_shortcode('[videos specific_video="'.$post->ID.'" heading_elem="h1"]')?>
			<?php
			}
			?>
		</article>
	</div>

<?php get_footer();?>
