ff.cms.fn.tree = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
            
	jQuery(targetid + ".jstree").hide();
	ff.pluginLoad("jquery.cookie", "/themes/library/plugins/jquery.cookie/jquery.cookie.js", function() {	
		ff.pluginLoad("jquery.tree", "/themes/library/plugins/jquery.jstree.old/jquery.tree.js", function() {	
			ff.pluginLoad("jquery.tree.plugins.cookie", "/themes/library/plugins/jquery.jstree.old/plugins/jquery.tree.cookie.js", function() {	
                if(jQuery(targetid + ".jstree").parent().children("h2").html() != null)
                    jQuery(targetid + ".jstree").parent().parent().prepend("<h2 class=\"title\">" + jQuery(targetid + ".jstree").parent().children("h2").html() + "</h2>");
				
                jQuery(targetid + ".jstree ul").parent().attr("rel", "folder");
                jQuery(targetid + ".jstree li a[rel]:not(:empty)").parent().prepend("<ul></ul>");

				jQuery(targetid + ".jstree a[rel]:not(:empty)").parent().attr("rel", "folder");
				jQuery(targetid + ".jstree li.home").attr("rel", "root");

				jQuery(targetid + ".jstree li.home").addClass("open");

				
				/*	jQuery(".jstree ul:not('.child') li").addClass("open");*/
				var jsTreeFirst = false;
				var jsTreeOpened = new Array();
				var jsTreeOpenedFirst = true;
				var jsTreeParseFirst = true;
				var jsTreeString = new String(jQuery(targetid + ".jstree").parent().attr("class"));

				var jsTreeCookie = new Object();

                /*var resImage = new Image; */
                /*resImage.src = ff.site_path + "/themes/library/plugins/jquery.jstree.old/themes/" + jsTreeString.replace("block menu ", "") + "/file.png"; */
                
                if(!(jsTreeString.replace("block menu ", "") === "undefined")) {
                    var jsFileImg = false;
                    var jsHomeImg = false;
                    var jsFolderImg = false;

	                if(jsTreeString.replace("block menu ", "") != "gdpvetrina") {
	                    jQuery(targetid + ".jstree a").prepend("<ins></ins>");

                        jsFileImg = ff.site_path + "/themes/library/plugins/jquery.jstree.old/themes/" + jsTreeString.replace("block menu ", "") + "/file.png";
                        jsHomeImg = ff.site_path + "/themes/library/plugins/jquery.jstree.old/themes/" + jsTreeString.replace("block menu ", "") + "/home.png";
                        jsFolderImg = ff.site_path + "/themes/library/plugins/jquery.jstree.old/themes/" + jsTreeString.replace("block menu ", "") + "/folder.png";
                    }
	            

	            
	                /*if(resImage.complete) { 
	                     jQuery(targetid + ".jstree a").prepend("<ins></ins>");
	                }  */               

					jQuery(targetid + ".jstree li a.current").parent().each(function() {
						jsTreeOpened.push("#" + jQuery(this).attr("id"));
					});
					
					if(!jsTreeOpened.length) {
						jsTreeCookie = { cookie : { prefix : jQuery(targetid + ".jstree").parent().attr("id") + "_" } };
                        if(jQuery.cookie(jQuery(targetid + ".jstree").parent().attr("id") + "_" + "selected") !== null) {
                            jsTreeFirst = true;
                        }
					}
					
				    jQuery(targetid + ".jstree").parent().tree({
						data : { 
									async : true,
									type: "html", 
									opts : {
										url :  false
									}
						},
						opened: jsTreeOpened,    
						ui : {
							animation: 200, 
							theme_path: ff.site_path + "/themes/library/plugins/jquery.jstree.old/themes/" + jsTreeString.replace("block menu ", "") + "/style.css",
							theme_name : jsTreeString.replace("block menu ", "")
						},
						types : {
							/* all node types inherit the "default" node type */
							"default" : {
								draggable : false,
								deletable : false,
								renameable : false, 
								icon : { 
									image : jsFileImg
								}
							},
							"root" : {
								icon : { 
									image : jsHomeImg
								}
							},
							"folder" : {
								icon : { 
									image : jsFolderImg
								}
							}
						},
						plugins : jsTreeCookie,
						callback : {
							onopen : function (n, t) {
							/*	alert(t.parent(n));*/
							
								jQuery(n).closest("ul").children("li").each(function() {
									/*alert(jQuery(this).attr("id"));*/
									if(jQuery(this).attr("id") != jQuery(n).attr("id") && !n.selected) {
										t.close_branch(jQuery(this));
									}
								});
								return true;
							},
							beforedata : function (n, t) {
								if(typeof jQuery(n).children("a[rel]:not(:empty)").attr("rel") != "undefined") {
									t.settings.data.opts.url = jQuery(n).children("a[rel]:not(:empty)").attr("rel");
									
								}
							},
							ondata: function (d, t) {
								if(d != "") {
									if(!jsTreeOpenedFirst) {
										if(jQuery(d).children("ul").is(".child")) {
											d = jQuery(d).children("ul.child").html();
										}

										var jsTreeOpened = new Array();
										
										jQuery(d).each(function() {
											if(jQuery(this).hasClass("current") && jQuery(this).attr("id") != "undefined") {
												jsTreeOpened.push("#" + jQuery(this).attr("id"));
											}
										});

										if(jsTreeOpened.length) {					
											t.opened = jsTreeOpened;
										}
									} else {
										jsTreeOpenedFirst = false;
									}
									return d;
								} else {
									return "";
								}
							}, 
							onparse: function (s, t) {
								
								if(!jsTreeParseFirst) {
									jQuery(s).children("li").each(function() {
										jQuery(this).children("a").prepend("<ins>&nbsp;</ins>");
										jQuery(this).children("a[rel]:not(:empty)").parent().attr("rel", "folder");
										jQuery(this).children("a[rel]:not(:empty)").parent().prepend("<ul></ul>");
										s = jQuery(this).parent().html().trim("\n");
									});

				                    if(jQuery(s).attr("id") != "undefined") {
				                        ff.cms.widgetInit("", true);
									} else {
				                        ff.cms.widgetInit("", "");
									}

									/*alert(jQuery(s).children("li.current").attr("id"));*/
								} else {
									jsTreeParseFirst = false;
								}
								return s;
							},
							onselect : function (n, t) {
								if(jsTreeFirst) {
								    jsTreeFirst = false;
                                    /*return;*/
	  							} else {
								    if(jQuery(n).children("a").attr("href").substring(0,1) != "#" && jQuery(n).children("a").attr("href") != "") {
									    window.location.href = jQuery(n).children("a").attr("href");
								    }
                                }
							}
						}
				    });
				    jQuery(targetid + ".jstree").fadeIn();
				}
			 }, false);
		}, false);
	}, false);
};
