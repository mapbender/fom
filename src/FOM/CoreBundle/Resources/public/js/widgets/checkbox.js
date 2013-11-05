var initCheckbox;
$(function() {
    initCheckbox = function(){
        var me     = $(this);
        var parent = me.parent(".checkWrapper");

        if(me.is(":checked")){
            parent.addClass("iconCheckboxActive");
        }

        if(me.is(":disabled")){
            parent.addClass("checkboxDisabled");
        }
    };
    var toggleCheckBox = function(){
        var me       = $(this);
        var checkbox = me.find(".checkbox");

        if(checkbox.is(":disabled")){
            me.addClass("checkboxDisabled");
        }else{
            if(checkbox.is(":checked")){
                me.removeClass("iconCheckboxActive");
                checkbox.get(0).checked = false;
            }else{
                me.addClass("iconCheckboxActive");
                checkbox.get(0).checked = true;
            }
        }
        
        checkbox.trigger('change');
    };
    $(window).on('load', function() {
        $('.checkbox').each(function() {
            initCheckbox.call(this);
        });
    });
    $(document).on("click", ".checkWrapper", toggleCheckBox);
});