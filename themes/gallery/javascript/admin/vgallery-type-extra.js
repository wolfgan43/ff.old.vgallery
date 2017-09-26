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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
jQuery(function() {
	ff.pluginAddInit("ff.cms.admin", function() {
		/*displayGroupButton();
		jQuery(document).on("click", "#VGalleryTypeModify_advanced_group[type=checkbox]", function() {
			displayGroupButton();
		});
		
		function displayGroupButton() {
			jQuery("#VGalleryTypeModify_vgalleryTypeGroup").hide();
			if(jQuery("#VGalleryTypeModify_advanced_group[type=checkbox]").is(":checked")) {
				jQuery("#VGalleryTypeModify_vgalleryTypeGroup").show();
			}
		}*/
		jQuery("#VGalleryTypeModify .showall.thumb").click(function() {
			var iconClass = jQuery("i", this).attr("class");
			if(iconClass.indexOf("minus") >= 0) {
				iconClass = iconClass.replace("minus", "plus");
			} else {
				iconClass = iconClass.replace("plus", "minus");
			}
			jQuery("i", this).attr("class", iconClass);
			jQuery(this).closest("FIELDSET.thumb").find("TR.hideable").toggleClass("hidden");
		});
		jQuery("#VGalleryTypeModify .showall.detail").click(function() {
			var iconClass = jQuery("i", this).attr("class");
			if(iconClass.indexOf("minus") >= 0) {
				iconClass = iconClass.replace("minus", "plus");
			} else {
				iconClass = iconClass.replace("plus", "minus");
			}
			jQuery("i", this).attr("class", iconClass);
			jQuery(this).closest("FIELDSET.detail").find("TR.hideable").toggleClass("hidden");
		});
	
		ff.cms.admin.makeNewUrl();
	
		jQuery("#VGalleryTypeModify_name").keyup(function(){
			ff.cms.admin.makeNewUrl();
		});
	});
});