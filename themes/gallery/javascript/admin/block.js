/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
 
ff.cms.admin.editor = (function () {
	var frameworkCssName	= "base";
	var selector 			= {
		"workground"		: ".workground",
		"menu"				: ".cmppanel",
		"body"				: ".-rightview",
		"selText"			: "CODE.sel",
		"draggable"			: ".-draggable",
		"droppable"			: ".-droppable",
		"active"			: ".-active",
		"custom"			: "#structureModify_is_custom",
		"frameworkCss"		: {
			"bootstrap"		: {
				"tab" 		: {
					"menu" 						: "nav nav-tabs",
					"menu-vertical" 			: "nav nav-tabs tabs-left",
					"menu-vertical-right" 		: "nav nav-tabs tabs-right",
					"menu-vertical-wrap" 		: true,
					"menu-item" 				: "",
					"menu-current" 				: "active",
					"pane" 						: "tab-content",
					"pane-item" 				: "tab-pane",
					"pane-current" 				: "active",
					"pane-item-effect" 			: "tab-pane fade",
					"pane-current-effect" 		: "active in"
				},
				"cols" 		: {
					"xs" 						: "", 
                    "sm"						: "", 
                    "md"						: "", 
                    "lg"						: ""	 			
				}
			},
			"foundation" 	: {
				"tab" 		: {
					"menu" 						: "tabs",
					"menu-vertical" 			: "tabs vertical",
					"menu-vertical-right" 		: "tabs vertical right",
					"menu-vertical-wrap" 		: false,
					"menu-item" 				: "tab-title",
					"menu-current" 				: "active",
					"pane" 						: "tabs-content",
					"pane-item" 				: "content",
					"pane-current" 				: "active",
					"pane-item-effect" 			: "content fade",
					"pane-current-effect" 		: "active"
				},
				"cols" 		: {
					"small" 						: "", 
	                "medium"						: "", 
	                "large"							: ""
				}				
			},
			"base" 			: {
				"tab" 		: {
					"menu" 						: "nav-tab",
					"menu-vertical" 			: "nav-tab vertical",
					"menu-vertical-right" 		: "nav-tab vertical right",
					"menu-vertical-wrap" 		: false,
					"menu-item" 				: "",
					"menu-current" 				: "current",
					"pane" 						: "tab-content",
					"pane-item" 				: "tab-pane",
					"pane-current" 				: "",
					"pane-item-effect" 			: "tab-pane fade",
					"pane-current-effect" 		: ""
				},
				"cols" 		: {
					"xs" 						: "", 
                    "sm"						: "", 
                    "md"						: "", 
                    "lg"						: ""	 			
				}				
			}
		}
	};
	var getClassByFrameworkCss = function(name, type) {
		if(!frameworkCssName)
			frameworkCssName = "base";
		return selector.frameworkCss[frameworkCssName][type][name];
	};
	var that = { // publics
		__ff : true, // used to recognize ff'objects
		"init" : function(params) { 
			function menu() {
				var tpl = '<ul class="' + getClassByFrameworkCss("menu", "tab") + '">'
							+ '<li class="' + getClassByFrameworkCss("menu-current", "tab") + '"><a href="#block-add" data-toggle="tab">Add</a></li>'
							+ '<li><a href="#block-edit" data-toggle="tab">Edit</a></li>'
						+ '</ul>'
						+ '<div class="' + getClassByFrameworkCss("pane", "tab") + '">'
							+ '<div id="block-add" class="' + getClassByFrameworkCss("pane-item-effect", "tab") + ' ' + getClassByFrameworkCss("pane-current-effect", "tab") + '">'
								+ jQuery(selector.menu).html()
								
							+ '</div>'
							+ '<div id="block-edit" class="' + getClassByFrameworkCss("pane-item-effect", "tab") + '">'
								
							+ '</div>'
						+ '</div>'
						
				jQuery(selector.menu).html(tpl);
			};
			function drag() {
				jQuery(selector.workground + " .layer .section").addClass(selector.droppable.trim("."));
				//jQuery(".workground .layer > :not(.section)").addClass("-droppable");
				
				ff.modules.restricted.drag(function(eventName, event, dropZone) {
					switch(eventName) {
						case "ondragstart":
							event.originalEvent.dataTransfer.setData("Text", "{" + event.originalEvent.dataTransfer.getData("Text") + "}");
						break;	
						case "ondrop":
							var data = event.originalEvent.dataTransfer.getData("Text");
							var target = data.trim("{").trim("}").split("-");
							if(jQuery(event.originalEvent.target).is(dropZone)) {
								jQuery(dropZone).prepend('<div class="col-xs-12 block">' + data+ '</div>');
							} else {
								jQuery('<div class="col-xs-12 block">' + data+ '</div>').insertAfter(event.originalEvent.target);
								
							}
							
							
							ff.ffPage.dialog.doOpen('dialogResponsive','/admin/pages/blocks/modify/' + target[0] + '?path=' + jQuery(".workground").data("path") + '&location=content&item=' + target[1]); 
						break;	
					
					}
					
				});				
			}
			
			function switchCustom() {
				jQuery(selector.custom).click(function() {
					jQuery("fieldset.wizard").removeClass("hidden");
					jQuery("fieldset.custom").removeClass("hidden");
					if(jQuery(this).is(":checked")) {
						jQuery("fieldset.wizard").hide();
						jQuery("fieldset.custom").slideDown(function () {
							jQuery("fieldset.custom TEXTAREA").data("codeMirrorInstance").refresh();				
							//jQuery("#structureModify .actions").removeClass("hidden");
						}); 
					} else {
						//jQuery("#structureModify .actions").addClass("hidden");
						jQuery("fieldset.custom").hide();
						jQuery("fieldset.wizard").slideDown(function () {
						}); 
					}
				});				
			}

			function toolbars() {
				jQuery(selector.workground + " .block, " + selector.workground + " .section, " + selector.workground + " .layer").each(function() {
					that.toolbar(this);
				});
				
			
			}
			
			jQuery(function() {
				params = params || {};
				
				selector.workground 		= params["workground"] 			? params["workground"] 			: selector.workground;
				selector.menu 				= params["menu"] 				? params["menu"] 				: selector.menu;
				selector.body				= params["body"] 				? params["body"] 				: selector.body;
				selector.selText			= params["selText"] 			? params["selText"] 			: selector.selText;
				selector.draggable			= params["draggable"] 			? params["draggable"] 			: selector.draggable;
				selector.droppable			= params["droppable"] 			? params["droppable"] 			: selector.droppable;
				selector.active				= params["active"] 				? params["active"] 				: selector.active;
				selector.custom				= params["custom"] 				? params["custom"] 				: selector.custom;
				selector.frameworkCss		= params["frameworkCss"] 		? params["frameworkCss"] 		: selector.frameworkCss;
				frameworkCssName			= params["frameworkCssName"]	? params["frameworkCssName"]	: ff.frameworkCss;

				jQuery("BODY").addClass(selector.body.trim("."));
				jQuery(selector.selText).mousedown(function() {
					jQuery(this).selectText();
				});
				
				menu();
				drag();
				switchCustom();
				toolbars();
				jQuery(selector.workground + " DIV").click(function(e) {
					that.focus(e);
				});			

			});
		},
		"focus" : function(e) {
			var target = (jQuery(e.target).is(".block, .section, .layer") 
				? jQuery(e.target)
				: jQuery(e.target).closest(".block, .section, .layer")
			);
			
			
			jQuery(selector.workground + " DIV").removeClass(selector.active.trim("."));
			target.addClass(selector.active.trim("."));
			jQuery("> .wrap", target).addClass(selector.active.trim("."));
		},
		"toolbar" : function(elem) {
			var tpl = '<nav class="toolbar">' + (jQuery(elem).attr("id") || "") + "." + jQuery(elem).attr("class").replace(/\s/g, ".") + '</nav>';
			jQuery(elem).prepend(tpl);
		}


	};

	that.init();
	
	return that;
	
})(); 