<?php
$permission = check_phonecall_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$db = ffDB_Sql::factory();

$UserNID = get_session("UserNID");

$cm->oPage->addContent(null, true, "rel");

if(check_phonecall_permission(true) != MOD_PHONECALL_GROUP_ADMIN) {
	$sSQL_Where = " AND (
						" . CM_TABLE_PREFIX . "mod_phonecall_message.ID_recipient IN (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . ")
						OR
						" . CM_TABLE_PREFIX . "mod_phonecall_message.ID_CC IN (SELECT anagraph.ID FROM anagraph WHERE anagraph.uid = " . $db->toSql($UserNID, "Number") . ")
					)";
}
/*
if($_REQUEST["frmAction"] == "phonecall_export") {
    $oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_xls"));
} else {
	$oGrid = ffGrid::factory($cm->oPage);
}*/
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "phonecall";
$oGrid->title = ffTemplate::_get_word_by_code("phonecall_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_phonecall_message.*
							, CONCAT(
								IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.ID_recipient > 0
									, CONCAT(
										'<div class=\"recipient\">'
										, (SELECT IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
											FROM anagraph
											WHERE anagraph.ID = " . CM_TABLE_PREFIX . "mod_phonecall_message.ID_recipient
										)
										, '</div>'
									)
									, ''
								)
								, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.ID_CC > 0
									, CONCAT(
										'<div class=\"cc\">'
										, (SELECT IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
											FROM anagraph
											WHERE anagraph.ID = " . CM_TABLE_PREFIX . "mod_phonecall_message.ID_CC
										)
										, '</div>'
									)
									, ''
								)
							) AS recipient
                            , CONCAT(
                            	IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.phone = ''
                            		, ''
                            		, CONCAT('<div class=\"phone\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_phone") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.phone, '</div>')
                            	)
                            	, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.fax = ''
                            		, ''
                            		, CONCAT('<div class=\"fax\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_fax") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.fax, '</div>')
                            	)
                            	, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.cell = ''
                            		, ''
                            		, CONCAT('<div class=\"cell\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_cell") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.cell, '</div>')
                            	)
                            	, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.email = ''
                            		, ''
                            		, CONCAT('<div class=\"email\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_email") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.email, '</div>')
                            	)
                            ) AS contact
	                        , (
	                        	SELECT IF(anagraph.uid > 0
		                            , IF(anagraph.billreference = ''
		                                , IF(CONCAT(anagraph.name, '', anagraph.surname) <> ''
                                			, IF(CONCAT(anagraph.name, ' ', anagraph.surname) = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                				, CONCAT(anagraph.name, ' ', anagraph.surname)
                                				, CONCAT(CONCAT(anagraph.name, ' ', anagraph.surname))
                                			)
                                			, IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
		                                )
		                                , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                                			, CONCAT(anagraph.name, ' ', anagraph.surname)
                                			, CONCAT(anagraph.billreference)
		                                )
		                            )
		                            , IF(anagraph.billreference = ''
                            			, CONCAT(anagraph.name, ' ', anagraph.surname)
                            			, anagraph.billreference
		                            )
		                        )
		                        FROM anagraph 
	                        		INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
		                        WHERE anagraph.uid = " . CM_TABLE_PREFIX . "mod_phonecall_message.owner

	                        ) AS owner 
                        FROM
                            " . CM_TABLE_PREFIX . "mod_phonecall_message
                        WHERE " . CM_TABLE_PREFIX . "mod_phonecall_message.status > 0
                        	$sSQL_Where
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "PhonecallModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = false;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
//$oGrid->buttons_options["export"]["display"] = true;

if(check_phonecall_permission(true) == MOD_PHONECALL_GROUP_ADMIN) {
	$oGrid->display_delete_bt = true;
} else {
	$oGrid->display_delete_bt = false;	
}

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_dir = "DESC";
$oGrid->addKeyField($oField);

// Campi di ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "data_ins";
$oField->container_class = "date";
$oField->data_source = "date";
$oField->src_table = CM_TABLE_PREFIX . "mod_phonecall_message";
$oField->base_type = "Date";
$oField->label = ffTemplate::_get_word_by_code("phonecall_date_label");
$oField->widget = "datepicker";
$oField->interval_from_label = ffTemplate::_get_word_by_code("phonecall_date_from");
$oField->interval_to_label = ffTemplate::_get_word_by_code("phonecall_date_to");
$oField->src_interval = true;
$oField->src_operation = "DATE([NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_ins";
$oField->container_class = "time";
$oField->data_source = "time";
$oField->src_table = CM_TABLE_PREFIX . "mod_phonecall_message";
$oField->base_type = "Time";
$oField->label = ffTemplate::_get_word_by_code("phonecall_time_label");
//$oField->widget = "timepicker";
$oField->interval_from_label = ffTemplate::_get_word_by_code("phonecall_time_from");
$oField->interval_to_label = ffTemplate::_get_word_by_code("phonecall_time_to");
$oField->src_interval = true;
$oField->src_operation = "TIME([NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "reference";
$oField->container_class = "reference";
$oField->label = ffTemplate::_get_word_by_code("phonecall_reference");
$oField->source_SQL = "SELECT DISTINCT
				        " . CM_TABLE_PREFIX . "mod_phonecall_message.reference AS nameID
				        , " . CM_TABLE_PREFIX . "mod_phonecall_message.reference AS name
				    FROM " . CM_TABLE_PREFIX . "mod_phonecall_message
				    WHERE 1
				    	$sSQL_Where
				    ORDER BY nameID";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oGrid->addSearchField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->container_class = "date";
$oField->label = ffTemplate::_get_word_by_code("phonecall_date");
$oField->base_type = "Date";
$oField->extended_type = "Date";
$oField->app_type = "Date";	
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time";
$oField->container_class = "time";
$oField->label = ffTemplate::_get_word_by_code("phonecall_time");
$oField->base_type = "Time";
$oField->extended_type = "Time";
$oField->app_type = "Time";	
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "owner";
$oField->container_class = "owner";
$oField->label = ffTemplate::_get_word_by_code("phonecall_owner");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "recipient";
$oField->container_class = "recipient";
$oField->label = ffTemplate::_get_word_by_code("phonecall_recipient");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "reference";
$oField->container_class = "reference";
$oField->label = ffTemplate::_get_word_by_code("phonecall_reference");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contact";
$oField->container_class = "contact";
$oField->label = ffTemplate::_get_word_by_code("phonecall_contact");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "message";
$oField->container_class = "message";
$oField->label = ffTemplate::_get_word_by_code("phonecall_message");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("phonecall_archived"))); 


if(check_phonecall_permission(true) != MOD_PHONECALL_GROUP_ADMIN) {
	$sSQL_Where = " AND (
						" . CM_TABLE_PREFIX . "mod_phonecall_message.owner = " . $db->toSql($UserNID, "Number") . "
					)";
}
/*
if($_REQUEST["frmAction"] == "phonecallOwned_export") {
    $oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_xls"));
} else {
	$oGrid = ffGrid::factory($cm->oPage);
}*/
$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "phonecallOwned";
$oGrid->title = ffTemplate::_get_word_by_code("phonecall_title");
$oGrid->source_SQL = "SELECT
                            " . CM_TABLE_PREFIX . "mod_phonecall_message.*
							, CONCAT(
								IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.ID_recipient > 0
									, CONCAT(
										'<div class=\"recipient\">'
										, (SELECT IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
											FROM anagraph
											WHERE anagraph.ID = " . CM_TABLE_PREFIX . "mod_phonecall_message.ID_recipient
										)
										, '</div>'
									)
									, ''
								)
								, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.ID_CC > 0
									, CONCAT(
										'<div class=\"cc\">'
										, (SELECT IF(anagraph.billreference = '', CONCAT(anagraph.name, ' ', anagraph.surname), anagraph.billreference)
											FROM anagraph
											WHERE anagraph.ID = " . CM_TABLE_PREFIX . "mod_phonecall_message.ID_CC
										)
										, '</div>'
									)
									, ''
								)
							) AS recipient
                            , CONCAT(
                            	IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.phone = ''
                            		, ''
                            		, CONCAT('<div class=\"phone\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_phone") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.phone, '</div>')
                            	)
                            	, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.fax = ''
                            		, ''
                            		, CONCAT('<div class=\"fax\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_fax") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.fax, '</div>')
                            	)
                            	, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.cell = ''
                            		, ''
                            		, CONCAT('<div class=\"cell\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_cell") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.cell, '</div>')
                            	)
                            	, IF(" . CM_TABLE_PREFIX . "mod_phonecall_message.email = ''
                            		, ''
                            		, CONCAT('<div class=\"email\">', '<label>" . ffTemplate::_get_word_by_code("phonecall_email") . "</label>', ' ', " . CM_TABLE_PREFIX . "mod_phonecall_message.email, '</div>')
                            	)

                            ) AS contact
                        FROM
                            " . CM_TABLE_PREFIX . "mod_phonecall_message
                        WHERE " . CM_TABLE_PREFIX . "mod_phonecall_message.status > 0
                        	$sSQL_Where
                        [AND] [WHERE] 
                        [HAVING]
                        [ORDER]";

$oGrid->order_default = "ID";
$oGrid->use_search = true;
$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify";
$oGrid->record_id = "PhonecallModify";
$oGrid->resources[] = $oGrid->record_id;
$oGrid->display_new = false;
$oGrid->display_edit_bt = false;
$oGrid->display_edit_url = true;
//$oGrid->buttons_options["export"]["display"] = true;

if(check_phonecall_permission(true) == MOD_PHONECALL_GROUP_ADMIN) {
	$oGrid->display_delete_bt = true;
} else {
	$oGrid->display_delete_bt = false;	
}

// Campi chiave
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oField->order_dir = "DESC";
$oGrid->addKeyField($oField);

// Campi di ricerca
$oField = ffField::factory($cm->oPage);
$oField->id = "data_ins";
$oField->container_class = "date";
$oField->data_source = "date";
$oField->src_table = CM_TABLE_PREFIX . "mod_phonecall_message";
$oField->base_type = "Date";
$oField->label = ffTemplate::_get_word_by_code("phonecall_date_label");
$oField->widget = "datepicker";
$oField->interval_from_label = ffTemplate::_get_word_by_code("phonecall_date_from");
$oField->interval_to_label = ffTemplate::_get_word_by_code("phonecall_date_to");
$oField->src_interval = true;
$oField->src_operation = "DATE([NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time_ins";
$oField->container_class = "time";
$oField->data_source = "time";
$oField->src_table = CM_TABLE_PREFIX . "mod_phonecall_message";
$oField->base_type = "Time";
$oField->label = ffTemplate::_get_word_by_code("phonecall_time_label");
//$oField->widget = "timepicker";
$oField->interval_from_label = ffTemplate::_get_word_by_code("phonecall_time_from");
$oField->interval_to_label = ffTemplate::_get_word_by_code("phonecall_time_to");
$oField->src_interval = true;
$oField->src_operation = "TIME([NAME])";
$oGrid->addSearchField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "reference";
$oField->container_class = "reference";
$oField->label = ffTemplate::_get_word_by_code("phonecall_reference");
$oField->source_SQL = "SELECT DISTINCT
				        " . CM_TABLE_PREFIX . "mod_phonecall_message.reference AS nameID
				        , " . CM_TABLE_PREFIX . "mod_phonecall_message.reference AS name
				    FROM " . CM_TABLE_PREFIX . "mod_phonecall_message
				    WHERE 1
				    	$sSQL_Where
				    ORDER BY nameID";
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true;
$oGrid->addSearchField($oField);

// Campi visualizzati
$oField = ffField::factory($cm->oPage);
$oField->id = "date";
$oField->container_class = "date";
$oField->label = ffTemplate::_get_word_by_code("phonecall_date");
$oField->base_type = "Date";
$oField->extended_type = "Date";
$oField->app_type = "Date";	
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "time";
$oField->container_class = "time";
$oField->label = ffTemplate::_get_word_by_code("phonecall_time");
$oField->base_type = "Time";
$oField->extended_type = "Time";
$oField->app_type = "Time";	
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "recipient";
$oField->container_class = "recipient";
$oField->label = ffTemplate::_get_word_by_code("phonecall_recipient");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "reference";
$oField->container_class = "reference";
$oField->label = ffTemplate::_get_word_by_code("phonecall_reference");
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "contact";
$oField->container_class = "contact";
$oField->label = ffTemplate::_get_word_by_code("phonecall_contact");
$oField->encode_entities = false;
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "message";
$oField->container_class = "message";
$oField->label = ffTemplate::_get_word_by_code("phonecall_message");
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("phonecall_archived_owned"))); 
?>
