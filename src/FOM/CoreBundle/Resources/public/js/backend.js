$(function() {
    $(".tabContainer").find(".tab").bind("click", function(e){
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");

        var containerId = me.addClass("active")
                            .attr("id")
                            .replace("tab", "tabContainer");
        $("#" + containerId).addClass("active");
    });
});