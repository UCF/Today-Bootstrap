var Generic = {};

Generic.defaultMenuSeparators = function($) {
	// Because IE sucks, we're removing the last stray separator
	// on default navigation menus for browsers that don't 
	// support the :last-child CSS property
	$('.menu.horizontal li:last-child').addClass('last');
};

Generic.removeExtraGformStyles = function($) {
	// Since we're re-registering the Gravity Form stylesheet
	// manually and we can't dequeue the stylesheet GF adds
	// by default, we're removing the reference to the script if
	// it exists on the page (if CSS hasn't been turned off in GF settings.)
	$('link#gforms_css-css').remove();
};

Generic.PostTypeSearch = function($) {
	$('.post-type-search')
		.each(function(post_type_search_index, post_type_search) {
			var post_type_search = $(post_type_search),
				form             = post_type_search.find('.post-type-search-form'),
				field            = form.find('input[type="text"]'),
				working          = form.find('.working'),
				results          = post_type_search.find('.post-type-search-results'),
				by_term          = post_type_search.find('.post-type-search-term'),
				by_alpha         = post_type_search.find('.post-type-search-alpha'),
				sorting          = post_type_search.find('.post-type-search-sorting'),
				sorting_by_term  = sorting.find('button:eq(0)'),
				sorting_by_alpha = sorting.find('button:eq(1)'),

				post_type_search_data  = null,
				search_data_set        = null,
				column_count           = null,
				column_width           = null,

				typing_timer = null,
				typing_delay = 300, // milliseconds

				prev_post_id_sum = null, // Sum of result post IDs. Used to cache results 

				MINIMUM_SEARCH_MATCH_LENGTH = 2;

			// Get the post data for this search
			post_type_search_data = PostTypeSearchDataManager.searches[post_type_search_index];
			if(typeof post_type_search_data == 'undefined') { // Search data missing
				return false;
			}

			search_data_set = post_type_search_data.data;
			column_count    = post_type_search_data.column_count;
			column_width    = post_type_search_data.column_width;

			if(column_count == 0 || column_width == '') { // Invalid dimensions
				return false;
			}

			// Sorting toggle
			sorting_by_term.click(function() {
				by_alpha.fadeOut('fast', function() {
					by_term.fadeIn();
					sorting_by_alpha.removeClass('active');
					sorting_by_term.addClass('active');
				});
			});
			sorting_by_alpha.click(function() {
				by_term.fadeOut('fast', function() {
					by_alpha.fadeIn();
					sorting_by_term.removeClass('active');
					sorting_by_alpha.addClass('active');
				});
			});

			// Search form
			form
				.submit(function(event) {
					// Don't allow the form to be submitted
					event.preventDefault();
					perform_search(field.val());
				})
			field
				.keyup(function() {
					// Use a timer to determine when the user is done typing
					if(typing_timer != null)  clearTimeout(typing_timer);
					typing_timer = setTimeout(function() {form.trigger('submit');}, typing_delay);
				});

			function display_search_message(message) {
				results.empty();
				results.append($('<p class="post-type-search-message"><big>' + message + '</big></p>'));
				results.show();
			}

			function perform_search(search_term) {
				var matches             = [],
					elements            = [],
					elements_per_column = null,
					columns             = [],
					post_id_sum         = 0;

				if(search_term.length < MINIMUM_SEARCH_MATCH_LENGTH) {
					results.empty();
					results.hide();
					return;
				}
				// Find the search matches
				$.each(search_data_set, function(post_id, search_data) {
					$.each(search_data, function(search_data_index, term) {
						if(term.toLowerCase().indexOf(search_term.toLowerCase()) != -1) {
							matches.push(post_id);
							return false;
						}
					});
				});
				if(matches.length == 0) {
					display_search_message('No results were found.');
				} else {

					// Copy the associated elements
					$.each(matches, function(match_index, post_id) {

						var element     = by_term.find('li[data-post-id="' + post_id + '"]:eq(0)'),
							post_id_int = parseInt(post_id, 10);
						post_id_sum += post_id_int;
						if(element.length == 1) {
							elements.push(element.clone());
						}
					});

					if(elements.length == 0) {
						display_search_message('No results were found.');
					} else {

						// Are the results the same as last time?
						if(post_id_sum != prev_post_id_sum) {
							results.empty();
							prev_post_id_sum = post_id_sum;
							

							// Slice the elements into their respective columns
							elements_per_column = Math.ceil(elements.length / column_count);
							for(var i = 0; i < column_count; i++) {
								var start = i * elements_per_column,
									end   = start + elements_per_column;
								if(elements.length > start) {
									columns[i] = elements.slice(start, end);
								}
							}

							// Setup results HTML
							results.append($('<div class="row"></div>'));
							$.each(columns, function(column_index, column_elements) {
								var column_wrap = $('<div class="' + column_width + '"><ul></ul></div>'),
									column_list = column_wrap.find('ul');

								$.each(column_elements, function(element_index, element) {
									column_list.append($(element));
								});
								results.find('div[class="row"]').append(column_wrap);
							});
							results.show();
						}
					}
				}
			}
		});
};


var handleAlerts = function($) {
	var ALERT_COOKIE_NAME = 'ucf_today_alerts';
	
	function extract_post_meta(data) {
		var post = [];
		post['id'] 		= data.substr(0, data.indexOf('-'));
		post['time']	= data.substr(data.indexOf('-') + 1, data.length);
		return (post['id'] == undefined || post['time'] == undefined) ? null : post;
	}
	function compact_post_meta(id, time) {return id + '-' + time;}
	
	$('#alerts ul li')
		.each(function(index, li){
			$(li)
				.find('a.close')
				.click(function(_event) {
					_event.preventDefault();
					var li 				= $('#alerts ul li:eq(' + index + ')'),
						hidden_posts 	= $.cookie(ALERT_COOKIE_NAME);
						
					var cur_post = extract_post_meta(li.attr('id').replace('alert-', ''));
					
					if(cur_post != null) {	
						if(hidden_posts !== null) { // the cookie is not set
							if(hidden_posts.indexOf(cur_post['id']) != -1) { // first time this post is being hidden? 
								hidden_posts = hidden_posts.split(',');
							
								for(var _index in hidden_posts) {
									var post = extract_post_meta(hidden_posts[_index]);
									if(post != null && cur_post['id'] == post['id']) {
										if(cur_post['time'] != post['id']) {
											/*	
												This alert is being hidden after it was updated in Wordpress.
												Update the cookie with the new post_modified time.
											*/ 
											$.cookie(ALERT_COOKIE_NAME, 
												$.cookie(ALERT_COOKIE_NAME)
													.replace(compact_post_meta(post['id'],post['time']), 
														compact_post_meta(cur_post['id'], cur_post['time'])),
															{ path: '/', domain: '.ucf.edu'});
										}
										break;
									}
								}
							} else {
								$.cookie(
									ALERT_COOKIE_NAME, 
									$.cookie(ALERT_COOKIE_NAME) + ',' + compact_post_meta(cur_post['id'], cur_post['time']),
									{ path: '/', domain: '.ucf.edu'}
								);
							}
						} else {
							$.cookie(
								ALERT_COOKIE_NAME, 
								compact_post_meta(cur_post['id'], cur_post['time']),
								{ path: '/', domain: '.ucf.edu'}
							);
						}
					}
					li.hide();
				});
		});

};

var fitHeaderText = function($) {
	// Force our header h1 to fit on one line
	var h1 = $('#page-title h1');
	if(h1.length == 1 && h1.children().length === 0) {
		h1.textFit({
			minFontSize: 25,
			maxFontSize: 52
		});
	}
};


var addEllipses = function($) {
	if ($('p.story-blurb').length > 0) { 
		$('p.story-blurb').each(function() {
			$(this).ellipsis();
		});
	}
};


var ieThumbCropper = function($) {
	$('body.ie-old .thumb.cropped').each(function() {
		var thumbWrap = $(this),
			thumbWrapW = thumbWrap.width(),
			thumbWrapH = thumbWrap.height(),
			thumb = thumbWrap.find('img');
		// clone the original thumb to get its width/height before css
		var clone = new Image();
			clone.src = thumb.attr('src');
		var thumbW = clone.width,
			thumbH = clone.height,
			isLandscape = (thumbW >= thumbH);
		// Landscape values; overridden for Portraits
		var newThumbW = Math.ceil((thumbWrapH * thumbW) / thumbH),
			newThumbH = thumbWrapH,
			thumbTop = '0',
			thumbLeft = Math.ceil('-' + ((newThumbW - thumbWrapW) / 2));

		// First, kill the background image on the .cropped div
		thumbWrap.attr('style', '');

		// Adjust new thumb val's for portrait thumbs
		if (isLandscape === false) {
			newThumbW = thumbWrapW,
			newThumbH = Math.ceil((thumbWrapW * thumbH) / thumbW),
			thumbTop = Math.ceil('-' + ((newThumbH - thumbWrapH) / 2)),
			thumbLeft = '0';
		}

		// Set new values
		thumb
			.css({
				'height' : newThumbH + 'px',
				'width' : newThumbW + 'px',
				'left' : thumbLeft + 'px',
				'top' : thumbTop + 'px',
				'max-width' : 'none'
			});
	});
};


var fixIETermLists = function($) {
	// IE is dumb and doesn't support the :after selector
	$('body.ie-old ul.term-list li:not(:last-child) a').each(function() {
		$(this).append(',');
	});
};


var ieVerticalBorders = function($) {
	// More :after (and :before) compensation for old IE...
	$('body.ie-old .border-left, body.ie-old .border-both').each(function() {
		$(this).append('<div class="ie-border-left"></div>');
	})
	$('body.ie-old .border-right, body.ie-old .border-both').each(function() {
		$(this).append('<div class="ie-border-right"></div>');
	})

};


if (typeof jQuery != 'undefined'){
	jQuery(document).ready(function($) {
		Webcom.slideshow($);
		Webcom.analytics($);
		Webcom.handleExternalLinks($);
		Webcom.loadMoreSearchResults($);
		
		/* Theme Specific Code Here */
		Generic.defaultMenuSeparators($);
		Generic.removeExtraGformStyles($);
		Generic.PostTypeSearch($);

		handleAlerts($);
		fitHeaderText($);
		addEllipses($);
		ieThumbCropper($);
		fixIETermLists($);
		ieVerticalBorders($);
	});
}else{console.log('jQuery dependency failed to load');}