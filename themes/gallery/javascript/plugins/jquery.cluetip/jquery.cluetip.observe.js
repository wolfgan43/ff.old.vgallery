ff.cms.fn.cluetip = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";	
	/*css*/
	ff.pluginLoad("jquery.cluetip", "/themes/library/plugins/jquery.cluetip/jquery.cluetip.js", function() {
			//var arrCluetip = new Array();
           /* jQuery(targetid + '.admin-bar[data-admin]').each(function() {
                if(jQuery(this).closest("div,li,td").attr("id") !== undefined)
                    if(arrCluetip[jQuery(this).closest("div,li,td").attr("id")] === undefined)
                        arrCluetip[jQuery(this).closest("div,li,td").attr("id")] = true;
                    else
                        arrCluetip[jQuery(this).closest("div,li,td").attr("id")] = false;
                        

            });*/

			jQuery(targetid + '.admin-bar[data-admin]').cluetip({
					activation: 'hover',  
					attribute: 'data-admin',        
					cluetipClass: 'jtip', 
					width: '100%', 
					titleAttribute:   'title',  
					positionBy: 'auto', 
					topOffset : 0,
					leftOffset: 0,
					showTitle: false,
					arrows: true,
					dropShadow: false,
					hoverIntent: true,
					waitImage : false,
					sticky: true,
					mouseOutClose: true,
					/*delayedClose: 2000, */
					closePosition: 'title',
					closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.cluetip/images/cross.png" alt="close" />',
					fx: {             
					    open:       'fadeIn', /* can be 'show' or 'slideDown' or 'fadeIn'*/
					    openSpeed:  ''
					},
					hoverIntent: {    
					    sensitivity:  1,
					    interval:     500,
					    timeout:      0
					}, 
					onActivate:       function(e) {
					    return true;
					}
				});            
            /*
            jQuery(targetid + '.admin-bar[data-admin]').each(function() {
                if(arrCluetip[jQuery(this).closest("div,li,td").attr("id")] !== undefined && arrCluetip[jQuery(this).closest("div,li,td").attr("id")]) {
				jQuery(this).closest("div,li,td").cluetip({
					activation: 'hover',  
					attribute: 'admin',        
					cluetipClass: 'jtip', 
					width: '100%', 
					titleAttribute:   'title',  
					positionBy: 'auto', 
					topOffset : 0,
					leftOffset: 0,
					showTitle: false,
					arrows: true,
					dropShadow: false,
					hoverIntent: true,
					waitImage : false,
					sticky: true,
					mouseOutClose: true,
					closePosition: 'title',
					closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.cluetip/images/cross.png" alt="close" />',
					fx: {             
					    open:       'fadeIn',
					    openSpeed:  ''
					},
					hoverIntent: {    
					    sensitivity:  1,
					    interval:     500,
					    timeout:      0
					}, 
					onActivate:       function(e) {
					    return true;
					}
				});
			} else{
				if(jQuery(this).next().is("img.nivoslider")) {
					jQuery(this).next().wrap('<a />');
				}

				jQuery(this).cluetip({
					activation: 'hover',  
					attribute: 'admin',        
					cluetipClass: 'jtip', 
					width: '100%', 
					titleAttribute:   'title',  
					positionBy: 'auto', 
					topOffset : 0,
					leftOffset: 0,
					showTitle: false,
					arrows: true,
					dropShadow: false,
					hoverIntent: true,
					waitImage : false,
					sticky: true,
					mouseOutClose: true,
					closePosition: 'title',
					closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.cluetip/images/cross.png" alt="close" />',
					fx: {             
					    open:       'fadeIn', 
					    openSpeed:  ''
					},
					hoverIntent: {    
					    sensitivity:  1,
					    interval:     500,
					    timeout:      0
					}, 
					onActivate:       function(e) {
					    return true;
					}
				});
			}
			jQuery(this).remove();
		});*/
	}, false);
};