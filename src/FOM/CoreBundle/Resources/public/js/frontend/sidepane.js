$(function() {

    var switchButton = $(".toggleSideBar");
    var sidePane = switchButton.closest("div.sidePane");
    var templateWrapper = $('#templateWrapper');
    var speed = 300;
    var animation = {};

    sidePane.data('isOpened', true);
    switchButton.on('click', function() {
        var isOpened = sidePane.data('isOpened');
        var align = sidePane.hasClass('right') ? 'right' : 'left';

        if(isOpened) {
            animation[align] =  ["-" + sidePane.outerWidth(true) + "px", "swing"];
            sidePane.animate(animation, speed, function() {
                templateWrapper.removeClass("sidePaneOpened");
                sidePane.data('isOpened', !isOpened);
            });
        } else {
            templateWrapper.addClass("sidePaneOpened");
            animation[align] = ["0px", "swing"];
            sidePane.animate(animation, speed, function() {
                sidePane.data('isOpened', !isOpened);
            });
        }
    });
});
