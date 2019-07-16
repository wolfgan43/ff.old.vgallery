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
require_once(__DIR__ . "/check/common.php");

$params = updater_get_params($cm);

$limit_operation = (isset($_REQUEST["lo"]) && strlen($_REQUEST["lo"])
	? $_REQUEST["lo"]
	: 200
);
$nowarning = (isset($_REQUEST["nowarning"]) && strlen($_REQUEST["nowarning"])
	? $_REQUEST["nowarning"]
	: false
);
if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
	$json = true;
} else {
	$json = $_REQUEST["json"];
}
$execute = $_REQUEST["exec"];

$db = $params["db"];
$realPathInfo = $params["user_path"];

$file_get_contents_master_failed = array();
$file_get_contents_slave_failed = array();

if(class_exists("ffTemplate")) {
	$delete_label = ffTemplate::_get_word_by_code("delete_file");
	$update_label = ffTemplate::_get_word_by_code("update_file");
	$addnew_label = ffTemplate::_get_word_by_code("addnew_file");
	
	$creation_dir_label = ffTemplate::_get_word_by_code("creation_failure_directory");
	$upload_file_label = ffTemplate::_get_word_by_code("upload_failure_file");
	$delete_file_label =  ffTemplate::_get_word_by_code("delete_failure_file");
	$delete_dir_label =  ffTemplate::_get_word_by_code("delete_failure_dir");
	$ftp_unavaible_label = ffTemplate::_get_word_by_code("ftp_unavaible_root_dir");
	$ftp_access_label = ffTemplate::_get_word_by_code("ftp_access_denied");
	$ftp_connection_label = ffTemplate::_get_word_by_code("ftp_connection_failure");
	$ftp_configuration_label = ffTemplate::_get_word_by_code("ftp_not_configutated");
	
	$wrong_source_data_label = ffTemplate::_get_word_by_code("wrong_source_data");
	$server_is_repository_label = ffTemplate::_get_word_by_code("server_is_repository");
    $external_no_available_label = ffTemplate::_get_word_by_code("external_no_available");

    $file_get_contents_master_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_master_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    $file_get_contents_master_failed["external_no_available"] = $external_no_available_label;

    $file_get_contents_slave_failed["expire_date"] = ffTemplate::_get_word_by_code("expire_date");
    $file_get_contents_slave_failed["different_host"] = ffTemplate::_get_word_by_code("different_host");
    $file_get_contents_slave_failed["external_no_available"] = $external_no_available_label;

    $unknown = ffTemplate::_get_word_by_code("unknown");
    $restrictions_in_effect_master = ffTemplate::_get_word_by_code("restrictions_in_effect_master");
    $restrictions_in_effect_slave = ffTemplate::_get_word_by_code("restrictions_in_effect_slave");
} else {
	$delete_label = "Delete file";
	$update_label = "Update file";
	$addnew_label = "Addnew file";
	
	$creation_dir_label = "Creation failure directory";
	$upload_file_label = "Upload failure file";
	$delete_file_label = "Delete failure file";
	$delete_dir_label =  "Delete failure dir";
	$ftp_unavaible_label = "Ftp unavaible root dir";
	$ftp_access_label = "Ftp access denied";
	$ftp_connection_label = "Ftp connection failure";
	$ftp_configuration_label = "Ftp not configutated";
	
	$wrong_source_data_label = "Wrong source_data";
	$server_is_repository_label = "Server is repository";
    $external_no_available_label = "External no available";

    $file_get_contents_master_failed["expire_date"] = "Expire Date";
    $file_get_contents_master_failed["different_host"] = "Different Host";
    $file_get_contents_master_failed["external_no_available"] = $external_no_available_label;

    $file_get_contents_slave_failed["expire_date"] = "Expire Date";
    $file_get_contents_slave_failed["different_host"] = "Different Host";
    $file_get_contents_slave_failed["external_no_available"] = $external_no_available_label;

    $unknown = "Unknown";
    $restrictions_in_effect_master = "restrictions_in_effect_master";
    $restrictions_in_effect_slave = "restrictions_in_effect_slave";
}
@set_time_limit(0);

$json = $_REQUEST["json"];
$execute = $_REQUEST["exec"];

$count_operation = 0;
define("LIMIT_OPERATION", $limit_operation);

$external = check_external($realPathInfo, $db);
if(is_array($external) && count($external)) {
    $master_site = $external["domain"];
    $repository_path = $external["path"];
    $repository_status = $external["status"];

	if($master_site != DOMAIN_INSET) {
		$external_path = $realPathInfo;
		
	    $json_master = @file_get_contents("http://" . $master_site . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET));
        if($json_master === false && strpos($master_site, "www.") === false) {
            $json_master = @file_get_contents("http://www." . $master_site . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET));
        }

	    if(strlen($json_master))
	        $arr_master = json_decode($json_master, true);

	    if(is_array($arr_master) && count($arr_master)) {
			$json_slave = file_post_contents(
				"http://" . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode(DOMAIN_INSET)
				, null
				, (defined("AUTH_USERNAME")
				? AUTH_USERNAME
				: null
			)
				, (defined("AUTH_PASSWORD")
				? AUTH_PASSWORD
				: null
			)
				, "GET"
				, "120"
			);
			if($json_slave === false && strpos(DOMAIN_INSET, "www.") === false) {
				$json_slave = file_post_contents(
					"http://www." . DOMAIN_INSET . FF_SITE_PATH . REAL_PATH . "/updater/check/external.php" . $external_path . "?s=" . urlencode("www." . DOMAIN_INSET)
					, null
					, (defined("AUTH_USERNAME")
					? AUTH_USERNAME
					: null
				)
					, (defined("AUTH_PASSWORD")
					? AUTH_PASSWORD
					: null
				)
					, "GET"
					, "120"
				);
			}

	        if(strlen($json_slave))
	            $arr_slave = json_decode($json_slave, true);

	        if(is_array($arr_slave)) {
	            $operation = array();
	            
	            krsort($arr_slave);
	            foreach($arr_slave AS $file_key => $file_value) {
	                if(!isset($arr_master[$file_key])) {
	                    $strAction = $file_key;
	                    $operation[] = array("data" => $delete_label
	                                                , "action" => "delete"
	                                                , "value" => $strAction
	                                                , "size" => $file_value
	                                            ); 
	                } else {
	                    if($arr_master[$file_key] != $file_value) {
	                        $strAction = $file_key;
	                        $operation[] = array("data" => $update_label
	                                                    , "action" => "update"
	                                                    , "value" => $strAction
	                                                    , "size" => $file_value
	                                                ); 
	                    }
	                }
	            }
	            reset($arr_slave);
	            
	            ksort($arr_master);
	            foreach($arr_master AS $file_key => $file_value) {
	                if(!isset($arr_slave[$file_key])) {
	                    $strAction = $file_key;
	                    $operation[] = array("data" => $addnew_label
	                                                , "action" => "addnew"
	                                                , "value" => $strAction
	                                                , "size" => $file_value
	                                            ); 
	                } else {
	                    if($arr_master[$file_key] != $file_value) {
	                        $strAction = $file_key;
	                        $operation[] = array("data" => $update_label
	                                                    , "action" => "update"
	                                                    , "value" => $strAction
	                                                    , "size" => $file_value
	                                                ); 
	                    }
	                }
	            }
	            reset($arr_master);
	            
	            if($json) {
	                if($execute) {
	                    if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
	                        // set up basic connection
	                        /*$conn_id = @ftp_connect(DOMAIN_INSET);
                            if($conn_id === false && strpos(DOMAIN_INSET, "www.") === false) {
                                $conn_id = @ftp_connect("www." . DOMAIN_INSET);
                            }*/
							$conn_id = @ftp_connect("localhost");
					        if($conn_id === false)
        						$conn_id = @ftp_connect("127.0.0.1");
							if($conn_id === false)
        						$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);
							
	                        if($conn_id !== false) {
	                            // login with username and password
	                            if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) {
	                                $local_path = FF_DISK_PATH;
	                                $part_path = "";
	                                $real_ftp_path = NULL;
	                                
	                                foreach(explode("/", $local_path) AS $curr_path) {
	                                    if(strlen($curr_path)) {
	                                        $ftp_path = str_replace($part_path, "", $local_path);
	                                        if(@ftp_chdir($conn_id, $ftp_path)) {
	                                            $real_ftp_path = $ftp_path;
	                                            break;
	                                        } 

	                                        $part_path .= "/" . $curr_path;
	                                    }
	                                }
									if($real_ftp_path === null) {
										if(@ftp_chdir($conn_id, "/conf/gallery/install")) {
											$real_ftp_path = "/";
										}
									}
                                    	
	                                if($real_ftp_path !== NULL) {
                                    	$criticalError = false;
                                    	$arrOperationError = array();
	                                    foreach($operation AS $key => $value) {
	                                        if(LIMIT_OPERATION > 0 && $count_operation >= LIMIT_OPERATION) {
	                                            break;
	                                        }
	                                        
	                                        switch($operation[$key]["action"]) {
	                                            case "addnew":
	                                                $part_path = "";
	                                                foreach(explode("/", dirname($operation[$key]["value"])) AS $tmp_path) {
	                                                    if(strlen($tmp_path)) {
	                                                        $part_path .= "/" . $tmp_path;
	                                                        
	                                                        if(!is_dir(FF_DISK_PATH . $part_path)) {
	                                                            if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
	                                                                $arrOperationError[] = array("data" => $creation_dir_label, "value" => $real_ftp_path . $part_path);
	                                                        }
	                                                    }
	                                                }

	                                                if($operation[$key]["size"] < 0) {
	                                                    if(!@ftp_mkdir($conn_id, $real_ftp_path . $operation[$key]["value"]))
	                                                        $arrOperationError[] = array("data" => $creation_dir_label, "value" => $real_ftp_path . $operation[$key]["value"]);
	                                                } else {
	                                                    $ret = @ftp_nb_put($conn_id
	                                                                        , $real_ftp_path . $operation[$key]["value"]
	                                                                        , "http://" . $master_site . REAL_PATH . "/updater/check/external.php" . str_replace("%2F", "/", rawurlencode($operation[$key]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
	                                                                        , FTP_BINARY
	                                                                        , FTP_AUTORESUME
	                                                                    );

	                                                    while ($ret == FTP_MOREDATA) {
	                                                       
	                                                       // Do whatever you want
	                                                       // Continue uploading...
	                                                       $ret = @ftp_nb_continue($conn_id);
	                                                    }
	                                                    if ($ret != FTP_FINISHED) {
	                                                       $arrOperationError[] = array("data" => $upload_file_label, "value" => $real_ftp_path . $operation[$key]["value"]);
	                                                    }
	                                                }
	                                                break;
	                                            case "update":
	                                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operation[$key]["value"], FF_DISK_PATH)) {
	                                                    $arrOperationError[] = array("data" => $delete_file_label, "value"  => $real_ftp_path . $operation[$key]["value"]);
														$criticalError = true;
														break;
													}
	                                                $ret = @ftp_nb_put($conn_id
	                                                                    , $real_ftp_path . $operation[$key]["value"]
	                                                                    , "http://" . $master_site . REAL_PATH . "/updater/check/external.php" . str_replace("%2F", "/", rawurlencode($operation[$key]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
	                                                                    , FTP_BINARY
	                                                                    , FTP_AUTORESUME
	                                                                );

	                                                while ($ret == FTP_MOREDATA) {
	                                                   
	                                                   // Do whatever you want
	                                                   // Continue uploading...
	                                                   $ret = @ftp_nb_continue($conn_id);
	                                                }
	                                                if ($ret != FTP_FINISHED) {
	                                                   $arrOperationError[] = array("data" => $upload_file_label, "value" => $real_ftp_path . $operation[$key]["value"]);
	                                                }
	                                                break;
	                                            case "delete":
	                                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operation[$key]["value"], FF_DISK_PATH))
	                                                    $arrOperationError[] = array("data" => $delete_dir_label, "value" => $real_ftp_path . $operation[$key]["value"]);

	                                                break;
	                                            default:            
	                                        }

											if($criticalError) {
                                            	if($count_operation) {
                                                    $strError = "";
                                                    $strInfo = "Max Connections: " . $count_operation;
                                                    $limit_operation = $count_operation;
                                                    $arrOperationError = array_values($operation);
												}
                                            	break;
                                            } 

	                                        unset($operation[$key]);
	                                        
	                                        $count_operation++; 
	                                    }
	                                    reset($operation);
	                                } else {
	                                    $strError = $ftp_unavaible_label;
	                                }
	                            } else {
	                                $strError = $ftp_access_label;
	                            }
	                        } else {
	                            $strError = $ftp_connection_label;
	                        }
	                        // close the connection and the file handler
	                        @ftp_close($conn_id);
	                    } else {
	                        $strError = $ftp_configuration_label;
	                    }

	                    if(strlen($strError)) {
                            echo json_encode(array("record" => $arrOperationError, "error" => $strError, "limit" => $limit_operation));
                        } else {
                            echo json_encode(array("record" => array_values($operation), "info" => $strInfo, "limit" => $limit_operation));
	                    }
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
	                            , " . $db->toSql(new ffData($operation_key, "Number")) . " AS `ID`
	                            , " . $db->toSql(new ffData($operation_value["data"], "Text")) . " AS `operation`
	                            , " . $db->toSql(new ffData($operation_value["value"], "Text")) . " AS `subject` ) ";
	                }
	                reset($operation);
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
		                $oGrid->use_paging = true;
		                $oGrid->default_records_per_page = 200;
						$oGrid->user_vars["operations"] = $operation;
						$oGrid->user_vars["master_site"] = $master_site;
						$oGrid->user_vars["creation_dir_label"] = $creation_dir_label;
						$oGrid->user_vars["upload_file_label"] = $upload_file_label;
						$oGrid->user_vars["delete_file_label"] = $delete_file_label;

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
		                $oField->id = "subject";
		                $oField->label = ffTemplate::_get_word_by_code("updater_subject");
		                $oGrid->addContent($oField);

		                $oButton = ffButton::factory($cm->oPage);
		                $oButton->id = "check_all";
		                $oButton->action_type = "gotourl";
		                $oButton->url = "#";
	                        $oButton->aspect = "link";
		                $oButton->label = ffTemplate::_get_word_by_code("updater_check_all");
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
			$strError = $server_is_repository_label;
	}
} else {
	if(!$nowarning)
		$strError = $external_no_available_label;
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
	$strError 				= "";
	$operations 			= $component->user_vars["operations"];
	$master_site 			= $component->user_vars["master_site"];
	$creation_dir_label 	= $component->user_vars["creation_dir_label"];
	$upload_file_label 		= $component->user_vars["upload_file_label"];
	$delete_file_label 		= $component->user_vars["delete_file_label"];

    if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
        // set up basic connection
        /*$conn_id = @ftp_connect(DOMAIN_INSET);
        if($conn_id === false && strpos(DOMAIN_INSET, "www.") === false) {
            $conn_id = @ftp_connect("www." . DOMAIN_INSET);
        }*/
		$conn_id = @ftp_connect("localhost");
	    if($conn_id === false)
        	$conn_id = @ftp_connect("127.0.0.1");
		if($conn_id === false)
	        $conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

        if($conn_id !== false) {
            // login with username and password
            if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) {
                $local_path = FF_DISK_PATH;
                $part_path = "";
                $real_ftp_path = NULL;
                
                foreach(explode("/", $local_path) AS $curr_path) {
                    if(strlen($curr_path)) {
                        $ftp_path = str_replace($part_path, "", $local_path);
                        if(@ftp_chdir($conn_id, $ftp_path)) {
                            $real_ftp_path = $ftp_path;
                            break;
                        } 

                        $part_path .= "/" . $curr_path;
                    }
                }
                    
                if($real_ftp_path !== NULL) {
                    foreach($component->recordset_keys AS $key => $value) {
                        switch($operations[$value["ID"]]["action"]) {
                            case "addnew":
                                $part_path = "";
                                foreach(explode("/", dirname($operations[$value["ID"]]["value"])) AS $tmp_path) {
                                    if(strlen($tmp_path)) {
                                        $part_path .= "/" . $tmp_path;
                                        
                                        if(!is_dir(FF_DISK_PATH . $part_path)) {
                                            if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
                                                $strError .= $creation_dir_label . " (" . $real_ftp_path . $part_path . ")" . "<br>";
                                        }
                                    }
                                }

                                if($operations[$value["ID"]]["size"] < 0) {
                                    if(!@ftp_mkdir($conn_id, $real_ftp_path . $operations[$value["ID"]]["value"]))
                                        $strError .= $creation_dir_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                } else {
                                    $ret = @ftp_nb_put($conn_id
                                                        , $real_ftp_path . $operations[$value["ID"]]["value"]
                                                        , "http://" . $master_site . "/updater/check/external" . str_replace("%2F", "/", rawurlencode($operations[$value["ID"]]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
                                                        , FTP_BINARY
                                                        , FTP_AUTORESUME
                                                    );

                                    while ($ret == FTP_MOREDATA) {
                                       
                                       // Do whatever you want
                                       // Continue uploading...
                                       $ret = @ftp_nb_continue($conn_id);
                                    }
                                    if ($ret != FTP_FINISHED) {
                                       $strError .= $upload_file_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                    }
                                }
                                break;
                            case "update":
                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operations[$value["ID"]]["value"], FF_DISK_PATH))
                                    $strError .= $delete_file_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                
                                $ret = @ftp_nb_put($conn_id
                                                    , $real_ftp_path . $operations[$value["ID"]]["value"]
                                                    , "http://" . $master_site . "/updater/check/external" . str_replace("%2F", "/", rawurlencode($operations[$value["ID"]]["value"])) . "?s=" . urlencode(DOMAIN_INSET)
                                                    , FTP_BINARY
                                                    , FTP_AUTORESUME
                                                );

                                while ($ret == FTP_MOREDATA) {
                                   
                                   // Do whatever you want
                                   // Continue uploading...
                                   $ret = @ftp_nb_continue($conn_id);
                                }
                                if ($ret != FTP_FINISHED) {
                                   $strError .= $upload_file_label . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";
                                }
                                break;
                            case "delete":
                                if(!ftp_purge_dir($conn_id, $real_ftp_path, $operations[$value["ID"]]["value"], FF_DISK_PATH))
                                    $strError .= ffTemplate::_get_word_by_code("delete_failure") . " (" . $real_ftp_path . $operations[$value["ID"]]["value"] . ")" . "<br>";

                                break;
                            default:            
                        }
                    }
                } else {
                    $strError = ffTemplate::_get_word_by_code("ftp_unavaible_root_dir");
                }
            } else {
                $strError = ffTemplate::_get_word_by_code("ftp_access_denied");
            }
        } else {
            $strError = ffTemplate::_get_word_by_code("ftp_connection_failure");
        }
        // close the connection and the file handler
        @ftp_close($conn_id);
    } else {
        $strError = ffTemplate::_get_word_by_code("ftp_not_configutated");
    }
    if(strlen($strError)) {
        $component->displayError($strError);
    } else {
        if(is_array($operations) && count($operations))
            ffRedirect($_SERVER["REQUEST_URI"]);
        else 
            ffRedirect($component->parent[0]->ret_url);
    }
}

function check_external($path, $db) {
	$sSQL = "SELECT updater_externals.domain, updater_externals.path, updater_externals.status FROM updater_externals WHERE LOCATE(updater_externals.path, " . $db->toSql($path, "Text") . ") > 0  AND updater_externals.status = " . $db->toSql("1", "Text");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$external["domain"] = $db->getField("domain", "Text", true);
		$external["path"] = $db->getField("path", "Text", true);
		$external["status"] = $db->getField("status", "Text", true);
		
		return $external;
	}
	return false;
}
