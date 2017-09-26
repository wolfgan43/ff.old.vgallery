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
    function get_field_default($tbl_default, $ID_tbl_default, $tbl_actual = null, $ID_tbl_actual = null, $field_default = array(), $field_actual = array())
    {
        $db = ffDB_Sql::factory();
        
        

        static $arrControlType = null;
        static $arrControlTypeRev = null;
        static $arrExtType = null;
        static $arrExtTypeRev = null;

        if($arrControlType === null) {
            $sSQL = "SELECT ID, name FROM check_control WHERE 1";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $arrControlType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
                    $arrControlTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
                } while($db->nextRecord());
            }
        }
        if($arrExtType === null) {
            $sSQL = "SELECT ID, name FROM extended_type WHERE 1";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $arrExtType[$db->getField("name", "Text", true)] = $db->getField("ID", "Number", true);
                    $arrExtTypeRev[$db->getField("ID", "Number", true)] = $db->getField("name", "Text", true);
                } while($db->nextRecord());
            }        
        }

        if($tbl_default && $ID_tbl_default > 0) 
        {
            $sSQL = "SELECT `" . $tbl_default . "`.* 
                        FROM `" . $tbl_default . "`
                        WHERE `" . $tbl_default . "`.ID = " . $db->toSql($ID_tbl_default, "Number");
            $db->query($sSQL);
            if($db->nextRecord()) {    
                $field_default = $db->record; 
                if(array_key_exists("ID_check_control", $field_default))
                    $field_default["control_type"] = $arrControlTypeRev[$field_default["ID_check_control"]];
                if(array_key_exists("ID_extended_type", $field_default)) {
                    if(!$field_default["ID_extended_type"])
                        $field_default["ID_extended_type"] = $arrExtType["String"];

                    $field_default["extended_type"] = $arrExtTypeRev[$field_default["ID_extended_type"]];
                }
            }
        }
            
        if($tbl_actual && $ID_tbl_actual > 0)
        {
              $sSQL = "SELECT `" . $tbl_actual . "`.* 
                        FROM `" . $tbl_actual . "`
                        WHERE `" . $tbl_actual . "`.ID = " . $db->toSql($ID_tbl_actual, "Number");
            $db->query($sSQL);
            if($db->nextRecord()) {    
                $field_actual = $db->record;
                if(array_key_exists("ID_check_control", $field_actual))
                    $field_actual["control_type"] = $arrControlTypeRev[$field_actual["ID_check_control"]];
                if(array_key_exists("ID_extended_type", $field_actual))
                    $field_actual["extended_type"] = $arrExtTypeRev[$field_actual["ID_extended_type"]];                
            }
        }
        
        return array(
            "default" => $field_default
            , "actual" => $field_actual
            , "extended_type" => $arrExtType
            , "extended_type_rev" => $arrExtTypeRev
            , "control_type" => $arrControlType
            , "control_type_rev" => $arrControlTypeRev
        );
    }
