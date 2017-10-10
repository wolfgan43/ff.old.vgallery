ff.cms.editor = {
	__init : false,
	menu : {
		"edit" : []
	},
    widgets : {
        "spinner" : { params : { "min" : 1 /*, culture : "px" */}, 
                        event : "spinstop"
                    },
        "button": { params : {},
                        event: ""
                    },
       "buttonset": { params : {},
                        event: ""
                    }/*,

        "ColorPicker" : { 
        				lib: {
        					path : "jquery.colorpicker",
        					css: [{"key": "colorpicker", "path": "css/colorpicker.css"}],
        					js: [{"key": "jQuery.fn.ColorPicker", "path": "jquery.colorpicker.js"}]
						},
        				params : {flat : "parent"}
                    },
        "niceScroll" : { 
        				lib: {
        					path : "jquery.nicescroll",
        					js: [{"key": "jQuery.fn.niceScroll", "path": "jquery.nicescroll.js"}]
						},
        				params : {flat : "parent"}
                    }
*/
    },
    target : "",
    timerChange : undefined,
    prefix : "cms",
    accordion: true,
/*
data : [
			{	
				"name" : "Section1",
				"class" : "section1",
				"rows" : [
				{
					"class" : "",
					"fields" : [
					{
						"name" : "Field1",
						"class" : "field1",
						"type" : "checkbox",
						"label" : "field 1 label",
						"widget": "",
						"value" : "", 
						"special": "",
						"pre" : "",
						"post" : ""
					},
					{
						"name" : "Field2",
						"class" : "field2",
						"type" : "input",
						"placeholder" : "test 124",
						"widget": "spinner", 
						"value" : "",
						"special" : "px",
						"pre" : "",
						"post" : ""
					},
					{
						"name" : "Field3",
						"class" : "field3",
						"type" : "selection",
						"label" : "we23rf2",
						"placeholder" : "teasdasd",
						"widget": "", 
						"options" : [{"label": "ciao", "value" : "asdasd"}, "pippo"], 
						"value" : "",
						"special" : "px",
						"pre" : "",
						"post" : ""
					}]
				}]
			}],

*/
	$editor                     : undefined,
	$editorMenu                	: undefined,
	init:function(id) { 
		if(!this.__init) {
	        var that = this;

		    this.$editor=$("#" + that.prefix + "-editor-container").css("z-index", $(".toolbaradmin").css("z-index") - 1);
		    this.$editorMenu=$("." + that.prefix + "-editor-menu");
		    
		    this.$editorMenu.each(function(i) {
		    	var rel = $(this).attr("rel");
		    	that.menu[rel] = [];
		    });
		    
		    for(var i in that.menu) {
				$(that.$editor).append('<div class="vg-panel ' + i + '"></div>');		    
		    }
		    //that.menu["edit"] = [];

			for(var widget in that.widgets) {
				if(that.widgets[widget]["lib"]) {
					var widgetPath = that.widgets[widget]["lib"]["path"];
					if(widgetPath.substring(0,1) != "/") {
						widgetPath = "/themes/library/plugins/" + widgetPath;
					}

					if(that.widgets[widget]["lib"]["css"]) {
						that.widgets[widget]["lib"]["css"].each(function(i, value) {
							ff.injectCSS(value["key"], widgetPath + "/" + value["path"]);
						});
					}
					if(that.widgets[widget]["lib"]["js"]) {
						that.widgets[widget]["lib"]["js"].each(function(i, value) {
							ff.pluginLoad(value["key"], widgetPath + "/" + value["path"]);
						});
					}
				}
			}

			$(this.$editorMenu).click(function() {
			    ff.cms.editor.display(undefined, $(this).attr("rel"));
			});
			$(document).on("change", "#" + $(this.$editor).attr("id") + " .editable", function(e) {
				if(that.timerChange) {
					clearTimeout(that.timerChange);
					that.timerChange = undefined;
				}
				var dataKey = $(e.target).attr("data-id");
				var dataValue = "";

				if($(e.target).is("input[type=input]")) {
					dataValue = $(e.target).val();
					
					that.timerChange = setTimeout("ff.cms.editor.change('" + dataKey + "', '" + dataValue + "');", 500);
				} else {
					if($(e.target).is("input[type=checkbox]") || $(e.target).is("input[type=radio]")) {
						if($(e.target).is(":checked")) {
							dataValue = true;
						} else {
							dataValue = false;
						}
					} else {
						dataValue = $(e.target).val();
					}
					ff.cms.editor.change(dataKey, dataValue);
				}
			});
			
   			$("body").bind("click", function(e) {
   				if(!that.$editor.find(e.target).length
   					&& !$(".toolbaradmin").find(e.target).length
   					&& !$(".toolbaradmin").is(e.target)
   					&& that.target
   					&& $("." + that.target, that.$editor).is(":visible")
   				) {
   				
   					that.display(false);
   				}
			});  				
			
			this.__init = true;
		}
	},
	"change" : function(dataKey, dataValue) {
		var that = this;
		try {
			if(dataKey) {
				arrDataKey = dataKey.split("-");
				var fieldObj = that.menu[that.target]["data"][arrDataKey[0]]["rows"][arrDataKey[1]]["fields"][arrDataKey[2]];
				
				if(fieldObj["value"] !== undefined)
					fieldObj["value"] = dataValue;

				if(fieldObj["obj"]) {
					fieldObj["obj"][fieldObj["key"]] = dataValue;
				}
				
				if(fieldObj["callback"]) {
					eval(fieldObj["callback"] + "(" + fieldObj["params"] + ");");
					that.draw();
				}
				
			}
		} catch(e) {
			
			console.log(e);
		}
	},
	"display" : function (show, target, editor, callback) {
		var that = this;
		
		if(target !== undefined) {
			that.target = target;
		}
		if(show === undefined) {
			show = !$(that.$editor).find("." + that.target).hasClass("opened");
		}

		that.draw(editor);

		that.$editorMenu.removeClass("selected");

		if(show) {
			if(!$(that.$editor).find("." + that.target).hasClass("opened")) {
				that.$editor.find("." + that.target).addClass("opened");
				that.$editor.show("slide", { direction: "right"}, function() {
					$(that.$editorMenu, "a[rel=" + that.target + "]").addClass("selected");
					$(that.$editor).find("." + that.target).fadeIn(function() {
						if(callback)
							callback($(that.$editor).find("." + that.target), true);
					});	
				});
			} else {
				if(callback)
					callback($(that.$editor).find("." + that.target), null);
			}			
		} else {
			that.$editor.find("." + that.target).hide().removeClass("opened");
			that.$editor.hide("slide", { direction: "right"}, function() {
				if(callback)
					callback($(that.$editor).find("." + that.target), false);
			});
		}
	},
	"loadData" : function (data, target) {

	},
	"initScroll" : function(target) {
		var that = this;
		
		/*$(that.$editor).find("." + target + " .sheet").niceScroll();*/
	},
	"initAccordion" : function(target) {
		var that = this;
		
		$(that.$editor).find("." + target + " .sheet > .section:not(.fixed)").hide();
		$(that.$editor).find("." + target + " .sheet > .section.active:not(.fixed)").show();

		if(that.accordion) {
			$(that.$editor).find("." + target + " .sheet > h3").addClass("ui-accordion-header ui-helper-reset ui-state-default ui-accordion-icons");
			$(that.$editor).find("." + target + " .sheet > .section:not(.fixed)").addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom");
			$(that.$editor).find("." + target + " .sheet > .section.active:not(.fixed)").addClass("ui-accordion-content-active").removeClass("active");
			
			$(that.$editor).find("." + target + " .sheet > .section.ui-accordion-content-active").prev().addClass("ui-accordion-header-active ui-state-active ui-corner-top").prepend('<i class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s"></i>');
			$(that.$editor).find("." + target + " .sheet > .section:not(.ui-accordion-content-active, .fixed)").prev().addClass("ui-corner-all").prepend('<i class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-e"></i>');
			
			$(that.$editor).find("." + target + " .sheet > h3").on("click", function(e) {
		        $(this).next().slideToggle(function(e) {
		            if ($(this).is(":visible")) {
		                $(this).addClass("ui-accordion-content-active")
		                .prev().toggleClass("ui-corner-all ui-corner-top").addClass("ui-accordion-header-active ui-state-active")
		                .children(".ui-accordion-header-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s");
		            }
		            else {
		                $(this).removeClass("ui-accordion-content-active")
		                .prev().toggleClass("ui-corner-all ui-corner-top").removeClass("ui-accordion-header-active ui-state-active")
		                .children(".ui-accordion-header-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s");
		            }
		        });
		    })
		    .hover(function(e) { $(this).toggleClass("ui-state-hover"); });			
		} else {
			$(that.$editor).find("." + target + " > h3").on("click", function(e) {
		        $(this).next().slideToggle(function(e) {
		            
		        });
		    });
		}
	},
	"draw" : function (sourceData, target) {
		var that = this;

		if(target === undefined)
			target = this.target;

		if(sourceData === undefined) {
			var sourceData = that.menu[target];

			$(that.$editor).find("." + target).html("");

			if(sourceData["tpl"]) {
				$(that.$editor).find("." + target).append(sourceData["tpl"]);
			}
			
			if(sourceData["data"]) {
				data.each(function(x) {
					if(data[x]["rows"]) {
						data[x]["rows"].each(function(y) {
							if(data[x]["rows"][y]["fields"]) {
								data[x]["rows"][y]["fields"].each(function(z, field) {
									var realValue = field["value"] || field["obj"][field["key"]];
									$("#" + that.prefix + '-' + x + "-" + y  + "-" + z).val(realValue)
								});
							}
						});
					}
				});
			}
		} else {
			that.menu[target] = sourceData;
			var tplGroup = "";
			var tplActions = "";

			$(that.$editor).find("." + target).html("");

			if(sourceData["tpl"]) {
				$(that.$editor).find("." + target).append(sourceData["tpl"]);
			}
			if(sourceData["data"]) {
		        sourceData["data"].each(function(i, value) {
        			var clas = "";
					var headerGroup = "";
					if(sourceData["data"][i]["class"]) {
						clas = " " + sourceData["data"][i]["class"];
        			}        	
					if(sourceData["data"][i]["accordion"] === false) {
						clas = " " + "fixed";
						if(sourceData["data"][i]["name"])
							headerGroup = '<h2 class="title">' + sourceData["data"][i]["name"] + '</h2>';
					} else {
						if(sourceData["data"][i]["accordion"] == "active") {
							clas = " " + "active";
        				}  
						headerGroup = '<h3 class="title">' + (sourceData["data"][i]["name"] ? sourceData["data"][i]["name"] : "") + '</h3>';
					}
		            tplGroup = tplGroup +  headerGroup +
		                                '<div class="section' + clas + '">' +
		                                    that.drawRow(sourceData["data"][i]["rows"], i) +
                            			'</div>';
		        });
		        if(tplGroup)
					tplGroup = '<div class="sheet">' + tplGroup + '</div>';
			}
			if(sourceData["actions"]) {
				var tplActions = "";
				
				for(var i in sourceData["actions"]) {
			        tplActions = tplActions + '<input type="button" class="btn" value="' + i + '" onclick="javascript:ff.cms.editor.processAction(' + "'" + i + "'" + ');" />';
				}
				if(tplActions)
					tplActions = '<div class="actions">' + tplActions + '</div>';
			}
			
			if(tplGroup || tplActions) {
				$(that.$editor).find("." + target).append(tplGroup + tplActions);
				
				that.initScroll(target);
				that.initAccordion(target);
				
				for(var widget in that.widgets) {
					try {
						$(that.$editor).find("." + widget).each(function() {
							var dataKey = $(this).attr("data-id");
							if(dataKey) {
								var arrDataKey = dataKey.split("-");
								var fieldObj = that.menu[that.target]["data"][arrDataKey[0]]["rows"][arrDataKey[1]]["fields"][arrDataKey[2]];
								var params = {};
								if(typeof fieldObj["widget"] == "object" && typeof fieldObj["widget"]["params"] == "object") {
									params = fieldObj["widget"]["params"];
								} else {
									params = that.widgets[widget]["params"];
								}
								eval("$('#" + $(this).attr("id") + "')." + widget + "(" + JSON.stringify(params) + ");");
								if(that.widgets[widget]["event"]) {
									eval("$('#" + $(this).attr("id") + "').on('" + that.widgets[widget]["event"] + "', function( event, ui ) { $(event.currentTarget).change(); })");
								}
							}
						});
					} catch(e) {
					    console.log(e);
					}
				}
			}
		}	 
	},
	"drawRow" : function(data, parent) {
		var that = this;
		var tplRow = "";
		
		if(data) {
	        data.each(function(i, value) {
        		var clas = "";
        		var widget = "";

				if(data[i]["class"]) {
					clas = " " + data[i]["class"];
        		} 

        		if(data[i]["widget"]) {
        			if(typeof data[i]["widget"] == "object") {
        				if(data[i]["widget"]["name"]) {
							widget = ' ' + data[i]["widget"]["name"];	
						}
        			} else {
						widget = ' ' + data[i]["widget"];
        			}
        		}

	            tplRow = tplRow + '<div class="row' + clas + widget + '">' +
            						that.drawField(data[i]["fields"], parent + "-" + i) +
	                            '</div>';
			});	
		}
		return tplRow;
	},
	"drawField" : function(data, parent) {
		var that = this;
		var tplField = "";
		
		if(data) {
	        data.each(function(i, value) {
        		var label = "";
        		var icon = "";
        		var placeholder = "";
        		var control = "";
        		var value = "";
        		var widget = "";
        		var pre = "";
        		var post = "";
        		var clas = "";
        		var checkValue = "";
        		var realValue = data[i]["value"] || data[i]["obj"][data[i]["key"]];

        		if(data[i]["icon"]) {
					icon = '<i class="icon ' + data[i]["icon"] + '"></i>';
        		}
        		if(data[i]["label"]) {
					label = '<label for="'+ that.prefix + '-' + parent + "-" + i  + '">' + data[i]["label"] + '</label>';
        		}
        		if(data[i]["placeholder"]) {
					placeholder = ' placeholder="' + data[i]["placeholder"] + '"';
        		}
        		if(realValue) {
					value = ' value="' + realValue + '"';
        		}

        		if(data[i]["widget"]) {
        			if(typeof data[i]["widget"] == "object") {
        				if(data[i]["widget"]["name"]) {
							widget = ' ' + data[i]["widget"]["name"];	
						}
        			} else {
						widget = ' ' + data[i]["widget"];
        			}
        		}

        		if(data[i]["pre"]) {
					pre = '<span class="pre">' + data[i]["pre"] + '</span>';
        		}
        		if(data[i]["post"]) {
					post = '<span class="post">' + data[i]["post"] + '</span>';
        		}

        		switch(data[i]["type"]) {
					case "input":
						control = label + pre + '<input id="'+ that.prefix + '-' + parent + "-" + i  + '" data-id="' + parent + "-" + i + '" class="editable' + widget + '" type="input" name="' + data[i]["name"] + '"' + placeholder + value + '/>' + post;
						break;
					case "checkbox":
						if(data[i]["options"] !== undefined) {
							if(data[i]["options"]["checked"] == realValue) {
								checkValue = ' checked="checked"';
								if(data[i]["options"]["unchecked"] !== undefined)
									value = ' value="' + data[i]["options"]["unchecked"] + '"';
							} else if(data[i]["options"]["checked"] !== undefined)
								value = ' value="' + data[i]["options"]["checked"] + '"';
						}
						control = pre + '<input id="'+ that.prefix + '-' + parent + "-" + i  + '" data-id="' + parent + "-" + i + '" class="editable' + widget + '" type="checkbox" name="' + data[i]["name"] + '"' + placeholder + value + checkValue + '/>' + post + label;
						break;
					case "option":
						if(data[i]["options"]) {
							value = ' value="' + data[i]["options"] + '"';
							if(data[i]["options"] == realValue)
								checkValue = ' checked="checked"';
						}
						control = pre + '<input id="'+ that.prefix + '-' + parent + "-" + i  + '" data-id="' + parent + "-" + i + '" class="editable' + widget + '" type="radio" name="' + data[i]["name"] + '"' + placeholder + value + checkValue + '/>' + post + label;
						break;
					case "selection":
						control = label + pre + '<select id="'+ that.prefix + '-' + parent + "-" + i  + '" data-id="' + parent + "-" + i + '" class="editable' + widget + '" name="' + data[i]["name"] + '"' + placeholder + '>';
						if(data[i]["options"] && $.isArray(data[i]["options"])) {
							control = control + that.drawFieldOption(data[i]["options"], realValue);
						}
						control = control + "</select>" + post;
						break;
					default:
						control = label + pre + '<span id="'+ that.prefix + '-' + parent + "-" + i  + '" ' + widget + placeholder  + '>' + value + '</span>' + post;
        		}
        		
        		if(data[i]["special"])
					control = control + that.drawFieldSpecial(data[i]["special"]);

        		if(data[i]["class"]) {
					clas = ' class="' + data[i]["class"] + '"';
        		}

	            tplField = tplField + '<span' + clas + '>' +
            						icon + control + 
	                            '</span>';
			});
		}		
		return tplField;	
	},
	"drawFieldOption" : function(data, realValue) {
		var option = "";
		var checkValue = "";
		var optionValue = "";
		var optionLabel = "";
		
		if(data) {
			data.each(function(i, value) {
				if(typeof data[i] == "object") {
					optionValue = data[i]["value"];
					optionLabel = data[i]["label"];
				} else {
					optionValue = data[i];
					optionLabel = data[i];
				}

				if(realValue && optionValue == realValue) {
					checkValue = ' selected="selected"';
				} else {
					checkValue = "";
				}

				option = option + '<option' + checkValue+ ' value="' + optionValue + '">' + optionLabel + '</option>';	
			});
		}
		return option;
	},
	"drawFieldSpecial" : function(data) {
		var special = "";
		
		switch(data) {
			case "px":
				special = '<select name="px">';
				special = special + this.drawFieldOption(["px", "%"]);
				special = special + "</select>";
				break;
		}
		
		return special;
	},
	"processAction" : function(action) {
		var that = this;
		if(action) {
			var callback = that.menu[that.target]["actions"][action];
			var funcName = undefined;
			var funcParams = undefined;
			if(callback) {
				try {
					if(typeof callback == "object") {
						funcName = callback["name"];
						if(callback["returnValue"]) 
							funcParams = JSON.stringify(that.menu[that.target]["data"]);
					} else {
						funcName = callback;
					}
					
					eval(funcName + "(" + funcParams + ");");
				} catch(e) {
					console.log(e);
				}
			}
			
			ff.cms.editor.display(false, that.target);	
		}
	}	
};