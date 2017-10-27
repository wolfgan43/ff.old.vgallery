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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!AREA_ROUTING_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();

if(isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "UPDATE `cache_page_redirect`
                    SET `status` = " . $db->toSql($_REQUEST["setvisible"]) . "
                    	, `last_update` = " . $db->toSql(new ffData(time(), "Number")) . "
                    WHERE 
                        `cache_page_redirect`.`ID` = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
   // $strError = make_routing_table_file();
    
    die(ffCommon_jsonenc(array("close" => false, "refresh" => true, "resources" => array("RedirectModify")), true));
}
/*
if($_REQUEST["keys"]["ID"] > 0) {
	$sSQL = "SELECT * FROM cache_page_redirect WHERE ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$all_phone = $db->getField("all_phone", "Number", true)
		$section_blocks = $db->getField("section_blocks", "Text", true);
		$layout_blocks = $db->getField("layout_blocks", "Text", true);
		$ff_blocks = $db->getField("ff_blocks", "Text", true);
		
		if($section_blocks == "" && $layout_blocks == "" && $ff_blocks == "")
			$check_system_control = false;
		else
			$check_system_control = true;
	}
}*/
  
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "RedirectModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("redirect_modify_title");
$oRecord->src_table = "cache_page_redirect";
$oRecord->addEvent("on_do_action", "RedirectModify_on_do_action");
$oRecord->addEvent("on_done_action", "RedirectModify_on_done_action");
$oRecord->insert_additional_fields["status"] = new ffData("1", "Number");

//$oRecord->allow_delete = !$check_system_control;
//$oRecord->user_vars["check_system_control"] = $check_system_control;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
      
$oField = ffField::factory($cm->oPage);
$oField->id = "header";
$oField->label = ffTemplate::_get_word_by_code("redirect_modify_header");
$oField->extended_type = "Selection";
$oField->multi_pairs = ffGetHTTPStatus(); 
$oField->required = true;
$oField->default_value = new ffData("301");
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "src_host";
$oField->label = ffTemplate::_get_word_by_code("redirect_modify_src_host");
//$oField->required = true;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "src_path";
$oField->label = ffTemplate::_get_word_by_code("redirect_modify_src_path");
$oField->required = true;
$oRecord->addContent($oField); 


$oField = ffField::factory($cm->oPage);
$oField->id = "destination";
$oField->label = ffTemplate::_get_word_by_code("redirect_modify_destination");
$oField->required = true;
$oRecord->addContent($oField); 


$arrBrowser = array();
$arrPlatform = array();
$arrPhone = array();
$arrTablet = array();
$arrUtilities = array();

$oField = ffField::factory($cm->oPage);
$oField->id = "user_agent_mobile";
$oField->label = ffTemplate::_get_word_by_code("redirect_modify_mobile");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "user_agent_tablet";
$oField->label = ffTemplate::_get_word_by_code("redirect_modify_tablet");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField);


if(check_function("class.browser")) 
{
	$refl = new ReflectionClass('Browser');
	$refl_browser = $refl->getConstants();
	if(is_array($refl_browser) && count($refl_browser)) {
		foreach($refl_browser AS $refl_browser_key => $refl_browser_value) {
			if(strpos($refl_browser_key, "BROWSER_") === 0) {
				$browser[$refl_browser_value] = substr($refl_browser_key, strlen("BROWSER_"));
			} elseif(strpos($refl_browser_key, "PLATFORM_") === 0) {
				$platform[$refl_browser_value] = substr($refl_browser_key, strlen("PLATFORM_"));
			}
		}
	}
	
	if(is_array($browser) && count($browser)) {
		ksort($browser);
		$browser_def["IE"] = array(6,7,8,9,10,11);
		foreach($browser AS $browser_key => $browser_value) {
			if(array_key_exists($browser_value, $browser_def)) {
				foreach($browser_def[$browser_value] AS $browser_ver) {
					$arrBrowser[] = array(new ffData($browser_key . "-" . $browser_ver), new ffData(ffTemplate::_get_word_by_code($browser_key) . " " . $browser_ver));
				}
			} else {
				$arrBrowser[] = array(new ffData($browser_key), new ffData(ffTemplate::_get_word_by_code($browser_key)));
			}
		}

		if(is_array($arrBrowser) && count($arrBrowser)) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "user_agent_browser";
			$oField->label = ffTemplate::_get_word_by_code("redirect_modify_browser");
			$oField->extended_type = "Selection";
			$oField->multi_pairs = $arrBrowser;
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
			$oField->widget = "checkgroup";
			$oRecord->addContent($oField); 			
		}
	}

	if(is_array($platform) && count($platform)) {
		ksort($platform);
		foreach($platform AS $platform_key => $platform_value) {
			$arrPlatform[] = array(new ffData($platform_key), new ffData(ffTemplate::_get_word_by_code($platform_key)));
		}
		
		if(is_array($arrPlatform) && count($arrPlatform)) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "user_agent_platform";
			$oField->container_class = "user-agent-platform";
			$oField->label = ffTemplate::_get_word_by_code("redirect_modify_platform");
			$oField->extended_type = "Selection";
			$oField->multi_pairs = $arrPlatform;
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("not_set");
			$oField->widget = "checkgroup";
			$oRecord->addContent($oField); 			
		}
	}
}

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);   
          
function RedirectModify_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
    	switch($action) {
    		case "insert":
    		case "update":

    			//$component->form_fields["source_path"]->setValue($component->form_fields["source_path"]->getValue());

    			/*$sSQL = "SELECT * FROM `cache_page_redirect` WHERE `source_path` = " . $db->toSql($component->form_fields["source_path"]->value) . " AND ID <> " . $db->toSql($component->key_fields["ID"]->value);
    			$db->query($sSQL);
    			if($db->numRows()) {
					$component->tplDisplayError(ffTemplate::_get_word_by_code("url_sitemap_not_unic"));
					return true;
				}*/
    			break;
    		default:
		}
    }
}

function RedirectModify_on_done_action($component, $action) {
    if(strlen($action)) {
    	switch($action) {
    		case "insert":
    		case "update":
    		case "confirmdelete":
    			$src_host = $component->form_fields["src_host"]->getValue();
    			if(!$src_host)
    				$src_host == DOMAIN_INSET;
    		
    			@unlink(CM_CACHE_PATH . "/redirect/" . $src_host . ".php");
    			@unlink(CM_CACHE_PATH . "/redirect/" . $src_host . ".rule.php");
    			break;
    			//$strError = make_routing_table_file();
    			break;
    		
    		default:
		}
    }
}


function make_routing_table_file() {
	$db = ffDB_Sql::factory();
	
	$real_file = FF_THEME_DIR . "/" . FRONTEND_THEME . "/routing_table.xml";
	
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
		        if($real_ftp_path === NULL && defined("FTP_PATH") && strlen(FTP_PATH)) {
		            if(@ftp_chdir($conn_id, FTP_PATH)) {
		                $real_ftp_path = FTP_PATH;
		            } 
		        }
				if($real_ftp_path !== NULL) {
					$tpl = ffTemplate::factory(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/contents");
					$tpl->load_file("routing_table.tpl", "main");

					$tpl->set_var("site_path", FF_SITE_PATH);
					$tpl->set_var("theme_inset", THEME_INSET);

					$sSQL = "SELECT cache_page_redirect.*
								, cache_page.user_path AS user_path  
							FROM cache_page_redirect
								INNER JOIN cache_page ON cache_page.ID = cache_page_redirect.ID_cache_page
							WHERE cache_page_redirect.status > 0
							ORDER BY cache_page_redirect.source_path";
					$db->query($sSQL);
					if($db->nextRecord()) {
						$count_user_agent = 0;
						do {
							$source_path = $db->getField("source_path", "Text", true);
							if(strpos($source_path, "?") !== false) {
								$source_query = substr($source_path, strpos($source_path, "?") + 1);
								$source_path = substr($source_path, 0, strpos($source_path, "?"));
							} else {
								$source_query = "";
							}

							$source_path = str_replace("*", "(.*)", $source_path);
							
							$revert = $db->getField("revert", "Number", true);
							
							$all_phone = $db->getField("all_phone", "Number", true);
							$all_tablet = $db->getField("all_tablet", "Number", true);
							
							$user_agent_browser = $db->getField("user_agent_browser", "Text", true);
							$user_agent_platform = $db->getField("user_agent_platform", "Text", true);
							$user_agent_phone = $db->getField("user_agent_phone", "Text", true);
							$user_agent_tablet = $db->getField("user_agent_tablet", "Text", true);
							$user_agent_utilities = $db->getField("user_agent_utilities", "Text", true);
							
							$header = $db->getField("header", "Text", true);
							$host = $db->getField("host", "Text", true);
							
							if(strlen($host)) {
								if(strpos($host, "http://") === 0) { 
									$host = substr($host, strlen("http://"));
								}
								if(strpos($host, "https://") === 0) { 
									$host = substr($host, strlen("https://"));
								}
                                if(strpos($host, "//") === 0) { 
                                    $host = substr($host, strlen("//"));
                                }
							}
							$user_path = $db->getField("user_path", "Text", true);
							

							$tpl->set_var("source_path", "^" . $source_path);

							if(strlen($user_agent_browser)) {
								$count_user_agent++;
								$tpl->set_var("browser", $user_agent_browser);
								$tpl->parse("SezRuleUserAgentBrowser", false);
							} else {
								$tpl->set_var("SezRuleUserAgentBrowser", "");
							}
							if(strlen($user_agent_platform)) {
								$count_user_agent++;
								$tpl->set_var("platform", $user_agent_platform);
								$tpl->parse("SezRuleUserAgentPlatform", false);
							} else {
								$tpl->set_var("SezRuleUserAgentPlatform", "");
							}
							if($all_phone) {
								$count_user_agent++;
								$tpl->set_var("phone", $user_agent_phone);
								$tpl->parse("SezRuleUserAgentPhone", false);
							} else {
								$tpl->set_var("SezRuleUserAgentPhone", "");
							}
							if($all_tablet) {
								$count_user_agent++;
								$tpl->set_var("tablet", $user_agent_tablet);
								$tpl->parse("SezRuleUserAgentTablet", false);
							} else {
								$tpl->set_var("SezRuleUserAgentTablet", "");
							}
							if(strlen($user_agent_utilities)) {
								$count_user_agent++;
								$tpl->set_var("utilities", $user_agent_utilities);
								$tpl->parse("SezRuleUserAgentUtilities", false);
							} else {
								$tpl->set_var("SezRuleUserAgentUtilities", "");
							}	

							if($count_user_agent) {
								$tpl->parse("SezRuleUserAgent", false);
							} else {
								$tpl->set_var("SezRuleUserAgent", "");
							}

							$tpl->set_var("source_query", $source_query);
							if($revert) {
								$tpl->parse("SezRevert", false);
							} else {
								$tpl->set_var("SezRevert", "");
							}
							
							$tpl->set_var("header", $db->getField("header", "Text", true));
							$tpl->set_var("user_path", $user_path);
							if(strlen($host)) {
								$tpl->set_var("host", $host);
								$tpl->parse("SezHost", false);
							} else {
								$tpl->set_var("SezHost", "");
							}
							$tpl->parse("SezRule", true);
						} while($db->nextRecord());	
					}
							
					$content = $tpl->rpparse("main", false);
					
					$handle = @tmpfile();
					@fwrite($handle, $content);
					@fseek($handle, 0);
					if(!@ftp_fput($conn_id, $real_ftp_path . $real_file, $handle, FTP_ASCII)) {
						$strError = ffTemplate::_get_word_by_code("unable_write_file");
					} else {
						if(@ftp_chmod($conn_id, 0777, $real_ftp_path . $real_file) === false) {
							if(@chmod(FF_DISK_PATH . $real_file, 0777) === false) {
								$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
							}
						}
					}
					@fclose($handle);

					$file_chmod = "644";
					if(substr(decoct( @fileperms(FF_DISK_PATH . $real_file)), 3) != $file_chmod) {
						$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
						if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $real_file) === false) {
						    if(@chmod(FF_DISK_PATH . $real_file, $file_chmod) === false) {
				            	$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
							}
						}
					}
				} else {
					$strError = ffTemplate::_get_word_by_code("ftp_unavailable_root_dir");
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
	
	return $strError;
}