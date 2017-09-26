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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
	function set_field_extended_type($component = null, $sSQL_Where = "", $widget = "activecomboex") {
		$cm = cm::getInstance();
		$db = ffDB_Sql::factory();
		
		if($widget == "activecomboex")
			$widget = "actex";
		
	    $component->base_type = "Number";
	    $component->source_SQL = "SELECT extended_type.ID
	                                , IFNULL(
	                                    (SELECT " . FF_PREFIX . "international.description
	                                        FROM " . FF_PREFIX . "international
	                                        WHERE " . FF_PREFIX . "international.word_code = CONCAT('ext_field_', extended_type.name)
	                                            AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
	                                            AND " . FF_PREFIX . "international.is_new = 0
	                                        ORDER BY " . FF_PREFIX . "international.description
	                                        LIMIT 1
	                                    )
	                                    , extended_type.name
	                                ) AS name
	                                , IFNULL(
	                                    (SELECT " . FF_PREFIX . "international.description
	                                        FROM " . FF_PREFIX . "international
	                                        WHERE " . FF_PREFIX . "international.word_code = CONCAT('ext_field_', extended_type.`group`)
	                                            AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
	                                            AND " . FF_PREFIX . "international.is_new = 0
	                                        ORDER BY " . FF_PREFIX . "international.description
	                                        LIMIT 1
	                                    )
	                                    , extended_type.`group` 
	                                ) AS `group` 
	                                , limit_by_data_type
	                            FROM extended_type
	                            WHERE 1 
	                                $sSQL_Where
	                            [AND] [WHERE]
	                            ORDER BY `group`, name";
	    $component->required = true;
	    $component->actex_group = "group";
	    $component->actex_update_from_db = true;
	    $component->widget = $widget;

		
		return $component;
	}