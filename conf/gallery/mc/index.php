<?php
    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

    if (!(MASTER_SITE == DOMAIN_INSET || MASTER_CONTROL)) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }
    //(is_array($externals) && count($externals) && array_search(DOMAIN_INSET, $externals) !== false)
    $sSQL_where = " " . CM_TABLE_PREFIX . "mod_security_domains.nome <> " . $db_gallery->toSql(DOMAIN_NAME);

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
    $oButton->class = cm_getClassByFrameworkCss("cog", "icon");
    //$oButton->label = "preview";
    $oButton->action_type = "submit";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("mc_edit");
    $oButton->display_label = false;
    //$oButton->image = "edit.png";
    $oGrid->addGridButton($oButton);

    $oButton = ffButton::factory($cm->oPage);
    $oButton->id = "install"; 
    $oButton->class = cm_getClassByFrameworkCss("cloud-download", "icon");
    //$oButton->class = cm_getClassByFrameworkCss("share", "icon", "rotate-90");
    //$oButton->label = "preview";
    $oButton->action_type = "submit";
    $oButton->aspect = "link";
    $oButton->label = ffTemplate::_get_word_by_code("mc_install");
    $oButton->display_label = false;
    //$oButton->image = "edit.png";
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
    
    function UpdaterDomain_on_before_parse_row($component) {
        $cm = cm::getInstance();

        if(isset($component->grid_buttons["install"])) {
            if($component->grid_buttons["install"]->action_type == "submit") {
                $ftp_host = $component->db[0]->getField("nome", "Text", true);
                $ftp_ip = ($component->db[0]->getField("ip_address", "Text", true)
                    ? $component->db[0]->getField("ip_address", "Text", true)
                    : gethostbyname($ftp_host)
                );
                if($ftp_ip === false && strpos($ftp_host, "www.") === false)
                    $ftp_ip = gethostbyname("www." . $ftp_host);
                
                $server_ip = gethostbyname($_SERVER["HTTP_HOST"]);
                if($ftp_ip == $server_ip)
                    $ftp_host = "localhost";

                $ftp_user = $component->db[0]->getField("ftp_user", "Text", true);
                $ftp_password = $component->db[0]->getField("ftp_password", "Text", true);
                $ftp_path = $component->db[0]->getField("ftp_path", "Text", true);
                $installable = false;

                if(strlen($ftp_host) && strlen($ftp_user) && strlen($ftp_password) && strlen($ftp_path)) {
					$installable = true;
/*
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
                                $installable = true;
                            }
                        }
                    }
                    @ftp_close($conn_id);
 */
                }

                if($installable) {
                    $cm->oPage->widgetLoad("dialog");
                    $cm->oPage->widgets["dialog"]->process(
                        $component->id . "_install_" . $component->key_fields["ID"]->getValue()
                        , array(
                            "tpl_id" => $component->id
                            //"name" => "myTitle"
                        , "url" => FF_SITE_PATH . ffcommon_dirname($component->grid_buttons["install"]->parent[0]->record_url) . "/installer/" . urlencode($component->db[0]->getField("nome")->getValue())
                        , "title" => ffTemplate::_get_word_by_code("mc_installer_title") . ": " . $component->db[0]->getField("nome")->getValue()
                        , "callback" => ""
                        , "class" => "ff-modal-small"
                        , "params" => array()
                        )
                        , $cm->oPage
                    );
                    $component->grid_buttons["install"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_install_" . $component->key_fields["ID"]->getValue() . "')";
                    $component->grid_buttons["install"]->visible = true;
                } else {
                    $component->grid_buttons["install"]->visible = false;
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
                        , "title" => ffTemplate::_get_word_by_code("mc_force_install_title")
                        , "callback" => ""
                        , "class" => "ff-modal-small"
                        , "params" => array()
                    )
                    , $cm->oPage
                );
                $component->grid_buttons["force"]->jsaction = "ff.ffPage.dialog.doOpen('" . $component->id . "_force_" . $component->key_fields["ID"]->getValue() . "')";
            }
            $component->grid_buttons["force"]->visible = true;
        }
    }
?>
