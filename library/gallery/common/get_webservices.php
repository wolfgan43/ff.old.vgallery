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
  function get_webservices($group = null, &$oPage = null) 
  {
  	  static $webservices = null;

	  if($webservices === null) {
	   	  $db = ffDB_Sql::factory();
		  $webservices = array();

		  $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains_fields.* 
				FROM " . CM_TABLE_PREFIX . "mod_security_domains
					INNER JOIN " . CM_TABLE_PREFIX . "mod_security_domains_fields ON " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . CM_TABLE_PREFIX . "mod_security_domains.ID 
				WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db->toSql(DOMAIN_NAME, "Text");
		  $db->query($sSQL);
          $recordset                                                            = $db->getRecordset();
          foreach($recordset AS $record) {
              $webservices[strtolower($record["group"])][$record["field"]]      = $record["value"];
          }
      }

	  if($oPage)
	  	  process_webservices($webservices);

	  if(strlen($group) && is_array($webservices) && array_key_exists($group, $webservices)) {
		  return $webservices[$group];
	  } else {
	      return $webservices;  
	  }
  }
  
  function process_webservices($webservices) {
    $cm = cm::getInstance();
    $globals = ffGlobals::getInstance("gallery");

  	foreach($webservices AS $service_name => $actual_srv) {
  		if($actual_srv["enable"] && is_file(VG_WEBSERVICES_PATH . "/" . $service_name . "/index." . FF_PHP_EXT) && filesize(VG_WEBSERVICES_PATH . "/" . $service_name . "/index." . FF_PHP_EXT)) {
            
            $res = $cm->doEvent("vg_on_webservices", array($service_name, $actual_srv));
            $rc = end($res);
			if ($rc !== null)
                $actual_srv = $rc;
            
  			require_once(VG_WEBSERVICES_PATH . "/" . $service_name . "/index." . FF_PHP_EXT);
		}
  	}
}