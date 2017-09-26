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
		ff.cms.admin.makeNewUrl();

		jQuery("#VGalleryFieldModify_name").keyup(function(){
			ff.cms.admin.makeNewUrl();
		});
		
		jQuery(".enable-field").each(function() {
			checkEnableField(jQuery(this));
		});

		jQuery("INPUT.enable-field").click(function() {
			checkEnableField(jQuery(this));
		});
		
		jQuery(".user-permission input[type=checkbox]").click(function() {
			var id = $(this).attr("class");
			setUserPermission(id);
		});
		
		jQuery(document).on("change keypress", "TD.disabled INPUT, TD.disabled SELECT", function(){
			enableField(jQuery(this));
		});
	});
});

function enableField(field) {
	var eq = field.closest("TD").index();
	field.closest("TABLE").find("TH:eq(" + eq + ") .enable-field").trigger("click");
}

function checkEnableField(field) {
	var selected_class = field.parents("TH").attr("class");
	if(!jQuery(field).is(":checked")) {
		jQuery("TD." + selected_class).addClass("disabled");
	} else{
		jQuery("TD." + selected_class).removeClass("disabled");
	}
}

function setUserPermission(id) {
	var list = "";
	$("input[type=checkbox]." + id + ":checked").each(function(){
		if(list.length)
			list += ",";
		list += $(this).val();
	});
	jQuery("#" + id).val(list);
}