if (!ff.cms) ff.cms = {};
ff.cms.landingPage = (function () {
	var bufferPage = {};
    var containerClass = "landing-container";
    var menuClass = "landing-menu";
    var selectedClass = "active";

    var searchPage = "/search";
    var page_parname = "page";
    
    var getUriParams = function (str) {
       var queryString = str || window.location.search || '';
       var keyValPairs = [];
       var params      = {};
       queryString     = queryString.replace(/.*?\?/,"");

       if (queryString.length)
       {
          keyValPairs = queryString.split('&');
          keyValPairs.each(function(pairNum, valPairs) {
             var qParam = valPairs.split('=');
             
             if (qParam[0].length)
                params[qParam[0]] = qParam[1];
          });
       }
       return params;
    };
	var that = { /* publics*/
		__init : false
		, "init" : function(params) { 
			var that = this;

			if(params) {
				containerClass  = params.containerClass || containerClass;
				menuClass 	    = params.menuClass 		|| menuClass;
				selectedClass 	= params.selectedClass 	|| selectedClass;
			}
			jQuery("." + menuClass + " LI A").click(function(e) {
                e.preventDefault();

                //h1
                var landingName = jQuery(".landing-page h1.title").text().split(":")[1];
                if(!landingName)
                    landingName = jQuery(".landing-page h1.title").text();
                if(jQuery(this).attr("rel")) 
                    jQuery(".landing-page h1.title").text(jQuery(this).text() + ": " + landingName);
                else
                    jQuery(".landing-page h1.title").text(landingName);

                //title
                var landingName = jQuery("title").text().split(":")[1];
                if(!landingName)
                    landingName = jQuery("title").text();

                if(jQuery(this).attr("rel")) 
                    jQuery("title").text(jQuery(this).text() + ": " + landingName);
                else
                    jQuery("title").text(landingName);
                
				that.load(this);
			});
			jQuery(document).on("click", "A.lp-grp", function() {
				that.load(jQuery("." + menuClass + " LI A[rel='" + jQuery(this).attr("rel") + "']"), ".block.landing-page");
			});			
			if(!that.__init) {
                if(window.location.pathname.indexOf(searchPage) === 0) {
                    var partUrl = window.location.pathname.split('/');
                    if(partUrl.length == 3)
                        jQuery("." + menuClass + " LI A:first").click();
                }                    

                window.addEventListener('popstate', function(e) {
                    var type = e.state;
                    
                    if(type === null) 
                        jQuery("." + menuClass + " LI A:first").click(); 
                    else
                        jQuery("." + menuClass + " LI A[rel='" + type + "']").click(); 
                });
				jQuery("." + containerClass).removeClass("hidden");
			}

			that.__init = true;
		}
		, "load" : function(elem) {
			var hash = jQuery(elem).attr("rel");
			var partUrl = window.location.pathname.split('/');
            var currentHash = ''; 

            if("/" + partUrl[partUrl.length - 2] != searchPage)
            	currentHash = partUrl.splice(-1).join("");
            	
            var url = partUrl.join('/');
            if(hash)
                url += '/' + hash;

            var queryParams = getUriParams();
            bufferPage[window.location.pathname] = queryParams[page_parname];            

            jQuery("." + menuClass + " LI").removeClass(selectedClass); 
            jQuery(elem).parent().addClass(selectedClass);

            if(currentHash != hash) {
	            ff.cms.load(url + (bufferPage[url] > 1 ? "?" + page_parname + "=" + bufferPage[url] : ""), jQuery("." + containerClass), "fadeIn", "replace", undefined, true, false);

                history.pushState(hash, null, ff.cms.updateUriParams(page_parname, bufferPage[url], url + window.location.search));     
			}    
		}
	};
	jQuery(function() {
		ff.cms.landingPage.init();
        
	});
	return that;
})();

