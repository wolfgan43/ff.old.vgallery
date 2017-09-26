<?php
$permission = check_crowdfund_permission();
if(!(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}
$db = ffDb_Sql::factory();
$options = mod_security_get_settings($cm->path_info);
$UserNID = get_session("UserNID");

$_REQUEST["keys"]["ID"] = $UserNID;
$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "UserModify";
$oRecord->title = ffTemplate::_get_word_by_code("crowdfund_user_info");
$oRecord->addEvent("on_done_action", "InfoUpdate_on_done_action");
$oRecord->resources[] = $oRecord->id; 
$oRecord->src_table = $options["table_name"];
if(isset($_REQUEST["smarturl"]))
    $oRecord->user_vars["smart_url"] = $_REQUEST["smarturl"];
    
$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if(isset($_REQUEST["account"]) && !$_REQUEST["account"])
{
    
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "name";
    $oField->container_class = "name";
    $oField->label = ffTemplate::_get_word_by_code("bill_name");
    $oField->required = true;
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "surname";
    $oField->container_class = "surname";
    $oField->label = ffTemplate::_get_word_by_code("bill_surname");
    $oField->required = true;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage); 
    $oField->id = "billcf";
    $oField->label = ffTemplate::_get_word_by_code("bill_cf");
    $oField->required = true;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "billpiva";
    $oField->label = ffTemplate::_get_word_by_code("bill_piva");
    $oField->required = false;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "billaddress";
    $oField->label = ffTemplate::_get_word_by_code("bill_address");
    $oField->required = true;
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "billcap";
    $oField->label = ffTemplate::_get_word_by_code("bill_cap");
    $oField->required = true;
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "billtown";
    $oField->label = ffTemplate::_get_word_by_code("bill_town");
    $oField->required = true;
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "billprovince";
    $oField->label = ffTemplate::_get_word_by_code("bill_province");
    $oField->required = true;
    $oRecord->addContent($oField);
    
}

if(isset($_REQUEST["mifid"]) && !$_REQUEST["mifid"])
{
    
    $cm->oPage->tplAddJs("offert-insert"
        , array(
            "file" => "offert-insert.js"
            , "path" => "/modules/crowdfund/themes/javascript"
            , "async" => true
    ));
    $oField = ffField::factory($cm->oPage);
    $oField->id = "question_1";
    $oField->label = ffTemplate::_get_word_by_code("question_1");
    $oField->class="mifid checkbox";
    $oField->extended_type = "Boolean";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->store_in_db = false;
    $oField->required = true;
    $oRecord->addContent($oField);

    $oField = ffField::factory($cm->oPage);
    $oField->id = "question_2";
    $oField->label = ffTemplate::_get_word_by_code("question_2");
    $oField->class="mifid checkbox";
    $oField->extended_type = "Boolean";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "question_3";
    $oField->label = ffTemplate::_get_word_by_code("question_3");
    $oField->class="mifid checkbox";
    $oField->extended_type = "Boolean";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "question_4";
    $oField->label = ffTemplate::_get_word_by_code("question_4");
    $oField->class="mifid checkbox";
    $oField->extended_type = "Boolean";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);
    
    $oField = ffField::factory($cm->oPage);
    $oField->id = "question_5";
    $oField->label = ffTemplate::_get_word_by_code("question_5");
    $oField->class="mifid checkbox";
    $oField->extended_type = "Boolean";
    $oField->unchecked_value = new ffData("0", "Number");
    $oField->checked_value = new ffData("1", "Number");
    $oField->required = true;
    $oField->store_in_db = false;
    $oRecord->addContent($oField);

    
}
$cm->oPage->addContent($oRecord);

function InfoUpdate_on_done_action($component, $action)
{
    $cm = cm::getInstance();
    $UserNID = get_session("UserNID");
    $db = ffDB_Sql::factory();
    if(strlen($action))
    {
        if(isset($_REQUEST["mifid"]) && !$_REQUEST["mifid"])
        {
            $mifid = true;
            switch($action) {
                case "insert":
                case "update":
                    foreach($component->form_fields AS $key => $value)
                    {
                        if(strpos($key, "question") !== false && !$component->form_fields[$key]->getValue())
                        {
                            $mifid = false;
                        }
                    }
                    if($mifid)
                    {
                        if(isset($_REQUEST["presente"]))
                        {
                            $options = mod_security_get_settings($cm->path_info);
                            if($_REQUEST["presente"])
                            {
                                $sSQL = "UPDATE " . $options["table_dett_name"] . "
                                            SET " . $options["table_dett_name"] . ".value = 1
                                            WHERE " . $options["table_dett_name"] . ".field = " . $db->toSql("Mifid compilato", "Text") . "
                                                AND " . $options["table_dett_name"] . ".ID_users = " . $db->toSql($UserNID, "Number");
                                $db->execute($sSQL);
                            } else {
                               $sSQL = "INSERT INTO " . $options["table_dett_name"] . "
                                            (
                                                ID
                                                , ID_users
                                                , field
                                                , value
                                            )
						VALUES
                                            (
                                                null
                                                , ". $db->toSql($UserNID, "Number") . "
                                                , ". $db->toSql("Mifid compilato", "Text") . "
                                                , ". $db->toSql("1", "Text") . "
                                            )";
                               $db->execute($sSQL);
                            }
                        }
                        
                    }
                    $component->redirect(FF_SITE_PATH . "/investi/" . $component->user_vars["smart_url"]);
                    break;
                default:
                    break;
            }
        }
    }
}