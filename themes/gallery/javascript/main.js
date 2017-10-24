ff.cms = (function () {
	var debug = (jQuery("#adminPanel").length > 0) || (window.location.search.indexOf("__debug__") >= 0);
	var countBlock = 0;
	var cacheBlock = {};
    var service = null;

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
			jQuery("head").append(jQuery(data).nextAll("LINK"));
			jQuery("head").append(jQuery(data).nextAll("SCRIPT"));

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

				jQuery.ajax({
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

		jQuery('INPUT.ajaxcontent[type=hidden]').each(function() {
		    var link = jQuery(this).val();
		    var elem = jQuery(this);
		    var eventName = jQuery(this).attr("data-ename") || undefined;

		    var id = jQuery(this).attr("id");

		    loadAjax(link, elem, false, "after", eventName);
		});

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
            		lazyBlock.push(id);
            		break;
            	case "load":
            	case "reload":
            		loadAjax(link, jQuery(this), arrAjaxOnReady[1], "replace", eventName);
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
					/*
                    var targetID = "";

                    if(jQuery(this).attr("rel")) {
                        targetID = "#" + jQuery(this).attr("rel");
                    } else if(jQuery(this).attr("href").indexOf("#") == 0) {
                        targetID = jQuery(this).attr("href");
                    }

                    var callback = jQuery(this).attr("data-callback");
                    var effect = jQuery(this).attr("data-effect");

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
                    if(!jQuery(this).hasClass("selected")) {
                        jQuery(this).closest(".block").find("a").each(function() {
                            var relContent = "";
                            if(jQuery(this).attr("rel")) {
                                relContent = "#" + jQuery(this).attr("rel");
                            } else if(jQuery(this).attr("href").indexOf("#") == 0) {
                                relContent = jQuery(this).attr("href");
                            }
                            if(relContent && jQuery(relContent).length && jQuery(relContent).hasClass("block")) {
                                jQuery(relContent).hide();
                            }
                            jQuery(this).removeClass("selected");

                        });
                        //jQuery(this).closest("div").find("a").removeClass("selected");
                        jQuery(this).addClass("selected");
                    }
                    if(targetID) {
                        if(jQuery(targetID).length) {
                            if(arrAjaxOnEvent[0] == "reload") {
                                loadAjax(link, jQuery(targetID), arrAjaxOnEvent[1], "replace", eventName);
                            } else if(arrAjaxOnEvent[0] == "load") {
                                if(jQuery(targetID).children().length > 0) {
                                    try {
                                        eval('jQuery(targetID).' + arrAjaxOnEvent[1] + '();');
                                    } catch(err) {
                                        console.err(arrAjaxOnEvent[1] + " is not a Valid Method(Effect)");
                                    }
                                } else {
                                    loadAjax(link, jQuery(targetID), arrAjaxOnEvent[1], "replace", eventName);
                                }
                            } else {
                                try{
                                    eval('jQuery(targetID).' + arrAjaxOnEvent[1] + '();');
                                } catch(err) {
                                    console.err(arrAjaxOnEvent[1] + " is not a Valid Method(Effect)");
                                }
                            }
                        }
                    }*/
                });
            }
		});

		if(lazyBlock.length) {
		    jQuery(window).bind("scroll.lazyBlock", processLazyBlock);
            processLazyBlock();
		    //setTimeout("jQuery(window).scroll()", 400); //da trovare una soluzione migliore
		}
	};
	var loadReq = function(target) {
		var deferReq = {};
		if(service) {
			jQuery.ajax({
				async: true,
				type: "POST",
				url: "/srv/request",
				data: "params=" + (target ? JSON.stringify(target) : JSON.stringify(service)),
				dataType : "json",
				cache: true
			}).done(function(response) {
				if(response) {
					for(var name in response) {
						var result = null;
						if(!service[name]) service[name] = {};

                        if(service[name]["tpl"] && service[name]["tpl"]["target"]) {
                            var target = service[name]["tpl"]["target"];
                            var tplVars = response[name][service[name]["tpl"]["vars"]] || response[name]["vars"];
                            var html = response[name]["html"];
                            if(!html && tplVars && jQuery(service[name]["tpl"]["target"]).length)
                                html = jQuery(service[name]["tpl"]["target"]).html();

                            if(tplVars && html) {
								if(typeof tplVars == "object") {
                                    for (var property in tplVars) {
                                        if (tplVars.hasOwnProperty(property)) {
                                            html = html.replaceAll("[" + property + "]", tplVars[property]);
                                        }
                                    }
                                    response[name]["output"] = html;
                                }
                            }
							if(target) {
                                var header = response[name]["header"] || "";
                                var footer = response[name]["footer"] || "";
                                var output = header + html + footer;
                                if(output) {
                                    if (jQuery(target, html).length > 0) {
                                        jQuery(target).replaceWith(output);
                                    } else {
                                        jQuery(target).html(output);
                                    }
                                }
							}
                        }

						if(service[name]["callback"])
							service[name]["callback"](response[name], service[name]["params"]);

						if(response[name]["result"] !== undefined)
							result = response[name]["result"];
						else
							result = response[name];

						if(result) { //da gestire i multi valori provenienti dalle chiamate asincrone con il timer
							service[name]["response"] = result;
						}

						if(response[name]["timer"]) {
							if(!service[name]["opt"]) service[name]["opt"] = {};
							service[name]["opt"]["timer"] = response[name]["timer"];
						}
						if(service[name]["opt"] && service[name]["opt"]["timer"]) {
							service[name]["opt"]["timer"] = false;
							if(!deferReq[response[name]["timer"]]) deferReq[response[name]["timer"]] = {};

							deferReq[response[name]["timer"]][name] = service[name];
						}
					}

					for(var time in deferReq) {
						setTimeout(function() {
							ff.cms.loadReq(deferReq[time]);
						}, time);
					}

				}
			}).fail(function(error) {
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
                    ff.cms.widgetInit("");
					//loadReq();
					setTimeout(loadReq, 200);
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
            "loadReq" : function(target) {
            	loadReq(target);
            },
			"get" : function(name, callback, params, opt) {
				var res = null;

				if(!name) {
					res = service;
				} else {
					if(!service) service = {};

					if(!service[name]) service[name] = {};

					if(callback) {
                        if(jQuery.isFunction(callback)) {
                            service[name]["callback"] = callback;
                        } else {
                            var tpl = {};

                            if(typeof(callback) == "object") {
                                var tpl = {};

                                tpl["target"] = callback["target"];
                                tpl["vars"] = callback["vars"];
                                if (callback["callback"])
                                    service[name]["callback"] = callback;
                            } else {
                                tpl["target"] = callback;
                            }
                        }
					} else {
						if(!opt) opt = {};
                            opt["async"] = true;
					}

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
				}
				return res;
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