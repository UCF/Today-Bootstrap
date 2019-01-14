			<div id="footer">
				<div class="row">
					<div class="span12 text-center">
						<?php wp_nav_menu( array (
							'theme_location' => 'social-links',
							'container' => 'div',
							'container_id' => 'social-menu-wrap',
							'menu_class' => 'menu screen-only',
							'menu_id' => 'social-menu',
							'depth' => 1,
							) );
						?>
					</div>
				</div>
				<div class="row" id="footer-widget-wrap">
					<div class="footer-widget span12">
						<?php if ( ! function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( '1st Subsidary Aside' ) ): ?>
							&nbsp;
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</body>
	<?php echo "\n" . footer_() . "\n"; ?>
</html>
