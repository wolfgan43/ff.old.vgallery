ff.cms.fn.superfish = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";

	/*css*/
	ff.pluginLoad("jquery.fn.hoverIntent", "/themes/library/plugins/jquery.hoverintent/jquery.hoverintent.js", function() {
		ff.pluginLoad("jquery.fn.superfish", "/themes/library/plugins/jquery.superfish/jquery.superfish.js", function() {
			ff.pluginLoad("jquery.fn.supersubs", "/themes/library/plugins/jquery.supersubs/jquery.supersubs.js", function() {
				jQuery(targetid + 'ul.superfish').superfish ({
			        hoverClass:    'sfHover',          /* the class applied to hovered list items */
			        pathClass:     'overideThisToUse', /* the class you have applied to list items that lead to the current page */
			        pathLevels:    1,                  /* the number of levels of submenus that remain open or are restored using pathClass */
			        delay:         800,                /* the delay in milliseconds that the mouse can remain outside a submenu without it closing */
			        animation:     {opacity:'show'},   /* an object equivalent to first parameter of jQuery�s .animate() method */
			        speed:         'normal',           /* speed of the animation. Equivalent to second parameter of jQuery�s .animate() method */
			        autoArrows:    false,               /* if true, arrow mark-up generated automatically = cleaner source code at expense of initialisation performance */
			        dropShadows:   true,               /* completely disable drop shadows by setting this to false */
			        disableHI:     false,              /* set to true to disable hoverIntent detection */
			        onInit:        function(){},       /* callback function fires once Superfish is initialised � 'this' is the containing ul */
			        onBeforeShow:  function(){},       /* callback function fires just before reveal animation begins � 'this' is the ul about to open */
			        onShow:        function(){},       /* callback function fires once reveal animation completed � 'this' is the opened ul */
			        onHide:        function(){}        /* callback function fires after a sub-menu has closed � 'this' is the ul that just closed */
			    });
			}, true);
		}, true);
	}, true);
};
