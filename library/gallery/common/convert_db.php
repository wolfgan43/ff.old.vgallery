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
function convert_db($collation, $character_set, $database_name = null, $db = null) {
	$strError = false;
		
	if($database_name === null)
		$database_name = FF_DATABASE_NAME;

	if($db === null)
		$db = ffDB_Sql::factory();
		
	if(!strlen($database_name))
		$strError = "database name required";
			
	if(!$strError) {	
		$sSQL = "SHOW CHARACTER SET LIKE " . $db->toSql($character_set);
		$db->query($sSQL);
		if($db->numRows()) {
			$sSQL = "SHOW COLLATION LIKE " . $db->toSql($collation);
			$db->query($sSQL);
			if(!$db->numRows()) {
				$strError = "invalid collation";	
			}
		} else {
			$strError = "invalid character_set";
		}
	}
	if(!$strError) {
		$sSQL = "ALTER DATABASE `" . $db->toSql($database_name, "Text", false) . "` DEFAULT CHARACTER SET " . $db->toSql($character_set, "Text", false) . " COLLATE " . $db->toSql($collation, "Text", false);
		$db->execute($sSQL);
		
		$sSQL = "SHOW TABLES";
		$db->query($sSQL);
		if($db->nextRecord()) {
			$table = array();
			do {
				//die($db->getField("Tables_in_" . FF_DATABASE_NAME, "Text", true));
				$table[$db->getField("Tables_in_" . $database_name, "Text", true)] = array();
			} while($db->nextRecord());
			ksort($table);
			reset($table);
		}

		foreach($table AS $table_key => $table_value) {
			$sSQL = "ALTER TABLE `" . $db->toSql($table_key, "Text", false) . "` ENGINE = MyISAM DEFAULT CHARACTER SET " . $db->toSql($character_set, "Text", false) . " COLLATE " . $db->toSql($collation, "Text", false);
			$db->execute($sSQL);
			
			$sSQL = "DESCRIBE `" . $db->toSql($table_key, "Text", false) . "`";
			$db->query($sSQL);
			if($db->nextRecord()) {
				$sSQL_change = "";
				do {
					if(strpos(strtolower($db->getField("Type", "Text", true)), "char") !== false || strpos(strtolower($db->getField("Type", "Text", true)), "text") !== false) {
						if(strlen($sSQL_change))
							$sSQL_change .= ", ";

						$sSQL_change .= " CHANGE `" . $db->toSql($db->getField("Field", "Text", true), "Text", false) . "` `" 
							. $db->toSql($db->getField("Field", "Text", true), "Text", false) . "` " 
							. $db->toSql($db->getField("Type", "Text", true), "Text", false) 
							. " CHARACTER SET " . $db->toSql($character_set, "Text", false) 
							. " COLLATE " . $db->toSql($collation, "Text", false) 
							. ($db->getField("Null", "Text", true) == "NO" 
		                        ? " NOT NULL " 
		                        : " NULL "
		                    );
	                        
	                        
	                    
					}
				} while($db->nextRecord());
				if(strlen($sSQL_change))
					$db->execute("ALTER TABLE `" . $db->toSql($table_key, "Text", false) . "` " . $sSQL_change);
			}
		}
	}	
	
	return $strError;
}
