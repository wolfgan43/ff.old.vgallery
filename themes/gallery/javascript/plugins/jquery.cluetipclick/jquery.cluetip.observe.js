ff.cms.fn.cluetipclick = function(targetid) {
	var targetid = targetid;
	if(targetid.length > 0)
		targetid = targetid + " ";
	/*css*/

	ff.pluginLoad("jquery.fn.rightClick", "/themes/library/plugins/jquery.rightclick/jquery.rightclick.js", function() {
		ff.pluginLoad("jquery.cluetip", "/themes/library/plugins/jquery.cluetip/jquery.cluetip.js", function() {
			/*var arrCluetip = new Array();
			jQuery(targetid + 'input[type="hidden"].cluetip').each(function() {
                if(jQuery(this).closest("div,li,td").attr("id") !== undefined)
				    if(arrCluetip[jQuery(this).closest("div,li,td").attr("id")] === undefined)
                        arrCluetip[jQuery(this).closest("div,li,td").attr("id")] = true;
                    else
                        arrCluetip[jQuery(this).closest("div,li,td").attr("id")] = false;
                        

			});*/

			jQuery(targetid + '.admin-bar[data-admin]').cluetip({
				activation: 'rightClick', 
				attribute: 'data-admin',        
				cluetipClass: 'jtip', 
			    width: '100%', 
				titleAttribute:   'title',  
				positionBy: 'mouse',
				showTitle: false,
				arrows: false,
				dropShadow: false,
				hoverIntent: true,
				waitImage : true,
				sticky: true,
				mouseOutClose: true,
				closePosition: 'title',
				closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.cluetipclick/images/cross.png" alt="close" />',
				fx: {             
				    open:       'fadeIn', /* can be 'show' or 'slideDown' or 'fadeIn'*/
				    openSpeed:  '1000'
				},
				hoverIntent: {    
				    sensitivity:  1,
				    interval:     400,
				    timeout:      1
				}, 
				onActivate:       function(e) {
				    return true;
				}
			});

			/*
			jQuery(targetid + 'input[type="hidden"].cluetip').each(function() {
                if(arrCluetip[jQuery(this).closest("div,li,td").attr("id")] !== undefined && arrCluetip[jQuery(this).closest("div,li,td").attr("id")]) {
					jQuery(this).closest("div,li,td").attr("admin", jQuery(this).val());
				    jQuery(this).closest("div,li,td").cluetip({
				        activation: 'rightClick', 
				        attribute: 'admin',        
				        cluetipClass: 'jtip', 
			      		width: '100%', 
				        titleAttribute:   'title',  
						positionBy: 'mouse',
						showTitle: false,
						arrows: false,
						dropShadow: false,
						hoverIntent: true,
						waitImage : true,
						sticky: true,
						mouseOutClose: true,
						closePosition: 'title',
						closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.cluetipclick/images/cross.png" alt="close" />',
						fx: {             
				            open:       'fadeIn',
				            openSpeed:  '1000'
				        },
						hoverIntent: {    
				            sensitivity:  1,
				            interval:     400,
				            timeout:      1
				        }, 
				        onActivate:       function(e) {
				            return true;
				        }
				    });
				} else {
					if(jQuery(this).next().is("img.nivoslider")) {
						jQuery(this).next().wrap('<a />');
					}

					jQuery(this).next().attr("admin", jQuery(this).val());
				    jQuery(this).next().cluetip({
				        activation: 'rightClick', 
				        attribute: 'admin',        
				        cluetipClass: 'jtip', 
			      		width: '100%', 
				        titleAttribute:   'title',  
						positionBy: 'mouse',
						showTitle: false,
						arrows: false,
						dropShadow: false,
						hoverIntent: true,
						waitImage : true,
						sticky: true,
						mouseOutClose: true,
						closePosition: 'title',
						closeText: '<img src="' + ff.site_path + '/themes/gallery/javascript/plugin/jquery.cluetipclick/images/cross.png" alt="close" />',
						fx: {             
				            open:       'fadeIn', 
				            openSpeed:  '1000'
				        },
						hoverIntent: {    
				            sensitivity:  1,
				            interval:     400,
				            timeout:      1
				        }, 
				        onActivate:       function(e) {
				            return true;
				        }
				    });
				}
				jQuery(this).remove();
			});*/
		}, false);
	}, false);
};