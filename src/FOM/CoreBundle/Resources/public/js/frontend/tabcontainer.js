var initTabContainer = function ($context) {
    $(".tabContainer, .tabContainerAlt").on('click', '.tab', function () {
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });
    $(".accordionContainer", $context).on('click', '.accordion', function (event) {
        var me = $(this);
        var tabcont = $(event.delegateTarget)
        var wasActive = me.hasClass('active');
        if (!wasActive) {
            tabcont.find(".active").removeClass("active");
            me.parents(".tabContainer:first,.tabContainerAlt:first").removeClass('noActive');
            me.addClass('active');
            $("#" + me.attr("id").replace("accordion", "container"), tabcont).addClass("active");
        }
    });
};

$(function () {
    // init tabcontainers --------------------------------------------------------------------
    initTabContainer($('body'));
});
