var Mapbender = (function($, Mapbender) {
    var Autocomplete = function(input, options){
        var self = this;
        this.input = $(input);
        for(name in options){
            if(name === 'url'){
                this.options[name] = options[name];
            }else if(this.options[name]){ // replace options attribute
                this.options[name] = options[name];
            }else if(typeof this[name] == 'function'){ // replace function
                this[name] = options[name];
            }
        }
        this.autocompleteList = this.input.parent(".autocompleteWrapper").find(".autocompleteList");
        if(!this.options.url || !this.autocompleteList)
            window.console && console.error("mbAutoComplete can't be implemented.");
        else{
            this.input.on('keyup', function(e){
                var txt = $(e.target).val();
                self.autocompleteList.html('').hide();
                if(txt.length >= self.options.minLength){
                    self.find(txt);
                }
            });
        }
    };
    Autocomplete.prototype = {
        options: {
            minLength: 2,
            requestType: 'GET',
            requestParamTerm: 'term',
            requestParamMaxresults: 'maxresults',
            requestValueMaxresults: 10,
            dataType: "json",
            dataIdx: 'idx',
            dataTitle: 'title'
        },
        find: function(term){
            var self = this;
            var data = {};
            data[this.options.requestParamMaxresults] = this.options.requestValueMaxresults;
            data[this.options.requestParamTerm] = term;
            $.ajax({
                url: this.options.url,
                type: this.options.requestType,
                data: data,
                dataType: this.options.dataType,
                success: $.proxy(self.open, self),
                error: function(data){
                    window.console && console.error("mbAutoComplete");
                }
            });
        },
        select: function(e){
            this.selected = {idx: $(e.target).attr('data-idx'), title: $(e.target).text()};
            this.input.val(this.selected.title);
            this.close();
        },
        open: function(data){
            this.selected = null;
            if(data.length > 0){
                var self = this;
                var res = "<ul>";
                $.each(data, function(idx, item){
                    res += '<li data-idx="' + item[self.options.dataIdx] + '">' + item[self.options.dataTitle] + '</li>';
                });
                res += "</ul>";
                this.autocompleteList.append(res).show();
                this.autocompleteList.find('li').on('click', $.proxy(self.select, self));
            }
        },
        close: function(){
            this.autocompleteList.html('').hide();
        }
    };
    Mapbender.Autocomplete = Autocomplete;

    return Mapbender;
})(jQuery, Mapbender || {});

$('body').delegate(':input', 'keyup', function(event){
    event.stopPropagation();
});