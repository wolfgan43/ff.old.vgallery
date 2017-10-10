jQuery(function() {
	jQuery("#tabs_rel_0 .grid").append("<input type=\"button\" id=\"check\" name=\"check\" value=\"check\" />");
    jQuery("#tabs_rel_0 .grid").append("<input type=\"button\" id=\"execute\" name=\"execute\" value=\"update\" disabled=\"disabled\" />");
    jQuery("#tabs_rel_0 .grid").append("<input type=\"button\" id=\"btstop\" name=\"btstop\" value=\"stop\" disabled=\"disabled\" />");
    jQuery("#tabs_rel_0 .grid").prepend("<input type=\"hidden\" id=\"loading\" name=\"loading\" value=\"0\" />");
    jQuery("#tabs_rel_0 .grid").prepend("<input type=\"hidden\" id=\"install\" name=\"install\" value=\"0\" />");
    jQuery("#tabs_rel_0 .grid").prepend("<input type=\"hidden\" id=\"stop\" name=\"stop\" value=\"0\" />");
    
	jQuery("#check").click(function()  {
	    jQuery("#check").attr("disabled", "disabled");  
		jQuery("#install").val("0");
		jQuery(".operation").each(function(cItem) {
		    jQuery(this).next().html("loading...");
		    var elem = jQuery(this);
		    
		    jQuery.ajax({
		           async: true,    
		           type: "GET",
		           url: ff.site_path+"/conf/gallery/updater" + jQuery(this).text().substring(0, jQuery(this).text().length -1), 
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
		                               jQuery(this).next().hide();
		                           } else {
		                               jQuery(this).next().show();
		                           }
		                       });
		                    
		                       for (a = 0; a < item.length; a++)
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
									jQuery(elem).next().find("tbody").show();
									jQuery("#btstop").click();
								}
		                   } else {
		                       jQuery(elem).next().html("Updated");
		                   }

		                   jQuery("#loading").val(parseInt(jQuery("#loading").val()) + 1);
		                       
		                   if(parseInt(jQuery("#loading").val()) >= jQuery(".operation").size()) {
		                       jQuery("#execute").removeAttr("disabled");
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
		                
		                   if(parseInt(jQuery("#loading").val()) >= jQuery(".operation").size()) {
		                       jQuery("#execute").removeAttr("disabled");
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

        while(parseInt(jQuery("#install").val()) < jQuery(".operation").size() && parseInt(jQuery("#stop").val()) <= 0) {
            jQuery("#install").val("0");

            jQuery(".operation").each(function(cItem) {
                
                var elem = jQuery(this);

                if(cItem == parseInt(jQuery("#install").val()) && parseInt(jQuery("#stop").val()) <= 0) {
                    jQuery(this).next().html("Install...");  
                    jQuery.ajax({
                           async: false,    
                           type: "GET",
                           url: ff.site_path+"/conf/gallery/updater" + jQuery(this).text().substring(0, jQuery(this).text().length -1), 
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
	                                            jQuery(this).next().hide();
	                                        } else {
	                                            jQuery(this).next().show();
	                                        }
	                                    });

	                                    for (a = 0; a < item.length; a++)
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
											jQuery(elem).next().find("tbody").show();
											jQuery("#btstop").click();
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
                               } 
                           }
                    });        
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