<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$is_owner = false;
if (!Auth::env("AREA_STATIC_SHOW_MODIFY")) {
$owner = $_REQUEST["owner"];
	if($owner == Auth::get("user")->id) {
    	use_cache(false);
    	$is_owner = true;
	} else {
	    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
}

if(strpos($cm->oPage->page_path, VG_SITE_MENU) === 0) {
	$simple_interface = true;
} else {
	$simple_interface = false;
}

$parent = urldecode($_REQUEST["parent"]);

// -------------------------
//          PAGINA
// -------------------------
if($_REQUEST["keys"]["ID"] > 0) {
    $sSql = "
        SELECT static_pages.*
        FROM `static_pages`
        WHERE static_pages.ID = " . $db_gallery->toSql(new ffData($_REQUEST["keys"]["ID"], "Number"));
    $db_gallery->query($sSql);
    if ($db_gallery->nextRecord()) {
        $user_path = stripslash($db_gallery->getField("parent")->getValue()) . "/" . $db_gallery->getField("name")->getValue();
        $static_name = $db_gallery->getField("name")->getValue();
        if($user_path == "/") {
            $user_path = "/home";
            $is_home = true;
		}
    }
} elseif(!strlen($parent)) {
	if(isset($_REQUEST["fullpath"])) {
		$full_path = $_REQUEST["fullpath"];
	} else {
		$full_path = $cm->real_path_info;
	}
	if($full_path == "/home")
		$full_path = "/";

    if($full_path == "/") {
        $sSql = "
            SELECT static_pages.ID AS ID_static_pages
            FROM `static_pages`
            WHERE static_pages.parent = " . $db_gallery->toSql("/") .  " 
        	    AND static_pages.name = " . $db_gallery->toSql("") . "
        	    AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number");
        $db_gallery->query($sSql);
        if ($db_gallery->nextRecord()) { 
            $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID_static_pages", "Number")->getValue();
            $user_path = "/home";
            $static_name = "";
            $is_home = true;
	    } else {
            $_REQUEST["keys"]["ID"] = "";
        }
    } elseif(strlen($full_path)) {
        $sSql = "
            SELECT static_pages.ID AS ID_static_pages
                , static_pages.name AS name 
            FROM `static_pages`
            WHERE static_pages.parent = " . $db_gallery->toSql(ffCommon_dirname($full_path)) .  " 
                AND static_pages.name = " . $db_gallery->toSql(basename($full_path)) . "
                AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number");
        $db_gallery->query($sSql);
        if ($db_gallery->nextRecord()) { 
            $_REQUEST["keys"]["ID"] = $db_gallery->getField("ID_static_pages", "Number")->getValue();
            $static_name = $db_gallery->getField("name")->getValue();
        } else {
            $_REQUEST["keys"]["ID"] = "";
        } 
    } else {
    	unset($_REQUEST["keys"]["ID"]);
        //$_REQUEST["keys"]["ID"] = "";
    }
    
} 

$ID_static_pages = $_REQUEST["keys"]["ID"];

if($ID_static_pages > 0 && isset($_REQUEST["frmAction"]) && isset($_REQUEST["setvisible"])) {
    if(check_function("get_locale"))
        $arrLang = get_locale("lang", true);    
    
    if(is_array($arrLang) && count($arrLang)) { 
        check_function("update_vgallery_seo");
        check_function("get_schema_fields_by_type");

		$src = get_schema_fields_by_type("page");

        foreach($arrLang AS $lang_code => $lang) {                    
            update_vgallery_seo(null, $ID_static_pages, $lang["ID"], null, null, null, $_REQUEST["setvisible"], null, $src["seo"], $src["field"]);
        }
    }                
    
	if(check_function("refresh_cache")) {
	    refresh_cache($src["cache"]["type"]
            , $ID_static_pages
            , ($_REQUEST["setvisible"] 
                ? "insert" 
                : "update"
            )
            , $user_path
        );
	}

	if($_REQUEST["XHR_DIALOG_ID"]) {
	    die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("StaticModify")), true));
	} else {
	    die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("StaticModify")), true));
	    //ffRedirect($_REQUEST["ret_url"]);
	}
} 


if(isset($_REQUEST["keys"]["ID"]))
{
	$static_menu_title = ffTemplate::_get_word_by_code("modify_static_menu");
	$sSQL = "SELECT static_pages.parent
					, static_pages.name
				FROM static_pages
				WHERE static_pages.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord())
	{
		$parent = $db_gallery->getField("parent", "Text", true);
		$name = $db_gallery->getField("name", "Text", true);
		if($parent == "/")
			$path = $parent . $name;
		else
			$path = $parent . "/" . $name;
		$static_menu_title_url = $path;
	}
} else 
{
	$static_menu_title = ffTemplate::_get_word_by_code("addnew_static_menu");
}

$root_path = "/";
$location = "";
$max_sort = 0;
if(strlen($parent)) {
	$sSQL = "SELECT static_pages.sort AS sort
				, static_pages.location AS location
			FROM static_pages 
			WHERE static_pages.parent = " . $db_gallery->toSql($parent) . "
				AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number") . "
				AND static_pages.location <> ''
			ORDER BY sort DESC ";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$max_sort = $db_gallery->getField("sort", "Number", true) + 1;
		$location = $db_gallery->getField("location", "Text", true);
	}
}

if($is_owner) {
	$sSQL = "SELECT COUNT(static_pages.ID) AS count_page
				, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) AS full_path
				, static_pages.location AS location
			FROM static_pages 
			WHERE static_pages.owner = " . $db_gallery->toSql(Auth::get("user")->id, "Number") . "
				AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number") . "
			ORDER BY LENGTH(full_path)";
	$db_gallery->query($sSQL);
	if($db_gallery->nextRecord()) {
		$root_path = $db_gallery->getField("full_path", "Text", true);
		$location = (strlen($db_gallery->getField("location", "Text", true)) ? $db_gallery->getField("location", "Text", true) : $location);
	}
}

// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "StaticModify";
$oRecord->resources[] = $oRecord->id;
//$oRecord->title = ffTemplate::_get_word_by_code("static_page_title");
$oRecord->src_table = "static_pages";
$oRecord->buttons_options["delete"]["display"] = Auth::env("AREA_STATIC_SHOW_DELETE");
$oRecord->addEvent("on_do_action", "StaticModify_on_do_action");
$oRecord->addEvent("on_done_action", "StaticModify_on_done_action");
$oRecord->user_vars["user_path"] = $user_path;
$oRecord->user_vars["parent"] = $parent;
$oRecord->user_vars["old_name"] = $static_name;
$oRecord->user_vars["is_home"] = $is_home;
$oRecord->insert_additional_fields["visible"] = new ffData("1", "Number");

/* Title Block */

$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-menu">' . Cms::getInstance("frameworkcss")->get("vg-static-menu", "icon-tag", array("2x", "menu")) . $static_menu_title . '<span class="smart-url">' . $static_menu_title_url . '</span>' . '<a class="slug-gotourl" href="javascript:void(0);" target="_blank">' . ffTemplate::_get_word_by_code("goto_url") . '</a>' .'</h1>';

if($is_home) {
    $oRecord->allow_delete     = false;
}

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);
/* 
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("static_page_name");

if($user_path == "/home" && !$parent) {
    $oField->control_type = "label";
} else {
    $oField->required = true;
}
$oRecord->addContent($oField);*/
if(!$is_home) {
	if($globals->ID_domain > 0) {
		$oRecord->insert_additional_fields["ID_domain"] =  new ffData($globals->ID_domain, "Number");
		$oRecord->additional_fields["ID_domain"] =  new ffData($globals->ID_domain, "Number");
		$oRecord->user_vars["ID_domain"] = $globals->ID_domain;
	} else {
		$sSQL = "SELECT cache_page_alias.* FROM cache_page_alias WHERE status > 0";
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_domain";
			$oField->label = ffTemplate::_get_word_by_code("drafts_modify_domain");
			$oField->widget = "activecomboex"; 
			$oField->source_SQL = "SELECT cache_page_alias.ID
										, cache_page_alias.host
									FROM cache_page_alias
									WHERE cache_page_alias.status > 0
									ORDER BY cache_page_alias.host";
			$oField->actex_update_from_db = true;
			$oRecord->addContent($oField);
		}	
	}
}
$oField = ffField::factory($cm->oPage);
$oField->id = "parent";
$oField->class ="activecomboex parent-page";
//$oField->class = "dialogSubTitle";
//$oField->display_label = false;
$oField->label = ffTemplate::_get_word_by_code("static_page_parent");
//$oField->extended_type = "Selection";
$oField->source_SQL = " SELECT
							IF(static_pages.parent = '/', CONCAT( static_pages.parent, static_pages.name ), CONCAT( static_pages.parent, '/', static_pages.name )) AS ID
							, IF(static_pages.name = ''
								, '/'
								, " . ($root_path == "/"
									? " CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name )"
									: " CONCAT(IF(LOCATE(" . $db_gallery->toSql($root_path) . ", CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name)) = 1
												, SUBSTRING(CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name, '/'), " . $db_gallery->toSql(strlen($root_path) + 1, "Number") . ")
												, CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name)
											)
										)	"
								) . "
							) AS name
						FROM static_pages 
                        WHERE 1
						" . ($globals->ID_domain > 0
							? " AND static_pages.ID_domain = " . $db_gallery->toSql($globals->ID_domain, "Number")
							: ""
						) . "
                        " . ($is_owner
                        	? "AND static_pages.owner = " . $db_gallery->toSql(Auth::get("user")->id, "Number")
                        	: ""
                        ) . "
                        " . (isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0
                            ?
                                ($is_home 
                                    ? "
                                        AND static_pages.parent = " . $db_gallery->toSql("/", "Text") . "
                                        AND static_pages.name = " . $db_gallery->toSql("", "Text")
                                    : " AND
                                        (
                                            static_pages.parent NOT LIKE '" . $db_gallery->toSql($user_path, "Text", false) . "%'
                                        OR
                                            static_pages.parent = " . $db_gallery->toSql("/", "Text") . "
                                        )
                                        AND static_pages.ID <> " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number")
                                ) 
                            : "" 
                        ) . "
						[AND] [WHERE]
	                    [HAVING]
                        [ORDER] [COLON] name
                        [LIMIT]";
$oField->multi_select_one = false;
$oField->actex_on_refill = 'function(obj) {
    ff.cms.admin.makeNewUrl("INPUT.title-page:first", "INPUT.alt-url:first", obj.value);
}';
$oField->actex_on_change = "function(obj, old_value, action) {
	if(action == 'change') {
		ff.cms.admin.makeNewUrl(undefined, undefined, obj.value);
	}
}";

/*
$oField->fixed_pre_content = '<span class="domain">' . DOMAIN_INSET . '</span>';
if($user_path != "/home")
	$oField->fixed_post_content = '<span id="slug-name"></span>';

if(strlen($user_path))	
	$oField->fixed_post_content .= ''; 
*/
/*$oField->widget = "autocompletetoken";
$oField->autocompletetoken_minLength = 0;
$oField->autocompletetoken_theme = "";
$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
$oField->autocompletetoken_label = ffTemplate::_get_word_by_code("autocompletetoken_label");
$oField->autocompletetoken_combo = true;
$oField->autocompletetoken_compare_having = "ID"; 
$oField->autocompletetoken_limit = 1;*/
                        
//$oField->widget = "actex";
//$oField->actex_autocomp = true;
$oField->widget = "activecomboex";
$oField->actex_update_from_db = true; 

//$oField->required = true;
if(strlen($parent)) {
    $oField->default_value = new ffData($parent, "Text");
}
if($is_home && !$parent) {
    $oField->control_type = "label";
}
$oRecord->addContent($oField);

if(!$simple_interface) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "location";
	$oField->label = ffTemplate::_get_word_by_code("static_page_location");
	$oField->extended_type = "Selection";
	$oField->widget = "autocompletetoken";
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_theme = "";
	$oField->autocompletetoken_not_found_label = ffTemplate::_get_word_by_code("autocompletetoken_not_found");
	$oField->autocompletetoken_init_label = ffTemplate::_get_word_by_code("autocompletetoken_init");
	$oField->autocompletetoken_searching_label = ffTemplate::_get_word_by_code("autocompletetoken_searching");
	$oField->autocompletetoken_combo = true;
	$oField->autocompletetoken_compare = "name"; 
	$oField->source_SQL = "SELECT name, name
    					FROM layout_location
						WHERE 1
						[AND] [WHERE]
						[ORDER] [COLON] interface_level, name
						[LIMIT]";
	
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_delimiter = ",";
	$oField->autocompletetoken_combo = true;
	$oField->default_value = new ffData($location);
	$oRecord->addContent($oField);
} else {
	$oRecord->insert_additional_fields["location"] =  new ffData($location);
}

if(!Cms::env("ENABLE_STD_PERMISSION") && !Cms::env("ENABLE_ADV_PERMISSION")) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "permission";
	$oField->label = ffTemplate::_get_word_by_code("static_page_permission");
	$oField->extended_type = "Selection";
	$oField->widget = "autocompletetoken";
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_combo = true;
	$oField->autocompletetoken_compare = "name"; 
	$oField->source_SQL = "SELECT gid, name
    					FROM " . CM_TABLE_PREFIX . "mod_security_groups
						[WHERE]
						ORDER BY name";
	
	$oField->autocompletetoken_minLength = 0;
	$oField->autocompletetoken_delimiter = ",";
	$oField->autocompletetoken_combo = true;
	$oRecord->addContent($oField);
}

/*
$oField = ffField::factory($cm->oPage);
$oField->id = "sort";
$oField->base_type = "Number";
$oField->label = ffTemplate::_get_word_by_code("static_page_sort");
$oField->default_value = new ffData($max_sort, "Number");
$oRecord->addContent($oField);*/

$oField = ffField::factory($cm->oPage);
$oField->id = "use_ajax";
$oField->container_class = "use-ajax";
$oField->label = ffTemplate::_get_word_by_code("static_page_use_ajax");
$oField->control_type = "checkbox";
$oField->extended_type = "Boolean";
$oField->checked_value = new ffData("1");
$oField->unchecked_value = new ffData("0");
$oField->properties["onchange"] = "javascript:ff.cms.admin.UseAjax();";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "ajax_on_event";
$oField->container_class = "use-ajax-dep on-event";
$oField->label = ffTemplate::_get_word_by_code("static_page_ajax_on_event");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
							array(new ffData("load fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fade"))),
							array(new ffData("load show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_show"))),
							array(new ffData("load fadeToggle"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_fadeToggle"))),
							array(new ffData("load hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_load_hide"))),
							array(new ffData("reload show"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_show"))), 
							array(new ffData("reload fadeIn"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_fade"))),
							array(new ffData("reload hide"), new ffData(ffTemplate::_get_word_by_code("block_ajax_on_event_reload_hide")))
					   );
$oField->default_value = new ffData("load fadeIn");
$oField->multi_select_one = false;
$oRecord->addContent($oField);

$oRecord->insert_additional_fields["owner"] =  new ffData(Auth::get("user")->id, "Number");
$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$cm->oPage->addContent($oRecord);

if(!$is_home) {
    $sSQL = "SELECT COUNT(" . FF_PREFIX . "languages.ID) AS count_lang FROM " . FF_PREFIX . "languages WHERE " . FF_PREFIX . "languages.status = '1'";
    $db_gallery->query($sSQL);
    if ($db_gallery->nextRecord()) {
        $count_lang = $db_gallery->getField("count_lang", "Number", true);
    }

    $oDetail_languages = ffDetails::factory($cm->oPage);
    if ($count_lang > 1) {
        $oDetail_languages->tab = true;
        $oDetail_languages->tab_label = "language";
    }
    $oDetail_languages->id = "StaticModifyLanguages";
    $oDetail_languages->title = ""; //ffTemplate::_get_word_by_code("static_page_title");
    $oDetail_languages->src_table = "static_pages_rel_languages";
    $oDetail_languages->order_default = "ID";
    $oDetail_languages->addEvent("on_do_action", "StaticModifyLanguages_on_do_action");
    $oDetail_languages->fields_relationship = array("ID_static_pages" => "ID");
    $oDetail_languages->display_new = false;
    $oDetail_languages->display_delete = false;
    $oDetail_languages->auto_populate_insert = true;
    $oDetail_languages->populate_insert_SQL = "SELECT 
                                                    " . FF_PREFIX . "languages.ID AS ID_languages
                                                    , " . FF_PREFIX . "languages.description AS language 
                                                    , " . FF_PREFIX . "languages.code AS code_lang
                                                    , '' AS value 
                                                FROM " . FF_PREFIX . "languages 
                                                WHERE " . FF_PREFIX . "languages.status = '1'";
    $oDetail_languages->auto_populate_edit = true;
    $oDetail_languages->populate_edit_SQL = "SELECT 
                                        static_pages_rel_languages.ID AS ID
                                        , " . FF_PREFIX . "languages.ID AS ID_languages
                                        , " . FF_PREFIX . "languages.description AS language
                                        , " . FF_PREFIX . "languages.code AS code_lang 
                                        , static_pages_rel_languages.meta_title AS title
                                        , static_pages_rel_languages.meta_description AS description
                                        , static_pages_rel_languages.visible AS visible
                                        , static_pages_rel_languages.alt_url AS alt_url
                                        , static_pages_rel_languages.meta_title AS meta_title
                                        , static_pages_rel_languages.smart_url AS smart_url
                                        , (SELECT drafts_rel_languages.value 
                                            FROM drafts_rel_languages 
                                            WHERE drafts_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID
                                                AND drafts_rel_languages.ID_drafts = (SELECT static_pages.ID_drafts FROM static_pages WHERE static_pages.ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number") . ")
                                        ) AS value
                                    FROM " . FF_PREFIX . "languages
                                        LEFT JOIN static_pages_rel_languages ON  static_pages_rel_languages.ID_languages = " . FF_PREFIX . "languages.ID AND static_pages_rel_languages.ID_static_pages = [ID_FATHER]
                                    WHERE
                                        " . FF_PREFIX . "languages.status = '1'";


    $oField = ffField::factory($cm->oPage);
    $oField->id = "IDr";
    $oField->data_source = "ID";
    $oField->base_type = "Number";
    $oDetail_languages->addKeyField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "language";
    $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_languages");
    $oField->store_in_db = false;
    $oDetail_languages->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "code_lang";
    $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_code");
    $oField->store_in_db = false;
    $oDetail_languages->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "ID_languages";
    $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_ID_languages");
    $oField->base_type = "Number";
    //$oField->required = true;
    $oDetail_languages->addHiddenField($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "smart_url";
    $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_smart_url");
    $oDetail_languages->addHiddenField($oField);

    if (!Cms::env("ENABLE_STD_PERMISSION") && Cms::env("ENABLE_ADV_PERMISSION")) {
        if (!$simple_interface) {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "visible";
            $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_visible");
            $oField->base_type = "Number";
            $oField->control_type = "checkbox";
            $oField->extended_type = "Boolean";
            $oField->checked_value = new ffData("1", "Number");
            $oField->unchecked_value = new ffData("0", "Number");
            $oField->default_value = $oField->checked_value;
            $oDetail_languages->addContent($oField);
        } else {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "visible";
            $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_ID_languages");
            $oField->base_type = "Number";
            $oField->default_value = new ffData("1", "Number");
            $oDetail_languages->addHiddenField($oField);
        }
    } else {
        $oDetail_languages->insert_additional_fields["visible"] = new ffData("1", "Number");
    }
    /*
    $oField = ffField::factory($cm->oPage);
    $oField->id = "meta_title";
    $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_meta_title");
    $oDetail_languages->addHiddenField($oField);*/

    $oField = ffField::factory($cm->oPage);
    $oField->id = "meta_title";
    $oField->class = "input title-page";
    $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_title");
    $oField->properties["onkeyup"] = "javascript:ff.cms.admin.makeNewUrl();";
    $oField->required = true;
    $oDetail_languages->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "alt_url";
    $oField->class = "input alt-url";
    $oField->label = ffTemplate::_get_word_by_code("static_page_alt_url");
    $oField->properties["onkeyup"] = "javascript:ff.cms.admin.makeNewUrl();";
    $oDetail_languages->addContent($oField);

    if (!$simple_interface) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "meta_description";
        $oField->class = "text optional hidden";
        $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_description");
        $oField->display_label = false;
        /*$oField->control_type = "textarea";
        if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
            $oField->widget = "ckeditor";
        } else {
            $oField->widget = "";
        }*/
        $oField->ckeditor_group_by_auth = true;
        $oField->extended_type = "Text";
        $oField->base_type = "Text";
        $oField->fixed_pre_content = '<a href="javascript:void(0);" class="optional-action" onclick="if(jQuery(\'TEXTAREA.optional\').hasClass(\'hidden\')) { jQuery(\'TEXTAREA.optional\').removeClass(\'hidden\'); } else { jQuery(\'TEXTAREA.optional\').addClass(\'hidden\'); }">' . ffTemplate::_get_word_by_code("rel_static_pages_description_action") . '</a>';
        $oDetail_languages->addContent($oField);
    }

    if (1) {
        $oField = ffField::factory($cm->oPage);
        $oField->id = "value";
        $oField->container_class = "draft";
        $oField->label = ffTemplate::_get_word_by_code("rel_static_pages_value");
        $oField->display_label = false;
        $oField->control_type = "textarea";
        if (file_exists(FF_DISK_PATH . FF_THEME_DIR . "/library/ckeditor/ckeditor.js")) {
            $oField->widget = "ckeditor";
        } else {
            $oField->widget = "";
        }
        $oField->ckeditor_group_by_auth = true;
        $oField->extended_type = "Text";
        $oField->base_type = "Text";
        $oField->store_in_db = false;
        $oDetail_languages->addContent($oField);
    }


    $oRecord->addContent($oDetail_languages);
    $cm->oPage->addContent($oDetail_languages);
}

$cm->oPage->tplAddJs("ff.cms.admin", "ff.cms.admin.js", FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools");

$js = '<script type="text/javascript">
        ff.pluginLoad("ff.cms.admin", "' . FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.admin.js" . '", function() {
			$( "#StaticModifyLanguages_jtab" ).on( "tabsactivate", function( event, ui ) {
				ff.cms.admin.makeNewUrl();
			});

		    ff.cms.admin.UseAjax();
		    ff.cms.admin.makeNewUrl();
        });            
	</script>';
$cm->oPage->addContent($js);

//$cm->oPage->tplAddJs("normalize", "slug.js", "/themes/responsive/ff/ffField/widgets/slug", false, true); 


function StaticModify_on_do_action($component, $action) {
	$globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();

    if(strlen($action)) {
    	$ID_node = $component->key_fields["ID"]->getValue();
    	
    	switch($action) {
            case "insert":
            case "update":
				if(is_array($component->detail["StaticModifyLanguages"]->recordset) && count($component->detail["StaticModifyLanguages"]->recordset)) {
					foreach($component->detail["StaticModifyLanguages"]->recordset AS $rst_key => $rst_value) {
						if(!$component->user_vars["is_home"] && stripslash($component->form_fields["parent"]->value->getValue()) . "/" . ffCommon_url_rewrite($rst_value["meta_title"]->getValue())  == "/home") {
      						$component->tplDisplayError(ffTemplate::_get_word_by_code("name_not_permitted"));
                        	return true;							
						}
					}
				}

            	$old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->user_vars["old_name"];
                if(!strlen($component->user_vars["name"]) && !$component->user_vars["is_home"]) {
					$component->user_vars["name"] = ffCommon_url_rewrite($component->detail["StaticModifyLanguages"]->recordset[0]["meta_title"]->getValue());
                }
                
                $new_parent = stripslash($component->form_fields["parent"]->value->getValue()) . "/" . $component->user_vars["name"];
                if($old_parent != $new_parent) {
                    $sSQL = "SELECT static_pages.*
                    		FROM static_pages 
                    		WHERE CONCAT(IF(static_pages.parent = '/', '', static_pages.parent), '/', static_pages.name) = " . $db->toSql($new_parent, "Text") . "
                    			AND static_pages.ID <> " . $db->toSql($ID_node);
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $component->tplDisplayError(ffTemplate::_get_word_by_code("element_not_unique"));
                        return true;
                    }
                }
          		break;
            case "confirmdelete":
				$sSQL = "SELECT static_pages.ID_drafts
							, drafts.name AS draft_name 
						FROM static_pages 
							INNER JOIN drafts ON drafts.ID = static_pages.ID_drafts
						WHERE static_pages.ID = " . $db->toSql($ID_node);
				$db->query($sSQL);
				if($db->nextRecord()) {
					$ID_draft = $db->getField("ID_drafts", "Number", true);
					$draft_name = $db->getField("draft_name", "Text", true);
					
					$sSQL = "DELETE FROM drafts_rel_languages WHERE ID_drafts = " . $db->toSql($ID_draft, "Number");
					$db->execute($sSQL);

					$sSQL = "DELETE FROM drafts WHERE ID = " . $db->toSql($ID_draft, "Number");
					$db->execute($sSQL);
					
					$sSQL = "SELECT layout.*
							FROM layout
							WHERE layout.value = " . $db->toSql($ID_draft) . "
								AND layout.ID_type = (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db->toSql("STATIC_PAGE_BY_DB") . ")
								AND layout.ID_location = (SELECT layout_location.ID FROM layout_location WHERE layout_location.name = " . $db->toSql("Content") . " LIMIT 1)
							";
					$db->query($sSQL);
					if($db->nextRecord()) {
						$ID_layout = $db->getField("ID", "Number", true);

						$sSQL = "DELETE FROM layout_path WHERE ID_layout = " . $db->toSql($ID_layout, "Number");
						$db->execute($sSQL);

						$sSQL = "DELETE FROM layout WHERE ID = " . $db->toSql($ID_layout, "Number");
						$db->execute($sSQL);
						
					}
					
				}
          	default:  
		}
	}
}


function StaticModify_on_done_action($component, $action) {
	$globals = ffGlobals::getInstance("gallery");
    $db = ffDB_Sql::factory();
//        ffErrorHandler::raise("aad", E_USER_ERROR, null, get_defined_vars());

    if(strlen($action)) {
        $ID_node = $component->key_fields["ID"]->getValue();

        switch($action) {
            case "insert":
                break;
            case "update":
                $old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->user_vars["old_name"];
                $new_parent = stripslash($component->form_fields["parent"]->value->getValue()) . "/" . $component->user_vars["name"];

                if(!$component->user_vars["is_home"] && $old_parent != $new_parent) {
                    $sSQL = "UPDATE static_pages SET static_pages.parent = REPLACE(static_pages.parent, " . $db->toSql($old_parent, "Text") . ", " . $db->toSql($new_parent, "Text") . ") 
                    		WHERE static_pages.parent LIKE '" . $db->toSql($old_parent, "Text", false) . "%'
							" . ($globals->ID_domain > 0
								? " AND static_pages.ID_domain = " . $db->toSql($globals->ID_domain, "Number")
								: ""
							);
                    $db->execute($sSQL);
                    
                    $sSQL = "UPDATE layout_path  SET layout_path.path = REPLACE(layout_path.path, " . $db->toSql($old_parent, "Text") . ", " . $db->toSql($new_parent, "Text") . ") 
                    		WHERE layout_path.path LIKE '" . $db->toSql($old_parent, "Text", false) . "%'
							" . ($globals->ID_domain > 0
								? " AND layout_path.ID_layout IN(
                    				SELECT layout.ID
                    				FROM layout
                    				WHERE 1								
										AND layout.ID_domain = " . $db->toSql($globals->ID_domain, "Number") . "
								)"
								: ""
							);
                    $db->execute($sSQL);
                }
                break;
            case "confirmdelete":
            	$old_parent = stripslash($component->form_fields["parent"]->value_ori->getValue()) . "/" . $component->user_vars["old_name"];
            	break;
            default:
        }
        
		if(!$component->user_vars["is_home"] && ($action == "insert" || $action == "update") && check_function("set_field_permalink"))
			set_field_permalink("static_pages", $component->key_fields["ID"]->getValue());

        //UPDATE CACHE
        /*$sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `static_pages`.last_update FROM static_pages WHERE static_pages.ID = " . $db->toSql($ID_node, "Number") . ") 
                WHERE 
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("STATIC_PAGES_MENU") . ")
                    )
                    ";
        $db->execute($sSQL);*/
        //UPDATE CACHE 
    }
}

function StaticModifyLanguages_on_do_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    switch($action) {
		case "insert":
		case "update":
		    $component->main_record[0]->user_vars["name"] = $component->main_record[0]->user_vars["old_name"];
		    $ID_node = $component->main_record[0]->key_fields["ID"]->value;
		    $old_parent = stripslash($component->main_record[0]->form_fields["parent"]->value_ori->getValue()) . "/" . $component->main_record[0]->user_vars["old_name"];

		    if(isset($component->main_record[0]->form_fields["ID_domain"])) {
    			$ID_domain = $component->main_record[0]->form_fields["ID_domain"]->getValue();
			} else {
				$ID_domain = $component->main_record[0]->user_vars["ID_domain"];
			}
		    
			$sSQL = "SELECT static_pages.ID_drafts 
					FROM static_pages 
						INNER JOIN drafts ON drafts.ID = static_pages.ID_drafts
					WHERE static_pages.ID = " . $db->toSql($ID_node);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_draft = $db->getField("ID_drafts", "Number", true);
			}
		    
			if(is_array($component->recordset) && count($component->recordset)) {
				foreach($component->recordset AS $rst_key => $rst_value) {
					if($component->recordset[$rst_key]["code_lang"]->getValue() != LANGUAGE_DEFAULT)
						continue;

					if(!$component->main_record[0]->user_vars["is_home"])
					{
						if(strlen($component->recordset[$rst_key]["meta_title"]->getValue())) {
							 $sSQL = "UPDATE 
									`static_pages` 
								SET 
									`name` = " . $db->toSql(ffCommon_url_rewrite($component->recordset[$rst_key]["meta_title"]->getValue())) . " 
								WHERE `static_pages`.`ID` = " . $db->toSql($ID_node, "Number");
							$db->execute($sSQL);
							$component->main_record[0]->user_vars["name"] = ffCommon_url_rewrite($component->recordset[$rst_key]["meta_title"]->getValue());
						} else {
							$component->displayError(ffTemplate::_get_word_by_code("smart_url_empty"));
							return true;
						}
					}
				}
			}
			$ID_new_draft = 0;
		    if(is_array($component->recordset) && count($component->recordset) > 0) {
		        foreach($component->recordset AS $rst_key => $rst_value) {
					if(isset($component->recordset[$rst_key]["value"])) {
						if(strlen($component->recordset[$rst_key]["value"]->getValue())) {
							if($ID_draft > 0) {
								$sSQL = "UPDATE drafts_rel_languages SET
											title = " . $db->toSql($component->recordset[$rst_key]["meta_title"]) . "
											, value = " . $db->toSql($component->recordset[$rst_key]["value"]) . "
										WHERE ID_drafts = " . $db->toSql($ID_draft, "Number") . "
											AND ID_languages = " . $db->toSql($component->recordset[$rst_key]["ID_languages"]);
								$db->execute($sSQL);
							} else {
								if(!$ID_new_draft) {
									$draft_name = ffCommon_url_rewrite(
										(strlen(basename($component->main_record[0]->form_fields["parent"]->getValue()))
											? basename($component->main_record[0]->form_fields["parent"]->getValue()) . "-"
											: ""
										) . $component->main_record[0]->user_vars["name"]);
									
									$sSQL = "INSERT INTO drafts 
											(
												ID
												, name
												, last_update
												, owner
												, ID_domain
											) VALUES (
												null
												, " . $db->toSql($draft_name) . "
												, " . $db->toSql(time(), "Number") . "
												, " . $db->toSql(Auth::get("user")->id, "Number") . "
												, " . $db->toSql($ID_domain, "Number") . "
											)";
									$db->execute($sSQL);
									$ID_new_draft = $db->getInsertID(true);
									
									$sSQL = "UPDATE 
					                        `static_pages` 
					                    SET 
					                        `ID_drafts` = " . $db->toSql($ID_new_draft, "Number") . " 
					                    WHERE `static_pages`.`ID` = " . $db->toSql($ID_node, "Number");
					                $db->execute($sSQL);

						            $sSQL = "INSERT INTO layout
						                    (
						                        ID
						                        , name
						                        , ID_type
						                        , value
						                        , params
						                        , ID_location
						                        , `order`
						                        , last_update
						                        , use_ajax
						                        , ID_domain
						                    ) VALUES (
						                        null
						                        , " . $db->toSql($draft_name) . "
						                        , (SELECT layout_type.ID FROM layout_type WHERE layout_type.name = " . $db->toSql("STATIC_PAGE_BY_DB") . ")
						                        , " . $db->toSql($ID_new_draft, "Number") . "
						                        , ''
						                        , (SELECT layout_location.ID FROM layout_location WHERE layout_location.name = " . $db->toSql("Content") . " LIMIT 1)
						                        , " . $db->toSql(1, "Number") . "
						                        , " . $db->toSql(time(), "Number") . "
						                        , " . $db->toSql(0, "Number") . "
							                    , " . $db->toSql($ID_domain, "Number") . "
						                    )";
						            $db->execute($sSQL);
						            $ID_layout = $db->getInsertID(true);

						            $sSQL = "INSERT INTO layout_path
						                    (
						                        ID
						                        , ID_layout
						                        , path
						                        , ereg_path
						                        , cascading
						                        , visible
						                    ) VALUES (
						                        null
						                        , " . $db->toSql($ID_layout, "Number") . "
						                        , " . $db->toSql(stripslash($component->main_record[0]->form_fields["parent"]->getValue()) . "/" . $component->main_record[0]->user_vars["name"]) . "
						                        , ''
						                        , " . $db->toSql("0", "Number") . "
						                        , " . $db->toSql("1", "Number") . "
						                    )";
						            $db->execute($sSQL);
								}
								
								$sSQL = "INSERT INTO drafts_rel_languages 
										(
											ID
											, ID_drafts
											, ID_languages
											, title
											, value
										) VALUES (
											null
											, " . $db->toSql($ID_new_draft, "Number") . "
											, " . $db->toSql($component->recordset[$rst_key]["ID_languages"]) . "
											, " . $db->toSql($component->recordset[$rst_key]["meta_title"]) . "
											, " . $db->toSql($component->recordset[$rst_key]["value"]) . "
										)";
								$db->execute($sSQL);
							}
						}
					}
		        
		           if(!$component->main_record[0]->user_vars["is_home"])
            			$component->recordset[$rst_key]["smart_url"]->setValue(ffCommon_url_rewrite($rst_value["meta_title"]->getValue()), "Text");

					if(check_function("update_vgallery_seo")) {
				        check_function("get_schema_fields_by_type");

						$src = get_schema_fields_by_type("page");

		            	$seo_update = update_vgallery_seo(
		            		array(
		            			"smart_url" => ($src["seo"]["smart_url"] && isset($rst_value["smart_url"]) && !$component->main_record[0]->user_vars["is_home"]
		            				? $component->recordset[$rst_key]["smart_url"]->getValue()
		            				: false
		            			)
		            			, "title" => ($src["seo"]["title"] && isset($rst_value["meta_title"])
		            				? $rst_value["meta_title"]->getValue()
		            				: null
		            			)
		            			, "header" => ($src["seo"]["header"] && isset($rst_value["header"])
		            				? $rst_value["header"]->getValue()
		            				: null
		            			)
		            		)
		            		, $ID_node->getValue()
		            		, $rst_value["ID_languages"]->getValue()
		            		, ($src["seo"]["description"] && isset($rst_value["description"])
		            			? $rst_value["description"]->getValue()
		            			: null
		            		)
		            		, null
		            		, ($src["seo"]["keywords"] && isset($rst_value["keywords"])
		            			? $rst_value["keywords"]->getValue()
		            			: null
		            		)
		            		, null
		            		, null
		            		, $src["seo"]
		            		, $src["field"]
		            		, "primary"
		            		, ($src["seo"]["alt_url"] && isset($rst_value["alt_url"])
		            			? $rst_value["alt_url"]->getValue()
		            			: null
		            		)
		            	);
					}

					if(check_function("refresh_cache")) {
						refresh_cache($src["cache"]["type"], $ID_node->getValue(), $action, $old_parent);
					}					
					
					
					
					
					
					
		        }
		        reset($component->recordset);
		    }
		//ffErrorHandler::raise($component->form_fields["title"]->value->getValue() . "ad", E_USER_ERROR, null, get_defined_vars());
		break;
		default:
	}
}