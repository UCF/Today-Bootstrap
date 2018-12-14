<?php @header( "HTTP/1.1 404 Not found", true, 404 ); ?>
<?php disallow_direct_load( '404.php' );?>

<?php get_header(); the_post(); ?>
	<div class="row page-content" id="page-not-found">
		<div class="span12">
			<article role="main">
				<h2>Page Not Found</h2>
				<?php
					$page = get_page_by_title( '404' );
					if($page){
						$content = $page->post_content;
						$content = apply_filters( 'the_content', $content );
						$content = str_replace( ']]>', ']]>', $content );
					}
				?>
				<?php if( $content ) : ?>
					<?php echo $content; ?>
				<?php else : ?>
					<p>The page you were looking for appears to have been moved, deleted or does not exist. Try using the navigation or search above or browse to the <a href="/">home page</a>.</p>
				<?php endif; ?>
			</article>
		</div>
	</div>
<?php get_footer(); ?>
