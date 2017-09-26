jQuery(function() {
	jQuery("#Updater").parent().append('<div class="actions" />');
	jQuery("#Updater").parent().children(".actions").append("<div class=\"unatantum\"><input type=\"checkbox\" id=\"unatantum\" name=\"unatantum\" value=\"1\" /><label>Clone data from Master site</label></div>");
    jQuery("#Updater").parent().children(".actions").append("<input class=\"button noactivebuttons\" type=\"button\" id=\"check\" name=\"check\" value=\"check\" />");
    jQuery("#Updater").parent().children(".actions").append("<input class=\"button noactivebuttons\" type=\"button\" id=\"execute\" name=\"execute\" value=\"update\" disabled=\"disabled\" />");
    jQuery("#Updater").parent().children(".actions").append("<input class=\"button noactivebuttons\" type=\"button\" id=\"btstop\" name=\"btstop\" value=\"stop\" disabled=\"disabled\" />");
    jQuery("#Updater").parent().prepend("<input type=\"hidden\" id=\"loading\" name=\"loading\" value=\"0\" />");
    jQuery("#Updater").parent().prepend("<input type=\"hidden\" id=\"install\" name=\"install\" value=\"0\" />");
    jQuery("#Updater").parent().prepend("<input type=\"hidden\" id=\"stop\" name=\"stop\" value=\"0\" />");
    
	jQuery("#unatantum").click(function() {
		if(jQuery(this).is(":checked")) {
			var countRow = jQuery("table.ffGrid tr").length;

			jQuery("table.ffGrid tr.positive:last").after('<tr class="negative unatantum">'
				+ '<td id="Updater_' + countRow + '_0" class="operation ffField text" ><span class="data">/data/unatantum</span></td>'
				+ '<td id="Updater_' + countRow + '_1" class="result ffField text" ><span class="data"></span></td>'
				+ '<td id="Updater_' + countRow + '_2" class="ffButton"><a href="javascript:void(0)" target="_self" class="icon ico-refresh" title="modifica" onclick="document.getElementById(\'frmAction\').value = \'Updater_\'; Updater(jQuery(this).parent().prev().prev());">'
				+ '</a></td>'
				+ '</tr>'
			);
		} else {
			jQuery("table.ffGrid tr.unatantum").remove();
		}
	});
    
	jQuery("#check").click(function()  {
	    jQuery("#check").attr("disabled", "disabled");  
		jQuery("#install").val("0");
		jQuery("td.operation").each(function(cItem) {
		    jQuery(this).next().html("loading...");
		    var elem = jQuery(this);
		    var sync = false;
		    var targetElem = jQuery(this).text().substring(0, jQuery(this).text().length );

		    if(targetElem.indexOf("sync") >= 0) 
		    {
				targetElem = targetElem.split("sync")[1];
				sync = true;
		    }
		    
		    targetElem = targetElem.replace("/data", "/data.php" + (sync ? "/sync" : ""));
		    targetElem = targetElem.replace("/externals", "/externals.php");
		    targetElem = targetElem.replace("/files", "/files.php" + (sync ? "/sync" : ""));
		    targetElem = targetElem.replace("/indexes", "/indexes.php");
		    targetElem = targetElem.replace("/structure", "/structure.php");
		    
		    jQuery.ajax({
		           async: true,    
		           type: "GET",
		           url: ff.site_path+"/conf/gallery/updater" + targetElem, 
		           data: "json=1",
		           dataType: "json",
		           cache: false, 
		           success: function(item) {
		               if(typeof jQuery != "undefined") {
						   var dataError = false;
						   
		                   if(item.length) {
		                       jQuery(elem).next().html("<table><thead><tr><th colspan=\"2\"><a href=\"#\">Not Updated: " + item.length + "</a></th></tr></thead><tbody class=\"hidden\"></tbody></table>");
		                       jQuery(elem).next().find("thead").click(function()  {
		                           if(jQuery(this).next().is(":visible")) {
		                               jQuery(this).next().addClass("hidden");
		                           } else {
		                               jQuery(this).next().removeClass("hidden");
		                           }
		                       });
		                       if(item.length < 1000) { 
			                       for (var a = 0; a < item.length; a++)
			                       {
			                           jQuery(elem).next().find("tbody").append("<tr><td>"
			                           + item[a]["data"]
			                           + "</td><td>"
			                           + item[a]["value"]
			                           + "</td></tr>");

					                    if(item[a]["data"] == "error") {
					                        dataError = true;
										}
					                }
									if(dataError) {
										jQuery(elem).next().find("thead").html("<tr><th colspan=\"2\"><a href=\"#\">Not Updated</a></th></tr>");
										jQuery(elem).next().find("tbody").removeClass("hidden");
										jQuery("#btstop").click();
								   	}
							   }
		                   } else {
		                       jQuery(elem).next().html("Updated");
		                   }

		                   jQuery("#loading").val(parseInt(jQuery("#loading").val()) + 1);
		                       
		                   if(parseInt(jQuery("#loading").val()) >= jQuery("td.operation").size()) {
		                       jQuery("#execute").removeAttr("disabled");
                               jQuery("#check").removeAttr("disabled");  
		                   }
		               }
		           },
		           error: function(XMLHttpRequest, textStatus, errorThrown) {
		               if(typeof jQuery != "undefined") {
		                   var checkError = XMLHttpRequest.responseText;

		                   checkError = checkError.toLowerCase();
		                
		                   if(checkError.indexOf("<body>") >= 0) {
		                       jQuery(elem).next().html("<strong>error: "+errorThrown+"</strong>");
		                   } else {
		                       jQuery(elem).next().html("<strong>error: "+XMLHttpRequest.responseText+"</strong>");
		                   }
		                   jQuery("#loading").val(parseInt(jQuery("#loading").val()) + 1);
		                
		                   if(parseInt(jQuery("#loading").val()) >= jQuery("td.operation").size()) {
		                       jQuery("#execute").removeAttr("disabled");
                               jQuery("#check").removeAttr("disabled");  
		                   }
		               }
		           } 
		    });        
		 });
	});

    jQuery("#execute").click(function()  {
    	jQuery("#check").attr("disabled", "disabled"); 
        jQuery("#execute").attr("disabled", "disabled");  
        jQuery("#stop").val("0");
        jQuery("#btstop").removeAttr("disabled");

        while(parseInt(jQuery("#install").val()) < jQuery("td.operation").size() && parseInt(jQuery("#stop").val()) <= 0) {
            //jQuery("#install").val("0");
            jQuery("td.operation").each(function(cItem) {
                var elem = jQuery(this);
                
			    /*var targetElem = jQuery(elem).text().substring(0, jQuery(elem).text().length);
			    var dataReset = false;

			    targetElem = targetElem.replace("/data", "/data.php");
			    targetElem = targetElem.replace("/externals", "/externals.php");
			    targetElem = targetElem.replace("/files", "/files.php");
			    targetElem = targetElem.replace("/indexes", "/indexes.php");
			    targetElem = targetElem.replace("/structure", "/structure.php");

                if(cItem == parseInt(jQuery("#install").val()) && parseInt(jQuery("#stop").val()) <= 0) {
                    var actualLabel = jQuery(this).next().html();
                    if(actualLabel.indexOf("Install...") == -1)
                        jQuery(this).next().html("Install...");

                    jQuery.ajax({
                           async: false,
                           type: "GET",
                           url: ff.site_path+"/conf/gallery/updater" + targetElem, 
                           data: "json=1&exec=1",
                           dataType: "json",
                           cache: false, 
                           success: function(item) {
                               if(typeof jQuery != "undefined") {
							   		var dataError = false;
							   		
	                                if(item.length) {
	                                    jQuery(elem).next().html("<table><thead><tr><th colspan=\"2\"><a href=\"#\">Not Updated: " + item.length + "</a></th></tr></thead><tbody class=\"hidden\"></tbody></table>");
	                                    jQuery(elem).next().find("thead").click(function()  {
	                                        if(jQuery(this).next().is(":visible")) {
	                                            jQuery(this).next().addClass("hidden");
	                                        } else {
	                                            jQuery(this).next().removeClass("hidden");
	                                        }
	                                    });
										if(item.length < 1000) {
		                                    for (var a = 0; a < item.length; a++)
		                                    {
		                                        jQuery(elem).next().find("tbody").append("<tr><td>"
		                                        + item[a]["data"]
		                                        + "</td><td>"
		                                        + item[a]["value"]
		                                        + "</td></tr>");

		                                        if(item[a]["data"] == "error") {
	                                        		dataError = true;
												}
		                                    }
											if(dataError) {
												jQuery(elem).next().find("thead").html("<tr><th colspan=\"2\"><a href=\"#\">Not Updated</a></th></tr>");
												jQuery(elem).next().find("tbody").removeClass("hidden");
												//jQuery("#btstop").click();
                                                
                                                jQuery("#install").val(parseInt(jQuery("#install").val()) + 1);
											}
										}

										if(item.length > 200) {
                                            jQuery(elem).next().html("Install... Remaining (" + item.length + ")");
											dataReset = true;
										}
	                                } else {
	                                    jQuery(elem).next().html("Updated");
	                                	jQuery("#install").val(parseInt(jQuery("#install").val()) + 1);    
	                                }
                               }
                           },
                           error: function(XMLHttpRequest, textStatus, errorThrown) {
                               if(typeof jQuery != "undefined") {
                                    var checkError = XMLHttpRequest.responseText;

                                    checkError = checkError.toLowerCase();
                                    
                                    if(checkError.indexOf("<body>") >= 0) {
                                        jQuery(elem).next().html("<strong>error: "+errorThrown+"</strong>");
                                    } else {
                                        jQuery(elem).next().html("<strong>error: "+XMLHttpRequest.responseText+"</strong>");
                                    }
                                    
                                    jQuery("#btstop").click();
                               } 
                           }
                    });        
                } 
                if(dataReset) {*/
                if(Updater(elem, cItem)) {
                	if(jQuery(elem).text().substring(0, jQuery(elem).text().length).indexOf("/data") == -1) {
                		jQuery("#install").val("0");
					}
                	return;
				}
             });
        }
        jQuery("#check").removeAttr("disabled");
        jQuery("#execute").removeAttr("disabled");
        jQuery("#btstop").attr("disabled", "disabled"); 
    });
    
    jQuery("#btstop").click(function()  {
        jQuery("#stop").val("1");
        jQuery(this).attr("disabled", "disabled");
    	jQuery("#execute").attr("disabled", "disabled"); 
    });
     
});

function Updater(elem, cItem) {
    var targetElem = jQuery(elem).text().substring(0, jQuery(elem).text().length);
    var dataReset = false;

    targetElem = targetElem.replace("/data", "/data.php");
    targetElem = targetElem.replace("/externals", "/externals.php");
    targetElem = targetElem.replace("/files", "/files.php");
    targetElem = targetElem.replace("/indexes", "/indexes.php");
    targetElem = targetElem.replace("/structure", "/structure.php");

    if(cItem === undefined || (cItem == parseInt(jQuery("#install").val()) && parseInt(jQuery("#stop").val()) <= 0)) {
        var actualLabel = jQuery(elem).next().html();
        if(actualLabel.indexOf("Install...") == -1)
            jQuery(elem).next().html("Install...");

        jQuery.ajax({
               async: false,
               type: "GET",
               url: ff.site_path+"/conf/gallery/updater" + targetElem, 
               data: "json=1&exec=1",
               dataType: "json",
               cache: false, 
               success: function(item) {
                   if(typeof jQuery != "undefined") {
                           var dataError = false;
                           
                        if(item.length) {
                            jQuery(elem).next().html("<table><thead><tr><th colspan=\"2\"><a href=\"#\">Not Updated: " + item.length + "</a></th></tr></thead><tbody class=\"hidden\"></tbody></table>");
                            jQuery(elem).next().find("thead").click(function()  {
                                if(jQuery(elem).next().is(":visible")) {
                                    jQuery(elem).next().addClass("hidden");
                                } else {
                                    jQuery(elem).next().removeClass("hidden");
                                }
                            });
                            if(item.length < 1000) {
                                for (var a = 0; a < item.length; a++)
                                {
                                    jQuery(elem).next().find("tbody").append("<tr><td>"
                                    + item[a]["data"]
                                    + "</td><td>"
                                    + item[a]["value"]
                                    + "</td></tr>");

                                    if(item[a]["data"] == "error") {
                                        dataError = true;
                                    }
                                }
                                if(dataError) {
                                    jQuery(elem).next().find("thead").html("<tr><th colspan=\"2\"><a href=\"#\">Not Updated</a></th></tr>");
                                    jQuery(elem).next().find("tbody").removeClass("hidden");
                                    //jQuery("#btstop").click();
                                    
                                    jQuery("#install").val(parseInt(jQuery("#install").val()) + 1);
                                }
                            }

                            if(item.length > 200) {
                                jQuery(elem).next().html("Install... Remaining (" + item.length + ")");
                                dataReset = true;
                            }
                        } else {
                            jQuery(elem).next().html("Updated");
                            jQuery("#install").val(parseInt(jQuery("#install").val()) + 1);    
                        }
                   }
               },
               error: function(XMLHttpRequest, textStatus, errorThrown) {
                   if(typeof jQuery != "undefined") {
                        var checkError = XMLHttpRequest.responseText;

                        checkError = checkError.toLowerCase();
                        
                        if(checkError.indexOf("<body>") >= 0) {
                            jQuery(elem).next().html("<strong>error: "+errorThrown+"</strong>");
                        } else {
                            jQuery(elem).next().html("<strong>error: "+XMLHttpRequest.responseText+"</strong>");
                        }
                        
                        jQuery("#btstop").click();
                   } 
               }
        });        
    }

    return dataReset;
}
