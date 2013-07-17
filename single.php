<?php disallow_direct_load('single.php');?>
<?php get_header(); the_post();?>

<?
	if(get_post_type($post->ID) == 'expert') {
		$page = get_page_by_title('Expert');
	}
	if(get_post_type($post->ID) == 'photoset') {
		$page = get_page_by_title('Photo Set');
	}
	if(!is_object($page)) {
		$page = get_page_by_title('Single');
	}
	if(get_post_type($post->ID) == 'video') {
		?>
		<div id="videos">
			<?=sc_videos(Array('specific_video' => $post->ID))?>
		</div>
		<?
	} else {
		?>
		<div id="single">
			<?=apply_filters('the_content', $page->post_content)?>
		</div>
		<?
	}
?>

<?php get_footer();?>