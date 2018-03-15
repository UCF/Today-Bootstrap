/* global $ */
(function () {
	'use strict';

	var menuSelector;
  var menuTriggerSelector;
  var $menuTrigger;
  var menuTriggerClass;
  var $menu;
  var menuSlideoutClass;
  var menuCloseSelector;
  var $bodyOverlay;
  var bodyOverlayClass;


	// function openMenu() {
  //   $menu.addClass(menuSlideoutClass);
  //   $menu.height($(document).height());
	// }

  function closeMenu() {
    $menu.removeClass(menuSlideoutClass);
    $menuTrigger.removeClass(menuTriggerClass);
    $bodyOverlay.removeClass(bodyOverlayClass);
  }

  function toggleMenu() {
    $menu.toggleClass(menuSlideoutClass);
    $menuTrigger.toggleClass(menuTriggerClass);
    $bodyOverlay.toggleClass(bodyOverlayClass);
  }

  function closeMobileMenuHandler() {
    $(document).click(function(e) {
      var $target = $(e.target);

        // Hide the mobile menu when anything else is clicked
        if (!$target.closest(menuSelector).length && !$target.closest(menuTriggerSelector).length) {
          if ($menu.width() > 200) {
            closeMenu();
          }
        }
    });
  }

  function closeMobileMenuIconHandler() {
    $menu.find(menuCloseSelector).click(closeMenu);
  }

	function setupEventHandlers() {
		$(menuTriggerSelector).click(toggleMenu);
	}

	function init() {
    menuSelector = '.site-nav';
    menuTriggerSelector = '.ucf-mobile-menu-trigger';
    $menuTrigger = $(menuTriggerSelector);
    menuTriggerClass = 'active';
    $menu = $(menuSelector);
    menuSlideoutClass = 'slideout';
    menuCloseSelector = '.close-icon';
    $bodyOverlay = $('#nav-overlay');
    bodyOverlayClass = 'in';

		setupEventHandlers();
		closeMobileMenuHandler();
		closeMobileMenuIconHandler();
	}

	$(init);
}());
