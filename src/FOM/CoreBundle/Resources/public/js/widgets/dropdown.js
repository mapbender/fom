$(function() {

    $('.dropdown').each(function() {
        var element = $(this);
        var dropdownList = element.find(".dropdownList");
        if(dropdownList.children().length === 0) {
            element.find("option").each(function(i, e) {
                $(e).addClass("opt-" + i);
                dropdownList.append($('<li class="item-' + i + '">' + $(e).text() + '</li>'));
            });
        }
        var select = element.find("select").val();
        element.find(".dropdownValue").text(element.find('option[value="' + select + '"]').text())

    }).on('click', function() {
        var me = $(this);
        var list = me.find(".dropdownList");
        var opts = me.find(".hiddenDropdown");
        if(list.css("display") == "block") {
            list.hide();
        } else {
            $(".dropdownList").hide();
            list.show();
            list.find("li").one("click", function(event) {
                event.stopPropagation();
                list.hide().find("li").off("click");
                var me2 = $(this);
                var opt = me2.attr("class").replace("item", "opt");
                me.find(".dropdownValue").text(me2.text());
                opts.find("[selected=selected]").removeAttr("selected");
                var val = opts.find("." + opt).attr("selected", "selected").val();
                opts.val(val).trigger('change');
            })
        }

        $(document).one("click", function() {
            list.hide().find("li").off("mouseout").off("click");
        });
        return false;
    })
});
