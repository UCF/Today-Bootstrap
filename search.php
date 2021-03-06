<?php $options = get_option( THEME_OPTIONS_NAME ); ?>
<?php $query_clean = htmlentities( $_GET['s'] ); ?>
<?php if ( $options['enable_google'] || $options['enable_google'] === null ): ?>
<?php
	$domain  = $options['search_domain'];
	$limit   = (int)$options['search_per_page'];
	$start   = ( is_numeric( $_GET['start'] ) ) ? (int)$_GET['start'] : 0;
	$results = get_search_results( $_GET['s'], $start, $limit, $domain );
?>
<?php get_header(); ?>
	<div class="row page-content" id="search-results">
		<div class="span12">
			<article>
				<h1>Search Results for: <?php echo $query_clean; ?></h1>
				<?php if ( count( $results['items'] ) ): ?>
				<ul class="result-list">
					<?php foreach ( $results['items'] as $result ): ?>
					<li class="item <?php echo mimetype_to_application( ( $result['mime'] ) ? $result['mime'] : 'html' ); ?>">
						<h3>
							<a href="<?php echo $result['url']; ?>">
								<?php
								if ( $result['title'] ):
									echo $result['title'];
								else:
									echo substr( $result['url'], 0, 45 ) . "...";
								endif; ?>
							</a>
						</h3>
						<a href="<?php echo $result['url']; ?>" class="ignore-external url sans"><?php echo $result['url']; ?></a>
						<div class="snippet">
							<?php echo str_replace( '<br>', '', $result['snippet'] ); ?>
						</div>
					</li>
				<?php endforeach;?>
				</ul>

				<?php if ( $start + $limit < $results['number'] ): ?>
				<a class="button more" href="./?s=<?php echo $query_clean; ?>&amp;start=<?php echo $start + $limit; ?>">More Results</a>
				<?php endif; ?>

				<?php else: ?>

				<p>No results found for "<?php echo $query_clean; ?>".</p>

				<?php endif; ?>
			</article>
		</div>
	</div>
<?php get_footer();?>

<?php else:?>
<?php get_header(); the_post(); ?>
	<div class="row page-content" id="search-results">
		<div class="span12">
			<h2>Search Results for: <?php echo $query_clean; ?></h2>
		</div>
		<div class="span7">
			<article>
				<h3>Stories</h3>
				<?php if ( have_posts() ):?>
					<ul class="story-list">
					<?php while ( have_posts() ): the_post();?>
						<?php
						$thumb_html = get_img_html( $post->ID, 'story' );
						?>
						<li class="clearfix">
							<div class="thumb">
								<a href="<?php echo get_permalink( $post->ID ); ?>">
									<?php echo $thumb_html; ?>
								</a>
							</div>
							<div class="content">
								<h3><a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $post->post_title; ?></a></h3>
								<p class="story-blurb">
									<?php echo get_excerpt( $post, $query_clean ); ?>
								</p>
							</div>
							<ul class="meta">
								<li class="date"><?php echo date( 'F d, Y', strtotime( $post->post_date ) ); ?></li>
								<?php
								$mainsite_tag = get_mainsite_tag();
								$tags = wp_get_post_tags( $post->ID );
								$cats = wp_get_post_categories( $post->ID );
								// get the total number of taxonomy terms for adding commas
								$alltaxs_count = count( $tags ) + count( $cats );
								foreach ( $tags as $t ) {
									// account for the mainsite tag and remove it from the tax count
									if ( $t->term_id === $mainsite_tag->term_id ) {
										$alltaxs_count = $alltaxs_count - 1;
									}
								}

								if ( $tags !== null || $cat_ids !== null ) {
								?>
								<li>
									<ul class="term-list">
										<?php
										$count = 0;
										if ( $tags ) {
											foreach ( $tags as $tag ) {
												if ( $tag->term_id != $mainsite_tag->term_id ) {
										?>
											<li>
												<a href="<?php echo get_tag_link( $tag->term_id ); ?>">
													<?php echo $tag->name?><?php echo ( ( $count + 1 ) !== $alltaxs_count ) ? ',' : ''; ?>
												</a>
											</li>
										<?php
												$count++;
												}
											}
										}
										if ( $cats ) {
											foreach ( $cats as $cat ) {
												$cat = get_category( $cat );
										?>
											<li>
												<a href="<?php echo get_category_link( $cat->term_id ); ?>">
													<?php echo $cat->name; ?><?php echo ( ( $count + 1 ) !== $alltaxs_count ) ? ',' : ''; ?>
												</a>
											</li>
										<?php
											$count++;
											}
										}
										?>
									</ul>
								</li>
								<?php
								}
								?>
							</ul>
						</li>
					<?php endwhile;?>
					</ul>
				<?php else:?>
					<p>No results found for "<?php echo $query_clean; ?>".</p>
				<?php endif;?>
			</article>
		</div>

		<div id="sidebar" class="span4 offset1">
			<div id="expert_results" class="border-bottom">
				<h3>Experts</h3>
				<?php
				$experts = get_posts_search( $query_clean, 'expert' );
				if ( $experts ) {
				?>
					<ul class="expert-list">
					<?php
					foreach ( $experts as $expert ) {
						$title = get_post_meta( $expert->ID, 'expert_title', true );
						$association = get_post_meta( $expert->ID, 'expert_association', true ) ;
					?>
						<li class="clearfix">
							<a href="<?php echo get_permalink( $expert->ID ); ?>"><?php echo get_img_html( $expert->ID, 'story' ); ?></a>
							<h4>
								<a href="<?php echo get_permalink( $expert->ID ); ?>">
									<?php echo $expert->post_title; ?>
								</a>
							</h4>
							<?php if ( $title ) { ?><p><?php echo $title; ?></p><?php } ?>
							<?php if ( $association ) { ?><p><?php echo $association; ?></p><?php } ?>
						</li>
					<?php
					}
					?>
					</ul>
				<?php
				}
				else {
				?>
					<p>There are no expert results.</p>
				<?php
				}
				?>
			</div>
			<div id="video_results" class="border-bottom">
				<h3>Videos</h3>
				<?php
				$videos = get_posts_search( $query_clean, 'video' );
				if ( $videos ) {
				?>
					<ul class="video-list">
					<?php
					foreach ( $videos as $video ) {
						$link = get_post_meta( $video->ID, 'video_url', true );
						$parts = parse_url( $link );
						parse_str( $parts['query'], $parts );
						$ytid = $parts['v'];
					?>
						<li class="clearfix">
							<a href="<?php echo $link; ?>">
								<img src="https://img.youtube.com/vi/<?php echo $ytid; ?>/mqdefault.jpg" alt="<?php echo $video->post_title; ?>" title="<?php echo $video->post_title; ?>" />
							</a>
							<h4>
								<a href="<?php echo $link; ?>">
									<?php echo $video->post_title; ?>
								</a>
							</h4>
						</li>
					<?php
					}
					?>
					</ul>
				<?php
				}
				else {
				?>
					<p>There are no video results.</p>
				<?php
				}
				?>
			</div>
			<div id="photoset_results" class="border-bottom">
				<h3>Photos</h3>
				<?php
				$photosets = get_posts_search( $query_clean, 'photoset' );
				if ( $photosets ) {
				?>
					<ul class="photoset-list">
					<?php foreach ( $photosets as $post ) { ?>
						<li>
							<a href="<?php echo get_permalink( $post->ID ); ?>">
								<?php echo get_img_html( $post->ID, 'ucf_photo' ); ?>
							</a>
							<h4><a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $post->post_title; ?></a></h4>
						</li>
					<?php } ?>
				<?php
				}
				else {
				?>
					<p>There are no photo results.</p>
				<?php
				}
				?>
			</div>
			<div>
				<h3>More News Archives</h3>
				<a id="archive_link" href="http://newsarchive.smca.ucf.edu" target="_blank">Newsroom Archives &raquo;</a>
			</div>
		</div>
	</div>
<?php get_footer(); ?>
<?php endif; ?>
