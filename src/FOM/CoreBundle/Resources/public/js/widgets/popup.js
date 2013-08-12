(function($){
  $.widget("mapbender.mbPopup", {
    options: {},
    buttons: [],
    popup: null,

    defaults: {
      draggable:            false,
      resizeable:           false, //not implemented yet
      showHeader:           true,
      showCloseButton:      true,
      cancelOnEsc:          true,
      cancelOnOverlayClick: true,
      transparency:         true,
      overflow:             false,
      method:               "POST", 

      width:                0, // 0 -> css
      height:               0, // 0 -> css
      cssClass:             "",

      title:                "",
      subTitle:             "",
      content:              "",
      btnOkLabel:           "OK",
      btnCancelLabel:       "Cancel",
    },

    _create: function(){
      // This is weird - when closing a popup, then opening a new one,
      // the old buttons are still there? So we reset them forcefully.
      this.buttons = [];
    },

    showHint: function(customOptions){
      var hintDefaults             = this.defaults;
      hintDefaults.resizeable      = false;
      hintDefaults.showCloseButton = false;
      hintDefaults.showHeader      = false;
      hintDefaults.cssClass        = "hint";

      this.options = $.extend(hintDefaults, customOptions);
      this.addButton(this.options.btnOkLabel, "button buttonYes right")
          ._createMarkup()
          ._show();
    },

    showAjaxModal: function(customOptions, url, yesClick, beforeLoad, afterLoad){
      var that = this;

      $.ajax({
          url: url,
          type: that.options.method,
          beforeSend:  beforeLoad,
          success: function(data){
              that.options = $.extend(that.defaults, customOptions);
              that.addButton(that.options.btnCancelLabel, "button buttonCancel critical")
                  .addButton(that.options.btnOkLabel, "button buttonYes", yesClick);
              that.options.content = data;
              that._createMarkup()
                  ._show();
              if(afterLoad != undefined){
                  afterLoad();
              }
          }
      });
    },

    showModal: function(customOptions, yesClick){
      with (this){
        options = $.extend(defaults, customOptions);
        addButton(options.btnCancelLabel, "button buttonCancel critical")
        .addButton(options.btnOkLabel, "button buttonYes", yesClick)
        ._createMarkup()
        ._show();
      }
    },

    showCustom: function(customOptions){
      this.options = $.extend(this.defaults, customOptions);
      this._createMarkup()._show();
    },

    setContent: function(text){
      this.popup.find(".popupContent").text("").append(text);
    },

    addButton: function(label, cssClass, clickFunction){
      var that   = this;
      cssClass   = (cssClass != undefined) ? 'class="' + cssClass + '"' : "";

      var button = $('<a href="#" ' + cssClass + '>' + label + '</a>')
                   .bind("click",
                         (typeof(clickFunction) == "function") ?
                         function(e){clickFunction.call(that, e)} :
                         function(e){that.close.call(that, e)});
      this.buttons.push(button);

      return this;
    },

    close: function(){
        var that = this;

        this.element.removeData("mapbenderMbPopup");
        // @fixme: This is due to different jQuery UI version in front- and
        // backend.
        this.element.removeData("mbPopup");

        var transEndEvent = this._whichTransitionEvent();
        if(transEndEvent && that.popup.hasClass('show')) {
          this.popup.bind(transEndEvent, $.proxy(this._destroy, this));
          that.popup.removeClass("show");
        } else {
          this._destroy();
        }

        return false;
    },

    _whichTransitionEvent: function() {
        var t;
        var el = document.createElement('fakeelement');
        var transitions = {
          'transition':'transitionend',
          'OTransition':'oTransitionEnd',
          'MozTransition':'transitionend',
          'WebkitTransition':'webkitTransitionEnd'
        };

        for(t in transitions){
            if( el.style[t] !== undefined ){
                return transitions[t];
            }
        }
    },

    _destroy: function(){
        this.popup.find(".popupButtons").find("*").unbind();
        this.options = {};
        this.buttons = [];
        this.popup.remove();
        this.popup = null;
    },

    _show: function(){
      var that = this;
      setTimeout(function() {
        that.popup.addClass("show");
      },10);
    },

    _getTemplate: function(){
      return $('<div id="popupContainer" class="popupContainer">' +
                 '<div id="popup" class="popup">' +
                   '<div id="popupHead" class="popupHead">' +
                     '<span id="popupTitle" class="popupTitle"></span>' +
                     '<span id="popupSubTitle" class="popupSubTitle"></span>' +
                     '<span class="popupClose right iconCancel iconBig"></span>' +
                   '</div>' +
                   '<div id="popupContent" class="clear popupContent"></div>' +
                   '<div class="popupButtons"></div>' +
                   '<div class="clearContainer"></div>' +
                 '</div>' +
                 '<div class="overlay"></div>' +
               '</div>');
    },

    _createMarkup: function(){
      with (this){
        popup = _getTemplate();

        popup.addClass(this.options.cssClass)
               .find(".popupTitle").text(options.title);
        popup.find(".popupContent")
               .append(options.content);
        popup.find(".popupSubTitle").text(options.subTitle);

        _removeHeader();
        _bindCancelEvents();
        _addAllButtons();
        _setSize();
        _addTransparency();
        _addOverflow();

        element.append(popup);
        _bindDrag();
      }
      return this;
    },

    _addTransparency: function(){
      if(this.options.transparency){
        this.popup.find(".popup").addClass("popupTransparency");
      }
    },

    _addOverflow: function(){
      if(this.options.overflow){
        this.popup.find(".popup").addClass("popupOverflow");
      }
    },

    _setSize: function(){
      var pop = this.popup.find(".popup");

      if(this.options.width > 0){
        pop.css("width", this.options.width);
      }
      if(this.options.height > 0){
        pop.find(".popupContent").css("height", this.options.height);
      }
    },

    _removeHeader: function(){
      if(!this.options.showHeader){
        this.popup.find(".popupHead").remove();
      }
    },

    _bindCancelEvents: function(){
      var that = this;

      if(this.options.cancelOnEsc){
        $(document).one("keyup", function(e){
          if(e.keyCode == 27) that.close();
        });
      }

      if(this.options.cancelOnOverlayClick){
        this.popup.find(".overlay").one("click", function(e){
          that.close();
        });
      }

      if(!this.options.showCloseButton){
        this.popup.find(".popupClose").remove();
      }else{
        this.popup.find(".popupClose").one("click", function(){
          that.close();
        });
      }
    },

    _bindDrag: function(){
      if(this.options.draggable) {

        var popup = $('#popup', this.popup),
            handle = $('#popupHead', this.popup);
            $("#popupHead").addClass("draggable");
        popup.draggable({
          handle: handle
        });

        // centrate the dialog, if it is draggable
        var leftOffset = 0;
        var popupWidth = this.popup.find(".popup").width();
        if(popupWidth > 0){
          leftOffset = ((window.innerWidth/2) - (popupWidth/2));
        }
        this.popup.css("left", leftOffset).find(".overlay").remove();

      }else{
        this.popup.addClass("modal");
      }
    },

    _addAllButtons: function(){
      if(this.buttons != null){
        var len = this.buttons.length;
        if(len > 0){
          var btnContainer = this.popup.find(".popupButtons");
          var btnContainer = $(".popupButtons", this.popup.get(0));

          for(var i = 0; i < len; ++i){
            btnContainer.append(this.buttons[i]);
          }
        }
      }
    }
  });
})(jQuery);
