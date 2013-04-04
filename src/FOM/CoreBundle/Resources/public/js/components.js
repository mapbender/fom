$(function() {
    // init tabcontainers --------------------------------------------------------------------
    $(".tabContainer").find(".tab").bind("click", function(){
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });





    // init filter inputs --------------------------------------------------------------------
    $(".listFilterInput").bind("keyup", function(){
        var me    = $(this);
        var val   = $.trim(me.val());
        var items = $("#" + me.attr("id").replace("input", "list")).find("li, tr");

        if(val.length > 0){
            var item = null;

            $.each(items, function(i, e){
                item = $(e);
                if(!item.hasClass("doNotFilter")){
                    (item.text().toUpperCase().indexOf(val.toUpperCase()) >= 0) ? item.show() 
                                                                                : item.hide();
                }
            });
        }else{
            items.show();
        }
    });





    // init validation feedback --------------------------------------------------------------
    $(".validationInput").one("keypress", function(){
      $(this).siblings(".validationMsgBox").addClass("hide");
    });





    // init user box -------------------------------------------------------------------------
    $("#accountOpen").bind("click", function(){
        var menu = $("#accountMenu");
        if(menu.hasClass("opened")){
            menu.removeClass("opened");
        }else{
            menu.addClass("opened");
        }
    });





    // init permissions table ----------------------------------------------------------------
    // init permissions toggle
    function setPermissionsToggleState(className){
        var me             = $("#" + className);
        var permBody       = $("#permissionsBody");
        var rowCount       = permBody.find("tr").length;
        var classNameCount = permBody.find(".selected." + className).length;

        me.removeClass("selected").removeClass("multi");

        if(rowCount == classNameCount){
            me.addClass("selected");
        }else if(classNameCount == 0){
            // do nothing!
        }else{
            me.addClass("multi");
        }
    }
    var togglePermission = function(){
        var me     = $(this);
        var tagBox = me.siblings(".tagBox");

        if(me.is(":checked")){
            tagBox.addClass("selected");
        }else{
            tagBox.removeClass("selected");
        }

        setPermissionsToggleState(me.attr("class"));
    }
    var toggleAllPermissions = function(){
        var me           = $(this);
        var className    = me.attr("id");
        var permElements = $("input." + className + ":visible");
//XXXVH? Bug here? :)
        permElements.prop("checked", !me.hasClass("selected")).change();
    }
    $(".permissionsTable").find("input")
                          .bind("change", togglePermission);
    $("#permissionsHead").find(".tagBox")
                         .bind("click", toggleAllPermissions);
    $("#permissionsHead").one("load", function(){
        $(this).find(".tagBox").each(function(){
            setPermissionsToggleState($(this).attr("id"));
        });
    }).load();






    // init open toggle trees ----------------------------------------------------------------
    var toggleList = function(){
        var parent = $(this).parent();
        if(parent.hasClass("closed")){
            parent.removeClass("closed");
        }else{
            parent.addClass("closed");
        }
    }
    $(".openCloseTitle").bind("click", toggleList);
});