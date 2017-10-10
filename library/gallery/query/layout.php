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
 function query_layout() {
 
 }

	function query_layout_by_smart_url($smart_url, $ID_domain = null) {
		$globals = ffGlobals::getInstance("gallery");
		$db = ffDB_Sql::factory();
		
		if(!$ID_domain)
			$ID_domain = $globals->ID_domain;	
	
		$sSQL = "
                SELECT  
                    layout.*
                    , layout_path.class AS block_class
                    , layout_path.default_grid AS block_default_grid
                    , layout_path.grid_md AS block_grid_md
                    , layout_path.grid_sm AS block_grid_sm
                    , layout_path.grid_xs AS block_grid_xs
                    , layout_path.fluid AS block_fluid
                    , layout_path.wrap AS block_wrap
                    , layout_type.name AS type
                    , layout_type.description AS type_description
                    , IF(layout_type.`class` = '', layout.value, layout_type.`class`) AS type_class
                    , layout_type.`group` AS type_group
                    , layout_type.`multi_id` AS multi_id
                    , layout_type.`tpl_path` AS tpl_path
                    , layout_location.name AS location
                    , layout_location.ID_layer AS ID_layer
                    , layout_path.path AS real_path
		            , layout_type.frequency AS frequency
		            , layout_path.visible AS visible
                FROM layout
                    INNER JOIN layout_type ON layout_type.ID = layout.ID_type 
                    INNER JOIN layout_location ON layout_location.ID = layout.ID_location 
                    INNER JOIN layout_path ON layout_path.ID_layout = layout.ID
                WHERE layout.smart_url = " . $db->toSql($smart_url) . "
	                AND (layout.ID_domain = " . $db->toSql($ID_domain, "Number") . "
	                    " . ($ID_domain > 0
                    		? " OR layout.ID_domain = 0"
                    		: ""
	                    ) . "
	                )
                ORDER BY layout.ID";

        return $sSQL;
	}
