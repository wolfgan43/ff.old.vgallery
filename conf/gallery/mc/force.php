<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_UPDATER_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

require_once(FF_DISK_PATH . "/conf" . GALLERY_PATH . "/updater/check/manifesto." . FF_PHP_EXT);

$db = ffDB_Sql::factory();

$cm->oPage->form_method = "POST"; 

$valid_domain = false;

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
        FROM " . CM_TABLE_PREFIX . "mod_security_domains 
        WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db->toSql(basename($cm->real_path_info));
$db->query($sSQL);
if($db->nextRecord()) {
    $ID_domain = $db->getField("ID", "Number",true);
    $ftp_ip = ($db->getField("ip_address", "Text", true)
                ? $db->getField("ip_address", "Text", true)
                : null
            );
    $ftp_host = $db->getField("nome", "Text", true);
    $ftp_user = $db->getField("ftp_user", "Text", true);
    $ftp_password = $db->getField("ftp_password", "Text", true);
    $ftp_path = $db->getField("ftp_path", "Text", true);

    $valid_domain = true;
} else {
    if(basename($cm->real_path_info) == DOMAIN_NAME) {
        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
                (
                    `ID`
                    , `nome`
                    , `creation_date`
                    , `status`
                    , `ip_address`
                    , `ftp_user`
                    , `ftp_password`
                    , `ftp_path`

                ) 
                VALUES 
                (
                    NULL
                    , " . $db->toSql(DOMAIN_NAME, "Text") . "
                    , CURDATE()
                    , '1' 
                    , " . $db->toSql($_SERVER["REMOTE_ADDR"], "Text") . " 
                    , " . $db->toSql(FTP_USERNAME, "Text") . " 
                    , " . $db->toSql(FTP_PASSWORD, "Text") . " 
                    , " . $db->toSql(FTP_PATH, "Text") . " 
                )";
        $db->execute($sSQL);
        $ID_domain = $db->getInsertID(true);
        $ftp_ip = null;
        $ftp_host = DOMAIN_NAME;
        $ftp_user = FTP_USERNAME;
        $ftp_password = FTP_PASSWORD;
        $ftp_path = FTP_PATH;

        $valid_domain = true;
    }
}
if($valid_domain) {
    if($_REQUEST["frmAction"] == "install") {
        $res = force_install($ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip, "execute");

        //if($_REQUEST["XHR_DIALOG_ID"])
        if($cm->oPage->isXHR()) {  
            die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("MCDomainModify")), true));
        } else {
            ffRedirect($_REQUEST["ret_url"]);
        }
        
       // else
       //     ffRedirect($_REQUEST["ret_url"]);
    } 

    if($cm->oPage->isXHR()) { 
        if(isset($_REQUEST["frmAction"]) && $_REQUEST["frmAction"] == "DomainSettings_update") {
            die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("MCDomainModify")), true));
        }

        if(isset($_REQUEST["json"])) {
            if($_REQUEST["frmAction"] == "update") {
                $params = $_REQUEST["params"];
                $arrParams = explode(",", $params);

                if(is_array($manifesto) && count($manifesto)) {
                    if(basename($cm->real_path_info) == DOMAIN_NAME) {
                        $real_file = FF_THEME_DIR . "/" . FRONTEND_THEME . "/manifesto.xml";
                        
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
                                        $tpl = ffTemplate::factory(FF_DISK_PATH . "/conf/gallery/mc");
                                        $tpl->load_file("manifesto.tpl", "Main");
                                        foreach ($manifesto AS $manifesto_key => $manifesto_value) {
                                            if(array_search($manifesto_key, $arrParams) === false) {
                                                $value = "0";
                                            } else {
                                                $value = "1";
                                            }
                                            $tpl->set_var("item_key", preg_replace('/[^a-zA-Z0-9]/', '', $manifesto_key));
                                            $tpl->set_var("item_id", $manifesto_key);
                                            $tpl->set_var("item_enable", $value);
                                            $tpl->parse("SezManifestoItem", true);
                                        }
                                        $content = $tpl->rpparse("Main", false);
                                    }
                                    
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
                                    
                                }
                            }
                        }                    
                    } else {
                        $db_update = ffDB_Sql::factory();

                        foreach ($manifesto AS $manifesto_key => $manifesto_value) {
                            if(array_search($manifesto_key, $arrParams) === false) {
                                $value = "0";
                            } else {
                                $value = "1";
                            }
                            $sSQL = "SELECT ID
                                        FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                                        WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
                                            AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($manifesto_value["type"]) . "
                                            AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($manifesto_key);
                            $db_update->query($sSQL);
                            if($db_update->nextRecord()) {
                                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains_fields SET 
                                            " . CM_TABLE_PREFIX . "mod_security_domains_fields.value = " . $db_update->toSql($value) . "
                                        WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
                                            AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($manifesto_value["type"]) . "
                                            AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($manifesto_key);
                                $db_update->execute($sSQL);
                            } else { 
                                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains_fields 
                                            (
                                                ID
                                                , ID_domains
                                                , `group`
                                                , field
                                                , value
                                            )
                                            VALUES
                                            ( 
                                                ''
                                                , " . $db_update->toSql($ID_domain, "Number") . "  
                                                , " . $db_update->toSql($manifesto_value["type"]) . "  
                                                , " . $db_update->toSql($manifesto_key) . "  
                                                , " . $db_update->toSql($value) . "  
                                            )";
                                $db_update->execute($sSQL);
                            }
                        }
                    }
                }
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("MCDomainModify")), true));
            }

            $manifesto_cat = array(
                "forms_framework" => array(
                	"ff_applet" => array()
                    , "ff_module" => array()
                    , "ff_theme" => array()
                    , "vgallery_cms" => array(
                        "vgallery_master_control" => array()
                        , "vgallery_ecommerce" => array()
                        , "vgallery_module" => array()
                        , "vgallery_plugin" => array()
                    )
                )
                , "jqueryui_theme" => array()
                , "external_app" => array()
                , "external_plugin" => array()
            );
            
            
            $tree = get_tree_cat($ID_domain, $manifesto_cat, $manifesto);
            
           // $tree_items = get_mc_items($ID_domain, $manifesto_cat);
            
            header("Content-type: application/json");
            die(json_encode($tree));
        }
    }
    /*
    if(strlen($ftp_host) && strlen($ftp_user) && strlen($ftp_password) && strlen($ftp_path)) {
        $res = force_install($ftp_host, $ftp_user, $ftp_password, $ftp_path);

        $file_diff = $res["total"] - $res["count"];

        $button = ffButton::factory($cm->oPage);
        $button->id = "install";

        if($file_diff < $res["total"]) {
            $button->label = ffTemplate::_get_word_by_code("force_reinstall");
        } else {
            $button->label = ffTemplate::_get_word_by_code("force_install");
        }
        $button->url = $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "?action=install";
        $button->class = "noactivebuttons";
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $button->action_type = "submit";
            $button->jsaction = "javascript:ff.ajax.doRequest({'action': 'install', 'url' : '" . $button->url . "'});"; 
        } else {
            $button->action_type = "gotourl";
        }
        $button->aspect = "link";
        $button->parent_page = array(&$cm->oPage);
        $cm->oPage->addContent($button);

        if($file_diff == 0) {
            $button = ffButton::factory($cm->oPage);
            $button->id = "gotoinstall";
            $button->class = "noactivebuttons";
            $button->label = ffTemplate::_get_word_by_code("goto_install");
            $button->action_type = "gotourl";
            $button->target = "_blank";
            $button->url = "http://" . $ftp_host . "/conf/gallery/install";
            $button->aspect = "link";
            $button->parent_page = array(&$cm->oPage);
            $cm->oPage->addContent($button);
        }
    } 
    */   
    if(1) {
        $cm->oPage->tplAddJs("ff.ajax", "ajax.js", "/themes/library/ff", false, false, null, true);
        $cm->oPage->tplAddJs("jquery.fn.tree", "jquery.jstree.min.js", "/themes/library/plugins/jquery.jstree", false, false, null, true);
        
        $tpl = ffTemplate::factory(ffCommon_dirname(__FILE__));
        $tpl->load_file("tree.html", "main");
        $tpl->set_var("site_path", FF_SITE_PATH);
        $tpl->set_var("json_path", $cm->oPage->page_path . $cm->real_path_info);
        
        $cm->oPage->addContent($tpl);
       
        if(basename($cm->real_path_info) == DOMAIN_NAME) {
            $ID_dialog = "setDefault";
        } else {
            $ID_dialog = $ID_domain;
        }
        $oButton_update = ffButton::factory($cm->oPage);
        $oButton_update->id = "ActionButtonUpdate";
        $oButton_update->label = ffTemplate::_get_word_by_code("ffRecord_update");
        $oButton_update->action_type = "submit";
        $oButton_update->jsaction = "javascript:updateManifesto('" . $ID_dialog . "');";
        $oButton_update->aspect = "link";
        $oButton_update->parent_page = array(&$cm->oPage);
        
        $oButton_cancel = ffButton::factory($cm->oPage);
        $oButton_cancel->id = "ActionButtonCancel";
        $oButton_cancel->label = ffTemplate::_get_word_by_code("ffRecord_close");
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oButton_cancel->action_type     = "submit";
            $oButton_cancel->frmAction        = "close";
        } else {
            $oButton_cancel->action_type = "gotourl";
            $oButton_cancel->url = "[RET_URL]";
        }
        $oButton_cancel->aspect = "link";
        $oButton_cancel->parent_page = array(&$cm->oPage);

        $cm->oPage->addContent('<div class="actions dialogActionsPanel force">' . $oButton_update->process() . $oButton_cancel->process() . '</div>');
    } else {
        $sSQL_field = get_mc_items_old($ID_domain, $manifesto); 
        $sSQL = $sSQL_field;

        $oGrid = ffGrid::factory($cm->oPage);
        $oGrid->id = "DomainSettings";
        $oGrid->resources[] = "MCDomainModify";
        $oGrid->title = ffTemplate::_get_word_by_code("domain_settings_title");
        $oGrid->source_SQL = $sSQL . " [WHERE] [ORDER] ";
        $oGrid->order_default = "ID";
        $oGrid->use_search = false;
        $oGrid->use_paging = false;
        $oGrid->addEvent("on_do_action", "domain_settings_on_do_action");
        $oGrid->ret_url = $_REQUEST["ret_url"];
        $oGrid->user_vars["ID_domain"] = $ID_domain;
        
        $oGrid->display_new = false;
        $oGrid->display_edit_bt = false;
        $oGrid->display_edit_url = false;
        $oGrid->display_delete_bt = false;

        // Campi chiave
        $oField = ffField::factory($cm->oPage);
        $oField->id = "ID";
        $oField->base_type = "Number";
        $oGrid->addKeyField($oField);

        // Campi visualizzati
        $oField = ffField::factory($cm->oPage);
        $oField->id = "group";
        $oField->label = ffTemplate::_get_word_by_code("domain_settings_group");
        $oField->control_type = "label";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "field";
        $oField->label = ffTemplate::_get_word_by_code("domain_settings_field");
        $oField->control_type = "label";
        $oGrid->addContent($oField);

        $oField = ffField::factory($cm->oPage);
        $oField->id = "value";
        $oField->label = ffTemplate::_get_word_by_code("domain_settings_value");
        $oField->base_type = "Text";
        $oField->extended_type = "Boolean";
        $oField->control_type = "checkbox";
        $oField->unchecked_value = new ffData("0", "Text");
        $oField->checked_value = new ffData("1", "Text");
        $oField->required = true;
        $oGrid->addContent($oField);

        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "ActionButtonUpdate";
        $oButton->label = ffTemplate::_get_word_by_code("ffRecord_update");
        $oButton->action_type = "submit";
        $oButton->frmAction = "update";
		$oButton->aspect = "link";
        $oGrid->addActionButton($oButton);
        
        $oButton = ffButton::factory($cm->oPage);
        $oButton->id = "ActionButtonCancel";
		$oButton->label = ffTemplate::_get_word_by_code("ffRecord_close");
        if($_REQUEST["XHR_DIALOG_ID"]) {
            $oButton->action_type     = "submit";
            $oButton->frmAction        = "close";
        } else {
            $oButton->action_type = "gotourl";
            $oButton->url = "[RET_URL]";
        }
        $oButton->aspect = "link";
        $oGrid->addActionButton($oButton);
        
        $cm->oPage->addContent($oGrid);
    }
}
         
function domain_settings_on_do_action($component, $action) {
    $db_update = ffDB_Sql::factory();

    switch($action) {
        case "update":
            if(is_array($component->recordset_values) && count($component->recordset_values)) {
                $ID_domain = $component->user_vars["ID_domain"];
                foreach ($component->recordset_values AS $record_key => $record_value) {
                    $sSQL = "SELECT ID
                                FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                                WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
                                    AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($record_value["group"]) . "
                                    AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($record_value["field"]);
                    $db_update->query($sSQL);
                    if($db_update->nextRecord()) {
                        $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_security_domains_fields SET 
                                    " . CM_TABLE_PREFIX . "mod_security_domains_fields.value = " . $db_update->toSql($record_value["value"]) . "
                                WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db_update->toSql($ID_domain, "Number") . "  
                                    AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db_update->toSql($record_value["group"]) . "
                                    AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db_update->toSql($record_value["field"]);
                        $db_update->execute($sSQL);
                    } else {
                        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains_fields 
                                    (
                                        ID
                                        , ID_domains
                                        , `group`
                                        , field
                                        , value
                                    )
                                    VALUES
                                    ( 
                                        ''
                                        , " . $db_update->toSql($ID_domain, "Number") . "  
                                        , " . $db_update->toSql($record_value["group"]) . "  
                                        , " . $db_update->toSql($record_value["field"]) . "  
                                        , " . $db_update->toSql($record_value["value"]) . "  
                                    )";
                        $db_update->execute($sSQL);
                    }
                }
            }
            if($_REQUEST["XHR_DIALOG_ID"])
                die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => array("MCDomainModify")), true));
                
//            ffRedirect($component->ret_url);
            break;
        default:

    }
}


function force_install($ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip = null, $action = "check") {
    $strError = "";
    $count_check_file = 0;

    $arrBasicInstallFile[] = "/conf/gallery/install/data.sql";
    $arrBasicInstallFile[] = "/conf/gallery/install/structure.sql";
    $arrBasicInstallFile[] = "/conf/gallery/install/index.php";
    $arrBasicInstallFile[] = "/conf/gallery/install/base.css";
    $arrBasicInstallFile[] = "/conf/gallery/install/base.js";
    $arrBasicInstallFile[] = "/conf/gallery/install/install.html";
    $arrBasicInstallFile[] = "/conf/gallery/install/install.css";
    $arrBasicInstallFile[] = "/conf/gallery/install/install.js";
    $arrBasicInstallFile[] = "/conf/gallery/install/jquery.min.js";
    $arrBasicInstallFile[] = "/conf/gallery/updater/data.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/externals.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/files.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/index.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/indexes.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/structure.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/db.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/exclude_fs.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/external.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/file.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/fixed_operations.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/force_drop_db.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/include_db.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/check/manifesto.php";
    $arrBasicInstallFile[] = "/conf/gallery/updater/js/updater.js";

	if(!$ftp_ip)
    	$ftp_ip = gethostbyname($ftp_host);

    if($ftp_ip === false && strpos($ftp_host, "www.") === false)
        gethostbyname("www." . $ftp_host);

    $server_ip = gethostbyname($_SERVER["HTTP_HOST"]);
    if($ftp_ip == $server_ip)
        $ftp_host = "localhost";
    
    if(strlen($ftp_host) && strlen($ftp_user) && strlen($ftp_password) && strlen($ftp_path)) {
        if($ftp_ip)
            $conn_id = @ftp_connect($ftp_ip);
        if($conn_id === false)
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
                    
                if($real_ftp_path !== NULL) {
                    foreach($arrBasicInstallFile AS $arrBasicInstallFile_value) {
                        if($action == "execute") {
                            $part_path = "";
                            foreach(explode("/", ffCommon_dirname($arrBasicInstallFile_value)) AS $tmp_path) {
                                if(strlen($tmp_path)) {
                                    $part_path .= "/" . $tmp_path;
                                    
                                    if(!@ftp_chdir($conn_id, $real_ftp_path . $part_path)) {
                                        if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
                                            $strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $part_path . ")" . "<br>";
                                    }
                                }
                            }

                            if(@ftp_size($conn_id, $real_ftp_path . $arrBasicInstallFile_value) >= 0) {
                                @ftp_delete($conn_id, $real_ftp_path . $arrBasicInstallFile_value);
                            }
                            $ret = @ftp_nb_put($conn_id
                                                , $real_ftp_path . $arrBasicInstallFile_value
                                                , FF_DISK_PATH . $arrBasicInstallFile_value
                                                , FTP_BINARY
                                                , FTP_AUTORESUME
                                            );

                            while ($ret == FTP_MOREDATA) {
                               
                               // Do whatever you want
                               // Continue uploading...
                               $ret = @ftp_nb_continue($conn_id);
                            }
                            if ($ret != FTP_FINISHED) {
                               $strError .= ffTemplate::_get_word_by_code("upload_failure_file") . " (" . $real_ftp_path . $arrBasicInstallFile_value . ")" . "<br>";
                            } else {
                                $count_check_file++;
                            }
                        } else {
                            if(@ftp_size($conn_id, $real_ftp_path . $arrBasicInstallFile_value) >= 0) {
                                $count_check_file++;
                            }
                        }
                    }
                    if($action == "execute") {
                        $config_updater_path = "/conf/gallery/config/updater.php";

                        $part_path = "";
                        foreach(explode("/", ffCommon_dirname($config_updater_path)) AS $tmp_path) {
                            if(strlen($tmp_path)) {
                                $part_path .= "/" . $tmp_path;
                                
                                if(!@ftp_chdir($conn_id, $real_ftp_path . $part_path)) {
                                    if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
                                        $strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $part_path . ")" . "<br>";
                                }
                            }
                        }

                        $config_updater_content = '<?php
    define("MASTER_SITE", "' . DOMAIN_INSET . '");

    define("FTP_USERNAME", "' . $ftp_user . '");
    define("FTP_PASSWORD", "' . $ftp_password . '");
    define("FTP_PATH", "' . $ftp_path . '");
    
    $config_check["updater"] = true;
?>';
                        $tempHandle = @tmpfile();
                        @fwrite($tempHandle, $config_updater_content);
                        @rewind($tempHandle);

                        if(@ftp_size($conn_id, $real_ftp_path . $config_updater_path) >= 0) {
                            @ftp_delete($conn_id, $real_ftp_path . $config_updater_path);
                        }
                        
                        $ret = @ftp_nb_fput($conn_id
                                            , $real_ftp_path . $config_updater_path
                                            , $tempHandle
                                            , FTP_BINARY
                                            , FTP_AUTORESUME
                                        );
                        while ($ret == FTP_MOREDATA) {
                           // Do whatever you want
                           // Continue upload...
                           $ret = @ftp_nb_continue($conn_id);
                        }
                        if ($ret != FTP_FINISHED) {
                           $strError .= ffTemplate::_get_word_by_code("upload_failure_file") . " (" . $real_ftp_path . $config_updater_path . ")" . "<br>";
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
    
    return array("total" => count($arrBasicInstallFile), "count" => $count_check_file, "error" => $strError);
}

function get_mc_items_old($ID_domain, $manifesto) {
    $db = ffDB_Sql::factory();
    
    if(is_array($manifesto) && count($manifesto)) {
        $count_field = 0;
        $sSQL_field = "";
        foreach($manifesto AS $manifesto_key => $manifesto_value) {
            $count_field++;

            if(strlen($sSQL_field))
                $sSQL_field .= " UNION ";
            
            $sSQL_field .= "
            ( 
                SELECT " . $db->toSql($count_field, "Number") . " AS ID 
                , " . $db->toSql($manifesto_value["type"]) . " AS `group`
                , " . $db->toSql($manifesto_key) . " AS field
                , (
                    IF(ISNULL((SELECT value 
                        FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields 
                        WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db->toSql($ID_domain, "Number") . " 
                            AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db->toSql($manifesto_value["type"]) . " 
                            AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db->toSql($manifesto_key) . "
                        ))
                        , " . ($manifesto_value["enable"] ? "1" : "0") . " 
                        , (SELECT value 
                            FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields 
                            WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db->toSql($ID_domain, "Number") . " 
                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.`group` = " . $db->toSql($manifesto_value["type"]) . " 
                                AND " . CM_TABLE_PREFIX . "mod_security_domains_fields.field = " . $db->toSql($manifesto_key) . "
                        )
                    )
                ) AS value
            )";
        }
    }    
    return $sSQL_field; 
}

function get_mc_items($ID_domain, $manifesto, $cat_key) {
    $tree = array();
    if(is_array($manifesto) && count($manifesto)) {
        $count_field = 0;
        $sSQL_field = "";
        foreach($manifesto AS $manifesto_key => $manifesto_value) {
            if(strpos($manifesto_key, $cat_key) === 0 && substr_count(str_replace($cat_key, "", $manifesto_key), "/") <= 1) {
                if($cat_key == $manifesto_key) {
                    
                } else {
                    if(strpos($manifesto_key, "/") === false) {
                        $manifesto_title = str_replace($cat_key, "", $manifesto_key);
                    } else {
                        $manifesto_title = basename($manifesto_key);
                    }
                    $tree[$count_field]["attr"]["id"]     = $manifesto_key;
                    $tree[$count_field]["data"]["title"]  = ucwords(str_replace("_", " ", $manifesto_title));
                    $tree[$count_field]["metadata"]       = array();
                    $tree[$count_field]["attr"]["rel"] = "file";
                    $tree[$count_field]["children"] = array();

                    if(get_item_data($ID_domain, $manifesto, $manifesto_key)) {
                        $tree[$count_field]["attr"]["class"]  = "checked";
                    } else {
                        $tree[$count_field]["attr"]["class"]  = "";
                    }

                    $count_field++; 
                }                
            }
        }    
    }
    return $tree;
}
function get_tree_cat($ID_domain, $schema, $manifesto) {
    $tree = array();

    if(is_array($schema) && count($schema)) {
        $count_tree = 0;
        foreach($schema AS $schema_key => $schema_value) {
            $tree[$count_tree]["data"] = array("title" => ucwords(str_replace("_", " ", $schema_key)));
            $tree[$count_tree]["attr"] = array("id" => $schema_key);
            $tree[$count_tree]["metadata"] = array();

            if(is_array($schema_value) && count($schema_value)) {
                $children = get_tree_cat($ID_domain, $schema[$schema_key], $manifesto);
            } else {
                $children = get_mc_items($ID_domain, $manifesto, $schema_key);
            }    
            if(is_array($children) && count($children)) {
                $tree[$count_tree]["state"] = (is_array($schema_value) && count($schema_value) ? "open" : "closed");
                $tree[$count_tree]["children"] = $children;
                if(array_key_exists($schema_key, $manifesto)) {
                    $tree[$count_tree]["attr"]["rel"] = "folder";
                }
            } else {
                $res = get_item_data($ID_domain, $manifesto, $schema_key);
                if($res === null) {
                    unset($tree[$count_tree]);
                    continue;
                } else {
                    $tree[$count_tree]["children"] = array();
                    $tree[$count_tree]["attr"]["rel"] = "file";
                    if($res)
                        $tree[$count_tree]["attr"]["class"]  = "checked";
                }
            }
            
            
            $count_tree++;
        }
    }
    return $tree;
}

function get_item_data($ID_domain, $manifesto, $key = null) {
    $cm = cm::getInstance();
    static $manifesto_data = null;
    
    if($manifesto_data === null) {
        $db = ffDB_Sql::factory();
        
        $manifesto_data = array();

        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains_fields.*
                FROM " . CM_TABLE_PREFIX . "mod_security_domains_fields
                WHERE " . CM_TABLE_PREFIX . "mod_security_domains_fields.ID_domains = " . $db->toSql($ID_domain, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            do {
                if(array_key_exists($db->getField("field", "Text", true), $manifesto)) {
                   $manifesto_data[$db->getField("field", "Text", true)] = $db->getField("value", "Text", true); 
                }
            } while($db->nextRecord());
        }

        if(!file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/manifesto.xml")) {
            $sSQL = "SELECT " . CM_TABLE_PREFIX . "layout.*
                    FROM " . CM_TABLE_PREFIX . "layout";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    if(!array_key_exists("ff_theme/" . $db->getField("theme", "Text", true), $manifesto_data) && array_key_exists("ff_theme/" . $db->getField("theme", "Text", true), $manifesto)) {
                        $manifesto_data["ff_theme/" . $db->getField("theme", "Text", true)] = "1";
                    }
                } while($db->nextRecord());
            }
            
            $restricted_settings = mod_restricted_get_all_setting();
            $manifesto_data["jqueryui_theme/" . "base"] = "1";
/*
            if(!array_key_exists("jqueryui_theme/" . $restricted_settings["JQUERYUI_ADMIN_THEME"], $manifesto_data) && array_key_exists("jqueryui_theme/" . $restricted_settings["JQUERYUI_ADMIN_THEME"], $manifesto)) {
                $manifesto_data["jqueryui_theme/" . $restricted_settings["JQUERYUI_ADMIN_THEME"]] = "1";
            }
            if(!array_key_exists("jqueryui_theme/" . $restricted_settings["JQUERYUI_RESTRICTED_THEME"], $manifesto_data) && array_key_exists("jqueryui_theme/" . $restricted_settings["JQUERYUI_RESTRICTED_THEME"], $manifesto)) {
                $manifesto_data["jqueryui_theme/" . $restricted_settings["JQUERYUI_RESTRICTED_THEME"]] = "1";
            }
            if(!array_key_exists("jqueryui_theme/" . $restricted_settings["JQUERYUI_MANAGE_THEME"], $manifesto_data) && array_key_exists("jqueryui_theme/" . $restricted_settings["JQUERYUI_MANAGE_THEME"], $manifesto)) {
                $manifesto_data["jqueryui_theme/" . $restricted_settings["JQUERYUI_MANAGE_THEME"]] = "1";
            }
*/
        }    
        if(is_array($manifesto) && count($manifesto)) {
            foreach($manifesto AS $manifesto_key => $manifesto_value) {
                if(!array_key_exists($manifesto_key, $manifesto_data)) {
                    $manifesto_data[$manifesto_key] = $manifesto_value["enable"];
                }
            }
        }
    }

    if(key === null) {
        return $manifesto_data;
    } else {
        if(array_key_exists($key, $manifesto_data))
            return $manifesto_data[$key];
        else
            return null;
    }
}
?>
