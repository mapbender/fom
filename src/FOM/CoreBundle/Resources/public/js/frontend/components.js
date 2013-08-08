$(function() {
    // init sidebar toggle -------------------------------------------------------------------
    var sideBarToggle = function(){
        var parent = $(this).parent().parent();
        (parent.hasClass("opened")) ? parent.removeClass("opened") : parent.addClass("opened");
    }
    $(".toggleSideBar").bind("click", sideBarToggle);

    // init tabcontainers --------------------------------------------------------------------
    $(".tabContainer").on('click', '.tab', function() {
        var me = $(this);
        me.parent().parent().find(".active").removeClass("active");
        me.addClass("active");
        $("#" + me.attr("id").replace("tab", "container")).addClass("active");
    });

    // init checkbox toggle ------------------------------------------------------------------
    var toggleCheckBox = function(event){
        var me     = $(this);
        var parent = me.parent();

        (me.is(":checked")) ? parent.addClass("iconCheckboxActive")
                            : parent.removeClass("iconCheckboxActive");
        if(me.is(":disabled")){
           parent.addClass("checkboxDisabled");
        }
    }
    $(document).on('change', '.checkbox', toggleCheckBox);
    $('.checkbox').trigger('change');
});
