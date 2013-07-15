<?php disallow_direct_load('category.php');?>
<?php get_header();?>
<?
	$page = get_page_by_title($wp_query->queried_object->name);
	if(is_null($page)) {
		$page = get_page_by_title('Category');
		if(is_null($page)) {
			$page = get_page_by_title('Home');
		}
	}
?>
	<div class="subpage">
		<?=apply_filters('the_content', $page->post_content)?>
	</div>
<?php get_footer();?>