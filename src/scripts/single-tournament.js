jQuery(document).ready(function ($) {
    let stickyElement = $(".tournament-info-card");
    stickyElement.css('top', ($("#masthead").outerHeight() / 10) + 4 + 'rem');

    $('.single-tournaments .grid .content .tabs-tab-header-item').each(function (index) {
        $(this).attr('data-tab', index);
    });
    $('.single-tournaments .grid .content .tabs-tab-content-item').each(function (index) {
        $(this).attr('data-tab', index);
    });
    $('.single-tournaments .grid .content .tabs-tab-header-item').first().addClass('active');
    $('.single-tournaments .grid .content .tabs-tab-content-item').first().addClass('active');

    $('.single-tournaments .tabs-tab-header-item').on('click', function () {
        let targetEl = $(this).attr('data-tab');
        $('.single-tournaments .tabs-tab-header-item').removeClass('active');
        $(this).addClass('active');

        $('.single-tournaments .tabs-tab-content-item').removeClass('active');
        $('.single-tournaments .tabs-tab-content-item[data-tab="' + targetEl + '"]').addClass('active');
    });
    $('.single-tournaments .lineup-wrapper > li').on('click', function (e) {
        e.preventDefault();
        // :not(.lineup-members-list)
        if (e.target !== this) {
            return;
        }
        $(this).find('.accordion-expand').toggleClass('active');
        $(this).toggleClass('active');
        $(this).find('.lineup-members').slideToggle();
    });
});