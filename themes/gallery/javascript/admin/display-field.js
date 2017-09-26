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
 function updateCustomize(id) {
    var pos = "";
    var nopos = "";
    var $table = jQuery(".ffGrid", jQuery("#Customize"));
    
	$table.find("tbody tr").each(function () {
		if($(this).find("input[type=checkbox]").is(":checked")) {
			if(pos)
				pos += ",";
			
			pos += jQuery(this).data("sort_id");
		} else {
			if(nopos)
				nopos += ",";
			
			nopos += jQuery(this).data("sort_id");
		}
	});
    document.getElementById("frmAction").value = "Customize_update";

	if(id) {
		ff.ffPage.dialog.doAction(id, "update", "Customize_", undefined, undefined, [{"name" : "pos", "value" : pos}, {"name" : "nopos", "value" : nopos}]);
	} else {
		document.getElementById("frmMain").action = window.location.href + "&pos=" + encodeURIComponent(pos) + "&nopos=" + encodeURIComponent(nopos);
		document.getElementById("frmMain").submit();
	}
    
}