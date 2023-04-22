
jQuery(document).ready(function ($) {
  let scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
  // set css variable for scrollbar width on body
  document.body.style.setProperty("--scrollbar-width", scrollBarWidth + "px");

  $("a[href='nolink']").on("click", function (e) {
    e.preventDefault();
  });


  // Update footer copyright year
  if ($('.current-year').length) {
    $('.current-year').text(new Date().getFullYear());
  }
  setTimeout(function () {
    $('#preloader').fadeOut('slow');
  }, 500);

  if ($('.iesf-hero-block').length) {
    $('.iesf-hero-block').css('margin-top', $('#masthead').outerHeight());
  }
});
