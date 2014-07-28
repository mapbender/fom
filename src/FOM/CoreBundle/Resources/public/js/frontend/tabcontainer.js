var initTabContainer = function($context){
    $(".tabContainer, .tabContainerAlt", $context).on('click', '.tab,.accordion', function() {
        var me = $(this);
        var tabcont;
        if(me.parent().hasClass('tabContainerAlt')){
            tabcont = me.parent();
        } else if(me.parent().parent().hasClass('tabContainer')){
            tabcont = me.parent().parent();
        }
        tabcont.find(".active").removeClass("active");
        me.addClass("active");
        if (me.hasClass('tab')) {
            $("#" + me.attr("id").replace("tab", "container"), tabcont).addClass("active");
        } else if (me.hasClass('accordion')) {
            $("#" + me.attr("id").replace("accordion", "container"), tabcont).addClass("active");
        }
    });
};

$(function() {
    // init tabcontainers --------------------------------------------------------------------
    initTabContainer($('body'));
});
