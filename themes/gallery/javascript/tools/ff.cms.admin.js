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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
ff.cms.admin = {};
ff.cms.admin.type = {};

ff.cms.admin.displayByDep = function(dep, display) { 
	var doRefresh = jQuery(".dep-" + dep).is(":visible") != display;

	if(display)
		jQuery(".dep-" + dep).removeClass("hidden");
	else
		jQuery(".dep-" + dep).addClass("hidden");
	
	//if(doRefresh && ff.ffPage.dialog)	
		//ff.ffPage.dialog.refresh();
};

ff.cms.admin.location = function() {
	if(ff.cms.admin.locationName !== undefined) {
		if(ff.cms.admin.locationName == "Content") 
			jQuery(".location-dep").fadeIn();
		else
			jQuery(".location-dep").hide();
	} else {
 		if(jQuery(".location select option:selected").text() == "Content" || jQuery(".location .label").text().indexOf("Content") >= 0) {
			jQuery(".location-dep").fadeIn();
		} else {
			jQuery(".location-dep").hide();
		}        		
	}
};

ff.cms.admin.makeNewUrl = function(target, altUrl, parentValue) {
    var target = target || "INPUT.title-page:visible:last";
    var altUrl = altUrl || "INPUT.alt-url:visible:last";

    if(!parentValue)
    	if(jQuery("SELECT.parent-page:visible:last").length)
    		parentValue = jQuery("SELECT.parent-page:visible:last").val() || "/";
    	else
    		parentValue = "";

    if(jQuery(target).val()) {
		ff.load("ff.ffField.slug", function () {
		    var pre = (parentValue && parentValue != "/" ? "/" : "");
			var slugName = ff.ffField.slug(target, undefined, pre);

    		if(jQuery(target).val().indexOf("#") === 0
    			|| jQuery(target).val().indexOf("javascript:") === 0
    		) {
		        jQuery(".slug-gotourl:last").attr("href", "javascript:void(0);").hide();
		        jQuery(".admin-title .smart-url:last").text(": " + parentValue + slugName).parent().css("opacity", "1");
			} else {
		        if(altUrl && jQuery(altUrl).length && jQuery(altUrl).val()) {
    				if(jQuery(altUrl).val().indexOf("#") === 0
    					|| jQuery(altUrl).val().indexOf("javascript:") === 0
    				) {
				        jQuery(".slug-gotourl:last").attr("href", "javascript:void(0);").hide();
				        jQuery(".admin-title .smart-url:last").text(": " + parentValue + slugName).parent().css("opacity", "1");
					} else {
				        jQuery(".slug-gotourl:last").attr("href", jQuery(altUrl).val()).fadeIn();
				        jQuery(".admin-title .smart-url:last").text(": " + jQuery(altUrl).val()).parent().css("opacity", "1");
					}
				} else {
				    jQuery(".slug-gotourl:last").attr("href", parentValue + slugName).fadeIn();
				    jQuery(".admin-title .smart-url:last").text(": " + parentValue + slugName).parent().css("opacity", "1");
				}
			}
		});    
    } else {
        jQuery(".slug-gotourl:last").attr("href", "javascript:void(0);").hide();
        jQuery(".admin-title .smart-url:last").text((parentValue ? ": " : "") + parentValue).parent().css("opacity", "0.5");
    }
};

ff.cms.admin.UseAjax = function() {
	if(jQuery(".use-ajax input[type=checkbox]").is(":checked")) {
        jQuery(".use-ajax-dep").fadeIn();
    } else {
        jQuery(".use-ajax-dep").hide();
    }
};
ff.cms.admin.isDir = function() {
    if(jQuery(".is_dir input[type=checkbox]").is(":checked")) {
        jQuery(".use-ajax").fadeIn();
    } else {
        if(jQuery(".use-ajax input[type=checkbox]").is(":checked")) {
            $(".use-ajax input[type=checkbox]").trigger( "click" );
        }
        jQuery(".use-ajax").hide();
    }
    ff.cms.admin.UseAjax();
}

ff.cms.admin.getTypeUrl = function(name, target, obj) { /*getLayoutTypeUrl*/
    //jQuery(".activecomboex-icon").hide();
                                         
    if(ff.cms.admin.type[name] === undefined || !ff.cms.admin.type[name]["url"].length > 0) {
        obj.buttonToggle("add", false);
        obj.buttonToggle("edit", false);
        obj.buttonToggle("delete", false);  
    } else {
        var relPathBlock = "";
        var targetValue = "";
        var subUrl = ff.cms.admin.type[name]["url"].replace(/\[value\]/g, "[[" + target + targetValue + "]]");
        var subResource = ff.cms.admin.type[name]["resource"].replace(/\[value\]/g, "[[" + target + targetValue + "]]");

        if(!parseInt(ff.cms.admin.type[name]["useID"]) > 0) {
                targetValue = "_TEXT_ENCODE";
        }
        if(ff.cms.admin.type[name]["key"] == "") {
                relPathBlock = "";
        } else {
                relPathBlock = "?" + ff.cms.admin.type[name]["key"] + "=";
        }

        if(ff.cms.admin.type[name]["add"].length > 0) {
            var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_" + target);
            tmpDlg["url"] = subUrl + "/" + ff.cms.admin.type[name]["add"].replace(/\[value\]/g, "[[" + target + targetValue + "]]");

            ff.ffPage.dialog.dialog_params.set("actex_dlg_" + target, tmpDlg);

            obj.buttonToggle("add", true);

        } else {   
            obj.buttonToggle("add", false);
        }
        
        if(ff.cms.admin.type[name]["edit"].length > 0) {
            var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_edit_" + target);
            tmpDlg["url"] = subUrl + "/" + ff.cms.admin.type[name]["edit"].replace(/\[value\]/g, "[[" + target + targetValue + "]]") + (relPathBlock ? relPathBlock + "[[" + target + targetValue + "]]" : "");

            ff.ffPage.dialog.dialog_params.set("actex_dlg_edit_" + target, tmpDlg);
            obj.buttonToggle("edit", true);
        } else {
            obj.buttonToggle("edit", false);
        }
        
        if(ff.cms.admin.type[name]["delete"].length > 0) {
            var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_delete_" + target); 
            tmpDlg["url"] = subUrl + "/" + ff.cms.admin.type[name]["delete"].replace(/\[value\]/g, "[[" + target + targetValue + "]]").replace(/%5Bvalue%5D/g, "[[" + target + targetValue + "]]").replace("--key--", (relPathBlock ? encodeURIComponent(relPathBlock) + "[[" + target + targetValue + "]]" : "")) + encodeURIComponent((relPathBlock.indexOf("?") >= 0 ? "&" : "?") + "frmAction=" + subResource + "_confirmdelete");

            ff.ffPage.dialog.dialog_params.set("actex_dlg_delete_" + target, tmpDlg);
            obj.buttonToggle("delete", true);
        } else {
            obj.buttonToggle("delete", false);
        }
    }
};
ff.cms.admin.getRemoteData = function(name, srvName, target, obj) { /*getLayoutRemoteData*/
	if(jQuery("#" + target).val() || jQuery("#" + target + " option[value!='']").length <= 0 ) { 
		ff.cms.admin.displayByDep("general", true);
        if(jQuery("#" + target + " option[value!='']").length)
		    obj.buttonToggle("add", true);

        jQuery("#LayoutModify .updater-service").hide();
	} else {
		//TODO: provvisorio da sistemare
		if(jQuery("#" + target + " option[value!='']").length)
			obj.buttonToggle("add", true);  

		ff.cms.admin.displayByDep("general", jQuery("#" + target).val());
		return;
	 	//TODO: provvisorio da sistemare
	 	
         if(ff.cms.admin.type[name] && ff.cms.admin.type[name]["add"].length > 0) {  
            obj.buttonToggle("add", false); 

			ff.cms.admin.displayByDep("general", false);
	        if(!jQuery("#LayoutModify .updater-service").length) {

	            /*
	            da gestire gli inserimenti
	            */
                var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_" + target);
                tmpDlg["url"] = tmpDlg["url"] + "?createnew";

                ff.ffPage.dialog.dialog_params.set("actex_dlg_" + target, tmpDlg);  
                 
                jQuery.ajax({
                    url: ff.site_path + "/api/updater" + srvName + "?out=html&url=" + encodeURIComponent(jQuery("#activecomboex_" + target + "_dialogaddlink a").attr("href"))
                }).done(function(data) {  
                    if(data) {
                        jQuery("#LayoutModify .general").append(data);
                    } else {
                        jQuery("#activecomboex_" + target + "_dialogaddlink").show();
                    }
                }).fail(function(data) {
                    jQuery("#LayoutModify .general").append('<div class="updater-service error">' + data["responseText"] + '</div>'); 
                    jQuery("#activecomboex_" + target + "_dialogaddlink").show();

                    if(jQuery("#" + target + " option").length <= 0)   
                	    ff.cms.admin.displayByDep("general", true);

                    jQuery("#LayoutModify .updater-service.error").fadeOut(1000);
                });
            } else {
                jQuery("#LayoutModify .updater-service").fadeIn();
                if(jQuery("#LayoutModify .updater-service").hasClass("error")) {
                    obj.buttonToggle("add", true); 
                    jQuery("#LayoutModify .updater-service.error").fadeOut(1000);
			    }
	        }
         }
	}                    
};

ff.cms.admin.getBlock = function(name, target, father, obj) { /*getLayoutBlock*/  
    if (!father || ff.cms.admin.type[name] === undefined || ff.cms.admin.type[name]["sub"] === undefined || !ff.cms.admin.type[name]["sub"]["url"].length > 0) {
        obj.buttonToggle("add", false);
        obj.buttonToggle("edit", false);
        obj.buttonToggle("delete", false);
    } else  {
        var relPathBlock = "";
        var targetValue = "";
        var subUrl = ff.cms.admin.type[name]["sub"]["url"].replace(/\[father\]/g, father.toLowerCase()).replace(/\[value\]/g, "[[" + target + targetValue + "]]");
        var subResource = ff.cms.admin.type[name]["sub"]["resource"].replace(/\[father\]/g, father.capitalize()).replace(/\[value\]/g, "[[" + target + targetValue + "]]");

        if(!parseInt(ff.cms.admin.type[name]["sub"]["useID"]) > 0) {
            targetValue = "_TEXT_ENCODE";
        }
        
        if(ff.cms.admin.type[name]["sub"]["key"] == "") {
            relPathBlock = "";
        } else {
            relPathBlock = "?" + ff.cms.admin.type[name]["sub"]["key"] + "=";
        }
        
        if(ff.cms.admin.type[name]["sub"]["add"].length > 0) {
            var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_" + target);
            tmpDlg["url"] = subUrl + "/" + ff.cms.admin.type[name]["sub"]["add"].replace(/\[father\]/g, father.toLowerCase()).replace(/\[value\]/g, "[[" + target + targetValue + "]]");
                                       
            ff.ffPage.dialog.dialog_params.set("actex_dlg_" + target, tmpDlg);
             obj.buttonToggle("add", true);
        } else {
             obj.buttonToggle("add", false);
        }
        if(ff.cms.admin.type[name]["sub"]["edit"].length > 0) {
            var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_edit_" + target);
            
            tmpDlg["url"] = subUrl + ff.cms.admin.type[name]["sub"]["edit"].replace(/\[father\]/g, father.toLowerCase()).replace(/\[value\]/g, "[[" + target + targetValue + "]]") + (relPathBlock ? relPathBlock + "[[" + target + targetValue + "_ENCODE]]" : "");
            ff.ffPage.dialog.dialog_params.set("actex_dlg_edit_" + target, tmpDlg);
             obj.buttonToggle("edit", true);
        } else {
             obj.buttonToggle("edit", false);
        }
        
        if(ff.cms.admin.type[name]["sub"]["delete"].length > 0) {
            var tmpDlg = ff.ffPage.dialog.dialog_params.get("actex_dlg_delete_" + target);
            tmpDlg["url"] = subUrl + ff.cms.admin.type[name]["sub"]["delete"].replace(/\[father\]/g, father.toLowerCase()).replace(/%5Bfather%5D/g, father.toLowerCase()).replace(/\[value\]/g, father.toLowerCase()).replace(/%5Bvalue%5D/g, "[[" + target + targetValue + "]]").replace("--key--", (relPathBlock ? relPathBlock + "[[" + target + targetValue + "]]" : "")) + encodeURIComponent((relPathBlock.indexOf("?") >= 0 ? "&" : "?") + "frmAction=" + subResource + "_confirmdelete");

            ff.ffPage.dialog.dialog_params.set("actex_dlg_delete_" + target, tmpDlg);
            obj.buttonToggle("delete", true);
        } else {
             obj.buttonToggle("delete", false);
        }
    }
};

ff.cms.admin.setNameByBlock = function(blockUpdate, tblsrc, item, subitem, tblsrcAlt, i18nLabel) { /*setNameByBlock*/
   var iconClass = "";
    var h1Class = "admin-title";
    var adminBarH1 = jQuery("H1." + h1Class + ":last");

    if(!blockUpdate) {
        var labelTblsrc = "";
        var labelItem = "";
        var labelSubItem = ""; 
        var tblsrc = (tblsrc !== undefined ? tblsrc : tblsrcAlt);
        var item = (item !== undefined ? item : (jQuery("#LayoutModify_items option:selected").val()
                                            ? jQuery("#LayoutModify_items option:selected").text()
                                            : ""));
        var subitem = (subitem !== undefined ? subitem : (jQuery("#LayoutModify_subitems option:selected").val() 
                                                    ? jQuery("#LayoutModify_subitems option:selected").text()
                                                    : ""));
        if(tblsrc.length) {
            labelTblsrc = tblsrc;
        }

        if(item.length && item != "") {
            labelItem = item;
            if(labelItem == "/") {
                labelItem = i18nLabel["item"];
            } else {
                labelItem = labelItem.replace("/", "");
            }
        }

        if(subitem.length && subitem != "") {
            labelSubItem = subitem;
            if(labelSubItem == i18nLabel["subitem"]) {
                labelSubItem = "";
            } else {
                labelSubItem = labelSubItem.replace("/", "");
            }
            
            if(!labelItem)
                labelItem = tblsrc;
        } else {
            if(labelItem && tblsrc) {
                labelItem = tblsrc + ": " + labelItem;
            }
        
        }
        if(labelItem.length > 0 || labelSubItem.length > 0) {
            jQuery("#LayoutModify_name").val(labelItem + (labelSubItem.length > 0 ? " (" + labelSubItem + ")" : ""));
            if(!jQuery("#LayoutModify_smart_url").val())
            	jQuery("#LayoutModify_smart_url").val(ff.slug(jQuery("#LayoutModify_name").val()));
        } else if(labelTblsrc.length > 0) {
            jQuery("#LayoutModify_name").val(labelTblsrc);
            if(!jQuery("#LayoutModify_smart_url").val())
            	jQuery("#LayoutModify_smart_url").val(ff.slug(jQuery("#LayoutModify_name").val()));
        } else {
            jQuery("#LayoutModify_name").val("");
            jQuery("#LayoutModify_smart_url").val("");
        }

        if(jQuery("#LayoutModify_name").val()) {
            if(adminBarH1.length > 0) {
                jQuery("#LayoutModify_name_label").parent().hide();
                adminBarH1.html(adminBarH1.find("i:last").outerHTML() + jQuery("#LayoutModify_name").val());
            } else {
                jQuery("#LayoutModify_name_label").parent().show();
                jQuery("#LayoutModify_name_label").text(jQuery("#LayoutModify_name").val());
            }
        }				
    }
	
    if(adminBarH1.length > 0 ) {
         if(jQuery("#LayoutModify_tblsrc").val() && jQuery("#LayoutModify_tblsrc").next().find("i").length) {
            var arrTblsrcClass = jQuery("#LayoutModify_tblsrc").next().find("i").attr("class").split(" ");
            for(var i in arrTblsrcClass) {
                    switch(arrTblsrcClass[i].split("-")[0]) {
                            case "type":
                                    h1Class = h1Class + " " + arrTblsrcClass[i];

                                    if(iconClass)
                                            iconClass = iconClass + " ";

                                    iconClass = iconClass + arrTblsrcClass[i];
                                    break;
                            case "icon":
                                    if(iconClass)
                                            iconClass = iconClass + " ";

                                    iconClass = iconClass + arrTblsrcClass[i];
                                    break;
                            default:

                    }
            }
            adminBarH1.attr("class", h1Class);
        }

        if(iconClass) {
            if(adminBarH1.find("i").length) {
                    adminBarH1.find("i").attr("class", iconClass);
            } else {
                    adminBarH1.prepend('<i class="' + iconClass + '"></i>');
            }
        }
    }
};


ff.cms.admin.path = function(elem, fillempty) { /*layoutPath*/
	var elemCascading = jQuery(elem).closest("tr").find(".layout-cascading:not([disabled])");
    if(jQuery(elem).val().length > 1) {
        if(jQuery(elem).val().indexOf("/") != 0) {
            jQuery(elem).val("/" + jQuery(elem).val());
        }
    } else if(fillempty) {
        if(elemCascading.is(":checked")) {
                if(jQuery(elem).val() == "/" || jQuery(elem).val() == "") {
                        jQuery(elem).val("*");
                }
        } else {
                if(jQuery(elem).val() == "*" || jQuery(elem).val() == "") {
                        jQuery(elem).val("/");
                }
        }
    }		
    if(jQuery(elem).val().indexOf("//") >= 0) {
            jQuery(elem).val(jQuery(elem).val().replace("//", "/*/"));
    }
    if(jQuery(elem).val().indexOf("**") >= 0) {
            jQuery(elem).val(jQuery(elem).val().replace("**", "*"));
    }

    if(jQuery(elem).val().slice(-1) == "*") {
    	elemCascading.attr("checked", "checked");
    } else {
    	elemCascading.removeAttr("checked");
    }
};

ff.cms.admin.pathCascading = function(elem) { /*layoutPathCascading*/
	var elemPath = jQuery(elem).closest("tr").find("INPUT.layout-path:not([disabled])");

    if(jQuery(elem).is(":checked")) {
		if(elemPath.val().slice(-1) != "*") {
            if(elemPath.val() == "/") {
            	elemPath.val("*");
            } else {
            	elemPath.val(elemPath.val() + "*");
            }

            /*
            if(elemPath.val().slice(-1) == "/") {
            	elemPath.val(elemPath.val() + "*");
            } else {
            	elemPath.val(elemPath.val() + "/*");
            }*/
        }			
    } else {
        if(elemPath.val() == "/*"
        	|| elemPath.val() == "*"
        ) {
        	elemPath.val("/");
        } else if(elemPath.val().slice(-1) == "*") {
        	elemPath.val(elemPath.val().slice(0, -1)); 
        }			
    }
    
    elemPath.focus();
};

ff.cms.admin.checkLimitLevel = function(elem) {
	if(jQuery(elem).val() > 1) {
		jQuery(".type-dir-selection").show();
	}
	else {
		jQuery(".type-dir-selection").hide();
	}
};

ff.cms.admin.fieldTitle = function(elem) {
	var title = jQuery(elem).val();
	var classNav = jQuery(elem).parents("fieldset").attr("id");
	
	jQuery(elem).parents("#field-detail_jtab").find('UL [aria-controls="' + classNav + '"] DIV').text(title);
	
};


ff.cms.admin.checkFieldTypeGridSystem = function(elem) {
	if(jQuery(elem).val() == 0 || jQuery(elem).val() == 2) {
		if(jQuery(elem).closest("tr").length > 0)
			jQuery(elem).closest("tr").find(".col-dep > *").fadeIn();
		else
			jQuery(elem).closest("FIELDSET").find("DIV.col-dep").fadeIn();
	} else {
		if(jQuery(elem).closest("tr").length > 0)
			jQuery(elem).closest("tr").find(".col-dep > *").hide();
		else
			jQuery(elem).closest("FIELDSET").find("DIV.col-dep").hide();
	}
	if(jQuery(elem).val() == -3) {
		if(jQuery(elem).closest("tr").length > 0)
			jQuery(elem).closest("tr").find(".fluid-dep > *").hide();
		else
			jQuery(elem).closest("FIELDSET").find("DIV.fluid-dep").hide();
	} else {
		if(jQuery(elem).closest("tr").length > 0)
			jQuery(elem).closest("tr").find(".fluid-dep > *").fadeIn();
		else
			jQuery(elem).closest("FIELDSET").find("DIV.fluid-dep").fadeIn();
	}
}

ff.cms.admin.translate = function(what, fromlang, tolang, callback) {
    $.ajax({
        url: "http://api.mymemory.translated.net/get?q=" + what + "&langpair=" + fromlang + "|" + tolang,
        success: function(data) {
        	if(callback)
            	callback($(data)[0].responseData.translatedText);
        }
    });
}

ff.cms.admin.checkiFrame = function(iFrame, title) {
	if(!title)
		title = "Installation Complete.";

	jQuery(iFrame).css({
		"width" : "100%"
		, "min-height": "600px"
	}).show();
	//jQuery(iFrame).parent().children(".installer").remove();

	
/*
	try {
	console.log(iFrame.contentDocument);
		if (!iFrame.contentDocument.location) {
		  //  jQuery(iFrame).hide().before('<div class="installer"><h1>' + title + '<a style="disp	lay:block;" target="_blank" href="' + jQuery(iFrame).attr("src") + '">Go to Site</a></h1></div>');
		}
	}
	catch(err) {
	console.log(err);	
	  //  jQuery(iFrame).hide().before('<div class="installer"><h1>' + title + '<a style="display:block;" target="_blank" href="' + jQuery(iFrame).attr("src") + '">Go to Site</a></h1></div>');
	}	
*/
}

