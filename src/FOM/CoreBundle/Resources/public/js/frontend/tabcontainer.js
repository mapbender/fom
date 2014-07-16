$(function() {
    // init tabcontainers --------------------------------------------------------------------
    $(".tabContainer, .tabContainerAlt").on('click', '.tab,.accordion', function() {
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        if (me.hasClass('tab')) {
            $("#" + me.attr("id").replace("tab", "container")).addClass("active");
        } else if (me.hasClass('accordion')) {
            $("#" + me.attr("id").replace("accordion", "container")).addClass("active");
        }
    });
});
