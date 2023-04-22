jQuery(document).ready(function ($) {
    // $("body").css("padding-top", $("#masthead").outerHeight() + "px");
    $(window).scroll(function () {
        scroll = $(window).scrollTop();
        if (scroll >= 100) {
            $("#masthead").addClass("sticky");
        } else {
            $("#masthead").removeClass("sticky");
        }
    });

});
