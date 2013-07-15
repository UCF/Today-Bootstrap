<?php $options = get_option(THEME_OPTIONS_NAME);?>
<?php get_header(); ?>
<?php $page = get_page_by_title('Home');?>
<div class="row page-content" id="<?=$page->post_name?>">
	<?php print apply_filters('the_content', $page->post_content);?>
</div>
<?php get_footer();?>