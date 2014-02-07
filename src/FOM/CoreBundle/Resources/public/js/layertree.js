$(function(){
    new Dragdealer('layer-opacity', {
      horizontal: true,
      vertical: false,
      steps: 100,
      handleClass: "layer-opacity-handle",
      animationCallback: function(x, y) {
        $("#layer-opacity").find(".layer-opacity-handle").text(Math.round(x * 100));
      }
    });
});
