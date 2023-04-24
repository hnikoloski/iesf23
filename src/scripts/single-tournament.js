jQuery(document).ready(function ($) {
    let stickyElement = $(".tournament-info-card");
    stickyElement.css('top', ($("#masthead").outerHeight() / 10) + 4 + 'rem');

    $('.single-tournaments .tabs-tab-header-item').on('click', function () {
        let targetEl = $(this).attr('data-tab');
        $('.single-tournaments .tabs-tab-header-item').removeClass('active');
        $(this).addClass('active');

        $('.single-tournaments .tabs-tab-content-item').removeClass('active');
        $('.single-tournaments .tabs-tab-content-item[data-tab="' + targetEl + '"]').addClass('active');
    });
    $('.single-tournaments .accordion-expand').on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).parent().toggleClass('active');
        $(this).siblings('.lineup-members').slideToggle();
    });
});