$(function() {
    var sidePane = $(".toggleSideBar").closest("div.sidePane");
    var isOpened = true;

    sidePane.find(".toggleSideBar").bind("click", function(e){
        sidePane.css("margin-left",isOpened? "-"+sidePane.width()+"px":"0px");
        isOpened = !isOpened;
    });
});
