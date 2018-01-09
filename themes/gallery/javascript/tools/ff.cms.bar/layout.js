if (!ff.cms) ff.cms = {};

ff.cms.layout = (function () {

	var that = { /* publics*/
		__init : false
		, canvas : {
			"prefix" : "wg"
			, "container" 	: undefined
			, "resolution" 	: undefined
			, "addrow"		: undefined
		}

		, data : {
			"framework" : "none"
			, "container" : {
				"width" : 1024
				, "height" : 768
				, "orientation" : "landscape"
			}
			, "rows" : {
				"top" : []
				, "central" : []
				, "bottom" : []
			}
		}
	    , "default": function() {
	    
		} 
		, "init": function() {
			var that = this;
			$(function() {
				that.canvas.container = $("#" + that.canvas.prefix + "-canvas");
				that.canvas.resolution = $("#" + that.canvas.prefix + "-resolution");
				that.canvas.addrow = $("#" + that.canvas.prefix + "-addrow");

				$("#" + that.canvas.prefix + "-canvas").sortable({
      				placeholder: "ui-state-highlight"
      				, create: function( event, ui ) {
						that.setResolution(true);
						
      				}
				});
   				$("body").bind("click", function(e) { 
   					if(!jQuery(e.target).closest("#" + that.canvas.prefix + "-canvas").length
   						&& !jQuery(e.target).closest("#" + ff.cms.editor.prefix + "-editor-container").length
   						&& !jQuery(e.target).closest("." + ff.cms.editor.prefix + "-editor").length
   					)
	   					ff.cms.layout.highlightRow();
				});  				
				$(document).on("click", "#" + that.canvas.prefix + "-canvas .canvas-row",function(e) {
					ff.cms.layout.highlightRow(e.target);
				});
			});
		
		},
		"highlightRow" : function(target) {
			var that = this;
			if(target === undefined) {
				$("#" + that.canvas.prefix + "-canvas .canvas-row").removeClass("highlight");
				ff.cms.editor.display(false, "edit");
			} else {
				if($(target).closest(".canvas-row").hasClass("highlight")) {
					$("#" + that.canvas.prefix + "-canvas .canvas-row").removeClass("highlight");
					ff.cms.editor.display(false, "edit");
				} else {
					$("#" + that.canvas.prefix + "-canvas .canvas-row").removeClass("highlight");
					$(target).closest(".canvas-row").addClass("highlight");
					ff.cms.editor.display(true, "edit", that.setEditor($(target).closest(".canvas-row").attr("id")));
				}
			}
		}
		, "displayControls" : function(params) {
			if(params["addrow"] !== undefined) {
				if(params["addrow"])
					$(this.canvas.addrow).fadeIn();
				else 
					$(this.canvas.addrow).hide();
			}
		}
	    , "setResolution": function(initRow) {
	    	var that = this;
	    	$(this.canvas.container).children().hide();
	    	$(this.canvas.container).animate({
				"width" : Math.round(this.data.container.width / 2)
				, "height" : Math.round(this.data.container.height / 2)
	    	}, 500, function() {
				$(that.canvas.container).children().show();
				if(initRow) {
					that.addRow();
				} else {
					that.refresh();
				}
	    	});
	        

	        $(this.canvas.resolution).find(".canvas-resolution-size").text(this.data.container.width + "x" + this.data.container.height);
	        if(parseInt(this.data.container.width) > parseInt(this.data.container.height)) {
	            this.data.container.orientation = "landscape";
	        } else {
	            this.data.container.orientation = "portrait";
	        }

	        $(this.canvas.resolution).attr("class", "flip " + this.data.container.orientation);
	        $(this.canvas.resolution).find(".canvas-orientation").text(this.data.container.orientation);

	    }
	    , "flipResolution": function() {
	        var oldResolutionWidth = this.data.container.width;
	        var oldResolutionHeight = this.data.container.height;
	        
	        this.data.container.width = oldResolutionHeight;
	        this.data.container.height = oldResolutionWidth;

	        if(this.data.container.orientation == "landscape") {
	            this.data.container.orientation = "portrait";
	        } else {
	            this.data.container.orientation = "landscape";
	        }
	        this.setResolution();
	    }    
	    , "changeResolution": function() {
	        var that = this;
	        
	        $(this.canvas.resolution).find(".canvas-resolution-flip").hide();
	        $(this.canvas.resolution).find("a.canvas-resolution-size").replaceWith('<span class="canvas-resolution-size"><input class="width" type="text" value="' + this.data.container.width + '" /><input class="height" type="text" value="' + this.data.container.height + '" /><input class="btn" type="button" value="save" /></span>');
	        $(this.canvas.resolution).find("input.btn").click(function() {
	            that.data.container.width = $(that.canvas.resolution).find(".width").val();
	            that.data.container.height = $(that.canvas.resolution).find(".height").val();

	            $(that.canvas.resolution).find("span.canvas-resolution-size").replaceWith('<a class="canvas-resolution-size" href="javascript:ff.cms.layout.changeResolution();">' + that.data.container.width + "x" + that.data.container.height + '</a>');
	            $(that.canvas.resolution).find(".canvas-resolution-flip").fadeIn();
	            that.setResolution();
	        });
	    }
	    , "addRow": function(row, source) {
	    	var countRow = $(this.canvas.container).children().length;
			var countRowLabel = (countRow ? countRow + 1 : "");
			var disableDelRow = false;
	    	if(!row) {
				row = {
					"id" : this.canvas.prefix + "-row" + countRowLabel
					, "name" : "Row" + countRowLabel
					, "class" : ""
					, "isFluid" : false
					, "xHeight" : 10
					, "cols": {
						"left" : []
						, "content" : []
						, "right" : []
					}
				}	
				    		
	    		if(!this.data.rows.central.length) {
	    			position = "central";
	    			
	    			row["xHeight"] = 100;
				} else if(this.data.rows.top.length <= this.data.rows.bottom.length) {
	    			position = "top";
					
					row["xHeight"] = 5 * (that.data.rows[position].length + 1);
	    		} else {
					position = "bottom";
	    			
					row["xHeight"] = 5 * (that.data.rows[position].length + 1);
	    		}

				row["class"] = position;
				row["position"] = position;
			}	    	

			if(source) {
				if(source["position"] == "next") {
					$("#" + this.data.rows[row["position"]][source["id"]]["id"]).after('<div id="' + row["id"] + '" class="canvas-row ' + row["class"] + '">' + '</div>');
				} else {
					$("#" + this.data.rows[row["position"]][source["id"]]["id"]).before('<div id="' + row["id"] + '" class="canvas-row ' + row["class"] + '">' + '</div>');
				}
			} else {
				switch(row["position"]) {
					case "top":
						$(this.canvas.container).find(".canvas-row.central").before('<div id="' + row["id"] + '" class="canvas-row ' + row["class"] + '">' + '</div>');
						break;
					case "bottom":
						$(this.canvas.container).find(".canvas-row.central").after('<div id="' + row["id"] + '" class="canvas-row ' + row["class"] + '">' + '</div>');
						break;
					default:
	    				$(this.canvas.container).html('<div id="' + row["id"] + '" class="canvas-row ' + row["class"] + '">' + '</div>');
	    				disableDelRow = true;
				}
			}
			this.setRow(row, (source 
									? (source["position"] == "next"
										? source["id"] + 1
										: source["id"]
									)
									: undefined
								)
						);
			this.addCol(row);

			if(!disableDelRow) 
				$('#' + row["id"]).append('<a class="canvas-delrow" href="javascript:ff.cms.layout.delRow(\'' + row["id"] + '\');"><i class="fui-gear"></i>Del Row</a>');

				$('#' + row["id"]).prepend('<a class="canvas-fluidrow" href="javascript:ff.cms.layout.fluidRow(\'' + row["id"] + '\');"><i class="fui-gear"></i>Fluid Row</a>');

	    	this.refresh();
		}
		, "addCol" : function(row, col, source) {
			var that = this;

			if(!col) {
				var sectionName = (row["position"] == "central" ? "Content" : row["position"].capitalize());
				var countRow = this.data.rows[row["position"]].length;
				var countRowLabel = (countRow - 1 ? countRow : "");

	    		col = {
						"id" : this.canvas.prefix + "-" + sectionName + countRowLabel
						, "name" : sectionName + countRowLabel
						, "class" : "content"
						, "position" : "content"
						, "width" : ""
						, "grid" : 12
					};
			} else {
				var sectionName = col["position"].capitalize();
				var countRow = row["cols"][col["position"]].length;
				var countRowLabel = (countRow ? countRow + 1: "");

				col["id"] = this.canvas.prefix + "-" + (row["position"] == "central" ? "Content" : row["position"].capitalize()) + sectionName + countRowLabel;
				col["name"] = sectionName + countRowLabel;
			}

			var typeColClass = (row["isFluid"] ? "mono" : "multi");
			if(source) {
				if(source["position"] == "next") {
					$("#" + row["cols"][col["position"]][source["id"]]["id"]).after('<div id="' + col["id"] + '" class="canvas-section ' + col["class"] + ' ' + typeColClass + '">' + col["name"] + '</div>');
				} else {
					$("#" + row["cols"][col["position"]][source["id"]]["id"]).before('<div id="' + col["id"] + '" class="canvas-section ' + col["class"] + ' ' + typeColClass + '">' + col["name"] + '</div>');
				}
			} else {
				switch(col["position"]) {
					case "left":
						$("#" + row["id"]).find(".canvas-section.content").before('<div id="' + col["id"] + '" class="canvas-section ' + col["class"] + ' ' + typeColClass + '">' + col["name"] + '</div>');
						break;
					case "right":
						$("#" + row["id"]).find(".canvas-section.content").after('<div id="' + col["id"] + '" class="canvas-section ' + col["class"] + ' ' + typeColClass + '">' + col["name"] + '</div>');
						break;
					default:
						$("#" + row["id"]).html('<div id="' + col["id"] + '" class="canvas-section ' + col["class"] + ' ' + typeColClass + '">' + col["name"] + '</div>');
				}
			}
			this.setCol(row, col, (source 
									? (source["position"] == "next"
										? source["id"] + 1
										: source["id"]
									)
									: undefined
								)
						);

						
			var gridMax = 12;
			var gridStep = Math.floor($("#" + row["id"]).width() / gridMax);
			$("#" + col["id"]).click(function() {
				
			}).resizable({
				  grid: gridStep
				  , containment: "#" + row["id"]
				  , handles : "e, w"
				  , resize : function(event, ui) {
				  	  var resCol = undefined;
				  	  var position = "";
				  	  var border = $(ui.element).attr("data-border");
				  	  var source = undefined;
				  	  var prevGrid = Math.floor(ui.originalSize.width / gridStep);
				  	  var actualGrid = Math.floor($(ui.element).width() / gridStep);


					  if(actualGrid > 0 && prevGrid > 0) {
					  	  if(!border) {
				  			  if(ui.originalSize.width > $(ui.element).width()) {
				  	  			  border = "right";
				  			  } else {
								  border = "left";
				  			  }
				  			  $(ui.element).attr("data-border", border)
						  }
						  if(prevGrid <= actualGrid) {
							  var rowWidth = $("#" + row["id"]).width();
							  var elem = undefined;
							  
							  if(border == "right") {
						  		  elem =  $(ui.element).next().attr("id");
							  } else {
						  		  elem =  $(ui.element).prev().attr("id");
							  }
							  if(elem) {
							  	  resCol = that.getProperty("col", elem);
								  if(border == "left") {
								  	  var diff = $("#" + resCol["obj"]["id"]).attr("data-diff");
								  	  var gridDiff = parseInt($("#" + resCol["obj"]["id"]).attr("data-grid-diff")) || 0;
				  	  if(actualGrid > gridMax) {
				  	  	  $(ui.element).width($(ui.element).width() - diff);
					  }
								  	  if(!diff) {
								  	  	  diff = ($(ui.element).width() - ui.originalSize.width) / (actualGrid - prevGrid);
								  	  	  $("#" + resCol["obj"]["id"]).attr("data-diff", diff);
									  }
									  console.log(prevGrid + "  " + actualGrid);
									  if(gridDiff != actualGrid) {
										  $("#" + resCol["obj"]["id"]).attr("data-grid-diff", actualGrid);
										  
								  		  $("#" + resCol["obj"]["id"]).width($("#" + resCol["obj"]["id"]).width() + (diff * (gridDiff > actualGrid ? 1 : -1)));
									  }
						  		  	/*$("#" + resCol["obj"]["id"]).width(Math.floor(rowWidth * (resCol["obj"]["grid"] + (prevGrid - actualGrid)) / 12) + "px");*/
								  } else if(border == "right") {
									  $("#" + resCol["obj"]["id"]).css("left", Math.floor(rowWidth * (resCol["obj"]["grid"] + (prevGrid - actualGrid)) / 12) + "px");
								  }
						  		  
							  }
						  }
					  }
/*
					  if(prevGrid != actualGrid && actualGrid > 0 && prevGrid > 0) {
					  	  console.log("ASD");
						  if(ui.originalPosition.left == ui.position.left) {
				  			  position = "right";
				  			  if($(ui.element).next().attr("id")) {
								 that.setProperty("col", $(ui.element).next().attr("id"), "grid", prevGrid - actualGrid);
				  			  }
						  } else {
				  			  if($(ui.element).prev().attr("id")) {
								 that.setProperty("col", $(ui.element).prev().attr("id"), "grid", prevGrid - actualGrid);
				  			  }

							  position = "left";
						  }
						  //that.refresh();
					  }*/
				  	  
				  }
				  , start : function(event, ui) {
				  }
				  , stop : function(event, ui) {
				  	  var resCol = undefined;
				  	  var position = "";
				  	  var border = "left";
				  	  var source = undefined;
				  	  var prevGrid = Math.floor(ui.originalSize.width / gridStep);
				  	  var actualGrid = Math.floor($(ui.element).width() / gridStep);
				  	  if(actualGrid > gridMax)
				  	  	actualGrid = gridMax;
					  
					  if(prevGrid != actualGrid && actualGrid > 0 && prevGrid > 0) {
					  	  resCol = that.setProperty("col", $(ui.element).attr("id"), "grid", actualGrid);
					  	  
				  		  if(ui.originalPosition.left == ui.position.left) {
				  	  		  border = "right";
				  		  }					  	  

						  if(prevGrid > actualGrid) {
						  	  if(col["position"] == "content") {
						  	  	  position = border;
							  } else {
								  position = col["position"];
								  source = {"id" : resCol["count"] 
								  			, "position" : ""
								  		};
								  if(row["cols"][position].length > 0) {
				  					  if(border == "right") {
				  	  				      source["position"] = "next";
				  					  }							  	  
								  }
							  }

				  			  that.addCol(row, {
									"id" : col["id"] + position.capitalize()
									, "name" : col["name"] + " " + position.capitalize()
									, "class" : position
									, "position" : position
									, "width" : ""
									, "grid" : prevGrid - actualGrid
							  }, source);
						  } else if(prevGrid < actualGrid) {
						  	  var rowWidth = $("#" + row["id"]).width();
							  var elem = undefined;
							  
						  	  if(border == "right") {
						  	  	  elem =  $(ui.element).next().attr("id");
						  	  } else {
						  	  	  elem =  $(ui.element).prev().attr("id");
						  	  }
						  	  if(elem) {
						  		  resCol = that.setProperty("col", elem, "grid", prevGrid - actualGrid, "+");	
						  		  $("#" + resCol["obj"]["id"]).width(Math.floor(rowWidth * resCol["obj"]["grid"] / 12) + "px");
							  }
						  }
					  }
				  	  that.refresh();
				  }
			});	
		}
		, "delRow" : function(id) {
			var position = $("#" + id).attr("class").replace("canvas-row ", "");
			
			for (var i in this.data.rows[position]){
		        if(this.data.rows[position][i]["id"] == id) {
					this.data.rows[position].splice(i, 1);
					break;
		        }
		    }
			$("#" + id).remove();
			this.refresh();
		}
		, "fluidRow" : function(id) {
			this.setProperty("row", id, "isFluid", null, "toggle");
		    this.refresh();
		}
		, "getProperty" : function(type, id, property) {
			var elem = undefined;
			var position = "";

			if(type == "row") {
				position = $("#" + id).attr("class").split(" ")[1];
				elem = this.data.rows[position];
			} else if(type == "col") {
				var parentId =  $("#" + id).closest(".canvas-row").attr("id");
				position = $("#" + parentId).attr("class").split(" ")[1];

				for (var i in this.data.rows[position]) {
			        if(this.data.rows[position][i]["id"] == parentId) {
						elem = this.data.rows[position][i]["cols"][$("#" + id).attr("class").split(" ")[1]];
						break;	
					}
				}
			}
			if(elem) {
				for (var i in elem){
			        if(elem[i]["id"] == id) {
			        	if(property !== undefined) {
							return { "count" : parseInt(i), "property" : elem[i][property] };
			        	} else {
							return { "count" : parseInt(i), "obj" : elem[i] };	
			        	}
			        }
			    }
			}					
		}
		, "setProperty" : function(type, id, property, value, operation) {
			var elem = this.getProperty(type, id);
			if(elem) {
			    switch(operation) {
					case "toggle":
						if(elem["obj"][property]) 
							elem["obj"][property] = false;
						else 
							elem["obj"][property] = true;
						break;
					case "+":
						elem["obj"][property] = parseInt(parseInt(elem["obj"][property]) + parseInt(value));
						break;
					case "-":
						elem["obj"][property] = parseInt(parseInt(elem["obj"][property]) - parseInt(value));
						break;
					default:
						elem["obj"][property] = value;
				}
				return elem;
			}
		}
		, "setRow" : function(row, source) {
			if(source) {
				this.data.rows[row["position"]].splice(source, 0, row);
			} else {
				switch(row["position"]) {
					case "top":
						this.data.rows["top"].push(row);
						break;
					case "bottom":
						this.data.rows["bottom"].unshift(row);
						break;
					default:
						this.data.rows["central"] = new Array(row);
				}
			}
		}
		, "setCol" : function(row, col, source) {
			if(source) {
				row["cols"][col["position"]].splice(source, 0, col);
			} else {
				switch(col["position"]) {
					case "left":
						row["cols"]["left"].push(col);
						break;
					case "right":
						row["cols"]["right"].unshift(col);
						break;
					default:
						row["cols"]["content"] = new Array(col);
				}
			}
			/*
			for(var i in this.data.rows[row["position"]]) {
				if(this.data.rows[row["position"]][i]["id"] == row["id"]) {
					switch(col["position"]) {
						case "left":
							this.data.rows[row["position"]][i]["cols"].push(col);
							break;
						case "right":
							this.data.rows[row["position"]][i]["cols"].unshift(col);
							break;
						default:
							this.data.rows[row["position"]][i]["cols"] = new Array(col);
					}
					break;
				}
			}*/
		}
		, "setEvent" : function() {
			
		}
		, "refresh" : function() {
	    	var containerHeight = $(this.canvas.container).height();
	    	var containerWidth = $(this.canvas.container).width(); 
			var heightSet = 0;
			var countRow = this.data.rows.top.length + this.data.rows.central.length + this.data.rows.bottom.length;

			$(this.canvas.container).find(".canvas-row").width(containerWidth );

	    	for(var i in this.data.rows.top) {
	    		$("#" + this.data.rows.top[i]["id"]).height(Math.round(containerHeight * this.data.rows.top[i]["xHeight"] / 100) + "px");
	    		
	    		this.refreshCol(this.data.rows.top[i]);
	    		
				heightSet = heightSet +  $("#" + this.data.rows.top[i]["id"]).outerHeight(true);
	    	}
	    	for(var i in this.data.rows.bottom) {
	    		$("#" + this.data.rows.bottom[i]["id"]).height(Math.round(containerHeight * this.data.rows.bottom[i]["xHeight"] / 100) + "px");

	    		this.refreshCol(this.data.rows.bottom[i]);

				heightSet = heightSet + $("#" + this.data.rows.bottom[i]["id"]).outerHeight(true); 
	    	}
	    	for(var i in this.data.rows.central) {
	    		/*$("#" + this.data.rows.central[i]["id"]).height((this.data.rows.central[i]["xHeight"]) + "%");*/
	    		
	    		this.refreshCol(this.data.rows.central[i]);

				heightSet = heightSet +  ($("#" + this.data.rows.central[i]["id"]).outerHeight(true) - $("#" + this.data.rows.central[i]["id"]).height());
	    	}

			this.displayControls({"addrow" : true});
			if(heightSet > containerHeight) {
				var rowMargin = ($(this.canvas.container).find(".canvas-row.central").outerHeight(true) - $(this.canvas.container).find(".canvas-row.central").height());
				var rowHeight = Math.floor(containerHeight / countRow);
				var rowCentralHeight = containerHeight - (rowHeight * countRow);
				
				if(((rowMargin + 4) * countRow) >= containerHeight) {
					$(this.canvas.container).find(".canvas-row").height(4);
					this.displayControls({"addrow" : false});
				} else {
					$(this.canvas.container).find(".canvas-row").height(rowHeight - rowMargin);
					$(this.canvas.container).find(".canvas-row.central").height(rowHeight + rowCentralHeight - rowMargin);
				}
			} else {
				$(this.canvas.container).find(".canvas-row.central").height(containerHeight - heightSet);
			}
			
			$(this.canvas.container).find(".canvas-section").each(function() {
				$(this).css({ 
					"height" : $(this).parent().height() + "px"
				});
			})
			
		}
		, "refreshCol" : function(row) {
			var widthSet = 4;
			var rowWidth = $("#" + row["id"]).width();

			if(row["cols"]) {
				row["cols"]["left"].each(function(x, value) {
					$("#" + row["cols"]["left"][x]["id"]).width(Math.floor(rowWidth * row["cols"]["left"][x]["grid"] / 12) + "px");
					$("#" + row["cols"]["left"][x]["id"]).css({"left" : widthSet + "px"});
					widthSet = widthSet + $("#" + row["cols"]["left"][x]["id"]).outerWidth(true);
				});
				row["cols"]["content"].each(function(x, value) {
					$("#" + row["cols"]["content"][x]["id"]).width(Math.floor(rowWidth * row["cols"]["content"][x]["grid"] / 12) + "px");
					$("#" + row["cols"]["content"][x]["id"]).css({"left" : widthSet + "px"});

					widthSet = widthSet + $("#" + row["cols"]["content"][x]["id"]).outerWidth(true);
				});
				row["cols"]["right"].each(function(x, value) {
					$("#" + row["cols"]["right"][x]["id"]).width(Math.floor(rowWidth * row["cols"]["right"][x]["grid"] / 12) + "px");
					$("#" + row["cols"]["right"][x]["id"]).css({"left" : widthSet + "px"});

					widthSet = widthSet + $("#" + row["cols"]["right"][x]["id"]).outerWidth(true);
				});


				if(widthSet > rowWidth) {

				} else {
					$("#" + row["cols"]["content"][0]["id"]).width($("#" + row["cols"]["content"][0]["id"]).width() + (rowWidth - widthSet));
				}			
			}

		    if(row["isFluid"]) {
	    		$("#" + row["id"] + " .canvas-section:not(.content)").hide();
				$("#" + row["id"] + " .canvas-section.content").addClass("mono").removeClass("multi");
				$("#" + row["id"] + " .canvas-section.content .ui-resizable-w").hide();
			} else {
				$("#" + row["id"] + " .canvas-section").fadeIn();
				$("#" + row["id"] + " .canvas-section.content").addClass("multi").removeClass("mono");
				$("#" + row["id"] + " .canvas-section.content .ui-resizable-w").show();
			}
		},
		"getColsByRow" : function(id) {
			var cols = this.getProperty("row", id, "cols");
			var res = [];

			for(var i in cols["property"]) {
				cols["property"][i].each(function(x, value) {
					res.push(cols["property"][i][x]);
				});
			}
			return res;
		},
		"setEditor" : function(id) {
			var that = this;
			var editor = {
				"actions" : {
					"update" : { 
						"name" : "ff.cms.layout.updateData",
						"returnValue" : true
					}
					, "close" : "ff.cms.layout.highlightRow"
				},
				"groups" : [{
					"name" : "none",
					"action" : "fadeIn"
				}],
				"data" : 
				[{
					"name" : "Framework",
					"class" : "framework",
					"accordion": false,
					"rows" : [
						{
							"fields" : [
								{
									"name" : "framework",
									"class" : "framework",
									"type" : "selection",
									"options" : ["none", "Bootstrap", "Foundation"],
									"obj" : this.data.container,
									"key" : "framework",
									"value" : "none",
									"params": ""
									
								}
							]
						}
					]
				},
				{
					"name" : "Container",
					"class" : "container",
					"accordion": false,
					"groups": ["none"],
					"rows" : [
						{
							"fields" : [
								{
									"name" : "Orientation",
									"class" : "orientation",
									"type" : "selection",
									"options" : ["landscape", "portrait"],
									"obj" : this.data.container,
									"key" : "orientation",
									"callback" : "ff.cms.layout.flipResolution",
									"params": ""
								}
							]
						},
						{
							"fields" : [
								{
									"name" : "Width",
									"class" : "width",
									"label" : "Width:",
									"type" : "input",
									"placeholder" : "Resolution Width",
									"widget": "spinner", 
									"post" : "px",
									"obj" : this.data.container,
									"key" : "width",
									"callback" : "ff.cms.layout.setResolution",
									"params": ""
								},							
								{
									"name" : "Height",
									"class" : "height",
									"label" : "Height:",
									"type" : "input",
									"placeholder" : "Resolution Height",
									"widget": "spinner", 
									"post" : "px",
									"obj" : this.data.container,
									"key" : "height",
									"callback" : "ff.cms.layout.setResolution",
									"params": ""
								}
							]
						}
					]
				}]
			}
			var row = this.getProperty("row", id);

			editor["data"].push({
				"name" : "Row",
				"class" : "row",
				"accordion" : "active",
				"rows" : [
					{
						"fields" : [
							{
								"name" : "class",
								"label" : "Class",
								"type" : "input",
								"obj" : row["obj"],
								"key" : "class"								
							},
							{
								"name" : "Fluid",
								"label" : "Fluid",
								"icon": "ico-edit",
								"type" : "checkbox",
								"options" : {"checked" : true, "unchecked" : false},
								"obj" : row["obj"],
								"key" : "isFluid",
								"widget": "button",
								"callback" : "ff.cms.layout.refresh"
							},
							{
								"name" : "minheight",
								"label" : "Min Height",
								"type" : "input",
								"obj" : row["obj"],
								"key" : "xHeight",
								"widget": {"name" : "spinner",
											"params" : {"max" : 100, "min" : 1}
								},
								"post" : "px",
								"callback" : "ff.cms.layout.refresh"
							}
						]
					}
				]
			});	
			/* da fare cosi
//"widget": {"name" : "spinner", "params" {"min" : 1, "max" : 12}}, */
			var cols = this.getColsByRow(id);
	
			cols.each(function(i, value) {
				editor["data"].push({
					"name" : cols[i]["name"],
					"class" : cols[i]["class"],
					"rows" : [
						{
							"fields" : [
								{
									"name" : "class",
									"label" : "Class",
									"type" : "input",
									"obj" : cols[i],
									"key" : "class",
									"callback" : "ff.cms.layout.refresh"								
								},	

								{
									"name" : "grid",
									"label" : "Grid",
									"type" : "input",
									"widget": {"name" : "spinner",
												"params" : {"max" : 12, "min" : 1}
									},
									"obj" : cols[i],
									"key" : "grid",
									"callback" : "ff.cms.layout.refresh",
									"groups": ["Bootstrap", "Foundation"]
								},							
								{
									"name" : "width",
									"label" : "Width",
									"type" : "input",
									"special" : "px",
									"widget" : "spinner",
									"obj" : cols[i],
									"key" : "width",
									"groups": ["none"]
								}
							]
						}
					]
				});
			});
				
			return editor;		
		},
		"updateData" : function(editor) {
			ff.cms.layout.highlightRow();
			console.log(editor);
			/*da finire ed espandere*/
		}
	};

	return that;
})();