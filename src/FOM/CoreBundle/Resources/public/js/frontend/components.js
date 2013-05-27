$(function() {
    // init sidebar toggle -------------------------------------------------------------------
    var sideBarToggle = function(){
        var parent = $(this).parent().parent();
        (parent.hasClass("opened")) ? parent.removeClass("opened") : parent.addClass("opened");
    }
    $(".toggleSideBar").bind("click", sideBarToggle);

    // init tabcontainers --------------------------------------------------------------------
    $(".tabContainer").find(".tab").live("click", function(){
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });

    // init dropdown list --------------------------------------------------------------------
    var initDropdown = function(){
        var me = $(this);
        var dropdownList = me.find(".dropdownList");
        var newElement;

        me.find("option").each(function(i, e){
            $(e).addClass("opt-" + i)
            newElement = $('<li class="item-' + i + '">' + $(e).text() + '</li>')
            dropdownList.append(newElement);
        });
        me.find(".dropdownValue").text(me.find("option:first").text())
    }
    var toggleList = function(){
        var me   = $(this);
        var list = me.find(".dropdownList");
        var opts = me.find(".hiddenDropdown");
        if(list.css("display") == "block"){
            list.hide();
        }else{
            list.show();
            list.find("li").bind("click", function(event){
                event.stopPropagation();
                list.hide().find("li").unbind("click");
                var me2 = $(this);
                var liIndex = me2.index();
                me.find(".dropdownValue").text(me2.text());
                opts.find("[selected=selected]").removeAttr("selected");
                opts.find('option:eq(' + liIndex + ')').attr("selected", "selected").change();
            })
        }

        $(document).bind("click", function(){
            list.hide().find("li").unbind("mouseout").unbind("click");
        });
        return false;
    }
    $(".dropdown").load(initDropdown).load().bind("click", toggleList);

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
    $(".checkbox").bind("change", toggleCheckBox).trigger("change");
});