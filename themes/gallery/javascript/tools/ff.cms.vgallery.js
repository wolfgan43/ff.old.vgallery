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
if (!ff.cms) ff.cms = {};

ff.cms.vgallery = (function () {

    var that = { /* publics*/
        __init : false
        , "init": function() {
            this.fullclick();
            this.filter();
            this.__init = true;
        }
        , "fullclick": function(elem) {
            if(!elem)
                elem = jQuery("body");

            jQuery(".vg-item[data-fullclick]", elem).click(function(e) {
                var target = e.target || e.srcElement;
                if(jQuery(this).attr("data-fullclick") && !jQuery(target).is("a") && !jQuery(target).closest("a").length)  {
                    window.location.href = jQuery(this).attr("data-fullclick");
                    return false;
                }
            }).css("cursor", "pointer");
        }
        , "filter": function(elem) {
            if(!elem)
                elem = jQuery("body");

            jQuery(".vg-item[data-ffl]", elem).click(function(e) {
                //ff.cms.getBlock();
            
            });
        }
    };

    jQuery(function() {
        that.init();
    });    

    return that;
})();

//jQuery(function() {
//});

/*
jQuery(function() {
	alert("ciao");
	checkEnableField();

	jQuery("INPUT.enable-field").click(function() {
		console.log("salve");
		checkEnableField();
	});
	
});

function checkEnableField() {
	jQuery(".enable-field").each(function() {
		
		var selected_class = jQuery(this).parents("TH").attr("class");
		if(jQuery(this).is(":not(:checked)")) {
			console.log("TD." + selected_class + "-field");
			jQuery("TD." + selected_class + "-field").attr('readonly','readonly');
		} else{
			console.log("mah " + "TD." + selected_class + "-field");
			jQuery("TD." + selected_class + "-field").attr('readonly','');
		}
	});
}*/