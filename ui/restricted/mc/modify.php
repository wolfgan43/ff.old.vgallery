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

if (!AREA_UPDATER_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$db = ffDB_Sql::factory();
// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "MCDomainModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("mc_domain_modify");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_security_domains";
$oRecord->addEvent("on_do_action", "MCDomainModify_on_do_action");

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "nome";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_name"); 
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_status");
$oField->base_type = "Number";
$oField->extended_type = "Selection";
$oField->multi_pairs = array(
    array(new ffData("0"), new ffData(ffTemplate::_get_word_by_code("status_disactive")))
    , array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("status_active")))
);
$oField->default_value = new ffData("1");
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "version";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_version");
$oField->control_type = "label";
$oField->default_value = new ffData("7");
$oField->data_type = ""; 
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oRecord->addContent(null, true, "whois"); 
$oRecord->groups["whois"] = array(
                                         "title" => ffTemplate::_get_word_by_code("mc_domain_modify_whois")
                                         , "cols" => 1
                                      );

$oField = ffField::factory($cm->oPage);
$oField->id = "registrar_name";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_registrar_name");
$oField->control_type = "label";
$oRecord->addContent($oField, "whois");
                                      
$oField = ffField::factory($cm->oPage);
$oField->id = "creation_date";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_creation_date");
$oField->base_type = "Date";
$oField->control_type = "label";
$oRecord->addContent($oField, "whois");

$oField = ffField::factory($cm->oPage);
$oField->id = "update_date";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_update_date");
$oField->base_type = "Date";
$oField->control_type = "label";
$oRecord->addContent($oField, "whois");

$oField = ffField::factory($cm->oPage);
$oField->id = "expiration_date";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_expiration_date");
$oField->base_type = "Date";
$oField->widget = "datechooser";
$oField->default_value = new ffdata((date("Y", time()) + 1) . "-" . date("m-d", time()), "Date", FF_SYSTEM_LOCALE);
$oField->required = true;
$oRecord->addContent($oField, "whois");

$oField = ffField::factory($cm->oPage);
$oField->id = "ip_address";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_ip_address");
if(isset($_REQUEST["keys"]["ID"]))
    $oField->required = true;
$oRecord->addContent($oField, "whois");


$oRecord->addContent(null, true, "access"); 
$oRecord->groups["access"] = array(
                                         "title" => ffTemplate::_get_word_by_code("mc_domain_modify_access")
                                         , "cols" => 1
                                      );

$oField = ffField::factory($cm->oPage);
$oField->id = "ftp_user";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_ftp_user");
$oRecord->addContent($oField, "access");

$oField = ffField::factory($cm->oPage);
$oField->id = "ftp_password";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_ftp_password");
$oField->extended_type = "Password";
$oRecord->addContent($oField, "access");

$oField = ffField::factory($cm->oPage);
$oField->id = "confirmpassword";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_ftp_password_confirm");
$oField->extended_type = "Password";
$oField->compare = "ftp_password";
$oRecord->addContent($oField, "access"); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ftp_path";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_ftp_path");
$oRecord->addContent($oField, "access");


$oRecord->addContent(null, true, "billing"); 
$oRecord->groups["billing"] = array(
                                         "title" => ffTemplate::_get_word_by_code("mc_domain_modify_billing")
                                         , "cols" => 1
                                      );

                                      
$sSQL = "SELECT anagraph_categories.ID
				, anagraph_categories.name
				, anagraph_categories.limit_by_groups 
		FROM anagraph_categories
		ORDER BY anagraph_categories.name";
$db->query($sSQL);
if($db->nextRecord()) {
	do {
		$limit_by_groups = $db->getField("limit_by_groups")->getValue();
		if(strlen($limit_by_groups)) {
			$limit_by_groups = explode(",", $limit_by_groups);
			
			if(count(array_intersect($user_permission["groups"], $limit_by_groups))) {
				if(strlen($allowed_ana_cat))
					$allowed_ana_cat .= ",";

				$allowed_ana_cat .= $db->getField("ID", "Number", true);
			}
		} else {
			if(strlen($allowed_ana_cat))
				$allowed_ana_cat .= ",";

			$allowed_ana_cat .= $db->getField("ID", "Number", true);
		}
	
	} while($db->nextRecord());
}

if(check_function("get_user_data"))
	$Fname_sql = get_user_data("Fname", "anagraph", null, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "billing_ID_anagraph";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_billing_ID_anagraph");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
        				INNER JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories) OR anagraph.categories = ''
				    WHERE (
				        	(anagraph_categories.location LIKE '%" . $db->toSql("bill_%_sent", "Text", false) . "%'
								OR anagraph_categories.location = ''
							) AND anagraph_categories.location NOT LIKE '%" . $db->toSql("nothing", "Text", false) . "%'

				        )
				        " . (strlen($allowed_ana_cat)
				        	? " AND (anagraph_categories.ID IN (" . $db->toSql($allowed_ana_cat, "Text", false) . ")
                                OR anagraph_categories.name = 'ecommerce online'
                            )"
				        	: ""
				        ) . "
				    GROUP BY anagraph.ID
				    ORDER BY Fname";

$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_WS_ECOMMERCE  . "/anagraph/all/modify";
$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => $oRecord->id . "_" . $oField->id);
//$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=AnagraphModify_confirmdelete";
$oField->resources[] = "AnagraphModify";
$oRecord->addContent($oField, "billing");


$oField = ffField::factory($cm->oPage);
$oField->id = "billing_month_before";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_billing_month_before");
$oRecord->addContent($oField, "billing");

$oField = ffField::factory($cm->oPage);
$oField->id = "billing_buy_price";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_billing_buy_price");
$oField->base_type = "Number";
$oField->app_type = "Currency"; 
$oRecord->addContent($oField, "billing");
                                      
$oField = ffField::factory($cm->oPage);
$oField->id = "billing_sell_price";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_billing_sell_price");
$oField->base_type = "Number";
$oField->app_type = "Currency"; 
$oRecord->addContent($oField, "billing");

$oField = ffField::factory($cm->oPage);
$oField->id = "billing_decumulation";
$oField->label = ffTemplate::_get_word_by_code("ecommerce_decumulation");
$oField->widget = "actex";
//$oField->widget = "activecomboex";
$oField->multi_pairs = array (
                            array(new ffData("scorporo"), new ffData(ffTemplate::_get_word_by_code("price_with_vat"))),
                            array(new ffData("incorporo"), new ffData(ffTemplate::_get_word_by_code("price_without_vat")))
                       );      
$oField->default_value = new ffData("incorporo", "Text");
$oField->multi_select_one = false;
$oRecord->addContent($oField, "billing");

$oRecord->addContent(null, true, "note"); 
$oRecord->groups["note"] = array(
                                         "title" => ffTemplate::_get_word_by_code("mc_domain_modify_note")
                                         , "cols" => 1
                                      );

$oField = ffField::factory($cm->oPage);
$oField->id = "note";
$oField->label = ffTemplate::_get_word_by_code("mc_domain_modify_note");
$oField->display_label = false;
$oField->extended_type = "Text";
$oRecord->addContent($oField, "note");
                                      
$cm->oPage->addContent($oRecord);

$cm->oPage->tplAddJs("ff.cms.admin.mc-modify");
// -------------------------
//          EVENTI
// -------------------------
function MCDomainModify_on_do_action($component, $action) {
    switch ($action) {
        case "insert":
        case "update":
            $db = ffDB_Sql::factory();
            
            $ftp_host = $component->form_fields["nome"]->getValue();
            $ftp_ip = gethostbyname($ftp_host);
            if($ftp_ip === false && strpos($ftp_host, "www.") === false)
                gethostbyname("www." . $ftp_host);

            $server_ip = gethostbyname($_SERVER["HTTP_HOST"]);
            if($ftp_ip == $server_ip)
                $ftp_host = "localhost";

            $ftp_user = $component->form_fields["ftp_user"]->getValue();
            $ftp_password = $component->form_fields["ftp_password"]->getValue();

            if(!strlen($ftp_password)) {
                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.ftp_password 
                        FROM " . CM_TABLE_PREFIX . "mod_security_domains
                        WHERE " . CM_TABLE_PREFIX . "mod_security_domains.ID = " . $db->toSql($component->key_fields["ID"]->value);
                $db->query($sSQL);
                if($db->nextRecord()) {
                    $ftp_password = $db->getField("ftp_password", "Text", true);
                }
            }
                
            $ftp_path = $component->form_fields["ftp_path"]->getValue();
            
            if(strlen($ftp_user) || strlen($ftp_password) || strlen($ftp_path)) { 
                $installable = false;
                if(strlen($ftp_host) && strlen($ftp_user) && strlen($ftp_password) && strlen($ftp_path)) {
                    $conn_id = @ftp_connect($ftp_host, 21, 3);

					if($conn_id === false && $ftp_host == "localhost")
        				$conn_id = @ftp_connect("127.0.0.1");
					if($conn_id === false && $ftp_host == "localhost")
        				$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);
                    
                    if($conn_id === false && strpos($ftp_host, "www.") === false && $ftp_host != "localhost")
						$conn_id = @ftp_connect("www." . $ftp_host, 21, 3);

                    if($conn_id !== false) { 
                        // login with username and password
                        if(@ftp_login($conn_id, $ftp_user, $ftp_password)) {
                            $local_path = $ftp_path;
                            $part_path = "";
                            $real_ftp_path = NULL;
                            
                            if(@ftp_chdir($conn_id, $local_path)) {
                                $real_ftp_path = $local_path;
                            } 
                                
                            if($real_ftp_path === NULL) {
								$strError = ffTemplate::_get_word_by_code("mc_domain_ftp_wrong_path");
							} else {
                                $installable = true;
                            }
                        } else {
							$strError = ffTemplate::_get_word_by_code("mc_domain_ftp_login_incorrect");
                        }
                    } else {
						$strError = ffTemplate::_get_word_by_code("mc_domain_ftp_connection_failed");
                    }
                    @ftp_close($conn_id);
                } else {
					$strError = ffTemplate::_get_word_by_code("mc_domain_ftp_data_empty");
                }

                if(!$installable) {
                    $component->tplDisplayError($strError);
                    return true;
                }
            }
            break;
        case "confirmdelete":
            break;
            
        default:            
    }
    

}
