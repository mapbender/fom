$(function() {
    // init tabcontainers
    $(".tabContainer").find(".tab").bind("click", function(){
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });

    // init filter inputs
    $(".listFilterInput").bind("keyup", function(){
        var me    = $(this);
        var val   = $.trim(me.val());
        var items = $("#" + me.attr("id").replace("input", "list")).find("li");
    
        if(val.length > 0){
            var item = null;
    
            $.each(items, function(i, e){
                item = $(e);
                (item.text().toUpperCase().indexOf(val.toUpperCase()) >= 0) ? item.show() 
                                                                            : item.hide();
            });
        }else{
            items.show();
        }
    });

    // init validation feedback
    $("form .button").trigger("focus");
    $(".validationInput").one("focus", function(){
      $(this).siblings(".validationMsgBox").addClass("hide");
    });

    // init user box
    $("#accountOpen").bind("click", function(){
        var menu = $("#accountMenu");
        if(menu.hasClass("opened")){
            menu.removeClass("opened");
        }else{
            menu.addClass("opened");
        }
    });
});