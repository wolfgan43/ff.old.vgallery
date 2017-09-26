<?php
$permission = check_phonecall_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();

$anagraph_params_user = "ag=1&ct=phonecall&bg=0&sg=0&cg=1&cf=0&cnf=1&gmap=0&user=1&rg=0&am=vertical&tab=0&ug=phonecalluser&fu=1&cef=1";


$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "PhonecallModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("phonecall_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_phonecall_message";
$oRecord->addEvent("on_done_action", "PhonecallModify_on_done_action");
if(check_phonecall_permission(true) == MOD_PHONECALL_GROUP_ADMIN) {
	$oRecord->buttons_options["delete"]["display"] = true;
} else {
	$oRecord->buttons_options["delete"]["display"] = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->container_class = "date";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_date");
$oField->base_type = "Date";
$oField->extended_type = "Date";
$oField->app_type = "Date";
$oField->widget = "datepicker";
$oField->required = true;
$oField->default_value = new ffData(date("Y-m-d", time()), "Date");
$oRecord->addContent($oField);  

$oField = ffField::factory($cm->oPage);
$oField->id = "time";
$oField->container_class = "time";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_time");
$oField->base_type = "Time";
$oField->extended_type = "Time";
$oField->app_type = "Time";
$oField->widget = "timepicker";
$oField->required = true;
$oField->default_value = new ffData(date("H:m", time()), "Time");
$oRecord->addContent($oField);  

if(check_function("get_user_data"))
	$Fname_sql = get_user_data("Fname", "anagraph", null, false);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_recipient";
$oField->container_class = "recipient";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_recipient");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
				    WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    					FROM " . CM_TABLE_PREFIX . "mod_security_users
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_PHONECALL_GROUP_ADMIN) . "
					    						OR " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_PHONECALL_GROUP_USER) . "
					    				)
				    GROUP BY anagraph.ID
				    ORDER BY Fname";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
if(check_phonecall_permission(true) == MOD_PHONECALL_GROUP_ADMIN) {
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params_user;
	$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => $oRecord->id . "_" . $oField->id);
	$oField->resources[] = "AnagraphModify";
}
$oField->required = true;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_CC";
$oField->container_class = "cc";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_cc");
$oField->base_type = "Number";
$oField->source_SQL = "SELECT
				        anagraph.ID
				        , " . $Fname_sql . " AS Fname
				    FROM anagraph
				    WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    					FROM " . CM_TABLE_PREFIX . "mod_security_users
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_PHONECALL_GROUP_ADMIN) . "
					    						OR " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_PHONECALL_GROUP_USER) . "
					    				)
				    GROUP BY anagraph.ID
				    ORDER BY Fname";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
if(check_phonecall_permission(true) == MOD_PHONECALL_GROUP_ADMIN) {
	$oField->actex_dialog_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path)  . "/anagraph/all/modify?" . $anagraph_params_user;
	$oField->actex_dialog_edit_params = array("keys[anagraph-ID]" => $oRecord->id . "_" . $oField->id);
	$oField->resources[] = "AnagraphModify";
}
$oRecord->addContent($oField);

$oRecord->addContent(null, true, "applicant"); 
$oRecord->groups["applicant"] = array(
	                             "title" => ffTemplate::_get_word_by_code("phonecall_modify_applicant")
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
		if(strlen($category) && ffCommon_url_rewrite($db->getField("name")->getValue()) == $category) {
        	$ID_category = $db->getField("ID")->getValue();
        }

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
	                          
$oField = ffField::factory($cm->oPage);
$oField->id = "reference";
$oField->container_class = "reference";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_reference");
$oField->source_SQL = "SELECT
				        IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference) AS FnameID
						, IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference) AS Fname
				    FROM anagraph
				    	LEFT JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories)
				    WHERE
				    	anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    					FROM " . CM_TABLE_PREFIX . "mod_security_users
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_PHONECALL_GROUP_ADMIN) . "
					    						OR " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_PHONECALL_GROUP_USER) . "
					    				)
				    " . (strlen($allowed_ana_cat)
				        ? " OR anagraph_categories.ID IN (" . $db->toSql($allowed_ana_cat, "Text", false) . ")" 
				        : ""
				    ) . "
				    [HAVING]
				    [ORDER] [COLON] Fname
				    [LIMIT]";
$oField->widget = "autocomplete";
$oField->autocomplete_minLength = 0;
$oField->autocomplete_readonly = false;
$oField->autocomplete_combo = true;
$oField->autocomplete_compare_having = "Fname";
$oField->required = true;
$oRecord->addContent($oField, "applicant");

$oField = ffField::factory($cm->oPage);
$oField->id = "phone";
$oField->container_class = "phone";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_phone");
$oRecord->addContent($oField, "applicant");

$oField = ffField::factory($cm->oPage);
$oField->id = "fax";
$oField->container_class = "fax";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_fax");
$oRecord->addContent($oField, "applicant");

$oField = ffField::factory($cm->oPage);
$oField->id = "cell";
$oField->container_class = "cell";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_cell");
$oRecord->addContent($oField, "applicant");

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->container_class = "email";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_email");
$oRecord->addContent($oField, "applicant");

$oRecord->addContent(null, true, "message"); 
$oRecord->groups["message"] = array(
	                             "title" => ffTemplate::_get_word_by_code("phonecall_modify_message")
	                             , "cols" => 1
	                          );

$oField = ffField::factory($cm->oPage);
$oField->id = "message";
$oField->container_class = "message";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_message");
$oField->display_label = false;
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oRecord->addContent($oField, "message");

$oField = ffField::factory($cm->oPage);
$oField->id = "message_archive";
$oField->container_class = "message_archive";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_message_archive");
$oField->base_type = "Text";
$oField->extended_type = "Text";
$oRecord->addContent($oField, "message");

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->container_class = "status";
$oField->label = ffTemplate::_get_word_by_code("phonecall_modify_status");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oField->default_value = new ffData("0", "Number");
$oRecord->addContent($oField, "message");

$oRecord->insert_additional_fields = array(
									"owner" =>  new ffData($UserNID, "Number")
								);
$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
								);

/*								
$cm->oPage->tplAddJs("jquery.printElement", "jquery.printelement.js", FF_THEME_DIR . "/library/plugins/jquery.printelement");     

$oButton_print = ffButton::factory($cm->oPage);
$oButton_print->id = "print";
$oButton_print->class = "button noactivebuttons";
//$oButton->action_type = "submit";

if(file_exists(FF_DISK_PATH . "/themes/" . FRONTEND_THEME . "/css/print_" . str_replace("/", "_", trim($cm->real_path_info, "/")) . ".css")) {
	$cm->oPage->tplAddCss(
					"print_" . str_replace("/", "_", trim($cm->real_path_info, "/"))
					, "print_" . str_replace("/", "_", trim($cm->real_path_info, "/")) . ".css"
		        	, null
		        	, "stylesheet"
		        	, "text/css"
		        	, "/themes/" . FRONTEND_THEME . "/css"
		        	, false
		        	, "print"
                    , false
                    , "bottom"
			    );

	$print_css = ", overrideElementCSS: ['http://" . DOMAIN_INSET . SITE_PATH . "/themes/" . FRONTEND_THEME . "/css/print_" . str_replace("/", "_", trim($cm->real_path_info, "/")) . ".css']";
}

$oButton_print->jsaction = "ff.pluginLoad('jquery.printElement', '/themes/library/plugins/jquery.printelement/jquery.printelement.js', function() { jQuery('#" . $oRecord->id . "_data').printElement({ pageTitle : '" . str_replace("/", " - ", ltrim($cm->real_path_info, "/")) . "'" . $print_css . " }); });";
$oButton_print->label = ffTemplate::_get_word_by_code("ffRecord_print");//Definita nell'evento
$oRecord->addActionButton($oButton_print);*/

$cm->oPage->addContent($oRecord);   

function PhonecallModify_on_done_action($component, $action) {
    
   
}
?>