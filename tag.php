<?php disallow_direct_load('tag.php');?>
<?php get_header();?>
<?
	$subpage = '';
	$page = get_page_by_title($wp_query->queried_object->name);
	if(is_null($page)) {
		$page = get_page_by_title('Tag');
		if(is_null($page)) {
			$page = get_page_by_title('Home');
		}
	} else {
		$subpage = $page->post_name; // TODO: why does this exist?
	}
?>
	<div class="subpage">
		<? if($subpage != '') echo '<div id="'.$subpage.'">';?>
		<?=apply_filters('the_content', $page->post_content)?>
		<? if($subpage != '') echo '</div>';?>
	</div>
<?php get_footer();?>