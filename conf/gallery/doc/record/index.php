<?php
$db = ffDB_Sql::factory(); 
/** 
 * Inizializzazione dell'oggetto ffRecord, 
 * $oRecord è lo standard, ma può essere usato qualsiasi nome, 
 * purchè sia rispettata la coerenza in seguito 
*/
$oRecord = ffRecord::factory($cm->oPage);
/** 
 * ID dell'oggetto. 
 * Se questo oggetto è in relazione con un altro (modifica) è importante che questo campo coincida 
 * con il record_ID del campo originale 
 */
$oRecord->id = "UtentiModify";
/** 
 * resources è un array che viene popolato con gli ID degli oggetti su cui si sta lavorando
 */
$oRecord->resources[] = $oRecord->id;
/**
 * Impedisce che le modifiche effettuate in questa zona di test diventino effettive
 */
$oRecord->skip_action = true;
/**
 * Titolo dell'oggetto record 
 */
$oRecord->title = ffTemplate::_get_word_by_code("utenti_modify"); 
/**
 * Tabella da cui vengono prese e/o salvate le informazioni.
 * Può essere dichiarata una sola tabella, se i dati arrivano da più fonti la gestione
 * verrà delegata negli eventi
 */
$oRecord->src_table = "cm_mod_security_users";

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
 * Nel caso sia un record in modifica è importante abbia un nome coerente con quello del campo chiave in visualizzazione. 
*/
$oRecord->addKeyField($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar";
/**
 * Dichiaro la classe che conterrà il campo (è opzionale)
 */
$oField->container_class = "avatar_uploadifive";
$oField->label = ffTemplate::_get_word_by_code("uploadifive"); 
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/doc/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/doc";
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = true;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->widget = "uploadify";
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar_uploadify";
$oField->label = ffTemplate::_get_word_by_code("uploadify");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/doc/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/doc";
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->file_writable = true;
$oField->widget = "uploadify"; 
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar2";
$oField->label = ffTemplate::_get_word_by_code("kcuploadifive");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/doc/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/doc";
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->file_writable = true;
$oField->widget = "kcuploadify"; 
if(check_function("set_field_uploader")) { 
	$oField = set_field_uploader($oField);
}	
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar_kcuploadifive";
$oField->label = ffTemplate::_get_word_by_code("kcuploadify");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/doc/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/doc";
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->file_writable = true;
$oField->widget = "kcuploadify"; 
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar_ckuploadify";
$oField->label = ffTemplate::_get_word_by_code("ckuploadifive");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/doc/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/doc";
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->file_writable = true;
$oField->widget = "ckuploadify"; 
if(check_function("set_field_uploader")) { 
//	$oField = set_field_uploader($oField);
}	
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "avatar_ckuploadifive";
$oField->label = ffTemplate::_get_word_by_code("ckuploadify");
$oField->base_type = "Text";
$oField->extended_type = "File";
$oField->file_storing_path = FF_DISK_UPDIR . "/doc/record";
$oField->file_temp_path = FF_DISK_UPDIR . "/tmp/doc";
$oField->file_show_filename = true;
$oField->file_full_path = true;
$oField->file_check_exist = false;
$oField->file_normalize = true;
$oField->file_show_preview = true;
$oField->file_saved_view_url = CM_SHOWFILES . "/[_FILENAME_]";
$oField->file_saved_preview_url = CM_SHOWFILES . "/avatar/[_FILENAME_]";
$oField->control_type = "file";
$oField->file_show_delete = true;
$oField->file_writable = true;
$oField->widget = "ckuploadify"; 
$oField->store_in_db = false;
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "username";
$oField->label = ffTemplate::_get_word_by_code("utenti_username");
$oField->base_type = "Text";
/** 
 * Indica l'obbligatorietà  del campo in questione, 
 * se non compilato restituirà  un errore 
 */
$oField->required = true;
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("utenti_name");
$oField->base_type = "Text";
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "surname";
$oField->label = ffTemplate::_get_word_by_code("utenti_surname");
$oField->base_type = "Text";
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "email";
$oField->label = ffTemplate::_get_word_by_code("utenti_email");
$oField->addValidator("email");
/* viene effettuato un controllo sulla coerenza del tipo di dato richiesto con il valore inserito, in caso questo non avvenga 
viene segnalato l'errore */
$oField->required = true;
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "status";
$oField->label = ffTemplate::_get_word_by_code("utenti_status");
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
/* indica la tipologia di campo che richiedo, nel db verrÃ  salvato lo 0 o un 1, quindi un numero, ma quello che sto chiedendo all'utente segue 
le regole del booleano */
$oField->control_type = "checkbox";
/* tipo di controllo che voglio che venga fatto, in questo caso a fronte di un dato booleano chiedo che corrisponda un checkbox, per cui avrÃ²
una casella da spuntare */
$oField->checked_value = new ffData("1", "Number");
$oField->unchecked_value = new ffData("0", "Number");
/* dichiaro i valori che voglio che la casella assuma in base al fatto di essere selezionata o no, sono due oggetti e il loro valore deve 
essere coerente al tipo di dato richiesto */
$oField->default_value = new ffData("1", "Number");
/* dichiaro il valore che voglio che questo campo abbia di base. Questa riga ha valore solo in caso di aggiunta di un elemento, 
in fase di modifica il valore del campo viene recuperato dal db */
$oField->store_in_db = false;
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "ID_type";
$oField->label = ffTemplate::_get_word_by_code("utenti_ID_type");
$oField->base_type = "Number";
$oField->widget = "actex";
$oField->source_SQL = "SELECT anagraph_type.ID,
								anagraph_type.name
							FROM anagraph_type";
$oField->actex_update_from_db = true;
$oField->store_in_db = false;
$oField->actex_dialog_url = $cm->oPage->site_path . VG_SITE_ADMIN . "/ecommerce/anagraph/type/modify";
$oField->actex_dialog_edit_params = array("keys[ID]" => null);
$oField->actex_dialog_delete_url = $oField->actex_dialog_url . "?frmAction=AnagraphTypeModify_confirmdelete";
$oField->resources[] = "AnagraphTypeModify";
$oRecord->addContent($oField); 

$oField = ffField::factory($cm->oPage);
$oField->id = "last_update";
$oField->label = ffTemplate::_get_word_by_code("utenti_last_update");
$oField->widget = "datepicker";
$oField->base_type = "Timestamp";
$oField->extended_type = "Date"; 
$oField->app_type = "Date";
$oField->default_value = new ffData(time(), "Timestamp");
$oField->store_in_db = false;
$oRecord->addContent($oField);

if(!isset($_REQUEST["keys"]["ID"]))
{
	$oField = ffField::factory($cm->oPage);
	$oField->id = "gmap_address";
	$oField->label = ffTemplate::_get_word_by_code("utenti_gmap");
	$oField->properties["style"]["width"] = "400px";
	$oField->properties["style"]["height"] = "200px";
	$oField->widget = "gmap";
	$oField->gmap_draggable = true;
	$oField->gmap_start_zoom = 10;
	$oField->gmap_force_search = true;
	$oField->gmap_update_class = "billaddress,billtown,billprovince,billstate";
	$oField->gmap_update_class_prefix = "bill";
	if(check_function("set_field_gmap")) { 
		$oField = set_field_gmap($oField);
	}
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "slider";
	$oField->label = ffTemplate::_get_word_by_code("utenti_slider");
	$oField->base_type = "Number";
	$oField->default_value = new ffData("75", "Number");
	$oField->widget = "slider";
	$oField->min_val = "0";
	$oField->max_val = "100";
	$oField->step = "5";
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->label = ffTemplate::_get_word_by_code("utenti_listgroup");
	$oField->id = "dependence";
	$oField->source_SQL = "SELECT DISTINCT description, description FROM settings ORDER BY description";
	$oField->widget = "listgroup";
	$oField->grouping_separator = ";";
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

	

	$oField = ffField::factory($cm->oPage);
	$oField->id = "location";
	$oField->label = ffTemplate::_get_word_by_code("utenti_checkgroup");
	$oField->extended_type = "Selection";
	$oField->widget = "checkgroup";
	$oField->grouping_separator = ",";  
	$oField->multi_pairs = array (
								array(new ffData("nothing"), new ffData(ffTemplate::_get_word_by_code("nothing"))), 
								array(new ffData("bill_qta_received"), new ffData(ffTemplate::_get_word_by_code("bill_qta_received"))), 
								array(new ffData("bill_qta_sent"), new ffData(ffTemplate::_get_word_by_code("bill_qta_sent"))),
								array(new ffData("bill_time_received"), new ffData(ffTemplate::_get_word_by_code("bill_time_received"))),
								array(new ffData("bill_time_sent"), new ffData(ffTemplate::_get_word_by_code("bill_time_sent"))),
								array(new ffData("bill_services_received"), new ffData(ffTemplate::_get_word_by_code("bill_services_received"))),
								array(new ffData("bill_services_sent"), new ffData(ffTemplate::_get_word_by_code("bill_services_sent")))
						   );      
	$oField->required = true;
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_items";
	$oField->label = ffTemplate::_get_word_by_code("utenti_autocompletetoken");
	$oField->base_type = "Number";
	$oField->required = true;
	//$oField->extended_type = "Selection";
	$oField->widget = "autocompletetoken";
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_limit = 1;
	$oField->autocompletetoken_theme = "";
	$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
	$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
	$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
	$oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
	$oField->autocompletetoken_combo = true;
	$oField->autocompletetoken_compare_having = "name";
	$oField->source_SQL = "SELECT ID, username FROM cm_mod_security_users
						   [WHERE]
						   [HAVING]
						   [ORDER] 
						   [LIMIT]"; 
	$oField->store_in_db = false;
	$oRecord->addContent($oField); 

	$oField = ffField::factory($cm->oPage);
	$oField->id = "vat_ns_description";    
	$oField->label = ffTemplate::_get_word_by_code("utenti_autocomplete");
	$oField->extended_type = "Selection";
	$oField->widget = "autocomplete";
	$oField->autocomplete_minLength = 0;
	$oField->autocomplete_combo = true;
	$oField->autocomplete_compare_having = "name";
	$oField->autocomplete_readonly = false;
	$oField->autocomplete_operation = "LIKE [[VALUE]%]"; 
	$oField->source_SQL = "SELECT DISTINCT " . FF_PREFIX . "international.description AS ID
								, " . FF_PREFIX . "international.description AS name
							FROM " . FF_PREFIX . "international
							WHERE " . FF_PREFIX . "international.word_code LIKE 'vat_ns%'
								AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
								AND " . FF_PREFIX . "international.is_new = 0
							[AND] [WHERE]
							[HAVING]
							[ORDER] [COLON] " . FF_PREFIX . "international.description
							[LIMIT]";
	$oField->actex_update_from_db = true;
	$oField->store_in_db = false;
	$oRecord->addContent($oField);

	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "value";
	$oField->label = ffTemplate::_get_word_by_code("utenti_ckeditor");
	$oField->control_type = "textarea";
	if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
	    $oField->widget = "ckeditor";
	} else {
	    $oField->widget = "";
	}
	$oField->ckeditor_group_by_auth = true;
	$oField->extended_type = "Text";
	$oField->base_type = "Text";
	$oField->store_in_db = false;
	$oRecord->addContent($oField);
	
	/*
	$oField = ffField::factory($cm->oPage);
	$oField->id = "full_description";
	$oField->container_class = "task-full-description";
	$oField->label = ffTemplate::_get_word_by_code("utenti_tiny_mce");
	$oField->extended_type = "Text";
	$oField->widget = "tiny_mce";
	$oField->store_in_db = false;
	$oRecord->addContent($oField);
	*/
	$oField = ffField::factory($cm->oPage);
	$oField->id = "smart_url";
	$oField->label = ffTemplate::_get_word_by_code("utenti_slug");
	$oField->widget = "slug";
	$oField->slug_title_field = "name";
	$oField->properties["readonly"] = "readonly";
	$oField->store_in_db = false;
	$oRecord->addContent($oField);
}
/** 
 * Viene innestato l'oggetto $oRecord all'interno della pagina 
 */
$cm->oPage->addContent($oRecord);