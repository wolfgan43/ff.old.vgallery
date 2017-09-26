ff.cms.fn.jstree = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
        
	ff.load("jquery.plugins.jstree", function() {
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

		jQuery.jstree._themes = ff.base_path + "/themes/library/plugins/jquery.jstree/themes/";

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
							image : ff.base_path + "/themes/library/plugins/jquery.jstree/themes/" + jsTreeString.replace("menu ", "") + "/file.png"
						},
						max_children  : 0,
	        			max_depth     : -2,
	        			valid_children: "none"
					},
					"root" : {
						icon : { 
							image : ff.base_path + "/themes/library/plugins/jquery.jstree/themes/" + jsTreeString.replace("menu ", "") + "/home.png"
						},
						max_children  : -1,
	        			max_depth     : 0,
	        			valid_children: ["folder", "default"]
					},
					"folder" : {
						icon : { 
							image : ff.base_path + "/themes/library/plugins/jquery.jstree/themes/" + jsTreeString.replace("menu ", "") + "/folder.png"
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
	});
};