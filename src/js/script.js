const Generic = {};

Generic.defaultMenuSeparators = function ($) {
  // Because IE sucks, we're removing the last stray separator
  // on default navigation menus for browsers that don't
  // support the :last-child CSS property
  $('.menu.horizontal li:last-child').addClass('last');
};

Generic.removeExtraGformStyles = function ($) {
  // Since we're re-registering the Gravity Form stylesheet
  // manually and we can't dequeue the stylesheet GF adds
  // by default, we're removing the reference to the script if
  // it exists on the page (if CSS hasn't been turned off in GF settings.)
  $('link#gforms_css-css').remove();
};

Generic.PostTypeSearch = function ($) {
  $('.post-type-search')
    .each((post_type_search_index, post_type_search) => {
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
      if (typeof post_type_search_data === 'undefined') { // Search data missing
        return false;
      }

      search_data_set = post_type_search_data.data;
      column_count    = post_type_search_data.column_count;
      column_width    = post_type_search_data.column_width;

      if (column_count == 0 || column_width == '') { // Invalid dimensions
        return false;
      }

      // Sorting toggle
      sorting_by_term.click(() => {
        by_alpha.fadeOut('fast', () => {
          by_term.fadeIn();
          sorting_by_alpha.removeClass('active');
          sorting_by_term.addClass('active');
        });
      });
      sorting_by_alpha.click(() => {
        by_term.fadeOut('fast', () => {
          by_alpha.fadeIn();
          sorting_by_term.removeClass('active');
          sorting_by_alpha.addClass('active');
        });
      });

      // Search form
      form
        .submit((event) => {
          // Don't allow the form to be submitted
          event.preventDefault();
          perform_search(field.val());
        });
      field
        .keyup(() => {
          // Use a timer to determine when the user is done typing
          if (typing_timer != null) {
            clearTimeout(typing_timer);
          }
          typing_timer = setTimeout(() => {
            form.trigger('submit');
          }, typing_delay);
        });

      function display_search_message(message) {
        results.empty();
        results.append($(`<p class="post-type-search-message"><big>${message}</big></p>`));
        results.show();
      }

      function perform_search(search_term) {
        let matches             = [],
          elements            = [],
          elements_per_column = null,
          columns             = [],
          post_id_sum         = 0;

        if (search_term.length < MINIMUM_SEARCH_MATCH_LENGTH) {
          results.empty();
          results.hide();
          return;
        }
        // Find the search matches
        $.each(search_data_set, (post_id, search_data) => {
          $.each(search_data, (search_data_index, term) => {
            if (term.toLowerCase().indexOf(search_term.toLowerCase()) != -1) {
              matches.push(post_id);
              return false;
            }
          });
        });
        if (matches.length == 0) {
          display_search_message('No results were found.');
        } else {

          // Copy the associated elements
          $.each(matches, (match_index, post_id) => {

            let element     = by_term.find(`li[data-post-id="${post_id}"]:eq(0)`),
              post_id_int = parseInt(post_id, 10);
            post_id_sum += post_id_int;
            if (element.length == 1) {
              elements.push(element.clone());
            }
          });

          if (elements.length == 0) {
            display_search_message('No results were found.');
          } else {

            // Are the results the same as last time?
            if (post_id_sum != prev_post_id_sum) {
              results.empty();
              prev_post_id_sum = post_id_sum;


              // Slice the elements into their respective columns
              elements_per_column = Math.ceil(elements.length / column_count);
              for (let i = 0; i < column_count; i++) {
                let start = i * elements_per_column,
                  end   = start + elements_per_column;
                if (elements.length > start) {
                  columns[i] = elements.slice(start, end);
                }
              }

              // Setup results HTML
              results.append($('<div class="row"></div>'));
              $.each(columns, (column_index, column_elements) => {
                let column_wrap = $(`<div class="${column_width}"><ul></ul></div>`),
                  column_list = column_wrap.find('ul');

                $.each(column_elements, (element_index, element) => {
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


const handleAlerts = function ($) {
  const alert_cookie_prefix = 'ucf_today_';

  // Functions to handle alert cookie creation/updating
  function alertCookieExists(alert) {
    // Return true if an alert cookie exists; false if it doesn't
    let alertID = alert.attr('id'),
      alertCookie = $.cookie(alert_cookie_prefix + alertID);
    if (typeof alertCookie !== 'undefined' && alertCookie !== null) {
      return true;
    }
    return false;
  }
  function isAlertUpdated(alert) {
    // Check the alert list item against its existing cookie;
    // if the timestamp on the list item is greater than the cookie
    // value, return true; otherwise return false
    let alertID = alert.attr('id'),
      newAlertTimestamp = parseInt(alert.attr('data-post-modified'), 10);
    if (alertCookieExists(alert) === true) {
      oldAlertTimestamp = parseInt($.cookie(alert_cookie_prefix + alertID), 10);
      if (newAlertTimestamp > oldAlertTimestamp) {
        return true;
      }
    }
    return false;
  }
  function createAlertCookie(alert) {
    let alertID = alert.attr('id'),
      alertTimestamp = parseInt(alert.attr('data-post-modified'), 10);
    $.cookie(
      alert_cookie_prefix + alertID,
      alertTimestamp,
      {
        path: '/',
        domain: '.ucf.edu'
      }
    );
  }
  function deleteAlertCookie(alert) {
    const alertID = alert.attr('id');
    if (alertCookieExists(alert) === true) {
      $.cookie(
        alert_cookie_prefix + alertID,
        null,
        {
          path: '/',
          domain: '.ucf.edu'
        }
      );
    }
  }
  function updateAlertCookie(alert) {
    let alertID = alert.attr('id'),
      alertCookie = $.cookie(alert_cookie_prefix + alertID);
    if (alertCookieExists(alert) === true) {
      deleteAlertCookie(alert);
      createAlertCookie(alert);
    }
  }

  // On-load
  $('#alerts ul li').each(function () {
    const alert = $(this);
    if (alertCookieExists(alert)) {
      if (isAlertUpdated(alert) === false) {
        alert.addClass('hidden');
      } else {
        alert.removeClass('hidden');
        deleteAlertCookie(alert);
      }
    } else {
      alert.removeClass('hidden');
    }
  });
  // On-click close event
  $('#alerts ul li .msg .close').click(function () {
    const alert = $(this).parents('li');
    alert.addClass('hidden');
    if (alertCookieExists(alert)) {
      updateAlertCookie(alert);
    } else {
      createAlertCookie(alert);
    }
  });
};


const fitHeaderText = function ($) {
  // Force our top header element text to fit on one line
  const fittext = function () {
    const header = $('#page-title h1, #page-title h2');
    if ($(window).width() > 767) {
      if (header.length == 1 && header.children().length === 0) {
        header
          .textFit({
            minFontSize: 22,
            maxFontSize: 52
          });
      }
    } else {
      header
        .children('.textfitted')
        .css('font-size', '1em');
    }
  };
  fittext();
  $(window).on('resize', () => {
    fittext();
  });
};


const addEllipses = function ($) {
  if ($('p.story-blurb').length > 0) {
    $('p.story-blurb').each(function () {
      $(this).ellipsis();
    });
  }
};


/* Assign browser-specific body classes on page load */
const addBodyClasses = function ($) {
  if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) { // test for MSIE x.x;
    const ieversion = new Number(RegExp.$1); // capture x.x portion and store as a number
    if (ieversion < 9) {
      $('body').addClass('ie-old');
    } else {
      $('body').addClass('ie-new');
    }
  }
};


const ieThumbCropper = function ($) {
  $('body.ie-old .thumb.cropped').each(function () {
    let thumbWrap = $(this),
      thumbWrapW = thumbWrap.width(),
      thumbWrapH = thumbWrap.height(),
      thumb = thumbWrap.find('img');
    // clone the original thumb to get its width/height before css
    const clone = new Image();
    clone.src = thumb.attr('src');
    let thumbW = clone.width,
      thumbH = clone.height,
      isLandscape = thumbW > thumbH;

    // Landscape values; overridden for Portraits
    let newThumbW = Math.ceil(thumbWrapH * thumbW / thumbH),
      newThumbH = thumbWrapH,
      thumbTop = '0',
      thumbLeft = `-${Math.ceil((newThumbW - thumbWrapW) / 2)}`;
    // Adjust vals if image still doesn't stretch completely
    if (newThumbW < thumbWrapW) {
      newThumbW = thumbWrapW;
      newThumbH = Math.ceil(thumbWrapW * thumbH / thumbW);
      thumbTop = `-${Math.ceil((newThumbH - thumbWrapH) / 2)}`;
      thumbLeft = '0';
    }

    // First, kill the background image on the .cropped div
    thumbWrap.attr('style', '');
    // Adjust new thumb val's for portrait thumbs
    if (isLandscape === false) {
      newThumbW = thumbWrapW,
      newThumbH = Math.ceil(thumbWrapW * thumbH / thumbW),
      thumbTop = `-${Math.ceil((newThumbH - thumbWrapH) / 2)}`,
      thumbLeft = '0';
      // Adjust vals if image still doesn't stretch completely
      if (newThumbH < thumbWrapH) {
        newThumbH = thumbWrapH;
        newThumbW = Math.ceil(thumbWrapH * thumbW / thumbH);
        thumbTop = '0';
        thumbLeft = `-${Math.ceil((newThumbH - thumbWrapH) / 2)}`;
      }
    }

    // Set new values
    thumb
      .css({
        height : `${newThumbH}px`,
        width : `${newThumbW}px`,
        left : `${thumbLeft}px`,
        top : `${thumbTop}px`,
        'max-width' : 'none'
      });
  });
};


const ieVerticalBorders = function ($) {
  // More :after (and :before) compensation for old IE...
  $('body.ie-old .border-left, body.ie-old .border-both').each(function () {
    $(this).append('<div class="ie-border-left"></div>');
  });
  $('body.ie-old .border-right, body.ie-old .border-both').each(function () {
    $(this).append('<div class="ie-border-right"></div>');
  });

};


const socialButtonTracking = function ($) {
  // Track social media button clicks, using GA's 'social' hitType.
  $('div.social a').click(function () {
    let link = $(this),
      target = link.attr('data-button-target'),
      network = '',
      socialAction = '';

    if (link.hasClass('share-facebook')) {
      network = 'Facebook';
      socialAction = 'Like';
    } else if (link.hasClass('share-twitter')) {
      network = 'Twitter';
      socialAction = 'Tweet';
    } else if (link.hasClass('share-googleplus')) {
      network = 'Google+';
      socialAction = 'Share';
    }

    ga('send', 'social', network, socialAction, target);
  });
};

const initMatchHeight = function ($) {
  $('.match-height').matchHeight();
};


if (typeof jQuery !== 'undefined') {
  jQuery(document).ready(($) => {
    Webcom.slideshow($);
    Webcom.handleExternalLinks($);
    Webcom.loadMoreSearchResults($);

    /* Theme Specific Code Here */
    Generic.defaultMenuSeparators($);
    Generic.removeExtraGformStyles($);
    Generic.PostTypeSearch($);

    handleAlerts($);
    fitHeaderText($);
    addEllipses($);
    addBodyClasses($);
    ieThumbCropper($);
    ieVerticalBorders($);
    socialButtonTracking($);
    initMatchHeight($);
  });
} else {
  console.log('jQuery dependency failed to load');
}
