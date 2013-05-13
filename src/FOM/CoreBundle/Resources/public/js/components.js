$(function() {
    // init tabcontainers --------------------------------------------------------------------
    $(".tabContainer").find(".tab").bind("click", function(){
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });





    // init filter inputs --------------------------------------------------------------------
    $(document).on("keyup", ".listFilterInput", function(){
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





    // kill some flashes ---------------------------------------------------------------------
    setTimeout(function(){$(".flashBox").addClass("kill");}, 2000);





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
        var checkedCount = permBody.find(".tagWrapper." + className + ".iconCheckboxActive").length;

        root.removeClass("iconCheckboxActive").removeClass("multi");

        if(rowCount == checkedCount){
            root.addClass("iconCheckboxActive");
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
        permElements.prop("checked", !me.hasClass("iconCheckboxActive")).change();
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












    //filter popupresult
    $("#addPermission").bind("click", function(){
        if(!$('body').data('mbPopup')) {
            var url = $(this).attr("href");

            if(url.length > 0){
                $("body").mbPopup();
                $("body").mbPopup('showAjaxModal', {title:"Add users and groups", btnOkLabel: "Add"}, url,
                    function(){
                        var proto = $("#permissionsHead").attr("data-prototype");

                        if(proto.length > 0){
                            var body  = $("#permissionsBody");
                            var count = body.find("tr").length;
                            var text, val, parent, newEl;

                            $("#listFilterGroupsAndUsers").find(".iconCheckboxActive").each(function(i, e){
                                parent = $(e).parent();
                                text   = parent.find(".labelInput").text().trim();
                                val    = parent.find(".hide").text().trim();

                                newEl = body.prepend(proto.replace(/__name__/g, count))
                                            .find("tr:first");

                                newEl.addClass("new").find(".labelInput").text(text);
                                newEl.find(".input").attr("value", val);
                                ++count;
                            });

                            $("body").mbPopup('close');
                        }
                    }, null, function(){
                        var item, text;
                        $("#listFilterGroupsAndUsers").find(".filterItem").each(function(i, e){
                            item = $(e);

                            $("#permissionsBody").find(".labelInput").each(function(i, e){
                                text = $(e).text().trim();
                                if(item.text().trim().toUpperCase().indexOf(text.toUpperCase()) >= 0){
                                    item.remove();
                                }
                            });
                        });
                    });
            }
        }

        return false;
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

        (me.is(":checked")) ? parent.addClass("iconCheckboxActive") 
                            : parent.removeClass("iconCheckboxActive");
    }
    $(document).on("change", ".checkbox", toggleCheckBox);




    // init dropdown list --------------------------------------------------------------------
    var loadDropDown = function(){
        var me            = $(this);
        var selected      = me.find(".selected");
        var dropDownInput = me.find(".dropdownValue");

        me.find(".dropdownList").hide();
        dropDownInput.val(selected.text()).attr("data-value", selected.attr("data-value"));
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
                var dropDownInput = parent.siblings(".dropdownValue");

                item.siblings(".selected").removeClass("selected");
                item.addClass("selected");

                parent.find("li").unbind("click");
                parent.parent().change();
            });
        }
    }
    $(document).on("change", ".dropdown", loadDropDown)
               .on("click", ".dropdown", switchValue);
});