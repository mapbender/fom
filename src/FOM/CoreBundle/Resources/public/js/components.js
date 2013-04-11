$(function() {
    // init tabcontainers --------------------------------------------------------------------
    $(".tabContainer").find(".tab").bind("click", function(){
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });





    // init filter inputs --------------------------------------------------------------------
    $(".listFilterInput").on("keyup", function(){
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
    // set permission root state
    function setPermissionsRootState(className){
        var root         = $("#" + className);
        var permBody     = $("#permissionsBody");
        var rowCount     = permBody.find("tr").length;
        var checkedCount = permBody.find(".tagWrapper." + className + ".checked").length;

        root.removeClass("checked").removeClass("multi");

        if(rowCount == checkedCount){
            root.addClass("checked");
        }else if(checkedCount == 0){
            // do nothing!
        }else{
            root.addClass("multi");
        }
    }
    // toggle all permissions
    var toggleAllPermissions = function(){
        var me           = $(this);
        var className    = me.attr("id");
        var permElements = $(".checkbox[data-perm-type=" + className + "]:visible");

        // change all tagboxes with the same permission type
        permElements.prop("checked", !me.hasClass("checked")).change();
        // change root permission state
        setPermissionsRootState(className);
    }
    // init permission root state
    var initPermissionRoot = function(){
        $(this).find(".tagWrapper").each(function(){
            setPermissionsRootState($(this).attr("id"));
            $(this).bind("click", toggleAllPermissions);
        });    
    }
    $("#permissionsHead").one("load", initPermissionRoot).load();

    // toggle permission Event
    var togglePermission = function(){
        setPermissionsRootState($(this).attr("data-perm-type"));
    }
    $(".permissionsTable").find(".checkbox").bind("click", togglePermission);













    $("#addPermission").bind("click", function(){
        if(!$('body').data('mbPopup')) {
            $("body").mbPopup();
            $("body").mbPopup('showModal', {content:"add some content!"});
        }
    });






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





    // init checkbox toggle ------------------------------------------------------------------
    var toggleCheckBox = function(){
        var me     = $(this);
        var parent = me.parent();

        (me.is(":checked")) ? parent.addClass("checked") 
                            : parent.removeClass("checked");
    }
    $(".checkbox").on("change", toggleCheckBox);




    // init dropdown list --------------------------------------------------------------------
    var loadDropDown = function(){
        var me            = $(this);
        var selected      = me.find(".selected");
        var dropDownLabel = me.find(".dropdownValue");

        me.find(".dropdownList").hide();
        dropDownLabel.text(selected.text()).attr("data-value", selected.attr("data-value"));
    }
    var switchValue = function(){
        var me        = $(this);
        var value     = me.find(".dropdownValue");
        var valueList = me.find(".dropdownList");

        if(valueList.is(":visible")){
            valueList.hide();
        }else{
            valueList.show().find("li").one("click", function(event){
                event.stopPropagation();
                var item          = $(this);
                var parent        = item.parent();
                var dropDownLabel = parent.siblings(".dropdownValue");

                item.siblings(".selected").removeClass("selected");
                item.addClass("selected");

                parent.find("li").unbind("click");
                parent.parent().change();
            });
        }
    }
    $(".dropdown").bind("change", loadDropDown).change().bind("click", switchValue);
});