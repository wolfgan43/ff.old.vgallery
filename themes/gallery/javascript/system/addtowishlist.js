function cartResponse(qta, message, response, cartUrl, labelHide, labelCheckOut) {
	if(qta === null) {
		qta = "";
	}

	if(response) {
		var status = "success";

		jQuery.blockUI({ 
			message: '<div class="cart-notify ' + status + '"><h1>' + qta + ' ' + message + '</h1><a class="hide" href="javascript:jQuery.unblockUI();">' + labelHide + '</a><a class="checkout" href="' + ff.site_path + cartUrl + '">' + labelCheckOut + '</a></div>'
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

function addToWishlist(userPath, tblSrc) {
	ff.pluginLoad("jquery.fn.block", "/themes/library/plugins/jquery.blockui/jquery.blockui.js", function() {
		ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
            var strError = "";
			
           if(strError.length > 0) {
                wishlistResponse('', strError, 0, ''); 
            } else {
                ff.ajax.doRequest({   
                    'action': 'add'
                    , 'component': null
                    , 'url' : ff.site_path + '/user/cart/addtocart'+ userPath + '?type=' + tblSrc + '&qta=1&cart=1'
                    , 'callback': function(data, value) {  
                        if(data === null) {
                            /*cartResponse('', 'Ajax error', 0, '', '', ''); */
                        } else {
                        	/*console.log(data);*/
                            if(data['message'] !== undefined && data['response'] !== undefined && data['labelHide'] !== undefined && data['labelElem'] !== undefined)
                                cartResponse(data['qta'], data['message'], data['response'], data['cartUrl'], data['labelHide'], data['labelCheckOut']); 
                        }
                    }
                });
            }
		}, false);
	}, false);

	return false;
}
