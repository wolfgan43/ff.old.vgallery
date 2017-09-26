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
		ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());
		ff.cms.admin.makeNewUrl();
		jQuery("#VGalleryModify_name").keyup(function(){
			ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());

			ff.cms.admin.makeNewUrl();
		});
		jQuery("#VGalleryModify_limit_level").keyup(function(){
			ff.cms.admin.checkLimitLevel(this);
			
			ff.cms.admin.displayByDep("vg-general", jQuery("#VGalleryModify_name").val() && jQuery("#VGalleryModify_limit_level").val() && jQuery("#VGalleryModify_limit_type_node").val());
		});

		jQuery("#VGalleryModify .menu-settings A").click(function() {
			jQuery("#VGalleryModify FIELDSET.settings > DIV.settings-data > DIV").hide();
			jQuery("#VGalleryModify FIELDSET.settings > DIV.settings-data > DIV." + jQuery(this).attr("rel")).fadeIn();

			jQuery("#VGalleryModify .menu-settings A").removeClass("selected");
			jQuery(this).addClass("selected");
			
			jQuery("#VGalleryModify FIELDSET.settings .settings-title").text(jQuery(this).text());
		}); 
		jQuery("#VGalleryModify .menu-settings A:first").click();
	});
});