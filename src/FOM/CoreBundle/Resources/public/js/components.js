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
        var checkedCount = permBody.find(".checkWrapper." + className + ".iconCheckboxActive").length;
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
        var self           = $(this);
        var className    = self.attr("id");
        var permElements = $(".checkWrapper[data-perm-type=" + className + "]:visible");
        var state        = !self.hasClass("iconCheckboxActive");
        var me;

        // change all tagboxes with the same permission type
        permElements.find(".checkbox").each(function(i,e){
            me = $(e);
            me.get(0).checked = state;

            if(state){
                me.parent().addClass("iconCheckboxActive");
            }else{
                me.parent().removeClass("iconCheckboxActive");
            }
        });

        // change root permission state
        setPermissionsRootState(className);
    }
    // init permission root state
    var initPermissionRoot = function(){
        $(this).find(".headTagWrapper").each(function(){
            setPermissionsRootState($(this).attr("id"));
            $(this).bind("click", toggleAllPermissions);
        });    
    }
    $("#permissionsHead").one("load", initPermissionRoot).load();

    // toggle permission Event
    var togglePermission = function(){
        setPermissionsRootState($(this).attr("data-perm-type"));
    }
    $(document).on("click", ".permissionsTable .checkWrapper", togglePermission);

    var popup;

    // add user or groups
    $("#addPermission").bind("click", function(){
        var self    = $(this);
        var url     = self.attr("href");
        var content = self.attr('title');

        if(popup){
            popup = popup.destroy();
        }

        if(url.length > 0){
            popup = new Mapbender.Popup2({
                title:"Add users and groups",
                closeOnOutsideClick: true,
                content: [
                    $.ajax({
                        url: url,
                        complete: function() {
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
                        }
                    })
                ],
                buttons: {
                    'cancel': {
                        label: 'Cancel',
                        cssClass: 'button buttonCancel critical right',
                        callback: function() {
                            this.close();
                        }
                    },
                    'add': {
                        label: 'Add',
                        cssClass: 'button right',
                        callback: function() {
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
                                    newEl.find(".view.checkWrapper").trigger("click");
                                    newEl.find(".userType")
                                         .removeClass("iconGroup")
                                         .removeClass("iconUser")
                                         .addClass(userType);
                                    ++count;
                                });

                                this.close();
                                $(".permissionsTable").show();
                                $("#permissionsDescription").hide();
                            }
                        }
                    }
                }
            });
        }

        return false;
    });
    var popup;

    var deleteUserGroup = function(){
        var self = $(this);
        var parent = self.parent().parent();
        var userGroup = ((parent.find(".iconUser").length == 1) ? "user " : "group ") + parent.find(".labelInput").text();

        if(popup){
            popup = popup.destroy();
        }
        popup = new Mapbender.Popup2({
            title: "Confirm delete",
            closeOnOutsideClick: true,
            content: [
                "Really delete " + userGroup + "?"
            ],
            buttons: {
                'cancel': {
                    label: 'Cancel',
                    cssClass: 'button buttonCancel critical right',
                    callback: function() {
                        this.close();
                    }
                },
                'ok': {
                    label: 'OK',
                    cssClass: 'button right',
                    callback: function() {
                        parent.remove();
                        this.close();
                    }
                }
            }
        });
        return false;
    }
    $("#permissionsBody").on("click", '.iconRemove', deleteUserGroup);






    // init open toggle trees ----------------------------------------------------------------
    var toggleTree = function(){
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
    $(".openCloseTitle").bind("click", toggleTree);
});