$(function() {
    // init sidebar toggle -------------------------------------------------------------------
    var sideBarToggle = function(){
        var parent = $(this).parent().parent();
        (parent.hasClass("opened")) ? parent.removeClass("opened") : parent.addClass("opened");
    }
    console.log($(".toggleSideBar"))
    $(".toggleSideBar").bind("click", sideBarToggle);
});