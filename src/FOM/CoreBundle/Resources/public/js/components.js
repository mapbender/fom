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
    $(document).on("keypress", ".validationInput", function(){
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

    // add user or groups
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
                                parent   = $(e).parent();
                                text     = parent.find(".labelInput").text().trim();
                                val      = parent.find(".hide").text().trim();
                                userType = parent.hasClass("iconGroup") ? "iconGroup" : "iconUser";
                                newEl = body.prepend(proto.replace(/__name__/g, count))
                                            .find("tr:first");

                                newEl.addClass("new").find(".labelInput").text(text);
                                newEl.find(".input").attr("value", val);
                                newEl.find(".view .checkbox").trigger("click");
                                newEl.find(".userType")
                                     .removeClass("iconGroup")
                                     .removeClass("iconUser")
                                     .addClass(userType);
                                ++count;
                            });

                            $("body").mbPopup('close');
                        }
                    }, null, function(){
                        var groupUserItem, text, me, groupUserType;
                        $("#listFilterGroupsAndUsers").find(".filterItem").each(function(i, e){
                            groupUserItem = $(e);
                            groupUserType = (groupUserItem.find(".tdContentWrapper")
                                                          .hasClass("iconGroup") ? "iconGroup" 
                                                                                 : "iconUser");

                            $("#permissionsBody").find(".labelInput").each(function(i, e){
                                me = $(e);
                                text = me.text().trim();
                                if((groupUserItem.text().trim().toUpperCase().indexOf(text.toUpperCase()) >= 0) &&
                                   (me.parent().hasClass(groupUserType))){
                                    groupUserItem.remove();
                                }
                            });
                        });
                    });
            }
        }

        return false;
    });
    var deleteUserGroup = function(){
        var me     = $(this);
        var parent = me.parent().parent();
        var userGroup = ((parent.find(".iconUser").length == 1) ? "user " : "group ") + parent.find(".labelInput").text();

        if(!$('body').data('mbPopup')) {
            $("body").mbPopup();
            $("body").mbPopup('showModal',
                {
                    title:"Confirm delete",
                    content:"Really delete " + userGroup + "?"
                },
                function(){
                    parent.remove();
                    $("body").mbPopup('close');
                });
        }
    }
    $("#permissionsBody").on("click", '.iconRemove', deleteUserGroup);






    // init open toggle trees ----------------------------------------------------------------
    var toggleList = function(){
        var me     = $(this);
        var parent = me.parent();
        if(parent.hasClass("closed")){
            me.removeClass("iconExpandClosed").addClass("iconExpand");
            parent.removeClass("closed");
        }else{
            me.addClass("iconExpandClosed").removeClass("iconExpand");
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
        if(me.is(":disabled")){
           parent.addClass("checkboxDisabled");
        }
    }
    $(document).on("change", ".checkbox", toggleCheckBox);
    $(".checkbox").trigger("change");




    // init dropdown list --------------------------------------------------------------------
    var toggleList = function(){
        var me   = $(this);
        var list = me.find(".dropdownList");
        var opts = me.find(".hiddenDropdown");
        if(list.css("display") == "block"){
            list.hide();
        }else{
            list.show();
            list.find("li").one("click", function(event){
                console.log("aaa");
                event.stopPropagation();
                list.hide().find("li").off("click");
                var me2 = $(this);
                var opt = me2.attr("class").replace("item", "opt");
                me.find(".dropdownValue").text(me2.text());
                opts.find("[selected=selected]").removeAttr("selected");
                opts.find("." + opt).attr("selected", "selected");
            })
        }

        $(document).one("click", function(){
            list.hide().find("li").off("mouseout").off("click");
        });
        return false;
    }
    $(document).on("click", ".dropdown", toggleList);
});