var initTabContainer = function($context){
    $(".tabContainer, .tabContainerAlt", $context).on('click', '.tab,.accordion', function(event) {
        var me = $(this);
        var tabcont = $(event.delegateTarget)
        var wasActive = me.hasClass('active');
        tabcont.find(".active").removeClass("active");
        if(me.hasClass('accordion')) {
            if(wasActive) {
                me.removeClass('active');
            } else {
                me.addClass('active');
                $("#" + me.attr("id").replace("accordion", "container"), tabcont).addClass("active");
            }
        } else {
            me.addClass("active");
            $("#" + me.attr("id").replace("tab", "container"), tabcont).addClass("active");
        }
    });
};

$(function() {
    // init tabcontainers --------------------------------------------------------------------
    initTabContainer($('body'));
});
