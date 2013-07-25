<?php
/**
 * Template Name: One Column
 **/
?>
<?php get_header(); the_post();?>
	<div class="row page-content" id="<?=$post->post_name?>">
		<div class="span12">
			<article role="main">
				<?php the_content();?>
			</article>
		</div>
	</div>
<?php get_footer();?>