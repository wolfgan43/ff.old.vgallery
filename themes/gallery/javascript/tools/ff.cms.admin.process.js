/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
jQuery(function() {
	jQuery(".notify-handler span").text(jQuery(".quick-notify").length);
	if(jQuery(".quick-notify").length > 0 ) {
		jQuery(".notify-handler span").addClass("full");
	} else {
		jQuery(".notify-handler span").removeClass("full");
	}
	//jQuery(".all-notifies").hide();

	jQuery(".notify-handler").click(function(){
		if(jQuery(this).hasClass("active")) {
			jQuery(this).removeClass("active");
			jQuery(".all-notifies").addClass("hidden");
		} else {
			jQuery(this).addClass("active");
			jQuery(".all-notifies").removeClass("hidden");
		}
		
		//jQuery(".all-notifies").fadeToggle();
	});

	/* chiusura notifiche forzata */
	jQuery(".notify-close").click(function() {
		var elem = jQuery(this).parent();
	    var title = jQuery(this).attr("rel");
	    var url = "http://" + window.location.hostname + ff.site_path + "/srv/notify";
	    if(title !== undefined) {
	        url = url + "?title=" + encodeURIComponent(title) + "&frmAction=hide";
	    
	        jQuery.ajax({
	           type: "GET",
	           url: url.substr(0, url.indexOf("?")),
	           data: url.substr(url.indexOf("?") + 1),
	           success: function(msg){
	           		jQuery(elem).fadeOut(function() {
	           			var countNotifies = parseInt(jQuery(".notify-handler span").text()) - 1;
	  	           		jQuery(".notify-handler span").text(countNotifies);   
	  	           		if(!countNotifies > 0) {
	  	           			jQuery(".all-notifies").remove();
	  	           			jQuery(".notify-handler span").removeClass("full");
	  	           		}      			
	           		});
	           }
	         });
	        
	    }
	});
	/* close all */
	jQuery(".notify-close-all").click(function() {
		var elem = jQuery(this).parent();
	    var url = "http://" + window.location.hostname + ff.site_path + "/srv/notify";
	        url = url + "?frmAction=hideall";
	    
	        jQuery.ajax({
	           type: "GET",
	           url: url.substr(0, url.indexOf("?")),
	           data: url.substr(url.indexOf("?") + 1),
	           success: function(msg){
	             jQuery(elem).fadeOut(function(){
	             		jQuery(".notify-handler span").text("0");   
	             		jQuery(".all-notifies").remove();		
	             		jQuery(".notify-handler span").removeClass("full");
	             });
	           }
	         });
	});
	
	/* cambio lingua */
	jQuery("#language-chooser").hover(function(){
		jQuery(".choose-lang-list").fadeIn();
	},function(){
		jQuery(".choose-lang-list").hide();
	});
	/* helper */

});