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
 * @subpackage updater
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
error_reporting((E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED) | E_STRICT);

define("REAL_PATH", "/conf/gallery");

$limit_operation = (isset($_REQUEST["lo"]) && strlen($_REQUEST["lo"])
						? $_REQUEST["lo"]
						: 200
					);
$nowarning = (isset($_REQUEST["nowarning"]) && strlen($_REQUEST["nowarning"])
						? $_REQUEST["nowarning"]
						: false
					);
if(!function_exists("ffCommon_dirname")) {
	function ffCommon_dirname($path) 
	{
		$res = dirname($path);
		if(dirname("/") == "\\")
		    $res = str_replace("\\", "/", $res);
		
		if($res == ".")
		    $res = "";
		    
		return $res;
	}
}
       
if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH"))
	require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/path.php");

if(!defined("FF_SITE_PATH") || !defined("FF_DISK_PATH")) {
    if (strpos(php_uname(), "Windows") !== false)
        $tmp_file = str_replace("\\", "/", __FILE__);
    else
        $tmp_file = __FILE__;

    if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
	    $st_document_root =  $_SERVER["DOCUMENT_ROOT"];
		if (substr($st_document_root,-1) == "/")
		    $st_document_root = substr($st_document_root,0,-1);

		$st_site_path = str_replace($st_document_root, "", str_replace("/conf/gallery/updater/indexes.php", "", $tmp_file));
		$st_disk_path = $st_document_root . $st_site_path;
	} elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
	    $st_document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
		if (substr($st_document_root,-1) == "/")
		    $st_document_root = substr($st_document_root,0,-1);

		$st_site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/conf/gallery/updater/indexes.php", "", $_SERVER["SCRIPT_FILENAME"]));
		$st_disk_path = $st_document_root . str_replace($st_document_root, "", str_replace("/conf/gallery/updater/indexes.php", "", $tmp_file));
	} else {
		$st_disk_path = str_replace("/conf/gallery/updater/indexes.php", "", $tmp_file);
		$st_site_path = str_replace("/conf/gallery/updater/indexes.php", "", $_SERVER["SCRIPT_NAME"]);
	}

    define("FF_SITE_PATH", $st_site_path);
    define("FF_DISK_PATH", $st_disk_path);
}

if(is_object($cm)) {
	$pathInfo = $cm->path_info;
	$realPathInfo = $cm->real_path_info;

	if($pathInfo == VG_RULE_UPDATER) { 
		if (!AREA_UPDATER_SHOW_MODIFY) {
			ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
		}
	}
	
	$cm->oPage->form_method = "post";
} else {
	//$pathInfo = "";
    $realPathInfo = $_SERVER['PATH_INFO'];
    if(substr($realPathInfo, 0, 1) !== "/" && array_key_exists('REDIRECT_URL', $_SERVER)) {
        $realPathInfo    = $_SERVER['REDIRECT_URL'];

        $arr_query_string = explode("&", $_SERVER['REDIRECT_QUERY_STRING']);
        if(is_array($arr_query_string) && count($arr_query_string)) {
            foreach($arr_query_string AS $arr_query_string_value) {
                $arr_query_string_data = explode("=", $arr_query_string_value);
                if(is_array($arr_query_string_data) && count($arr_query_string_data)) {
                    $_REQUEST[$arr_query_string_data[0]] = urldecode($arr_query_string_data[1]);
                    $_GET[$arr_query_string_data[0]] = urldecode($arr_query_string_data[1]);
                }
            }
        }
    }
}

if(strpos($realPathInfo, $_SERVER["SCRIPT_NAME"]) === 0)
	$realPathInfo = substr($realPathInfo, strlen($_SERVER["SCRIPT_NAME"]));

if(!defined("MASTER_SITE"))
	require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php");

if(!defined("FF_DATABASE_NAME")) {
	require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/db.php");
	require_once(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(ffCommon_dirname(__FILE__)))) . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
	$db =  new ffDB_Sql;
} else {
	$db = ffDB_Sql::factory();	
}

if(!defined("DOMAIN_INSET"))
	define("DOMAIN_INSET", $_SERVER["HTTP_HOST"]);

//require_once(ffCommon_dirname(ffCommon_dirname(__FILE__)) . "/config/updater.php");		

if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	$json = true;
} else {
	$json = $_REQUEST["json"];
}
$execute = $_REQUEST["exec"];

$file_get_contents_master_failed = array();
$file_get_contents_slave_failed = array();

if(class_exists("ffTemplate")) {
	$drop_table_label = ffTemplate::_get_word_by_code("drop_table");
	$need_table_label = ffTemplate::_get_word_by_code("need_create_table");
	
	$delete_label = ffTemplate::_get_word_by_code("delete_index");
	$update_label = ffTemplate::_get_word_by_code("alter_index");
	$addnew_label = ffTemplate::_get_word_by_code("create_index");
	
	$wrong_source_data_label = ffTemplate::_get_word_by_code("wrong_source_data");
	$master_server_label = ffTemplate::_get_word_by_code("master_server_same_domain");
    $updater_configuration_label = ffTemplate::_get_word_by_code("updater_not_configurated");

    $file_get_contents_master_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_master_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    
    $file_get_contents_slave_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_slave_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    
    $unknown = ffTemplate::_get_word_by_code("unknown");
    $restrictions_in_effect_master = ffTemplate::_get_word_by_code("restrictions_in_effect_master");
    $restrictions_in_effect_slave = ffTemplate::_get_word_by_code("restrictions_in_effect_slave");
} else {
    $drop_table_label = "Drop table";
    $need_table_label = "Need create table";

    $delete_label = "Delete file";
    $update_label = "Update file";
    $addnew_label = "Addnew file";

    $wrong_source_data_label = "Wrong source_data";
    $master_server_label = "Master server same domain";
    $updater_configuration_label = "Updater not configurated";

    $file_get_contents_master_failed["expire_date"] = "Expire Date";
    $file_get_contents_master_failed["different_host"] = "Different Host";
    
    $file_get_contents_slave_failed["expire_date"] = "Expire Date";
    $file_get_contents_slave_failed["different_host"] = "Different Host";
    
    $unknown = "Unknown";
    $restrictions_in_effect_master = "restrictions_in_effect_master";
    $restrictions_in_effect_slave = "restrictions_in_effect_slave";
}
@set_time_limit(0);

$count_operation = 0;
define("LIMIT_OPERATION", $limit_operation);
   
if(defined("MASTER_SITE") && strlen(MASTER_SITE)) {
    if(MASTER_SITE != DOMAIN_INSET) { 
        $json_master = @file_get_contents("http://" . MASTER_SITE . REAL_PATH . "/updater/check/db.php/indexes" . "?s=" . urlencode(DOMAIN_INSET));
        if($json_master === false && strpos(MASTER_SITE, "www.") === false) {
            $json_master = @file_get_contents("http://www." . MASTER_SITE . REAL_PATH . "/updater/check/db.php/indexes" . "?s=" . urlencode("www." . DOMAIN_INSET));
        }

        if(strlen($json_master))
            $arr_master = json_decode($json_master, true);

        if(is_array($arr_master) && count($arr_master)) {
			if(defined("AUTH_USERNAME") && strlen(AUTH_USERNAME) && defined("AUTH_PASSWORD") && strlen(AUTH_PASSWORD)) {
				$context = stream_context_create(array(
					"ssl"=>array(
						"verify_peer" => false,
						"verify_peer_name" => false,
					)
					, 'http' => array(
						'header'  => "Authorization: Basic " . base64_encode(AUTH_USERNAME . ":" . AUTH_PASSWORD)
						, 'method' => 'GET'
						, 'timeout' => 120 //<---- Here (That is in seconds)						
					)
				));

	            $json_slave = @file_get_contents("http://" . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/db.php/indexes" . "?s=" . urlencode(DOMAIN_INSET), false, $context);
	            if($json_slave === false && strpos(DOMAIN_INSET, "www.") === false) {
	                $json_slave = @file_get_contents("http://www." . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/db.php/indexes" . "?s=" . urlencode("www." . DOMAIN_INSET), false, $context);
	            }
			} else {
				$context = stream_context_create(array(
					"ssl"=>array(
						"verify_peer" => false,
						"verify_peer_name" => false,
					)
					, 'http' => array(
						'method' => 'GET'
						, 'timeout' => 120 //<---- Here (That is in seconds)
					)
				));					
	            $json_slave = @file_get_contents("http://" . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/db.php/indexes" . "?s=" . urlencode(DOMAIN_INSET), false, $context);
	            if($json_slave === false && strpos(DOMAIN_INSET, "www.") === false) {
	                $json_slave = @file_get_contents("http://www." . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/db.php/indexes" . "?s=" . urlencode("www." . DOMAIN_INSET), false, $context);
	            }
			}        
                                  
            if(strlen($json_slave))
                $arr_slave = json_decode($json_slave, true);

            if(is_array($arr_slave)) {
                $operation = array();
                require(ffCommon_dirname(__FILE__) . "/check/force_drop_db.php");

                foreach($arr_slave AS $table_key => $table_value) {
                    if(!isset($arr_master[$table_key])) {
                        $force_drop = false;
                        if((array_key_exists($table_key, $db_drop) && $db_drop[$table_key] == true)) {
                            $force_drop = true;
                        }
                        if(!$force_drop && is_array($db_drop_prefix) && count($db_drop_prefix)) {
                            foreach($db_drop_prefix AS $drop_prefix_value) {
                                if(strlen($drop_prefix_value) 
                                    && strpos($table_key, $drop_prefix_value) === 0
                                ) {
                                    $force_drop = true;
                                    break;
                                }
                            }
                        }
                        if($force_drop) {   
	                        $sSQL = "DROP TABLE `" . $table_key . "`";
	                        $operation[] = array("data" => $drop_table_label . " " . $table_key, "value" => $sSQL);
	                        //elimina la tabella deprecata 
						}
                    } else {
                        foreach($arr_slave[$table_key] AS $index_key => $index_value) {
                            if(!isset($arr_master[$table_key][$index_key])) {
                                if($index_value["Key_name"] == "PRIMARY") {
                                    $sSQL = "ALTER TABLE `" . $table_key . "` DROP PRIMARY KEY";
                                } else {
                                    $sSQL = "ALTER TABLE `" . $table_key . "` DROP INDEX `" . $index_value["Key_name"] . "`";
                                }  

                                $operation[] = array("data" => $delete_label . " " . $table_key . " (" . $index_value["Key_name"] . ")", "value" => $sSQL);
                                //Elimina l'indice deprecato
                            }
                        }
                    }
                } 
                reset($arr_slave);


                foreach($arr_master AS $table_key => $table_value) {
                    if(!isset($arr_slave[$table_key])) {
                        $sSQL = "";
                        $operation[] = array("data" => $need_table_label . " " . $table_key, "value" => $sSQL);
                        //crea la tabella con tutti i campi nuovi
                    } else {
                        $tmp_master_operation = array();
                        $tmp_slave_operation = array();
                        foreach($arr_master[$table_key] AS $index_key => $index_value) {
                        	if($index_value["Key_name"] != "PRIMARY"
                        		&& $index_value["Index_type"] == "BTREE"
                        		&& $index_value["Non_unique"] == 0
                        	) {
								$indexType = "UNIQUE";
                        	} else {
								$indexType = $index_value["Index_type"];
                        	}
                        	
                            if(!isset($arr_slave[$table_key][$index_key])) {
                                if($index_value["Key_name"] == "PRIMARY") {
                                    $sSQL = "ALTER TABLE `" . $table_key . "` ADD PRIMARY KEY ( " . $index_value["Column_name"] . " )";
                                    $operation[] = array("data" => $addnew_label . " " . $table_key . " (" . $index_value["Key_name"] . ")", "value" => $sSQL);
                                } else {
                                    if(strlen($tmp_master_operation[$indexType][$index_value["Key_name"]]))
                                        $tmp_master_operation[$indexType][$index_value["Key_name"]] .= ", ";
                                    
                                    $tmp_master_operation[$indexType][$index_value["Key_name"]] .= $index_value["Column_name"];
                                }
                            } else {
                                $tmp = array_diff_assoc($arr_master[$table_key][$index_key], $arr_slave[$table_key][$index_key]); 
                                if(count($tmp)) {
                                    if($arr_slave[$table_key][$index_key]["Key_name"] == "PRIMARY") {
                                        $sSQL = "ALTER TABLE `" . $table_key . "` DROP PRIMARY KEY ";
                                        $sSQL .= ", ADD PRIMARY KEY ( " . $index_value["Column_name"] . " )";
                                        $operation[] = array("data" => $update_label . " " . $table_key . " (" . $index_value["Key_name"] . ")", "value" => $sSQL);
                                    } else {
                                        if(strlen($tmp_slave_operation[$indexType][$index_value["Key_name"]]))
                                            $tmp_slave_operation[$indexType][$index_value["Key_name"]] .= ", ";
                                        
                                        $tmp_slave_operation[$indexType][$index_value["Key_name"]] .= $index_value["Column_name"];
                                    }
                                }
                            }
                        } 
                        if(is_array($tmp_master_operation) && count($tmp_master_operation)) {
                            foreach($tmp_master_operation AS $tmp_master_key => $tmp_master_value) {
                                if(is_array($tmp_master_value) && count($tmp_master_value)) {
                                    foreach($tmp_master_value AS $sub_tmp_master_key => $sub_tmp_master_value) {
                                        switch($tmp_master_key) {
                                            case "UNIQUE":
                                                $sSQL = "ALTER TABLE `" . $table_key . "` ADD UNIQUE `" . $sub_tmp_master_key . "` ( " . $sub_tmp_master_value . " )";
                                                break;
                                            case "FULLTEXT":
                                                $sSQL = "ALTER TABLE `" . $table_key . "` ADD FULLTEXT `" . $sub_tmp_master_key . "` ( " . $sub_tmp_master_value . " )";
                                                break;
                                            default:
                                                $sSQL = "ALTER TABLE `" . $table_key . "` ADD INDEX `" . $sub_tmp_master_key . "` ( " . $sub_tmp_master_value . " )";
                                                break;
                                        }

                                        
                                        $operation[] = array("data" => $addnew_label . " " . $table_key . " (" . $index_value["Key_name"] . ")", "value" => $sSQL);
                                    }
                                }
                            }
                            //Crea l'indice univoco
                        }
                        if(is_array($tmp_slave_operation) && count($tmp_slave_operation)) {
                            foreach($tmp_slave_operation AS $tmp_slave_key => $tmp_slave_value) {
                                if(is_array($tmp_slave_value) && count($tmp_slave_value)) {
                                    foreach($tmp_slave_value AS $sub_tmp_slave_key => $sub_tmp_slave_value) {
                                        $sSQL = "ALTER TABLE `" . $table_key . "` DROP INDEX `" . $sub_tmp_slave_key . "` ";
                                         
                                        switch($tmp_slave_key) {
                                            case "UNIQUE":
                                                $sSQL .= ", ADD UNIQUE `" . $sub_tmp_slave_key . "` ( " . $sub_tmp_slave_value . " )";
                                                break;
                                            case "FULLTEXT":
                                                $sSQL .= ", ADD FULLTEXT `" . $sub_tmp_slave_key . "` ( " . $sub_tmp_slave_value . " )";
                                                break;
                                            default:
                                                $sSQL .= ", ADD INDEX `" . $sub_tmp_slave_key . "` ( " . $sub_tmp_slave_value . " )";
                                                break;
                                        }   

                                        $operation[] = array("data" => $update_label . " " . $table_key . " (" . $index_value["Key_name"] . ")", "value" => $sSQL);
                                    }
                                }
                            }
                            //Elimina+Crea l'indice 
                        }
                    }
                }
                reset($arr_master); 

                if($json) {
                    if($execute) { 
                        if(is_array($operation) && count($operation)) {
                            foreach($operation AS $operation_key => $operation_value) {
                                if(LIMIT_OPERATION > 0 && $count_operation >= LIMIT_OPERATION) {
                                    break;
                                }
                                $sSQL = $operation_value["value"];
                                if(strlen($sSQL))
                                    $db->execute($sSQL);

                                unset($operation[$operation_key]);
                                
                                $count_operation++;
                            } 
                            reset($operation);
                        }
                        echo json_encode(array("record" => array_values($operation), "limit" => $limit_operation));
                    } else {
                        echo json_encode(array("record" => $operation));
                    }
                    exit;
                }

                $sSQL = "";
                if(is_array($operation) && count($operation)) {
                    foreach($operation AS $operation_key => $operation_value) {
                        if(strlen($sSQL)) 
                            $sSQL .= " UNION ";
                        $sSQL .= " ( SELECT '0' AS `check`
                                , " . $db->toSql($operation_key, "Number") . " AS `ID`
                                , " . $db->toSql($operation_value["data"], "Text") . " AS `operation`
                                , " . $db->toSql($operation_value["value"], "Text") . " AS `sql` ) ";
                    }
					if(class_exists("ffGrid")) {
	                    $oGrid = ffGrid::factory($cm->oPage);
	                    $oGrid->id = "UpdaterCheck";
	                    $oGrid->title = ffTemplate::_get_word_by_code("updater_title");
	                    $oGrid->source_SQL = $sSQL . " [WHERE] [ORDER]";
	                    $oGrid->order_default = "ID";
	                    $oGrid->display_edit_bt = false;
	                    $oGrid->display_edit_url = false;
	                    $oGrid->display_delete_bt = false;
	                    $oGrid->display_new = false;
	                    $oGrid->addEvent("on_do_action", "UpdaterCheck_on_do_action");
	                    $oGrid->use_paging = false;
	                    $oGrid->user_vars["operations"] = $operation;

	                    // Campi chiave
	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "ID";
	                    $oField->base_type = "Number";
	                    $oGrid->addKeyField($oField);

	                    // Campi visualizzati
	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "check";
	                    $oField->label = ffTemplate::_get_word_by_code("updater_check");
	                    $oField->control_type = "checkbox";
	                    $oField->extended_type = "Boolean";
	                    $oField->checked_value = new ffData("1");
	                    $oField->unchecked_value = new ffData("0");
	                    $oGrid->addContent($oField);

	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "operation";
	                    $oField->label = ffTemplate::_get_word_by_code("updater_operation");
	                    $oGrid->addContent($oField);

	                    $oField = ffField::factory($cm->oPage);
	                    $oField->id = "sql";
	                    $oField->label = ffTemplate::_get_word_by_code("updater_sql");
	                    $oGrid->addContent($oField);

	                    $oButton = ffButton::factory($cm->oPage);
	                    $oButton->id = "check_all";
	                    $oButton->action_type = "gotourl";
	                    $oButton->url = "#";
	                    $oButton->label = ffTemplate::_get_word_by_code("updater_check_all");
	                    $oButton->aspect = "link";
	                    $oButton->properties["onclick"] = 'if(jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\') == false) { jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\', \'checked\'); } else { jQuery(\'INPUT[type=\\\'checkbox\\\']\').attr(\'checked\', \'\'); }';
	                    $oGrid->addActionButton($oButton);
	                    
	                    $oButton = ffButton::factory($cm->oPage);
	                    $oButton->id = "update";
	                    $oButton->action_type = "submit";
	                    $oButton->frmAction = "update";
	                    $oButton->aspect = "link";
	                    $oButton->label = ffTemplate::_get_word_by_code("updater_execute");
	                    $oGrid->addActionButton($oButton);

	                    $oButton = ffButton::factory($cm->oPage);
	                    $oButton->id = "cancel";
	                    $oButton->action_type = "gotourl";
	                    $oButton->url = "[RET_URL]";
	                    $oButton->aspect = "link";
	                    $oButton->label = ffTemplate::_get_word_by_code("updater_cancel");
	                    $oGrid->addActionButton($oButton);

	                    $cm->oPage->addContent($oGrid);
					} else {
						print_r($operation);
						exit;
					}
                } else {
                    if(function_exists("ffRedirect")) {
                        ffRedirect(urldecode($_REQUEST["ret_url"]));
					} else {
                        header("Location: " . urldecode($_REQUEST["ret_url"]));
                        exit;
					}
                }
            } else {
                $strError = $wrong_source_data_label . " (" . (!is_array($arr_slave) && isset($file_get_contents_slave_failed[$json_slave]) ? $file_get_contents_slave_failed[$json_slave] : (strlen($json_slave) ? $json_slave : $restrictions_in_effect_slave)) . ")";
            }
        } else {
            $strError = $wrong_source_data_label . " (" . (!is_array($arr_master) && isset($file_get_contents_master_failed[$json_master]) ? $file_get_contents_master_failed[$json_master] : (strlen($json_master) ? $json_master : $restrictions_in_effect_master)) . ")";
        }    
    } else {
    	if(!$nowarning)
        	$strError = $master_server_label;
    }
} else {
	if(!$nowarning)
    	$strError = $updater_configuration_label;
}

if($strError) {
    if($json) {
        echo json_encode(array("record" => array(), "error" => $strError));
        exit;
    } elseif(is_object($cm)) {
        $cm->oPage->fixed_pre_content = $strError;
    } else {
    	echo $strError;
    	exit;
    }
} else {
	echo json_encode(array("record" => array()));
	exit;	
}

function UpdaterCheck_on_do_action($component, $action) {
    $operations = $component->user_vars["operations"];
    
    $db = ffDB_Sql::factory();

    foreach($component->recordset_keys AS $key => $value) {
        $sSQL = $operations[$value["ID"]]["value"];
        $db->execute($sSQL);
    }
    
    if(is_array($operations) && count($operations))
        ffRedirect($_SERVER["REQUEST_URI"]);
    else 
        ffRedirect($component->parent[0]->ret_url);
}  
  
