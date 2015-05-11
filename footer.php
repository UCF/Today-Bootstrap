			<div id="footer">
				<div class="row">
					<div class="span12 text-center">
						<?php echo display_social( get_permalink( $top_feature->ID ), $top_feature->post_title )?>
					</div>
				</div>
				<div class="row" id="footer-widget-wrap">
					<div class="span12 text-center">
						<p id="subfooter" role="contentinfo" class="vcard">
							<span class="adr">
								<span class="street-address">4000 Central Florida Blvd. </span>
								<span class="locality">Orlando</span>,
								<span class="region">Florida</span>,
								<span class="postal-code">32816</span> |
								<span class="tel"><a href="tel:4078232000">407.823.2000</a></span>
							</span>
							<br>
							&copy; <a href="http://www.ucf.edu" class="print-noexpand fn org url">
								<span class="organization-name">University of Central Florida</span>
							</a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</body>
	<?php echo "\n".footer_()."\n"?>
</html>
