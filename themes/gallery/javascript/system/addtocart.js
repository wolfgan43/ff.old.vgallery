function cartResponse(qta, message, response, cartUrl, labelHide, labelCheckOut) {
	if(qta === null) {
		qta = "";
	}

	if(response) {
		var status = "success";

		jQuery.blockUI({ 
			message: '<div class="cart-notify ' + status + '">'
						+ '<h1>' + qta + ' ' + message + '</h1>' 
							+ '<a class="hide" href="javascript:jQuery.unblockUI();">' + labelHide + '</a>' 
							+ '<a class="checkout" href="' + ff.site_path + cartUrl + '">' + labelCheckOut + '</a>' 
						+ '</div>'
			, timeout: 7000
			, showOverlay: false
	    }); 
	} else {
		var status = "error";

		jQuery.blockUI({ 
			message: '<h1 class="cart-notify ' + status + '">' + qta + ' ' + message + '</h1>'
			, timeout: 4000
			, showOverlay: false 
	    }); 
	}
} 

function addToCart(userPath, tblSrc, elem, ref, cartName, pricelist) {
	ff.pluginLoad("jquery.fn.block", "/themes/library/plugins/jquery.blockui/jquery.blockui.js", function() {
		ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
			var qtaInput = "";
			var ssInput = "";
			var dInput = "";
			var dsInput = "";
			var dtInput = "";
			var strPriceList = "";
            var strError = "";
			
			var cartContainer = jQuery(elem).parent().parent();
			
			if(jQuery(".add-qta", cartContainer).val() === undefined) {
			    qtaInput = '&qta=1';
			} else {
                if(parseInt(jQuery(".add-qta", cartContainer).val()) == jQuery(".add-qta", cartContainer).val()) {
			        qtaInput = '&qta=' + jQuery(".add-qta", cartContainer).val();
                } else {
                    strError = "Wrong Qta";
                }
                jQuery(".add-qta", cartContainer).val("1");
            }

			if(jQuery(".add-support", cartContainer).val() === undefined)
    			ssInput = '';
			else
			    ssInput = '&ss=' + jQuery(".add-support", cartContainer).val();

			if(jQuery("INPUT.add-date", cartContainer).length) {
				jQuery("INPUT.add-date", cartContainer + ":checked").each(function() {
					if(dInput)
						dInput = dInput + "|";
					dInput = dInput + jQuery(this).val();
				});
				dInput = '&d=' + encodeURIComponent(dInput);
			} else if(jQuery("SELECT.add-date", cartContainer).length) {
				jQuery("SELECT.add-date", cartContainer).each(function() {
					if(jQuery(this).val()) {
						if(dInput)
							dInput = dInput + "|";
						dInput = dInput + jQuery(this).val();
					}
				});
				dInput = '&d=' + encodeURIComponent(dInput);
			} else if(jQuery(".add-date", cartContainer).val()) {
				dInput = '&d=' + jQuery(".add-date", cartContainer).val();
			} else {
				if(jQuery(".date-since", cartContainer).val())
    				dsInput = '&ds=' + jQuery(".date-since", cartContainer).val();
				
				if(jQuery(".date-to", cartContainer).val())
    				dtInput = '&dt=' + jQuery(".date-to", cartContainer).val(); 
			}
			
  			if(pricelist > 0) {
				strPriceList = '&pl=' + pricelist; 
 			}

			if(cartName === true)
				cartName = "1";
			else if(cartName === false)
				cartName = "";
          
           if(strError.length > 0) {
                cartResponse('', strError, 0, '', '', ''); 
            } else {
                ff.ajax.doRequest({   
                    'action': 'add'
                    , 'component': null
                    , 'url' : ff.site_path + '/user/cart/addtocart'+ userPath + '?type=' + tblSrc + qtaInput + ssInput + dInput + dsInput + dtInput + strPriceList + '&ref=' + ref + '&cart=' + cartName
                    , 'callback': function(data, value) {  
                        if(data === null) {
                            /*cartResponse('', 'Ajax error', 0, '', '', ''); */
                        } else {
                            if(data['qta'] !== undefined && data['message'] !== undefined && data['response'] !== undefined && data['cartUrl'] !== undefined && data['labelHide'] !== undefined && data['labelCheckOut'] !== undefined)
                                cartResponse(data['qta'], data['message'], data['response'], data['cartUrl'], data['labelHide'], data['labelCheckOut']); 
                        }
                    }
                });
            }
		}, false);
	}, false);

	return false;
}

function refreshItemCart(target, timer) {
	jQuery.ajax({
	    async: true,    
	    type: "GET",
	    url: window.location.pathname, 
	    data: window.location.search.replace("?", ""),
	    cache: true, 
	    success: function(item) {
	        var item_id = undefined;

			if(item.length > 0) {
	            item_id = jQuery(item).find("#" + target).attr("id");
	            if(item_id == "#")
	                item_id = "";

				if(item_id !== undefined) {
					if(item_id.length) {
	                    ff.cms.widgetInit("#" + item_id, true);
	                } else {
	                    ff.cms.widgetInit("", true);
	                }
	            }
			}
			if(item_id.length > 0) {
		        jQuery("#" + item_id).html(jQuery(item).find("#" + item_id).html());
		        
		        var callback = undefined;
		        if(jQuery(item).attr("id") !== undefined && eval("typeof " + jQuery(item).attr("id").replace(/[^a-zA-Z 0-9]+/g, "")) === "function") {
		            callback = jQuery(item).attr("id").replace(/[^a-zA-Z 0-9]+/g, "");
				} else if(eval("typeof " + item_id.replace(/[^a-zA-Z 0-9]+/g, "")) === "function") {
					callback = item_id.replace(/[^a-zA-Z 0-9]+/g, "");
				}

		        if(callback !== undefined && callback.length > 0) {
					eval(callback + "();");
					if(ff.ajax === undefined) {
						ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
							ff.cms.doEventFF('#' + callback, callback);										
						});
					} else {
						ff.cms.doEventFF('#' + callback, callback);
					}
		        }
		        
				refreshItemCartTimer(callback + "timer", parseInt(timer));
				setTimeout("refreshItemCart('" + target + "', '" + timer + "');", (parseInt(timer) * 1000));
			}
	    }
	});
}


function refreshItemCartTimer(elem, timer, actualTime) {
	if(jQuery("." + elem).hasClass(elem) !== undefined
		&& timer !== undefined && timer > 0
	) {
		if(actualTime === undefined)
			actualTime = timer;

		if(actualTime > 0) {
			jQuery("." + elem).text(actualTime);

			actualTime = actualTime - 1;

			setTimeout("refreshItemCartTimer('" + elem + "', '" + timer + "', '" + actualTime + "');", 1000);
		}
	}
}
