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
    
    if (!(MASTER_SITE == DOMAIN_INSET || MASTER_CONTROL)) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }
	
	$db = ffDB_Sql::factory();
    //(is_array($externals) && count($externals) && array_search(DOMAIN_INSET, $externals) !== false)
    $sSQL_where = " " . CM_TABLE_PREFIX . "mod_security_domains.nome <> " . $db->toSql(DOMAIN_NAME);

    $cm->oPage->widgetLoad("dialog");

    
    $oGrid = ffGrid::factory($cm->oPage);
    $oGrid->full_ajax = true;
    $oGrid->id = "UpdaterDomain";
    $oGrid->title = ffTemplate::_get_word_by_code("mc_title");
    $oGrid->source_SQL = "SELECT * FROM " . CM_TABLE_PREFIX . "mod_security_domains WHERE $sSQL_where [AND] [WHERE] [HAVING] [ORDER]";
    $oGrid->order_default = "nome";
    $oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
    $oGrid->record_id = "MCDomainModify";
    $oGrid->resources[] = $oGrid->record_id;
    $oGrid->addEvent("on_before_parse_row", "UpdaterDomain_on_before_parse_row");
    
    $oGrid->display_edit_bt = false;
    $oGrid->display_edit_url = true;
    $oGrid->display_delete_bt = true;
    $oGrid->display_new = true;
    $oGrid->use_paging = true;

    // Campi chiave
    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID";
    $oField->base_type = "Number";
    $oGrid->addKeyField($oField);

    // Campi visualizzati
    $oField = ffField::factory($cm->oPage);
    $oField->id = "nome";
    $oField->label = ffTemplate::_get_word_by_code("mc_domain_name"); 
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ip_address";
    $oField->label = ffTemplate::_get_word_by_code("mc_domain_ip_address");
    $oGrid->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "expiration_date";
    $oField->label = ffTemplate::_get_word_by_code("mc_domain_expiration_date");
    $oField->base_type = "Date";
    $oGrid->addContent($oField);

   /* $cm->oPage->widgets["dialog"]->process(
         "dialogForce"
         , array(
            "tpl_id" => null
            //"name" => "myTitle"
            , "url" => ""
            , "title" => ""
            , "callback" => ""
            , "class" => ""
            , "params" => array(
            )
            , "resizable" => true
            , "position" => "center"
            , "draggable" => true
            , "doredirects" => true
        )
        , $cm->oPage
    ); */
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "force"; 
    $oButton->class = Cms::getInstance("frameworkcss")->get("cog", "icon");
    //$oButton->label = "preview";
    $oButton->action_type = "submit";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("mc_edit");
    //$oButton->image = "edit.png";
    $oButton->display_label = false;
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "install"; 
    $oButton->class = Cms::getInstance("frameworkcss")->get("cloud-download", "icon");
    //$oButton->class = Cms::getInstance("frameworkcss")->get("share", "icon", "rotate-90");
    //$oButton->label = "preview";
    $oButton->action_type = "submit";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("mc_install");
    //$oButton->image = "edit.png";
    $oButton->display_label = false;
    $oGrid->addGridButton($oButton);    

    
    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "default"; 
    $oButton->class = "noactivebuttons";
    $oButton->action_type = "submit";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("mc_set_default");
    $cm->oPage->widgets["dialog"]->process(
            "setDefault"
            , array(
                "tpl_id" => "UpdaterDomain"
                , "title" => ffTemplate::_get_word_by_code("mc_set_default_title")
                , "url" => $cm->oPage->site_path . $cm->oPage->page_path . "/force/" . DOMAIN_NAME
            )
            , $cm->oPage
        );
    $oButton->jsaction = "ff.ffPage.dialog.doOpen('setDefault')";
    $oGrid->addActionButtonHeader($oButton);    
        
    $cm->oPage->addContent($oGrid); 
    
    if(is_file(FF_DISK_PATH . "/conf/gallery/install/complete.css")) {
	    $cm->oPage->tplAddCss("install", array(
	        "embed" => file_get_contents(FF_DISK_PATH . "/conf/gallery/install/complete.css")
	    ));
	}
    
    function UpdaterDomain_on_before_parse_row($component) {
        $cm = cm::getInstance();

        if(isset($component->grid_buttons["install"])) {
            if($component->grid_buttons["install"]->action_type == "submit") {
                $ftp_host = $component->db[0]->getField("nome", "Text", true);
                $ftp_ip = gethostbyname($ftp_host);
                if($ftp_ip === false && strpos($ftp_host, "www.") === false)
                    gethostbyname("www." . $ftp_host);

                $server_ip = gethostbyname($_SERVER["HTTP_HOST"]);
                if($ftp_ip == $server_ip)
                    $ftp_host = "localhost";

                $ftp_user = $component->db[0]->getField("ftp_user", "Text", true);
                $ftp_password = $component->db[0]->getField("ftp_password", "Text", true);
                $ftp_path = $component->db[0]->getField("ftp_path", "Text", true);
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
                                
                            if($real_ftp_path !== NULL) {
                                $installable = true;
                            }
                        }
                    }
                    @ftp_close($conn_id);
                }

                if($installable) {
                    $component->grid_buttons["install"]->form_action_url = FF_SITE_PATH . ffcommon_dirname($component->grid_buttons["install"]->parent[0]->record_url) . "/force/" . urlencode($component->db[0]->getField("nome")->getValue()) . "?ret_url=" . urlencode($component->parent[0]->getRequestUri());
                    if($_REQUEST["XHR_CTX_ID"]) {
                        $component->grid_buttons["install"]->jsaction = "ff.ajax.ctxDoRequest('[[XHR_CTX_ID]]', {'action': 'install', 'url' : '[[frmAction_url]]'});";
                    } else {
                    	if($real_ftp_path) {
                    		/*$arrFtpPath = explode("/", trim($real_ftp_path, "/"));
                    		unset($arrFtpPath[0]);
                    		if(count($arrFtpPath))
                    			$iframe_path = "/" . implode("/", $arrFtpPath);*/
                    			
                    		$iframe_path = str_replace(array("/public_html", "/httpdocs", "/httpsdocs"), "", $real_ftp_path);
						}
                        $js_open_updater_window = " jQuery('<iframe src=\'http://" . $component->db[0]->getField("nome")->getValue() . $iframe_path . "/conf/gallery/install\' onload=\'ff.cms.admin.checkiFrame(this);\' />').dialog({ 
                        		resizable: true
                        		, modal: false
                        		, width: 500
                        		, height: 470
                        		, title: '" . ffTemplate::_get_word_by_code("install_title") . ": " . $component->db[0]->getField("nome")->getValue() . "'
                        	}).width(500).height(470);";
                        $component->grid_buttons["install"]->jsaction = "ff.ajax.doRequest({'action': 'install', fields: [], 'url' : '[[frmAction_url]]', 'callback' : function() {" . $js_open_updater_window . "}});";
//                        $component->grid_buttons["install"]->action_type = "gotourl";
//                        $component->grid_buttons["install"]->url = FF_SITE_PATH . ffcommon_dirname($component->grid_buttons["install"]->parent[0]->record_url) . "/force/" . $component->db[0]->getField("nome")->getValue() . "?frmAction=install&ret_url=" . urlencode($component->parent[0]->getRequestUri());
                    }
                    $component->grid_buttons["install"]->display = true;
                } else {
                    $component->grid_buttons["install"]->display = false;
                }
            }
        }
        
        if(isset($component->grid_buttons["force"])) {
            if($component->grid_buttons["force"]->action_type == "submit") {
                $cm->oPage->widgetLoad("dialog");
                $cm->oPage->widgets["dialog"]->process(
                     $component->id . "_force_" . $component->key_fields["ID"]->getValue()
                     , array(
                        "tpl_id" => $component->id
                        //"name" => "myTitle"
                        , "url" => FF_SITE_PATH . ffcommon_dirname($component->grid_buttons["install"]->parent[0]->record_url) . "/force/" . urlencode($component->db[0]->getField("nome")->getValue())
                                . "?ret_url=" . urlencode($component->parent[0]->getRequestUri())
                        , "title" => ffTemplate::_get_word_by_code("mc_force_install_title")
                        , "callback" => ""
                        , "class" => ""
                        , "params" => array()
                    )
                    , $cm->oPage
                );
                $component->grid_buttons["force"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_force_" . $component->key_fields["ID"]->getValue() . "')";
            }
            $component->grid_buttons["force"]->display = true;
        }
    }
