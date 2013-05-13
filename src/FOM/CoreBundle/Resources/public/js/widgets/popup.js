(function($){
  $.widget("mapbender.mbPopup", {
    options: {},
    buttons: [],
    popup: null,

    defaults: {
      draggable:            true,
      resizeable:           false, //not implemented yet
      modal:                true,
      showHeader:           true,
      showCloseButton:      true,
      cancelOnEsc:          true,
      cancelOnOverlayClick: true,
      method:               "POST", 

      width:                0, // 0 -> css
      cssClass:             "",

      title:                "",
      subTitle:             "",
      content:              "",
      btnOkLabel:           "OK",
      btnCancelLabel:       "Cancel",
    },

    _create: function(){},

    showHint: function(customOptions){
      var hintDefaults             = this.defaults;
      hintDefaults.draggable       = false;
      hintDefaults.resizeable      = false;
      hintDefaults.showCloseButton = false;
      hintDefaults.showHeader      = false;
      hintDefaults.cssClass        = "hint";

      this.options = $.extend(hintDefaults, customOptions);
      this.addButton(this.options.btnOkLabel, "buttonYes")
          ._createMarkup()
          ._show();
    },

    showAjaxModal: function(customOptions, url, yesClick, beforeLoad, afterLoad){
      var that               = this;
      var modalDefaults      = that.defaults;
      modalDefaults.cssClass = "modal";

      that.options = $.extend(modalDefaults, customOptions);
      that.addButton(that.options.btnCancelLabel, "button buttonCancel critical")
          .addButton(that.options.btnOkLabel, "button buttonYes", yesClick);

      $.ajax({
          url: url,
          type: that.options.method,
          beforeSend:  beforeLoad,
          success: function(data){
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
        var modalDefaults      = defaults;
        modalDefaults.cssClass = "modal";

        options = $.extend(modalDefaults, customOptions);
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

    addButton: function(label, cssClass, clickFunction, append){
      var that   = this;
      cssClass   = (cssClass != undefined) ? 'class="' + cssClass + '"' : "";

      var button = $('<a href="#" ' + cssClass + '>' + label + '</a>')
                   .bind("click", 
                         (typeof(clickFunction) == "function") ? 
                         function(e){clickFunction.call(that, e)} :
                         function(e){that.close.call(that, e)});

      that.buttons.push(button);

      return this;
    },

    close: function(){
      var that = this;

      that.popup.removeClass("show");
      setTimeout(function() {that._destroy()},200);

      return false;
    },

    _destroy: function(){
      with (this){
        popup.find(".popupButtons").find("*").unbind();
        options = {};
        buttons = [];
        popup.remove();
        popup = null;
      }
    },

    _show: function(){
      var that = this;
      setTimeout(function() {that.popup.addClass("show")},10);
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
        _removeOverlay();
        _bindCancelEvents();
        _addAllButtons();
        _setWidth();

        this.element.append(popup);

        _bindDrag();
      }
      return this;
    },

    _setWidth: function(){
      if(this.options.width > 0){
        this.popup.find(".popup").css("width", this.options.width);
      }
    },

    _removeHeader: function(){
      if(!this.options.showHeader){
        this.popup.find(".popupHead").remove();
      }
    },

    _removeOverlay: function(){
      if(!this.options.modal){
        this.popup.find(".overlay").remove();
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
      if(this.options.draggable){
        //XXXVH:todo
        var head = $("#popupHead");
        head.draggable();
        head.draggable({ scroll: false });
      }
    },

    _addAllButtons: function(){
      if(this.buttons != null){
        var len = this.buttons.length;
        if(len > 0){
          var btnContainer = this.popup.find(".popupButtons");
          for(var i = 0; i < len; ++i){
            btnContainer.append(this.buttons[i]);
          }
        }
      }
    }
  });
})(jQuery);