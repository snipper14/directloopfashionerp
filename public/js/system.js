$(document).ready(function () {
    $(window).scroll(function (e) {
        var $el = $(".order-cart");
        var isPositionFixed = $el.css("position") == "fixed";
        if ($(this).scrollTop() > 200 && !isPositionFixed) {
            $el.css({ position: "fixed", top: "25%" });
        }
        if ($(this).scrollTop() < 200 && isPositionFixed) {
            $el.css({ position: "static", top: "25%" });
        }
    });
});
