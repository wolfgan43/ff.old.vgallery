ff.cms.fn.lavalamp = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
			
	/*css */
	ff.pluginLoad("jquery.lavalamp", "/themes/library/plugins/jquery.lavalamp/jquery.lavalamp.js", function() {	
		ff.pluginLoad("jquery.easing", "/themes/library/plugins/jquery.easing/jquery.easing.js", function() {	
			ff.pluginLoad("jquery.easing", "/themes/library/plugins/jquery.easing/jquery.easing.compatibility.js", function() {	
				jQuery(targetid + ".lavalamp > li.current").addClass("selectedLava");
			    jQuery(targetid + ".lavalamp").lavaLamp({
			        fx: "backout",
			        speed: 700,
			        autoReturn: true,
			        returnHome: false,
			        click: function(event, menuItem) {
			            return true;
			        }
			    });
			}, true);
		}, true);
	}, true);
};
