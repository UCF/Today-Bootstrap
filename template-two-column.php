<?php
/**
 * Template Name: Two Column
 **/
?>
<?php get_header(); the_post();?>
	<div class="row page-content" id="<?=$post->post_name?>">
		<div class="span9">
			<article>
				<?php the_content();?>
			</article>
		</div>
		
		<div id="sidebar" class="span3">
			<?=get_sidebar();?>
		</div>
	</div>
<?php get_footer();?>