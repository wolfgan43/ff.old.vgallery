var startH1 = "";
var startTitle = "";
jQuery(function() {
    startH1 = jQuery("H1").text();
    startTitle = jQuery("TITLE").text();
	jQuery("#Updater").parent().append('<div class="actions" />'); 
	jQuery("#Updater").parent().children(".actions").append("<div class=\"unatantum\"><input type=\"checkbox\" id=\"unatantum\" name=\"unatantum\" value=\"1\" /><label>Clone data from Master site</label></div>");
    jQuery("#Updater").parent().children(".actions").append("<input class=\"button noactivebuttons hidden\" type=\"button\" id=\"btstop\" name=\"btstop\" value=\"stop\" />");
    jQuery("#Updater").parent().children(".actions").append("<input class=\"button noactivebuttons\" type=\"button\" id=\"check\" name=\"check\" value=\"check\" />");
    jQuery("#Updater").parent().children(".actions").append("<input class=\"button noactivebuttons\" type=\"button\" id=\"execute\" name=\"execute\" value=\"update\" />");
    jQuery("#Updater").parent().prepend("<input type=\"hidden\" id=\"loading\" name=\"loading\" value=\"0\" />");
    jQuery("#Updater").parent().prepend("<input type=\"hidden\" id=\"install\" name=\"install\" value=\"0\" />");
    jQuery("#Updater").parent().prepend("<input type=\"hidden\" id=\"stop\" name=\"stop\" value=\"0\" />");
    

	jQuery("#unatantum").click(function() {
		if(jQuery(this).is(":checked")) {
			var countRow = jQuery("table.ffGrid tr").length;

			jQuery("table.ffGrid").append('<tr class="' + (jQuery("table.ffGrid tr:last").hasClass("positive") ? "negative" : "positive") + ' unatantum">'
				+ '<td id="Updater_' + countRow + '_0" class="operation ffField text" ><span class="data">/data/unatantum</span></td>'
				+ '<td id="Updater_' + countRow + '_1" class="result ffField text" ><span class="data"></span></td>'
				+ '<td id="Updater_' + countRow + '_2" class="ffButton"><a href="javascript:void(0)" target="_self" class="icon ico-refresh" title="modifica" onclick="document.getElementById(\'frmAction\').value = \'Updater_\'; Updater(true, jQuery(this).parent().prev().prev(), undefided, true);">' 
				+ '</a></td>'
				+ '</tr>'
			);
		} else {
			jQuery("table.ffGrid tr.unatantum").remove();
		}
	});
    
	jQuery("#check").click(function()  {
		jQuery("H1").text("Check Database...");
        jQuery("TITLE").text("Check Database...");

		UpdaterDisplayButton(false);
		
		Updater(false);
		/*jQuery("td.operation").each(function(cItem) {
			Updater(false, this, undefined, true);
		});*/
	});

    jQuery("#execute").click(function()  {
        jQuery("H1").text("Updating Database...");
        jQuery("TITLE").text("Updating Database...");

		UpdaterDisplayButton(false);
        
    	Updater(true);
    });
    
    jQuery("#btstop").click(function()  {
    	jQuery("td.result:contains('...')").text("Cancelled");

        UpdaterDisplayButton(true);
    });
     
});

function UpdaterDisplayButton(stop) {
	if(stop === undefined)
		stop =  parseInt(jQuery("#stop").val());
	else {
		jQuery("#stop").val(stop ? 1 : 0);
		if(!stop) {
			jQuery("td.operation").removeClass("done");
		}
	}
	if(stop) {
        jQuery("H1").text(startH1);
        jQuery("TITLE").text(startTitle);

		jQuery("#unatantum").removeAttr("disabled").css("opacity", "1");
        jQuery("#check").removeAttr("disabled").css("opacity", "1");
		jQuery("#execute").removeAttr("disabled").css("opacity", "1");
		jQuery("#btstop").addClass("hidden");
	} else {
        jQuery("#unatantum").attr("disabled", "disabled").css("opacity", "0.5"); 
    	jQuery("#check").attr("disabled", "disabled").css("opacity", "0.5"); 
        jQuery("#execute").attr("disabled", "disabled").css("opacity", "0.5");  
        jQuery("#btstop").removeClass("hidden");
	}
};

function Updater(exec, targetElem, limit, disableCascading) {
	if(!targetElem)
		targetElem = jQuery("td.operation:first");

	if(!limit)
		limit = jQuery(targetElem).attr("data-limit") || 0;

	if(jQuery(targetElem).length > 0 && (!parseInt(jQuery("#stop").val()) || (exec && disableCascading))) {
	    var targetService = jQuery(targetElem).text();
	    var dataReset = false;

	    targetService = targetService.replace("/data", "/data.php").trim();
	    targetService = targetService.replace("/externals", "/externals.php").trim();
	    targetService = targetService.replace("/files", "/files.php").trim();
	    targetService = targetService.replace("/indexes", "/indexes.php").trim();
	    targetService = targetService.replace("/structure", "/structure.php").trim();

		var actualLabel = jQuery(targetElem).next();
		if(exec)
			actualLabel.html("Install...");
		else 
			actualLabel.html("Loading...");

		jQuery.ajax({
		       type: "GET",
		       url: ff.site_path+"/conf/gallery/updater" + targetService, 
		       data: "json=1" + (exec ? "&exec=1" : "") + (limit ? "&lo=" + limit : ""),
		       dataType: "json",
		       cache: false, 
		       success: function(item) {
		       		if(!parseInt(jQuery("#stop").val()) || (exec && disableCascading)) {
		       			var label = "";
		       			var index = jQuery("td.operation").index(targetElem);
		       			index++;
			            if(item["record"] && item["record"].length) {
							if(item["error"]) {
								label = "Update Error (" + item["error"] + ")";
			                } else {
								if(exec) {
 									index = 0;

									if(item["info"]) {
										label = item["info"];
									} else {
										label = "Install... Remaining (" + item["record"].length + ")";
									}
								} else {
									if(item["info"]) {
										label = item["info"] + ": " + item["record"].length + "";
									} else {
		                    			label = "Not Updated: " + item["record"].length + "";
									}
								}
							}
							actualLabel.html("<table><thead><tr><th colspan=\"2\"><a href=\"#\">" + label + "</a></th></tr></thead><tbody class=\"hidden\"></tbody></table>");
			                actualLabel.find("thead").click(function()  {
			                    if(jQuery(this).closest("TABLE").find("TBODY").hasClass("hidden")) {
			                        jQuery(this).closest("TABLE").find("TBODY").removeClass("hidden");
			                    } else {
			                        jQuery(this).closest("TABLE").find("TBODY").addClass("hidden");
			                    }
			                });

			                if(item["record"].length < 1000) {
			                    for (var a = 0; a < item["record"].length; a++)
			                    {
			                        actualLabel.find("tbody").append("<tr><td>"
			                        + item["record"][a]["data"]
			                        + "</td><td>"
			                        + item["record"][a]["value"]
			                        + "</td></tr>");
			                    }
			                }

							if(item["limit"])
								jQuery(targetElem).attr("data-limit", item["limit"]);

							if(!exec || (!disableCascading && !item["error"])) 
								Updater(exec, jQuery("td.operation:eq(" + index + ")"));
							else 
								jQuery(targetElem).addClass("done");

			            } else {
							jQuery(targetElem).addClass("done");
			            	if(item["error"]) {
								actualLabel.html(item["error"]);
							} else {			            
				                actualLabel.html("Updated");
							}

							if(!disableCascading)
		                		Updater(exec, jQuery("td.operation:eq(" + index + ")"));							
			            }
			            
			            if(jQuery("td.operation").length == jQuery("td.operation.done").length)
		            		UpdaterDisplayButton(true);
					}
		       },
		       error: function(XMLHttpRequest, textStatus, errorThrown) {
		            jQuery(targetElem).addClass("done");
		            
		            if(XMLHttpRequest.responseText && XMLHttpRequest.responseText.toLowerCase().indexOf("<body>") >= 0) {
		                actualLabel.html("<strong>error: "+errorThrown+"</strong>");
		            } else {
		                actualLabel.html("<strong>error: "+XMLHttpRequest.responseText+"</strong>");
		            }

		            if(jQuery("td.operation").length == jQuery("td.operation.done").length)
		            	UpdaterDisplayButton(true);
		       }
		});        
	} else {
		UpdaterDisplayButton(true);
	}
    return;
}
