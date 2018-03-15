/** ****************************************************************************\
 Global Namespace
 the only variable exposed to the window should be Webcom
\******************************************************************************/
const Webcom = {};

if (!window.console) {
  window.console = {
    log() {
      return;
    }
  };
}

// for jslint validation
/* global window, document, Image, google, $, jQuery */

Webcom.handleExternalLinks = function ($) {
  $('a:not(.ignore-external)').each(function () {
    const url  = $(this).attr('href');
    const host = window.location.host.toLowerCase();

    if (url && url.search(host) < 0 && url.search('http') > -1) {
      $(this).attr('target', '_blank');
      $(this).addClass('external');
    }
  });
};

Webcom.loadMoreSearchResults = function ($) {
  const more        = '#search-results .more';
  const items       = '#search-results .result-list .item';
  const list        = '#search-results .result-list';
  const start_class = 'new-start';

  let next = null;
  let sema = null;

  var load = function () {
    if (sema) {
      setTimeout(() => {
        load();
      }, 100);
      return;
    }

    if (next == null) {
      return;
    }

    // Grab results content and append to current results
    const results = $(next).find(items);

    // Add navigation class for scroll
    $(`.${start_class}`).removeClass(start_class);
    $(results[0]).addClass(start_class);

    $(list).append(results);

    // Grab new more link and replace current with new
    const anchor = $(next).find(more);
    if (anchor.length < 1) {
      $(more).remove();
    }
    $(more).attr('href', anchor.attr('href'));

    next = null;
  };

  const prefetch = function () {
    sema = true;
    // Fetch url for href via ajax
    const url = $(more).attr('href');
    if (url) {
      $.ajax({
        url,
        success(data) {
          next = data;
        },
        complete() {
          sema = false;
        }
      });
    }
  };

  const load_and_prefetch = function () {
    load();
    prefetch();
  };

  if ($(more).length > 0) {
    load_and_prefetch();

    $(more).click(() => {
      load_and_prefetch();
      const scroll_to = $(`.${start_class}`).offset().top - 10;

      let element = 'body';

      if ($.browser.mozilla || $.browser.msie) {
        element = 'html';
      }

      $(element).animate({
        scrollTop : scroll_to
      }, 1000);
      return false;
    });
  }
};

Webcom.slideshow = function ($) {
  /**
	 * Create slideshow of arbitrary objects.  Class each item to be a slide
	 * as 'slide', and recommend you set a static height and width on the
	 * slideshow container.
	 *
	 * Example:
	 * <div class="slides">
	 *   <img class="slide"...>
	 *   <div class="slide">...</div>
	 * </div>
	 *
	 * $('.slides').slideShow({
	 *   'transition_length' : 2000,
	 *   'cycle_length': 4000
	 * });
	 *
	 * The options can be overridden by setting the data-tranlen and
	 * data-cyclelen attributes on the slideshow container.
	 **/
  $.fn.slideShow = function (args) {
    var cycle = function (items, index) {
      if (items.length < 1) {
        return;
      }

      const next_index = (index + 1) % items.length;

      const active = $(items[index]);
      const next   = $(items[next_index]);

      if (animating) {
        animation(active, next, () => {
          setTimeout(() => {
            cycle(items, next_index);
          }, options.cycle_length);
        });
      } else {
        setTimeout(() => {
          cycle(items, next_index - 1);
        }, 100);
      }
      return;
    };

    const slideAnimation = function (active, next, complete_callback) {
      next.css({
        right : width
      });
      next.show();

      active.animate({
        right : `-=${width}`
      }, options.transition_length, () => {
        next.css({
          right : 0
        });
      });

      next.animate({
        right : 0
      }, options.transition_length, () => {
        next.css({
          right : 0
        });
        complete_callback();
      });
    };
    slideAnimation.init = function (container, items) {
      const first = $(items[0]);

      container.css({
        position : 'relative',
        overflow : 'hidden'
      });
      items.css({
        position : 'absolute',
        display  : 'none',
        width    : `${width}px`
      });

      first.show();
    };

    const fadeAnimation = function (active, next, complete_callback) {
      active.animate({
        opacity : '0.0'
      }, options.transition_length, () => {
        active.css({
          display : 'none',
          opacity : '1.0'
        });
      });
      next.css({
        display : 'block',
        opacity : '0.0'
      });
      next.animate({
        opacity : '1.0'
      }, options.transition_length, () => {
        next.css({
          display : 'block',
          opacity : '1.0'
        });
        complete_callback();
      });
    };
    fadeAnimation.init = function (container, items) {
      const first = $(items[0]);

      if ($.browser.msie) {
        // Lessens the visibility of the opacity filter bug in IE6/7/8
        container.css({
          'background-color' : '#000'
        });
      }
      container.css({
        position : 'relative',
        overflow : 'hidden'
      });

      items.css({
        position : 'absolute',
        display  : 'none'
      });

      first.show();
    };

    const animations = {
      slide : slideAnimation,
      fade  : fadeAnimation
    };

    // Options configurations
    const defaults = {
      transition_length : 1000,
      cycle_length      : 5000,
      animation         : 'slide'
    };
    var options   = $.extend({}, defaults, args);

    const container = $(this);
    const height    = container.height();
    var width     = container.width();
    const items     = container.children();
    var animating = true;

    // Stop animation while user mouse is hovering
    $(container).mouseenter(() => {
      animating = false;
    });
    $(container).mouseleave(() => {
      animating = true;
    });

    // data attribute overrides
    if (container.attr('data-tranlen')) {
      options.transition_length = parseInt(container.attr('data-tranlen'));
    }
    if (container.attr('data-cyclelen')) {
      options.cycle_length = parseInt(container.attr('data-cyclelen'));
    }

    var animation  = animations[options.animation];
    animation.init(container, items);

    return setTimeout(() => {
      cycle(items, 0);
    }, options.cycle_length);
  };

  $('.slideshow.fade').slideShow({
    transition_length : 750,
    cycle_length      : 2000,
    animation         : 'fade'
  });
  $('.slideshow.slide').slideShow({
    transition_length : 750,
    cycle_length      : 2000,
    animation         : 'slide'
  });
};
