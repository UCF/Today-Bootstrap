			<div id="footer">
				<div class="row" id="footer-widget-wrap">
					<div class="footer-widget-1 span4">
						<?php if(!function_exists('dynamic_sidebar') or !dynamic_sidebar('1st Subsidary Aside')):?>
							<a href="<?=bloginfo('url')?>"><img src="<?=THEME_IMG_URL?>/ucftoday4_small.png" alt="UCF Today" title="UCF Today" /></a>
						<?php endif;?>
					</div>
					<div class="footer-widget-2 span4">
						<?php if(!function_exists('dynamic_sidebar') or !dynamic_sidebar('2nd Subsidary Aside')):?>
						&nbsp;
						<?php endif;?>
					</div>
					<div class="footer-widget-3 span4">
						<?php if(!function_exists('dynamic_sidebar') or !dynamic_sidebar('3rd Subsidary Aside')):?>
						&nbsp;
						<?php endif;?>
					</div>
				</div>
			</div>
		</div>
	</body>
	<?="\n".footer_()."\n"?>
</html>