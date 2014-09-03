<?php
function display_events($header='h2', $css=null) { 
	$options = get_option(THEME_OPTIONS_NAME);
	$qstring = (bool)strpos($options['events_url'], '?');
	$url     = $options['events_url'];
	if (!$qstring){
		$url .= '?';
	}else{
		$url .= '&';
	}
	$start	 = 0;
	// Check for a given limit, then a set Options value, then if none exist, set to 5
	if ($options['events_max_items']) {
		$limit = $options['events_max_items'];
	}
	else {
		$limit = 5;
	}
	$events  = get_events($start, $limit);
	if($events !== NULL && count($events)):
		ob_start();
	?>
		<div class="events <?=$css?>">
			<<?=$header?>>Events @ UCF</<?=$header?>>
			<ul class="event-list">
				<?php foreach($events as $item):
					$start 		= new DateTime($item['starts']);
					$month 		= $start->format('M');
					$day 		= $start->format('j');
					$iso 		= $start->format('c');
					//$month 		= date('M', strtotime($item['starts']));
					//$day  		= date('j', strtotime($item['starts']));
					//$iso  		= date('c', strtotime($item['starts']));
					$link 		= $url.'eventdatetime_id='.$item['id']; // TODO: use 'url' after unify-events launches
					$title		= $item['title'];
				?>
				<li class="vevent clearfix">
					<div class="dtstart">
						<span class="month"><?=$month?></span>
						<span class="day"><?=$day?></span>
						<span class="value-title" title="<?=$iso?>"></span>
					</div>
					<span class="summary"><a class="url" href="<?=$link?>"><?=$title?></a></span>
				</li>
				<?php endforeach;?>
			</ul>
			<p class="more"><a href="http://events.ucf.edu?upcoming=upcoming">More Events</a></p>
		</div>
	<?php else:?>
		<p>Events could not be retrieved at this time.  Please try again later.</p>
	<?php endif;?>
<?php
	return ob_get_clean();
}


function get_events($start, $limit){
	$options = get_option(THEME_OPTIONS_NAME);
	$qstring = (bool)strpos($options['events_url'], '?');
	$url     = $options['events_url'];
	if (!$qstring){
		$url .= '?';
	}else{
		$url .= '&';
	}
	$url    .= 'upcoming=upcoming&format=json';
	
	// Set a timeout
	$opts = array('http' => array(
						'method'  => 'GET',
						'timeout' => FEED_FETCH_TIMEOUT
	));
	$context = stream_context_create($opts);
	
	// Grab the feed
	$raw_events = file_get_contents($url, false, $context);
	if ($raw_events) {	
		$events = json_decode($raw_events, TRUE);
		$events = array_slice($events, $start, $limit);
		return $events;
	}
	else { return NULL; }
}


function display_announcements($param, $value, $header='h3', $css) {
	$param = ($param == 'role' || $param == 'keyword' || $param == 'time') ? $param : 'role';
	$value = $value !== null ? sanitize_title($value) : 'all';

	// Create transient key; e.g. 'announcements_role_staff'
	$feed = 'announcements_'.$param.'_'.$value;

	if( ($html = get_transient($feed)) !== False) {
		return $html;
	} else {
		$json = get_announcements($param, $value);
		if(!empty($json)) {
			ob_start();
			?>
			<div class="<?=$css?>" id="announcements">
				<h3>Announcements</h3>
				<ul class="announcement-list">
					<? foreach($json as $item) { ?>
						<li>
							<h4><a href="<?=$item?>"><?=$item['post_title']?></a></h4>
							<p class="story-blurb">
								<?=strip_tags($item['post_content'])?>
							</p>
						</li>
					<? } ?>
				</ul>
			</div>
			<?
			$html = ob_get_contents(); 
			set_transient($feed, $html, ANNOUNCE_CACHE_DURATION);
			ob_end_clean();
			return $html;
		}
	}
}


function get_announcements($param, $value) {
	$param = ($param == 'role' || $param == 'keyword' || $param == 'time') ? $param : 'role';
	$value = $value !== null ? $value : 'all';

	$url = ANNOUNCE_DEFAULT;
	$qstring = (bool)strpos(ANNOUNCE_DEFAULT, '?');
	if (!$qstring){
		$url .= '?';
	}else{
		$url .= '&';
	}
	$url = $url.$param.'='.$value;

	// Set a timeout
	$opts = array('http' => array(
						'method'  => 'GET',
						'timeout' => FEED_FETCH_TIMEOUT
	));
	$context = stream_context_create($opts);

	// Grab the feed
	$raw_announcements = file_get_contents($url, false, $context);
	if ($raw_announcements) {	
		$announcements = json_decode($raw_announcements, TRUE);
		return $announcements;
	}
	else { return NULL; }
}

?>