(function ($) {
    $.fn.initSidebar = function () {
        return this.each(function () {
            const $element = $(this);

            if ($element.hasClass('sidebar')) {
                $("nav ul li").on('click', function () {
                    $(".sidebar ul li.active").removeClass('active');
                    $(this).addClass('active');
                });

                $('.open-btn').on('click', function () {
                      $('.sidebar').addClass('active');
                });

                $('.close-btn').on('click', function () {
                      $('.sidebar').removeClass('active');
                })
            }

        })
    }
})(jQuery)





