<?php
//require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$oRecord = ffRecord::factory($oPage);

$db_gallery->query("SELECT module_search.*
                        FROM 
                            module_search
                        WHERE 
                            module_search.name = " . $db_gallery->toSql(new ffData($oRecord->user_vars["MD_chk"]["params"][0])));
if($db_gallery->nextRecord()) {
    $ID_search = $db_gallery->getField("ID")->getValue();
    $search_name = $db_gallery->getField("name")->getValue();
    $require_note = $db_gallery->getField("require_note")->getValue();
    $tpl_search_path = $db_gallery->getField("tpl_search_path")->getValue();
    $show_title = $db_gallery->getField("show_title")->getValue();
    $area = $db_gallery->getField("area")->getValue();
    $contest = $db_gallery->getField("contest")->getValue();
}

$tpl_data["custom"] = "adv-search.html";
$tpl_data["base"] = null;

$tpl_data["result"] = get_template_cascading($user_path, $tpl_data);
if($tpl_data["result"]["path"] && $tpl_data[$tpl_data["result"]["type"]]) {
    $oRecord->template_dir = $tpl_data["result"]["path"];
    $oRecord->template_file = $tpl_data[$tpl_data["result"]["type"]];
    if(check_function("MD_search_on_done_action")) {
    	$oRecord->addEvent("on_tpl_parse", "MD_search_on_tpl_parse");
	}
}

$oRecord->id = $oRecord->user_vars["MD_chk"]["id"];
$oRecord->class = $oRecord->user_vars["MD_chk"]["id"];
$oRecord->use_own_location = $oRecord->user_vars["MD_chk"]["own_location"];
$oRecord->skip_action = true;

$oRecord->buttons_options["cancel"]["display"] = false;
$oRecord->buttons_options["update"]["display"] = false;
$oRecord->buttons_options["print"]["display"] = false;
$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("search_start"); 

setJsRequest("ff.cms.search", "tools");

$sSQL = "SELECT module_search_fields.*
                , extended_type.name AS extended_type
                , extended_type.ff_name AS ff_extended_type
                , check_control.ff_name AS check_control
                , module_search_fields_group.name AS `group_field`
                , module_search_fields_group.default_grid AS group_default_grid
                , module_search_fields_group.grid_md AS group_grid_md
                , module_search_fields_group.grid_sm AS group_grid_sm
                , module_search_fields_group.grid_xs AS group_grid_xs
            FROM module_search_fields
                LEFT JOIN extended_type ON extended_type.ID = module_search_fields.ID_extended_type
                LEFT JOIN check_control ON check_control.ID = module_search_fields.ID_check_control
                LEFT JOIN module_search_fields_group ON module_search_fields_group.ID = module_search_fields.ID_search_fields_group
            WHERE module_search_fields.ID_module = " . $db_gallery->toSql($ID_search, "Number") . "
                AND NOT(module_search_fields.hide > 0)
            ORDER BY module_search_fields.`order`, module_search_fields.name";
$db_gallery->query($sSQL);
if($db_gallery->nextRecord()) 
{
	$framework_css = Cms::getInstance("frameworkcss")->getFramework();
    do { 
        $field_name = $db_gallery->getField("name")->getValue();

        $arrField[$field_name]["ID"]                                = $db_gallery->getField("ID")->getValue();
        $arrField[$field_name]["name"]                              = $db_gallery->getField("name")->getValue();
        $arrField[$field_name]["group"]["field"]                    = ($db_gallery->getField("group_field")->getValue() 
                                                                        ? ffCommon_url_rewrite($db_gallery->getField("group_field")->getValue()) 
                                                                        : null);
        $arrField[$field_name]["group"]["class"]["default"]         = ffCommon_url_rewrite($db_gallery->getField("group_field")->getValue());
        $arrField[$field_name]["class"]["default"]                  = "search_" . ffCommon_url_rewrite($arrField[$field_name]["name"]);
        $arrField[$field_name]["unic_value"]                        = $db_gallery->getField("unic_value", "Number", true);
        $arrField[$field_name]["writable"]                          = $db_gallery->getField("writable", "Number", true);
        $arrField[$field_name]["ID_fields"]                      	= $db_gallery->getField("ID_fields", "Number", true);
        $arrField[$field_name]["data_source"]                       = $db_gallery->getField("data_source", "Text", true);
        $arrField[$field_name]["data_limit"]                        = $db_gallery->getField("data_limit", "Number", true);
        $arrField[$field_name]["disable_select_one"]                = $db_gallery->getField("disable_select_one", "Number", true);
        $arrField[$field_name]["check_control"]                     = $db_gallery->getField("check_control")->getValue();
        $arrField[$field_name]["extended_type"]                     = $db_gallery->getField("extended_type")->getValue();
        $arrField[$field_name]["ff_extended_type"]                  = $db_gallery->getField("ff_extended_type")->getValue();
        $arrField[$field_name]["display_label"]                     = !$db_gallery->getField("hide_label", "Number", true);
        $arrField[$field_name]["area"]                              = $area;

        if(strlen($db_gallery->getField("custom_placeholder", "Text", true)))
            $arrField[$field_name]["placeholder"] = ffTemplate::_get_word_by_code($db_gallery->getField("custom_placeholder", "Text", true));
        else
            $arrField[$field_name]["placeholder"] = ffTemplate::_get_word_by_code($arrField[$field_name]["name"]);

        if(is_array($framework_css))
        {
            if(!array_key_exists("grid", $arrField[$field_name]["group"]["class"])) {
                $arrField[$field_name]["group"]["class"]["grid"] = Cms::getInstance("frameworkcss")->get(array(
                        (int) $db_gallery->getField("group_grid_xs", "Number", true)
                        ,(int) $db_gallery->getField("group_grid_sm", "Number", true)
                        ,(int) $db_gallery->getField("group_grid_md", "Number", true)
                        ,(int) $db_gallery->getField("group_default_grid", "Number", true)
                ), "col");
            }

            $arrField[$field_name]["framework_css"]["component"] = array(
                $db_gallery->getField("default_grid", "Number", true)
                , $db_gallery->getField("grid_md", "Number", true)
                , $db_gallery->getField("grid_sm", "Number", true)
                , $db_gallery->getField("grid_xs", "Number", true)
            );

            if($arrField[$field_name]["display_label"]) {
                $arrField[$field_name]["framework_css"]["label"] = array(
                    $db_gallery->getField("label_default_grid", "Number", true)
                    , $db_gallery->getField("label_grid_md", "Number", true)
                    , $db_gallery->getField("label_grid_sm", "Number", true)
                    , $db_gallery->getField("label_grid_xs", "Number", true)
                );
            }
        }
    } while($db_gallery->nextRecord());
}

if(is_array($arrField) && count($arrField)) 
{
    foreach($arrField AS $field_key => $field_value) 
    {
        if (strlen($field_value["group"]["field"]) && !isset($oRecord->groups[$field_value["group"]["field"]])) 
        { 
            $oRecord->addContent(null, true, $field_value["group"]["field"]); 
            if($use_tab) {
                $oRecord->addTab($field_value["group"]["field"]);
                $oRecord->setTabTitle($field_value["group"]["field"], ffTemplate::_get_word_by_code("search_" . $field_value["group"]["field"]));
            } else {
                $gridGroup[$field_value["group"]["field"]] = $db_gallery->toSql($field_value["group"]["field"], "Text");
            }
            $oRecord->groups[$field_value["group"]["field"]] = array(
                "title" => ffTemplate::_get_word_by_code("search_" . $field_value["group"]["field"])
                , "cols" => 1
                , "class" => implode(" ", array_filter($field_value["group"]["class"]))
                , "tab" => ($use_tab ? $field_value["group"]["field"] : null)
            );
        }

        if(is_array($field_value["class"]) && count($field_value["class"]))
            $field_class = implode(" ", $field_value["class"]);

        switch ($field_value["name"]) 
        {
            default:
                $field_id = $field_value["ID"];

                $obj_page_field = ffField::factory($oPage);
                $obj_page_field->store_in_db = false;
                
                $obj_page_field->container_class = $field_class;
                $obj_page_field->user_vars["name"] = $field_value["name"];
                $obj_page_field->data_type = ""; 

                if(check_function("get_field_by_extension"))
                    $js .= get_field_by_extension($obj_page_field, $field_value, "search");
                $obj_page_field->id = $field_value["name"];
                $obj_page_field->autocomplete_readonly = false;

               // if(isset($_GET[ffCommon_url_rewrite($field_value["name"])]) && strlen($_GET[ffCommon_url_rewrite($field_value["name"])])) {
                //    $obj_page_field->default_value = new ffData($_GET[ffCommon_url_rewrite($field_value["name"])], $field_value["ff_extended_type"]);
               // }
                $obj_page_field->properties["onkeydown"] = "return ff.submitProcessKey(event, '" . $oRecord->user_vars["MD_chk"]["id"] . "_ActionButtonInsert')";
                
                $oRecord->addContent($obj_page_field, $field_value["group"]["field"]);
                break;
        }

        $oPage->addContent($oRecord);  
    }
}
 
if(check_function("get_layout_settings")) {
	$arrLayout = get_layout_by_block($area, "/" . $contest);

	$unic_id = $arrLayout["prefix"] . $arrLayout["ID"] . "T";

	$oRecord->buttons_options["insert"]["action_type"] = "gotourl";
	//$oRecord->buttons_options["insert"]["url"] = "javascript:ff.cms.search.block(this, '/" . $area . "/" . $contest . "','" . $unic_id . "');";
	$oRecord->buttons_options["insert"]["url"] = "javascript:ff.cms.search.block(this, '/" . $contest . "','" . $unic_id . "');";
	$oRecord->properties["data-advsrc-target"] = $unic_id;
}

