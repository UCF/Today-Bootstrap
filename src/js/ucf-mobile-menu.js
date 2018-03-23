/* global $ */
(function () {


  const $menu = $('.ucf-mobile-menu');

  function openMenu() {
    $menu.addClass('slide-out');
    $menu.height($(document).height());
  }

  function closeMobileMenu() {
    $menu.removeClass('slide-out');
  }

  function closeMobileMenuHandler() {
    $(document).click((e) => {
      const $target = $(e.target);

      // Hide the mobile menu when anything else is clicked
      if (!$target.closest('.ucf-mobile-menu').length && !$target.closest('.ucf-mobile-menu-trigger').length) {
        if ($menu.width() > 200) {
          closeMobileMenu();
        }
      }
    });
  }

  function closeMobileMenuIconHandler() {
    $menu.find('.close-icon').click(closeMobileMenu);
  }

  function setupEventHandlers() {
    $('.ucf-mobile-menu-trigger').click(openMenu);
  }

  function init() {
    setupEventHandlers();
    closeMobileMenuHandler();
    closeMobileMenuIconHandler();
  }

  $(init);
}());
