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
ff.cms = (function () {
	var debug = (jQuery("#adminPanel").length > 0) || (window.location.search.indexOf("__debug__") >= 0);
	var countBlock = 0;
	var cacheBlock = {};
    var service = null;
    var serviceDefer = {};
    var serviceTimer = false;
    var ajaxRequests = {};

	var updateQueryString = function (uri, key, value, st) {
	  if(!st)
	  	st = "?";

	  var re = new RegExp("([" + st + "&])" + key + "=.*?(&|#|$)", "i");
	  if (uri.match(re)) {
          if(value) {
	        uri = uri.replace(re, '$1' + key + "=" + value + '$2');
          } else {
          	uri = uri.replace(re, '$1' + '$2').replace("&&", "&").replace("?&", "?").trim("&");
          }
	  } else {
        if(value) {
	  	    var separator = uri.indexOf(st) !== -1 ? "&" : st;	  	
	        uri = uri + (uri.substr(uri.length - 1) == separator ? "" : separator) + key + "=" + value;   
        } else {
            uri = uri.trim("&");
        }
	  }
	  return (uri == st ? "" : uri);
	};
	var doEventFF = function(callback, item) {
		ff.ajax.addEvent({
			"event_name" : "onUpdatedContent",
			"func_name" : function (params, data) {
				if (
					jQuery('#' + item).attr("id") !== undefined
					&& (item == params.component || jQuery('#' + item).find("#" + params.component).length > 0)
				) {
					if(eval("typeof " + callback) != "undefined" && jQuery.isFunction(eval(callback))) {
						eval(callback + "('" + item + "');");
					} else if(eval("typeof ff.cms.e." + callback) != "undefined" && jQuery.isFunction(eval("ff.cms.e." + callback))) {
						eval("ff.cms.e." + callback + "('" + item + "');");
					}
				}
			}
		});
	};
	var userCallback = function(callback, item) {
		var isCallback = false;
		if(!item)
			item = callback;

		if(callback) {
			if(typeof window[callback] !== undefined && jQuery.isFunction(window[callback])) {
				isCallback = true;

				eval(callback + "('" + item + "');");

				if(debug)
					console.warn(callback + " function's syntax is Deprecated. Change it in ff.cms.e." + callback);
			} else if(typeof ff["cms"]["e"][callback] !== undefined && jQuery.isFunction(ff["cms"]["e"][callback])) {
				isCallback = true;

                    eval("ff.cms.e." + callback + "('" + item + "');");
			} else if(jQuery.isFunction(callback)) {
				callback(item);
			} else {
				if(debug)
					console.info("ff.cms.e." + callback + " is not a function");
			}

			if(isCallback) {
				if(ff.ajax === undefined) {
					ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
						doEventFF(callback, item);
					});
				} else {
					doEventFF(callback, item);
				}
			}
		} else if(item && typeof ff["cms"]["e"]["block"] !== undefined && jQuery.isFunction(ff["cms"]["e"]["block"])) {
			eval("ff.cms.e.block('" + item + "');");
		}
	};
	var widgetProcess = function(func, targetid) {
		var res = "";
		if(targetid !== undefined) {
			if(eval("typeof ff.cms.fn." + func) != "undefined" && jQuery.isFunction(eval("ff.cms.fn." + func))) {
				res = eval("ff.cms.fn." + func + "('" + targetid + "')"); 
			} else {
				if(debug)
					console.error("ff.cms.fn." + func + " is not a plugin" + " (reference: " + (targetid ? targetid : "MainPage") + " )");
			}
		} else { 
			if(debug)
				console.error("Reference ID undefined in widgetProcess");
		}
		return res;
	};
	var parseError = function(elem, mode, effect) {
		switch(mode) {
			case "before":
			case "after":
			case "prepend":
			case "append":
				break;
			case "replace":
			default:   
		        jQuery(elem).html("Non trovato!");
		}  
		/* da arricchire*/  
    };
	var parseBlock = function(elem, data, mode, effect) {
		if(jQuery(elem).length) {
			var itemID = jQuery(elem).attr("id");

			if(itemID && jQuery("#" + itemID, data).length)
				data = jQuery("#" + itemID, data);

			itemID = "#" + jQuery(data).attr("id");
			
			switch(mode) {
				case "before":
					jQuery(elem).before(data);
					jQuery(elem).remove();
					//itemID = "#" + jQuery(data).attr("id");
					break;
				case "after":
					jQuery(elem).after(data);
					jQuery(elem).remove();
					//itemID = "#" + jQuery(data).attr("id");
					break;
				case "prepend":
					jQuery(elem).prepend(jQuery(data).html());
					break;
			    case "append":
	//				jQuery(elem).after(data);
					//itemElem = jQuery(elem).next();
			        jQuery(elem).append(jQuery(data).html());
//			        jQuery(itemElem).remove();
			        break;
			    case "replace":
			    default:   
	                if(jQuery(data).attr("id") != jQuery(elem).attr("id") && jQuery(data).is(".block"))
	                    jQuery(elem).html(jQuery(data).outerHTML());
	                else
				        jQuery(elem).html(jQuery(data).html());
			        //itemElem = jQuery(elem);
			}
            jQuery("head").append(jQuery("LINK,STYLE,SCRIPT", data));
            //jQuery("head").append(jQuery(data).nextAll("LINK"));
            //jQuery("head").append(jQuery(data).nextAll("STYLE"));
            //jQuery("head").append(jQuery(data).nextAll("SCRIPT"));
			
			//jQuery(itemID).attr("id"			, itemAttr["id"]);
			jQuery(itemID).attr("data-admin"	, jQuery(data).attr("data-admin"));
			jQuery(itemID).attr("data-src"		, jQuery(data).attr("data-src"));
			jQuery(itemID).attr("data-ready"	, jQuery(data).attr("data-ready"));
			jQuery(itemID).attr("data-event"	, jQuery(data).attr("data-event"));
			jQuery(itemID).attr("data-ename"	, jQuery(data).attr("data-ename"));
			jQuery(itemID).addClass(jQuery(data).attr("class"));

			if(jQuery(itemID).hasClass("block"))
				userCallback(undefined, itemID.replace("#", ""));

			effect = ff.cms.widgetInit(itemID, effect);
            
			switch (effect) {
				case false:
					jQuery(itemID).fadeIn();
					break;
				case "fadeInToggle":
					jQuery(itemID).fadeToggle();
					break;
				case "slideDownToggle":
					jQuery(itemID).slideToggle();
					break;
			    case "slideUp":
			        jQuery(itemID).slideUp();
			        break;
			    case "slideDown":
			        jQuery(itemID).slideDown();
			        break;
			    case "hide":
			        jQuery(itemID).hide();
			        break;
			    case "show":
			        jQuery(itemID).show();
					break;
				case "fadeOut":
					jQuery(itemID).show();
					jQuery(itemID).fadeOut();
				case "fadeIn":
				default:
					jQuery(itemID).hide();
					jQuery(itemID).fadeIn();
			}
            
            ff.lazyImg();
			
			return itemID;
		}	
	
	};
	var loadAjax = function(link, elem, effect, mode, onClickCallback, blockUI, jumpUI) {
		var itemID = elem.attr("id");
        if(blockUI === undefined)
            blockUI = jQuery(elem).hasClass("blockui");
            
        if(blockUI) {
            if(mode != "append" && mode != "prepend")
                ff.cms.blockUI(elem, (jumpUI ? elem : false));
            else
                ff.cms.blockUI();
        }

		if(cacheBlock[link]) {
			if(mode != "append" || mode != "prepend") {
    			itemID = parseBlock(elem, cacheBlock[link], mode, effect);
			}

			if(onClickCallback)
				onClickCallback(itemID);

            ff.cms.unblockUI(elem);

			return cacheBlock[link];
		} else {
			if(link.indexOf("://") > 0) {
                jQuery(elem).html('<iframe src="' + link + '"></iframe>');
                if(blockUI)
                    ff.cms.unblockUI(elem);
			} else {
                var itemID = "#" + jQuery(elem).attr("id");
                var pathname = link.split("?")[0];
                var query = link.split("?")[1];
                if(pathname == window.location.pathname) {
                	query = (query ? query + "&" : "") +  new Date().getTime();
                }

                var index = "xhr-" + Date.now();
                ajaxRequests[index] = jQuery.ajax({
			        async: true,    
			        type: "GET",
			        url: pathname, 
			        data: query,
			        cache: true
				}).done(function(data) {
					if(data) {
						var item = (typeof data == "object" 
							? data["html"] || ""
							: data
						);
						
						item = item.trim();
						
						cacheBlock[link] = item;
						
						itemID = parseBlock(elem, item, mode, effect);
					} else {
                        parseError(elem, mode, effect);
                    }
					/*if(item && item.length > 0) {
					    item = item.trim();
					   	cacheBlock[link] = item;

	                    itemID = parseBlock(elem, item, mode, effect);
			        }*/

                        
                        

				}).fail(function(data) {
			        switch(data.status) {
			            case "404":
		            		jQuery(elem).html(data.statusText);
		            		break;
			            default:
			        }
			    }).always(function() {
                    delete ajaxRequests[index];

                    if(blockUI)
					    ff.cms.unblockUI(elem); 

                    if(onClickCallback)
                        onClickCallback(itemID);  
				});
			}
		}
	};	
	var loadAjaxLink = function(elem, linkDefault, linkContainer, ajaxOnEvent, eventName) {
		var arrAjaxOnEvent = ["load", "fadeIn"];
			
	    var callback = jQuery(elem).attr("data-callback");
	    var effect = jQuery(elem).attr("data-effect");
		var link = jQuery(elem).attr("data-src") || jQuery(elem).attr("href") || linkDefault;
		var container = jQuery(elem).closest(".block");
		var targetID = "ajaxcontent";
		if(!jQuery("#" + targetID).length) {
			targetID = jQuery(container).attr("id") + targetID;
			jQuery(container).after('<div id="' + targetID + '"></div>');		
		}
		if(linkContainer === undefined)
			linkContainer = ".block";

	    if(ajaxOnEvent) {
	        arrAjaxOnEvent = ajaxOnEvent.split(" ");
	    }
	    if(effect)
	        arrAjaxOnEvent[1] = effect;
	        
	    if(callback) {
	        try {
	            eval("ff.cms.e." + id.replace(/[^a-zA-Z 0-9]+/g, "") + " = function() { " + callback + " }");
	        } catch(err) {
	            console.err(err + ": " + callback);
	        }
	    }
	    if(jQuery(elem).hasClass(ff.cms["class"]["current"])) {
	    	arrAjaxOnEvent[1] = arrAjaxOnEvent[1] + "Toggle";
	    	jQuery(elem).removeClass(ff.cms["class"]["current"]);
	    } else {
		    if(linkContainer) {
		        jQuery(elem).closest(linkContainer).find("a").each(function() {
		            var relContent = "";
		            if(jQuery(this).attr("rel")) {
		                relContent = "#" + jQuery(this).attr("rel");
		            } else if(jQuery(this).attr("href").indexOf("#") == 0) {
		                relContent = jQuery(this).attr("href");
		            }
		            if(relContent && jQuery(relContent).length && jQuery(relContent).hasClass("block")) {
		                jQuery(relContent).hide(); 
		            }

		            jQuery(this).removeClass(ff.cms["class"]["current"]);
		        });
		        //jQuery(elem).closest("div").find("a").removeClass("selected");
		        jQuery(elem).addClass(ff.cms["class"]["current"]);
		    }
		}	    
	    loadAjax(link, jQuery("#" + targetID), arrAjaxOnEvent[1], "replace", eventName); 
	};
	var loadContent = function() {
		var lazyBlock = [];
 		var processLazyBlock = function() {
		  for (var i = 0; i < lazyBlock.length; i++) {
		    if (/*jQuery("#" + lazyBlock[i]).is(":visible") &&*/ ff.inView("#" + lazyBlock[i], 0.5) ) {
				that.getBlock(lazyBlock[i], { "jumpUI" : false, "blockUI" : false});
				lazyBlock.splice(i, 1);
				i--;		    
		    }
		  }
      	  if(!lazyBlock.length)
      		jQuery(window).unbind("scroll.lazyBlock");		  
		};	
		
		/*jQuery('INPUT.ajaxcontent[type=hidden]').each(function() {
		    var link = jQuery(this).val();
		    var elem = jQuery(this);
		    var eventName = jQuery(this).attr("data-ename") || undefined;
		    
		    var id = jQuery(this).attr("id");

		    loadAjax(link, elem, false, "after", eventName);
		});*/

		jQuery(document).on("click.ajaxcontent", "a.ajaxcontent", function(e) {
			e.preventDefault();
			
			loadAjaxLink(this);
			
			return false;
		});
		
		jQuery('.block').each(function() {
			var arrAjaxOnReady = ["load", "fadeIn"];
			var arrAjaxOnEvent = ["load", "fadeIn"];
            
            var id = jQuery(this).attr("id");
            var link = jQuery(this).attr("data-src");
            var ajaxOnReady = jQuery(this).attr("data-ready");
            var ajaxOnEvent = jQuery(this).attr("data-event");
            var eventName = jQuery(this).attr("data-ename");
			if(ajaxOnReady)
				arrAjaxOnReady = ajaxOnReady.split(" ");

			if(!link)
				arrAjaxOnReady[0] = "";

	        switch(arrAjaxOnReady[0]) {
                case "inview":
                    if(!ff.inView("#" + id, 0.5)) {
                        lazyBlock.push(id);
                        break;
                    }
            	case "load":
            	case "reload":
                    ff.cms.get(link);
            		//loadAjax(link, jQuery(this), arrAjaxOnReady[1], "replace", eventName);
					break;
            	case "preload":
            		if(!jQuery(this).is(":visible"))
            			break;
            	default:
 					if(eventName) {
					    userCallback(eventName, id);
					} else if(id) {
						userCallback(id.replace(/[^a-zA-Z 0-9]+/g, ""), id);
					}
					break;
				case "standby":
	        }
            /* Rel Block to a Link*/
            if(id && jQuery("a[rel=" + id + "], a[href=#" + id + "]").is("a")) {
                jQuery("a[rel=" + id + "], a[href=#" + id + "]").each(function() {
                    if(jQuery(this).attr("onclick")) {
                        jQuery(this).attr("data-callback", jQuery(this).attr("onclick"));
                        jQuery(this).attr("onclick", "");
                    }
                });
                jQuery("a[rel=" + id + "], a[href=#" + id + "]").bind("click.ajaxcontent", function(e) {
					e.preventDefault();

					loadAjaxLink(this, link, undefined, ajaxOnEvent, eventName);
                });
            }                
		});
		
		if(lazyBlock.length) {
		    jQuery(window).bind("scroll.lazyBlock", processLazyBlock); 
            processLazyBlock();
		    //setTimeout("jQuery(window).scroll()", 400); //da trovare una soluzione migliore
		}

		if(jQuery("BODY").data("admin")) {
            ff.cms.get("admin", {"target": "BODY"}, {"sid" : jQuery("BODY").data("admin")}
                , {"inject" : "prepend"}
            );
        }
	};
    var loadReq = function(target, reset) {
        var socket = 1;
        var req = null;
        if(reset && serviceTimer) {
            serviceTimer = true;
        }
        if(target) {
            if(typeof target == "object") {
                if (debug)
                    console.info("Service Set", target);

                req = target;

                target = false;
            } else {
                if (debug && serviceDefer[target])
                    console.info("Service Defer (" + target + "): " + serviceDefer[target]["status"] + " [exec]", serviceDefer[target]["req"]);

                if (serviceDefer[target] && serviceDefer[target]["status"] == "pending" && serviceDefer[target]["req"].length) {
                    req = {};
                    serviceDefer[target]["req"].each(function (i, name) {
                        req[name] = service[name];
                        if (req[name]["keys"] && req[name]["response"])
                            delete req[name]["response"];
                    });
                    serviceDefer[target]["status"] = "running";
                    serviceDefer[target]["req"] = [];

                    if (debug)
                        console.info("Service Defer (" + target + "): " + serviceDefer[target]["status"], serviceDefer[target]["req"]);
                }
            }
        } else {
            req = service;
            if(debug)
                console.info("Service All: " + document.readyState + " [exec]", service);
        }

        if(req) {
            var index = "xhr-" + Date.now();
            ajaxRequests[index] = jQuery.ajax({
                async: true,
                type: "POST",
                url: "/srv/request",
                data: "params=" + encodeURIComponent(JSON.stringify(req)) + "&js=" + ff.libToString("js") + "&css=" + ff.libToString("css"),
                dataType : "json",
                cache: true
            }).done(function(response) {
                if (target !== false && response) {
                    var assets = {
                        "libs" : ""
                        , "js" : []
                        , "css" : []
                    };
                    var globalVars = {
                        "selector" : {}
                        , "data" : {}
                    };

                    for (var name in response) {
                        switch(name) {
                            case "assets":
                                assets["libs"] = response[name];
                                break;
                            default:

                                if (!service[name])
                                    service[name] = {};

                                if (response[name] && typeof response[name] == "object" && !Array.isArray(response[name])) {
                                    var serviceTpl = service[name]["tpl"] || {};
                                    var serviceOpt = service[name]["opt"] || {};

                                    globalVars["selector"] = serviceTpl["vars"] || {};
                                    switch (serviceTpl["template"]) {
                                        case "text/template":
                                            var target = serviceTpl["target"];
                                            var source = serviceTpl["source"];

                                            if (target && source) {
                                                var html = "";
                                                var tplRemove = [];
                                                if (Array.isArray(response[name]["result"])) {
                                                    response[name]["result"].each(function (i, item) {
                                                        var tpl = jQuery(source).html();

                                                        for (var property in item) {
                                                            if (item.hasOwnProperty(property) && item[property]) {
                                                                tpl = tpl.replaceAll("{{" + property + "}}", item[property]);
                                                                if (!item[property])
                                                                    tplRemove.push('*[data-if="' + property + '"]');
                                                            }
                                                        }
                                                        html += tpl;
                                                    });
                                                } else {
                                                    var tpl = jQuery(source).html();
                                                    var tplVars = response[name][serviceTpl["vars"]] || response[name]["vars"];
                                                    for (var property in tplVars) {
                                                        if (tplVars.hasOwnProperty(property) && tplVars[property]) {
                                                            tpl = tpl.replaceAll("{{" + property + "}}", tplVars[property]);
                                                            if (!tplVars[property])
                                                                tplRemove.push('*[data-if="' + property + '"]');
                                                        }
                                                    }
                                                    html = tpl;
                                                }

                                                if (html) {
                                                    var pattern = /{{([^}]+)}}/g;
                                                    while (match = pattern.exec(html)) {
                                                        tplRemove.push('*[data-if="' + match[1] + '"]');
                                                        html = html.replaceAll("{{" + match[1] + "}}", "");
                                                    }
                                                    if (tplRemove.length) {
                                                        html = jQuery(html);
                                                        jQuery(tplRemove.join(","), html).remove();

                                                    }

                                                    if (serviceOpt["inject"] == "prepend")
                                                        jQuery(target).prepend(html);
                                                    else
                                                        jQuery(target).append(html);
                                                }
                                                globalVars["data"]["counter"] = jQuery(target).children().length;
                                            }
                                            break;
                                        case "text/x-handlebars-template":
                                            var target = serviceTpl["target"];
                                            var source = serviceTpl["source"];
                                            var result = response[name]["result"] || null;

                                            if (target && source) {
                                                var html = "";
                                                //ff.pluginLoad("Handlebars", "https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.min.js", function () {
                                                if (Array.isArray(result)) {
                                                    result.each(function (i, item) {
                                                        var tpl = jQuery(source).html();
                                                        var template = Handlebars.compile(tpl);
                                                        html += template(item);
                                                    });
                                                } else if (result) {
                                                    var template = Handlebars.compile(tpl);
                                                    html = template(result);
                                                }

                                                if (html) {
                                                    if (serviceOpt["inject"] == "prepend")
                                                        jQuery(target).prepend(html);
                                                    else
                                                        jQuery(target).append(html);
                                                }

                                                globalVars["data"]["counter"] = jQuery(target).children().length;
                                                //});
                                            }
                                            break;
                                        default:
                                            var target = response[name]["target"] || serviceTpl["target"];
                                            if (target) {
                                                var source = response[name]["source"] || target;
                                                var tplVars = response[name]["vars"];
                                                var tplProperties = response[name][serviceTpl["properties"]] || response[name]["properties"];
                                                var html = response[name]["html"];
                                                if (!html) {
                                                    var tpl = jQuery(source);

                                                    jQuery("STYLE", tpl).appendTo(jQuery("head"));
                                                    jQuery("SCRIPT", tpl).appendTo(jQuery("head"));

                                                    html = jQuery(tpl).html();
                                                }

                                                if (html) {
                                                    if (typeof tplProperties == "object") {
                                                        for (var propType in tplProperties) {
                                                            if (tplProperties.hasOwnProperty(propType)) {
                                                                for (var property in tplProperties[propType]) {
                                                                    if (tplProperties[propType].hasOwnProperty(property)) {
                                                                        html = html.replaceAll('data-' + propType + '="<!--{{' + property + '}}-->"', propType + '="' + tplProperties[propType][property] + '"');
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if (typeof tplVars == "object") {
                                                        for (var property in tplVars) {
                                                            if (tplVars.hasOwnProperty(property)) {
                                                                if (globalVars["selector"][property])
                                                                    globalVars["data"][property] = tplVars[property];

                                                                html = html.replaceAll('<!--{{' + property + '}}-->', tplVars[property]);
                                                            }
                                                        }
                                                    }

                                                    if (serviceOpt["inject"] == "prepend") {
                                                        jQuery(target).prepend(html);
                                                    } else if(serviceOpt["inject"] == "append") {
                                                        jQuery(target).append(html);
                                                    } else if (jQuery(source, html).length > 0) {
                                                        if (source == target)
                                                            jQuery(target).replaceWith(jQuery(source, html).html());
                                                        else
                                                            jQuery(target).html(jQuery(source, html).html());
                                                    } else {
                                                        jQuery(target).html(html);
                                                    }
                                                }
                                            }
                                    }

                                    if (target && service[name]["opt"]) {
                                        switch (service[name]["opt"]["effect"]) {
                                            case false:
                                                jQuery(target).fadeIn();
                                                break;
                                            case "fadeInToggle":
                                                jQuery(target).fadeToggle();
                                                break;
                                            case "slideDownToggle":
                                                jQuery(target).slideToggle();
                                                break;
                                            case "slideUp":
                                                jQuery(target).slideUp();
                                                break;
                                            case "slideDown":
                                                jQuery(target).slideDown();
                                                break;
                                            case "hide":
                                                jQuery(target).hide();
                                                break;
                                            case "show":
                                                jQuery(target).show();
                                                break;
                                            case "fadeOut":
                                                jQuery(target).show();
                                                jQuery(target).fadeOut();
                                            case "fadeIn":
                                                jQuery(target).hide();
                                                jQuery(target).fadeIn();
                                                break;
                                            default:
                                        }

                                        if (service[name]["opt"]["block"] === true) {
                                            ff.cms.blockUI(target);
                                        } else if (service[name]["opt"]["block"] === false) {
                                            ff.cms.unblockUI(target);
                                        }
                                        // ff.lazyImg();
                                    }


                                    if (response[name]["result"]) { //da gestire i multi valori provenienti dalle chiamate asincrone con il timer
                                        service[name]["response"] = response[name]["result"];
                                    }
                                    if (response[name]["keys"]) {
                                        if (!service[name]["keys"])
                                            service[name]["keys"] = [];

                                        response[name]["keys"].each(function (i, key) {
                                            if (service[name]["keys"].indexOf(key) < 0) {
                                                service[name]["keys"].push(key);
                                            }
                                        });
                                    }

                                    if (response[name]["js"]) {
                                        assets["js"].push(response[name]["js"]);
                                    }
                                    if (response[name]["css"]) {
                                        assets["css"].push(response[name]["css"]);
                                    }

                                    if (response[name]["timer"]) {
                                        if (!service[name]["opt"]) service[name]["opt"] = {};
                                        service[name]["opt"]["timer"] = response[name]["timer"];
                                    }
                                    if (service[name]["opt"] && service[name]["opt"]["timer"]) {
                                        service[name]["opt"]["timer"] = false;
                                        if (!serviceDefer[response[name]["timer"]]) {
                                            serviceDefer[response[name]["timer"]] = {
                                                "req": []
                                                , "status": "idle"
                                            };
                                        }
                                        var timer = response[name]["timer"];
                                        if (serviceDefer[timer]["req"].indexOf(name) < 0) {
                                            serviceDefer[timer]["req"].push(name);

                                            if (debug)
                                                console.info("Service Defer (" + timer + "): " + serviceDefer[timer]["status"] + " [add queue]", serviceDefer[timer]["req"]);
                                        }

                                        if (serviceDefer[timer]["status"] == "idle") {
                                            serviceDefer[timer]["status"] = "pending";

                                            if (debug)
                                                console.info("Service Defer (" + timer + "): " + serviceDefer[timer]["status"] + " [setTimeout]", serviceDefer[timer]["req"]);

                                            setTimeout(function () {
                                                ff.cms.loadReq(timer);
                                            }, timer);
                                        }
                                    }
                                } else {
                                    if (response[name] !== null && debug)
                                        console.warn("Request Service: the response is not a object " + name, response[name]);
                                }

                                if(service[name]["callback"]) {
                                    if (jQuery.isFunction(service[name]["callback"])) {
                                        res = service[name]["callback"](response[name], globalVars);
                                        if (res) globalVars = res;
                                    } else {
                                        if(debug)
                                            console.warn("Request Service: the callback is not a function " + name, service[name]);
                                    }
                                }
                        }
                    }

                    if(globalVars["selector"]) {
                        for (var property in globalVars["selector"]) {
                            if (globalVars["selector"].hasOwnProperty(property)) {
                                jQuery(globalVars["selector"][property]).hide();
                                if(globalVars["data"][property]) {
                                    jQuery(globalVars["selector"][property]).text(globalVars["data"][property]).removeClass("hidden").show();
                                }
                            }
                        }
                    }
                    if(assets["libs"]) {
                        jQuery(assets["libs"]).appendTo(jQuery("head"));
                    }
                    if (assets["js"].length) {
                        jQuery('<script type="text/javascript">' + assets["js"].join(" ") + '</script>').appendTo(jQuery("head"));
                    }
                    if (assets["css"].length) {
                        jQuery('<style type="text/css">' + assets["css"].join(" ") + '</style>').appendTo(jQuery("head"));
                    }
                }
            }).always(function() {
                delete ajaxRequests[index];
                if(target) {
                    if (serviceDefer[target]["req"].length) {
                        serviceDefer[target]["status"] = "pending";
                    } else {
                        serviceDefer[target]["status"] = "idle";
                    }
                    if(debug)
                        console.info("Service Defer (" + target + "): " + serviceDefer[target]["status"] + " [ajax done]", serviceDefer[target]["req"]);

                    if(isFinite(String(target))) {
                        if (serviceDefer[target]["req"].length) {
//                            serviceDefer[target]["status"] = "pending";
                            if (debug)
                                console.info("Service Defer (" + target + "): " + serviceDefer[target]["status"] + " [setTimeout]", serviceDefer[target]["req"]);

                            var timer = target;
                            setTimeout(function () {
                                ff.cms.loadReq(timer);
                            }, timer);
                        } else {
//                            serviceDefer[target]["status"] = "idle";

                        }
                    } else {
                        // serviceDefer[target]["status"] = "completed";

                    }
                } else {
                    if(debug)
                        console.info("Service All: " + document.readyState + " [ajax done]", service);
                }

            }).fail(function(error) {
            }).error(function (xmlHttpRequest, textStatus, errorThrown) {
                if(xmlHttpRequest.readyState == 0 || xmlHttpRequest.status == 0)
                    return;  // it's not really an error
            });
        }
    };
	var that = { // publics
            __ff : false, // used to recognize ff'objects
            "skipInit" : false,
            "actualPath" : "http://" + window.location.hostname + window.location.pathname,
            "class" : {
                    "current" : "active"
            },
            "fn" : {},
            "e" : {},
            "libs" : {},
			"debug" : function() {
				debug = !debug;
				return (debug ? "Start Debugging..." : "End Debug.");
			},
			"dump" : function() {
				console.log({
					"service" : service
					, "defer" : serviceDefer
					, events : jQuery._data( jQuery(document)[0], 'events' )
					, timer: serviceTimer
				});
			},
            "initCMS" : function() {
                var that = this;

                jQuery("#above-the-fold").remove();

                ff.fn.frame = function (params, data) {
                    if(params.component !== undefined
                        && data.html !== undefined
                        && jQuery("#" + params.component).attr("id") !== undefined
                    ) {
                        ff.cms.widgetInit("#" + params.component);
                    }
                };
                ff.pluginAddInit("ff.ajax", function () {
                    ff.ajax.addEvent({
                            "event_name" : "onUpdatedContent"
                            , "func_name" : ff.fn.frame
                    });
                    ff.ajax.addEvent({
                        "event_name"	: "onSuccess"
                        , "func_name"	: function (data, params, injectid) {
                            if (data.modules && data.modules.security && data.modules.security.loggedin) {
                                if(typeof ga !== "undefined") {
                                    var tracker = ga.getAll()[0];
                                    if (tracker) {
                                        tracker.send('event', {'userId':  'u-' + data.modules.security.UserNID});
                                    }
                                }

                            }
                        }
                    });
                });

                loadContent();
                jQuery(window).on('beforeunload', function () {
                    ff.cms.abortXHR();
                });

                if(document.readyState == "complete") {
                    serviceTimer = true;
                    if (serviceDefer["complete"] && serviceDefer["complete"]["req"]) {
                        serviceDefer["complete"]["status"] = "pending";
                        loadReq("complete");

                    }
                } else {
                    if(serviceDefer["loading"] && serviceDefer["loading"]["req"]) {
                        serviceDefer["loading"]["status"] = "pending";
                        loadReq("loading");
                    }

                    jQuery(document).on("ready", function(){
                        if(serviceDefer["interactive"] && serviceDefer["interactive"]["req"]) {
                            serviceDefer["interactive"]["status"] = "pending";
                            loadReq("interactive");
                        }
                        //loadReq();

                    });

                    jQuery(window).on("load", function (e) {
                        serviceTimer = true;
                        if (serviceDefer["complete"] && serviceDefer["complete"]["req"]) {
                            serviceDefer["complete"]["status"] = "pending";
                            loadReq("complete");
                        }
                    });
                }
            },
            "abortXHR" : function() {
                for (var xhr in ajaxRequests) {
                    if (ajaxRequests.hasOwnProperty(xhr) && xhr.indexOf("xhr-") === 0) {
                        ajaxRequests[xhr].abort();
                    }
                }
                //ajaxRequests = {};

                return ajaxRequests
            },
            "blockUI" : function(blockElem, jumpTo, noAjaxBlock) {
                if(blockElem)
                    jQuery(blockElem).addClass("loading");

                if(jumpTo) {
                    var margin = 170; //da parametrizzare e dinamicizzare
                    window.scrollTo(0, jQuery(jumpTo)[0].offsetTop - margin);
                }

                if(!noAjaxBlock) {
                    if(!countBlock) {
                        ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {			
                            ff.ajax.blockUI();
                        });
                    }

                    countBlock++;
                }
            },
            "unblockUI" : function(blockElem, noAjaxBlock) {
                if(blockElem) {    
                    //jQuery(blockElem).css({"opacity": "", "pointer-events": ""});
                    jQuery(blockElem).removeClass("loading");
                }

                if(!noAjaxBlock) {
                    countBlock--; 
                    if(!countBlock)
                        ff.ajax.unblockUI();
                }
            },
            "widgetInit" : function(targetid, displayMode) {
                if(targetid)
                    userCallback(targetid.replace(/[^a-zA-Z 0-9]+/g, ""), targetid.replace("#", ""));

                    for (var func in ff.cms.fn) {
                            var res = widgetProcess(func, targetid);
                            if(res)
                            displayMode = res;
                    }

                    return displayMode;
            },
            "addEvent" : function(id, callback) {
                    ff.cms.e[id.replace(/[^a-zA-Z 0-9]+/g, "")] = callback;
            },
            "updateUriParams" : function(key, value, uri, searchIn) {
                var parser = document.createElement('a');
                parser.href = uri || window.location.href;

                var pathname = parser.pathname;
                if(pathname && pathname != "/")
                    pathname = "/" + pathname.trim("/");

                var search = parser.search;
                var hash = parser.hash;

                switch(searchIn) {
                    case "path":
                        break;
                    case "hash":
                        hash = updateQueryString(hash, key, value, "#");
                        break;
                    case "search":
                    default:
                        search = updateQueryString(search, key, value);

                }
                return pathname + search + hash;
            },
            "filter" : function(elem, container) {
                var objTerm = undefined;
                var term = "";
                if(typeof elem == "object") {
                    objTerm = jQuery(elem);
                    term = objTerm.val() || objTerm.attr("data-rel");
                } else {
                    term = elem;
                }

                if(!container)
                container = jQuery(".vg-item[data-ffl]").closest(".block");

                if(objTerm && objTerm.is("a")) {
                    objTerm.closest("dl").find("dd").removeClass("active");
                    objTerm.parent().addClass("active");
                }
                ff.pluginLoad("jq.isotope", "https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/2.2.1/isotope.pkgd.js", function(){
                    jQuery(container).isotope({
                        itemSelector: '.vg-item',
                        layoutMode: 'fitRows',
                        filter: function() {
                            return !term 
								|| (objTerm
									? (objTerm.is("input") 
										? jQuery(this).text().match( term )
										: term == jQuery(this).attr("data-ffl") 
									)
									: true
								);
                        }
                    }); 
                });
            },
            "login" : {
                "social" : function(name, title) {
                        ff.modules.security.social.requestLogin(title, '/login/social/' + name);
                },
                "open" : function(title, url, ret_url, id) {
                        ff.modules.security.openLoginDialog(title, url, ret_url, id);
                },
                "submit" : function(action, component, elem, containerSuccess) {
                    var url = '/login';
                    switch(action) {
                        case "active":
                            url = '/login/activation';
                            action = 'request';
                            break;
                        case "recover":
                            url = '/login/lostpassword';
                            action = 'request';
                            break;
                        case "login":
                        case "logout":
                            default:
                    }

                    ff.modules.security.submit(action, component, elem, url, containerSuccess);

                    return false;
                }
            },
            "load" : function(link, elem, effect, mode, onClickCallback, blockUI, jumpUI) {
                loadAjax(link, elem, effect, mode, onClickCallback, blockUI, jumpUI);
            },
			"loadReq" : function(target, reset) {
				loadReq(target, reset);
			},
			"get" : function(name, callback, params, opt) {
				var res = null;
				if(!opt) opt = {};

				if(!name) {
					res = service;
				} else {
					if(!service) service = {};

					if(service[name]) {
						service[name]["counter"] = (!service[name]["counter"]
								? service[name]["counter"] = 2
								: service[name]["counter"]++
						);
						console.warn(name + " Already Exist: Old", service[name], " New", {"callback": callback, "params": params, "opt": opt});
						var url = name;

						name = name + "#" + service[name]["counter"];
					}

					service[name] = {};
					if(callback) {
						if(jQuery.isFunction(callback)) {
							service[name]["callback"] = callback;
						} else {
							var tpl = {};

							if(typeof(callback) == "object") {
								var tpl = {};

								tpl["target"] = callback["target"];
								tpl["vars"] = callback["vars"];
								if(callback["template"]) {
									tpl["source"] = "#" + callback["template"];
									tpl["template"] = jQuery(tpl["source"]).attr("type");
								}
								if (callback["callback"])
									service[name]["callback"] = callback["callback"];
							} else {
								tpl["target"] = callback;
							}
						}
					} else {
						opt["async"] = true;
					}

					if(url)
						opt["url"] = url;

					if(tpl)
						service[name]["tpl"] = tpl;

					if(params)
						service[name]["params"] = params;

					if(opt)
						service[name]["opt"] = opt;

					if(service[name]["response"])
						res = service[name]["response"];
					else
						res = service[name];

					var queue = (0 && opt["priority"]
							? document.readyState + "-" + opt["priority"]
							: document.readyState
					);
					if(!serviceDefer[queue])
						serviceDefer[queue] = {
							"req" : []
							, "status" : "idle"
						};

					if(serviceDefer[queue]["req"].indexOf(name) < 0) {
						serviceDefer[queue]["req"].push(name);

						if (debug)
							console.info("Service Defer (" + queue + "): " + serviceDefer[queue]["status"] + " [add queue]", serviceDefer[queue]["req"]);
					}

					if(serviceTimer === true
						&& serviceDefer[queue]["req"].length
					) {
						serviceDefer[queue]["status"] = "pending";
						serviceTimer = setTimeout(function() {
							ff.cms.loadReq(queue, true)
						}, 100);
					}
				}
				return res;
			},
			"set" : function(name, params, opt, keys) {
				var srv = {};

				if(!opt)
					opt = service[name]["opt"];
				if(!keys)
					keys = service[name]["keys"];

				srv[name] = {
					"opt" : opt
					, "params" : params
					, "keys" : keys
				};

				loadReq(srv);
			},
            "getBlock" : function(id, params, callback) {
                if(id) {
                    var elem = jQuery("#" + id);

                    if(!callback && jQuery.isFunction(params)) {
                            callback = params;
                            params = {};
                    } else if(!params)
                            params = {};

                    if(params["callback"] && !callback)
                            callback = params["callback"];

                    //ff.cms.getBlock("L33T", {"effect" : "fadeIn", "mode": "append", "url" : "/aasdasdsd", "search" : {"nome": "francesca"}, "page" : 2, "count": 333, "sort" : [{"nome": "asc"}, "specializzazione"], function(data) {})

                    var ajaxOnEvent = elem.attr("data-event") || "";

                    var link = (params["url"]) || elem.attr("data-src") || (window.location.pathname + (window.location.hash.indexOf("_") >= 0 ? "" : window.location.hash.replace("#", "/")));
                                    var effect = (params["effect"]) || ajaxOnEvent.split(" ")[1] || "";
                    var mode = (params["mode"]) || "replace";
                    var blockUI = (params["blockUI"] !== undefined ? params["blockUI"] : true);
                    var jumpUI = (params["jumpUI"] !== undefined ? params["jumpUI"] : elem);
                    if(params["infinite"]) {
                        effect = "show";
                        mode = "append";
                        jumpUI = false;
                    }
                    if(link) {
                        var linkHistory = window.location.href;
                        var linkParams = [];
                        if(params["search"]) {
                            if(typeof params["search"] == "string") {
                                linkParams.push("q=" + params["search"]);
                            } else {
                                for(var term in params["search"]) {
                                    linkParams.push(term + "=" + params["search"][term]);
                                    linkHistory = ff.cms.updateUriParams(term, params["search"][term], linkHistory);
                                }
                            }
                        }

                        if(params["page"]) {  
                            linkParams.push("page=" + params["page"]);
                            linkHistory = ff.cms.updateUriParams("page", (params["page"] > 1 ? params["page"] : ""), linkHistory);
                        }
                        if(params["count"]) {
                            linkParams.push("count=" + params["count"]);
                            linkHistory = ff.cms.updateUriParams("count", params["count"], linkHistory);
                        }
                        if(params["sort"]) {
                            linkParams.push("sort=" + params["sort"]);
                            linkHistory = ff.cms.updateUriParams("sort", params["sort"], linkHistory);
                        }
                        if(params["dir"]) {
                            linkParams.push("dir=" + params["dir"]);
                            linkHistory = ff.cms.updateUriParams("dir", params["dir"], linkHistory);
                        }
                        if(params["ffl"]) {
                            linkParams.push("ffl=" + params["ffl"]);
                            linkHistory = ff.cms.updateUriParams("ffl", params["ffl"], linkHistory);
                        }

                        history.replaceState(null, null, linkHistory);

                        loadAjax(link + (linkParams.length ? "?" + linkParams.join("&") : ""), elem, effect, mode, callback, blockUI, jumpUI);
                        return false;
                    }
                }
            }
	};
	if(!debug)
            history.replaceState(null, null, window.location.pathname.replace('//', '/') + window.location.search.replace("&__nocache__", "").replace("?__nocache__&", "?").replace("?__nocache__", "").replace("&__debug__", "").replace("?__debug__&", "?").replace("?__debug__", "") + window.location.hash);

	jQuery(function() {
        ff.cms.initCMS();
	});
	
	return that;
})();