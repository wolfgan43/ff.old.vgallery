<?php
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
function MD_maps_on_before_parse_row($component) {
    $component->row_class = ffCommon_url_rewrite($component->db[0]->getField("vgallery_name", "Text", true)) . " mrk-" . $component->db[0]->getField("ID", "Number", true);
    $component->row_properties = array("data-rel" => $component->db[0]->getField("ID", "Number", true)); 
}

function module_maps_tabs($arrVgallery) {
    $tpl = ffTemplate::factory(get_template_cascading($globals->user_path, "marker_tab.html", "/modules/maps", ffCommon_dirname(__FILE__)));
    $tpl->load_file("marker_tab.html", "main");
    
    array_unshift($arrVgallery, ffTemplate::_get_word_by_code("all"));
    
    foreach($arrVgallery AS $name => $value) {
        $tpl->set_var("vgallery_name_norm", ffCommon_url_rewrite($value));
        $tpl->set_var("vgallery_name", $value);
        $tpl->parse("SezVgallerySelector", true);
    }
    
    return $tpl->rpparse("main", false);
}