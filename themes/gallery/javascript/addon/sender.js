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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
ff.cms.addon.sender = function(component) {
	jQuery(".hide", component).hide();
	jQuery("a.send, a.re-send", component).click(function() {
		var elem = jQuery(this);
		
		jQuery(elem).addClass("wait");
		jQuery.post(ff.site_path + "/srv/sender" + jQuery(component).find(".reference").val()
			, { 
				name : jQuery(component).find(".name input").val()
				, email : jQuery(component).find(".mail input").val()
			}
			, function(data) {
				jQuery(elem).removeClass("wait");
				if(jQuery(elem).hasClass("send")) {
					jQuery(component).find(".re-send").fadeIn();
					jQuery(elem).hide();
				}
				if(data.status == "ok") {
					jQuery(component).html(data.error);
				} else {
					jQuery(component).find(".error").html(data.error);
					jQuery(component).find(".to").slideDown();
				}
			}
		);
		
	});
};