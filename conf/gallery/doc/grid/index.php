<?php

/**
 * Inizializza la struttura a tab
 */
$cm->oPage->addContent(null, true, "rel"); 

/** 
 * Inizializzazione dell'oggetto ffGrid, 
 * $oGrid è lo standard, ma può essere usato qualsiasi nome, 
 * purchè sia rispettata la coerenza in seguito 
*/
$oGrid = ffGrid::factory($cm->oPage);
/** 
 * ID della griglia, deve essere univoco nella pagina 
 */
$oGrid->id = "utenti_semplice";
/** 
 * La query utilizzata per recuperare i dati da visualizzare. 
 * Possono essere anche più tabelle in join.
 * [AND] [WHERE] [HAVING] [ORDER]) devono essere lasciati,
 * perche vengono automaticamente popolati dai parametri della grid
 */
$oGrid->source_SQL = "SELECT *
						FROM cm_mod_security_users
						WHERE 1
						[AND] [WHERE] [HAVING] [ORDER]";
/** 
 * Il campo rispetto al quale ordinare i dati estratti della tabella. 
 * Deve essere uno dei campi dichiarati in tabella. 
 */
$oGrid->order_default = "username";
/** 
 * Nasconde il tasto add_new
 */
$oGrid->display_new = false;
/**
 * Nasconde il campo di ricerca
 */
$oGrid->use_search = false;
/**
 * Nasconde il campo "esporta"
 */
$oGrid->buttons_options["export"]["display"] = false;
$oGrid->use_paging = false;

/** 
 * Inizializzazione dell'oggetto ffField, 
 * elemento base di tutte le sovrastrutture del framework (grid, record e detail) 
 */
$oField = ffField::factory($cm->oPage);
/** 
 * ID del field, deve essere univoco all'interno di un oggetto 
 */
$oField->id = "ID";
/** 
 * Tipo del dato, 
 * se non espresso si sottointende Text 
 */
$oField->base_type = "Number";
/** 
 * Viene dichiarato il campo chiave (possono essere più di uno), 
 * non sono visibili all'interno della tabella (se si vuole vedere questo dato bisogna ridichiararlo sotto). 
 * Serve per la visualizzazione e per la modifica dei dati 
 */
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
/** 
 * Il nome con cui si vuole che il campo sia visualizzato
 */
$oField->label = ffTemplate::_get_word_by_code("utenti_username");
$oField->base_type = "Text";
/** 
 * Viene aggiunto il campo(visibile) alla tabella 
 */
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("utenti_email");
$oField->base_type = "Text";
$oGrid->addContent($oField);

/** 
 * Inserisce la struttura all'interno di un tab, dichiarandone la label 
 */
$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("griglia_semplice"))); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->id = "utenti_con_aggiunta";
$oGrid->source_SQL = "SELECT *
			FROM cm_mod_security_users
			WHERE 1
			[AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "username";
/** 
 * Percorso di riferimento del record.
 */
$oGrid->record_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/record";
/** 
 * Nome dell'oggetto che elaborerà  il dato in modifica 
 */
$oGrid->record_id = "UtentiModify";
/** 
 * resources è un array che viene popolato con gli ID degli oggetti su cui si sta lavorando
 */
$oGrid->resources[] = $oGrid->record_id;
/**
 * Abilita ajax per le ricerche
 */
$oGrid->ajax_search = true;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
/**
 * Nasconde la label del campo
 */
$oField->display_label = false;
$oField->label = ffTemplate::_get_word_by_code("utenti_avatar");
$oField->base_type = "Text";
$oField->extended_type = "File";
/**
 * Percorso in cui verrà salvato il file
 */
$oField->file_storing_path = DISK_UPDIR . "/anagraph/[ID_VALUE]";
/**
 * Percorso in cui verrà salvato il file temporaneo
 */
$oField->file_temp_path = DISK_UPDIR . "/anagraph";
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->uploadify_model = $avatar_model;
$oField->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $oField->uploadify_model_thumb . "/[_FILENAME_]";
$oField->control_type = "picture_no_link";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("utenti_username");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("utenti_email");
$oField->base_type = "Text";
$oGrid->addContent($oField);

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("griglia_con_aggiunta"))); 


$oGrid = ffGrid::factory($cm->oPage);
/** 
 * Tutti le operazioni vengono eseguite in dialog, 
 * è altrimenti possibile decidere per le singole operazioni se si voglia la dialog o 
 * il reindirizzamento in pagina
 */
$oGrid->full_ajax = true;
$oGrid->id = "utenti_con_dialog";
$oGrid->source_SQL = "SELECT *
			FROM cm_mod_security_users
			WHERE 1
			[AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "username";
$oGrid->record_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/record";
$oGrid->record_id = "UtentiModify";
$oGrid->resources[] = $oGrid->record_id;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
$oField->display_label = false;
$oField->label = ffTemplate::_get_word_by_code("utenti_avatar");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/anagraph/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/anagraph";
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->uploadify_model = $avatar_model;
$oField->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $oField->uploadify_model_thumb . "/[_FILENAME_]";
$oField->control_type = "picture_no_link";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("utenti_username");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("utenti_email");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("griglia_con_dialog"))); 

$oGrid = ffGrid::factory($cm->oPage);
$oGrid->full_ajax = true;
$oGrid->id = "utenti_con_dragsort";
$oGrid->source_SQL = "SELECT *
			FROM cm_mod_security_users
			WHERE 1
			[AND] [WHERE] [HAVING] [ORDER]";
$oGrid->order_default = "username";
$oGrid->record_url = $cm->oPage->site_path . ffCommon_dirname($cm->oPage->page_path) . "/record";
$oGrid->record_id = "UtentiModify";
$oGrid->resources[] = $oGrid->record_id;
/**
 * Abilitazione dragsort per ordinare elementi
 */
$oGrid->widget_deps[] = array(
	"name" => "dragsort"
	, "options" => array(
		  &$oGrid
		, array(
			"resource_id" => "layout_location"
			, "service_path" => $cm->oPage->site_path . $cm->oPage->page_path
		)
		, "ID"
	)
);

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oGrid->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
$oField->display_label = false;
$oField->label = ffTemplate::_get_word_by_code("utenti_avatar");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = DISK_UPDIR . "/anagraph/[ID_VALUE]";
$oField->file_temp_path = DISK_UPDIR . "/anagraph";
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->uploadify_model = $avatar_model;
$oField->uploadify_model_thumb = ($avatar_model == "default" ? "avatar" : "avatar" . $avatar_model);
$oField->file_saved_view_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/[_FILENAME_]";
$oField->file_saved_preview_url = FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . $oField->uploadify_model_thumb . "/[_FILENAME_]";
$oField->control_type = "picture_no_link";
$oGrid->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("utenti_username");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("utenti_email");
$oField->base_type = "Text";
$oGrid->addContent($oField); 

$cm->oPage->addContent($oGrid, "rel", null, array("title" => ffTemplate::_get_word_by_code("griglia_con_dragsort"))); 

