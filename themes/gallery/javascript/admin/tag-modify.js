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
 function getTraslation(elem) {
        var name = jQuery(elem).val();
        if(name.length)
        {
            var fromLang = jQuery(elem).attr("id").replace("autocomplete_TagsModify_content_", "");
            jQuery(".tags.translate INPUT.ui-autocomplete-input").each(function() {  
                if(!jQuery(this).val())
                {
                    var idDest = jQuery(this).attr("id");
                    var idHidden = idDest.replace("autocomplete_", "");
                    var destLang = idDest.replace("autocomplete_TagsModify_content_", "");

                	ff.cms.admin.translate(name, fromLang, destLang, function(translated) {
                        jQuery("#" + idDest).val(translated);
                        jQuery("#" + idHidden).val(translated);
                	});
                }
            });
        }
}
jQuery(".tags.translate INPUT.ui-autocomplete-input").first().load(function() {
    console.log(jQuery(".tags.translate INPUT.ui-autocomplete-input").first().val());
    console.log("hey");
    jQuery(".tags.translate INPUT.ui-autocomplete-input").first().change();
});