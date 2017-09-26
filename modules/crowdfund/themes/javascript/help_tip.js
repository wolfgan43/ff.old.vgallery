jQuery(function() {
    jQuery("a.hint").each(function() {
        var elem = $(this);
        ff.injectCSS("jquery.qtip", '/themes/site/css/jquery.qtip.css', function(){ 
            ff.pluginLoad("jQuery.fn.qtip", '/themes/library/plugins/jquery.qtip2/jquery.qtip.js', function(){ 

                $(elem).qtip({
                    content: {  
                             text: 'Loading...',
                            ajax: {
                                    url:  $(elem).attr('rel') ,
                                    type: "GET",
                                    data: {"out" : "html"},
                                    success: function(data, status) {
                                            this.set("content.text", data); 

                                    } 
                            }
                    },
                    show: { 
                            event: 'click',
                            solo: true // Only show one tooltip at a time
                    },
                    hide: 'unfocus'
                });
            });
        });
    });
});