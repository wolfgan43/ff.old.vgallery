ff.cms.fn.jstree = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
        
	ff.pluginLoad("jquery.hotkeys", "/themes/library/plugins/jquery.hotkeys/jquery.hotkeys.js", function() {
		ff.pluginLoad("jquery.cookie", "/themes/library/plugins/jquery.cookie/jquery.cookie.js", function() {
			ff.pluginLoad("jquery.jstree", "/themes/library/plugins/jquery.jstree/jquery.jstree.js", function() {
				jQuery(targetid + ".jstree li.home").attr("rel", "root");
				
				/*jQuery(targetid + ".jstree li a[rel]:not(:empty)").parent().prepend("<ul></ul>"); */
				jQuery(targetid + ".jstree li a[rel]:not(:empty)").parent().addClass("jstree-closed");
				jQuery(targetid + ".jstree a[rel]:not(:empty)").parent().attr("rel", "folder");
				
				var jsTreeOpened = new Array(jQuery(targetid + ".jstree li.home").attr("id"));
				var jsTreePlugins = new Array("themes", "types", "ui","html_data");
				var jsTreeString = new String(jQuery(targetid + ".jstree").parent().attr("class"));
				var jsTreeCookie = "";
				var jsTreeFirst = true;
				
				jQuery(targetid + ".jstree li.current").each(function() {
					jsTreeOpened.push("#" + jQuery(this).attr("id"));
				});

				if(!(jsTreeOpened.length > 1)) {
					jsTreeCookie = jQuery(targetid + ".jstreenew").parent().attr("id");
					jsTreePlugins.push("cookies");
				}

				jQuery.jstree._themes = ff.site_path + "/themes/library/plugins/jquery.jstree/themes/";

				jQuery(targetid + ".jstree").jstree({
					"themes" : {
				        "theme" : jsTreeString.replace("menu ", ""),
				        "dots" : true,
				        "icons" : true
				    },
					"types" : {
						"max_children" : -2,
						"max_depth" : -2,
						"valid_children" : [ "all" ],
						"type_attr" : "rel", 
						"types" : {
							/* all node types inherit the "default" node type*/
							"default" : {
								icon : { 
									image : ff.site_path + "/themes/library/plugins/jquery.jstree/themes/" + jsTreeString.replace("menu ", "") + "/file.png"
								},
								max_children  : 0,
	        					max_depth     : -2,
	        					valid_children: "none"
							},
							"root" : {
								icon : { 
									image : ff.site_path + "/themes/library/plugins/jquery.jstree/themes/" + jsTreeString.replace("menu ", "") + "/home.png"
								},
								max_children  : -1,
	        					max_depth     : 0,
	        					valid_children: ["folder", "default"]
							},
							"folder" : {
								icon : { 
									image : ff.site_path + "/themes/library/plugins/jquery.jstree/themes/" + jsTreeString.replace("menu ", "") + "/folder.png"
								},
								max_children  : -2,
	        					max_depth     : -2,
	        					valid_children: ["default"]
							}
						}
					},
					"cookies" : {
						save_opened : "jstree_" + jsTreeCookie + "_open", 
						save_selected : "jstree_" + jsTreeCookie + "_select",
						auto_save : true, 
						cookie_options : {
							path: '/'
						}
					},
					"core" : { "initially_open" : jsTreeOpened },
					"html_data" : {
				        "data" : jQuery(targetid + ".jstree").parent().html(),
				        "ajax" : {
							"data" : function (n, t) {
				                return jQuery(n).children("a[rel]:not(:empty)").attr("rel").substr(jQuery(n).children("a[rel]:not(:empty)").attr("rel").indexOf("?") + 1);
				            },
				            "url" : function (n, t) {
				                return jQuery(n).children("a[rel]:not(:empty)").attr("rel").substr(0, jQuery(n).children("a[rel]:not(:empty)").attr("rel").indexOf("?"));
				            }
				            , "success" : function(s, t, x) {
								jQuery(s).children("li").each(function() {
									jQuery(this).children("a[rel]:not(:empty)").parent().attr("rel", "folder");
									if(!jQuery(this).hasClass("current")) {
										jQuery(this).children("a[rel]:not(:empty)").parent().addClass("jstree-open");
									} else {
										jQuery(this).children("a[rel]:not(:empty)").parent().addClass("jstree-closed");
									}
									
					                if(jQuery(this).attr("id") !== undefined) {
					                    ff.cms.widgetInit("#" + jQuery(this).attr("id"), true);
									}

									s = jQuery(this).parent().html();
								});

								return s;
							}
						}
				    }, 
					"plugins" : jsTreePlugins




				});























			/*


				//jQuery(targetid + ".jstree ul").parent().attr("rel", "folder");
				
				jQuery(targetid + ".jstree li a[rel]:not(:empty)").parent().prepend("<ul></ul>"); 
				//jQuery(targetid + ".jstree a").prepend("<ins>&nbsp;</ins>"); 
				jQuery(targetid + ".jstree a[rel]:not(:empty)").parent().attr("rel", "folder");
				jQuery(targetid + ".jstree li.home").attr("rel", "root");

				//jQuery(targetid + ".jstree li.home").addClass("open");
				jQuery.jstree._themes = ff.site_path + "/themes/library/plugins/jquery.jsTree/themes/";
				
				
				//	jQuery(".jstree ul:not('.child') li").addClass("open");
				var jsTreeFirst = false;
				var jsTreeOpened = new Array();
				var jsTreePlugins = new Array("themes", "html_data");
				var jsTreeOpenedFirst = true;
				var jsTreeParseFirst = true;
				var jsTreeString = new String(jQuery(targetid + ".jstree").parent().attr("class"));

				var jsTreeCookie = new Object();
				
				jQuery(targetid + ".jstree li.current").each(function() {
					jsTreeOpened.push("#" + jQuery(this).attr("id"));
				});
				
				
				if(!jsTreeOpened.length) {
					//jsTreeCookie = { cookie : { prefix : jQuery(targetid + ".jstree").parent().attr("id") + "_" } };
					jsTreePlugins.push("ui");
					jsTreePlugins.push("cookie");
					jsTreeFirst = true;
				}
				
			    jQuery(targetid + ".jstree").parent().jstree({
					data : { 
								async : true,
								type: "html", 
								opts : {
									url :  false
								}
					},
					opened: jsTreeOpened,
					"themes" : {
				        "theme" : "default",
				        "dots" : true,
				        "icons" : true
				    },
					ui : {
						animation: 200, 
						theme_name : jsTreeString.replace("menu ", "")
					},
					types : {
						// all node types inherit the "default" node type
						"default" : {
							draggable : false,
							deletable : false,
							renameable : false, 
							icon : { 
								image : ff.site_path + "/themes/library/plugins/jquery.jsTree/themes/" + jsTreeString.replace("menu ", "") + "/file.png"
							}
						},
						"root" : {
							icon : { 
								image : ff.site_path + "/themes/library/plugins/jquery.jsTree/themes/" + jsTreeString.replace("menu ", "") + "/home.png"
							}
						},
						"folder" : {
							icon : { 
								image : ff.site_path + "/themes/library/plugins/jquery.jsTree/themes/" + jsTreeString.replace("menu ", "") + "/folder.png"
							}
						},
					},

					plugins : jsTreePlugins,
					callback : {
						onopen : function (n, t) {
						//	alert(t.parent(n));
						
							jQuery(n).closest("ul").children("li").each(function() {
								//alert(jQuery(this).attr("id"));
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
									s = jQuery(this).parent().html();
								});

			                    if(jQuery(s).attr("id") != "undefined") {
			                        ff.cms.widgetInit("", true);
								} else {
			                        ff.cms.widgetInit("", "");
								}

								//alert(jQuery(s).children("li.current").attr("id"));
							} else {
								jsTreeParseFirst = false;
							}
							return s;
						},
						onselect : function (n, t) {
							if(jsTreeFirst) {
							    jsTreeFirst = false;
							   // return;
	  						}
							    
							if(jQuery(n).children("a").attr("href").substring(0,1) != "#" && jQuery(n).children("a").attr("href") != "") {
								window.location.href = jQuery(n).children("a").attr("href");
							}
						}
					}
			    });
			         //jQuery(this).css("display", "none");
			         */
			}, false);
		}, false);
	}, false);
};