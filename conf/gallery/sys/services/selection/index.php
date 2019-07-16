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
 * @subpackage services
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

// impedisce a google d'indicizzare il servizio
if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") !== false)
{
    die('<html>
			<head>
				<title>no resource</title>
				<meta name="robots" content="noindex,nofollow" />
				<meta name="googlebot" content="noindex,nofollow" />
			</head>
		</html>');
}

// impedisce l'accesso diretto ai browser
if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
{    
    http_response_code(404);
    exit;
}

//require_once("../../../../../../ff/main.php");
//require_once("../../../../../../modules/security/common.php");

//if ($plgCfg_ActiveComboEX_UseOwnSession)


$php_array                  = array();
$actex_sql                  = null;
$actex_skip_empty           = false;
$actex_group                = null;
$actex_attr                 = null;
$hide_result_on_query_empty = false;
$actex_preserve_field       = null;

$operation_sign             = "=";
$image_field                = "image";

$compare                    = "";
$compare_having             = "";
$operation                  = "LIKE [%[VALUE]%]";
$limit                      = 100;

$father_value    = $_REQUEST["father_value"];

$search_value = $_REQUEST["term"];
$search_value = str_replace("%", "\%", $search_value);
$search_value = str_replace(" ", "%", $search_value);
$search_value = str_replace("*", "%", $search_value);

$data_src = $_REQUEST["data_src"];
$type = basename($cm->real_path_info);
switch($type) {
    case "place":
        break;
    case "degree":
        break;
    case "tags":
        break;
    default:
        if(check_function("get_schema_def")) {
            $service_schema = get_schema_def();
        }
        
        $actex_sql = $service_schema["db"]["selection_data_source"][$type]["query"];

        if(isset($service_schema["db"]["selection_data_source"][$type]["group"]))
            $actex_group = $service_schema["db"]["selection_data_source"][$type]["group"];
        if(isset($service_schema["db"]["selection_data_source"][$type]["compare"]))
            $compare = $service_schema["db"]["selection_data_source"][$type]["compare"];
        if(isset($service_schema["db"]["selection_data_source"][$type]["compare_having"]))
            $compare_having = $service_schema["db"]["selection_data_source"][$type]["compare_having"];
        if(isset($service_schema["db"]["selection_data_source"][$type]["limit"]))
            $limit = $service_schema["db"]["selection_data_source"][$type]["limit"];
        if(isset($service_schema["db"]["selection_data_source"][$type]["actex_field"]))
            $actex_field = $service_schema["db"]["selection_data_source"][$type]["actex_field"];
                 
}

if(!strlen($actex_sql) && $hide_result_on_query_empty)
    die(cm::jsonParse(array()));

$strCompareWhere = "";
$strCompareHaving = "";
$sSqlWhere = "";
$relevance = array();
$relevance_search = array();
if($search_value)
    $relevance_search = explode("%", $search_value);

$db = ffDB_Sql::factory();

if ($operation && strpos($operation, "[VALUE]") !== false)
{
	$strOperation = " " . str_replace("[VALUE]", $db->toSql(new ffData($search_value), "Text", false), $operation) . " ";
	if (strpos($operation, "[") !== false && strpos($operation, "]") !== false)
	{
		$strOperation = str_replace("[", "'", $strOperation);
		$strOperation = str_replace("]", "'", $strOperation);
	} 
	else 
	{
		$strOperation = "";
	}
}

if (!strlen($strOperation)) 
{
	$strOperation = " LIKE '%" . $db->toSql(new ffData($search_value), "Text", false) . "%' COLLATE utf8_general_ci";
}

if (is_array($compare)) 
{
	foreach ($compare AS $compare_value) 
	{
		if (!strlen($compare_value))
			continue;

		if (strlen($strCompareWhere))
			$strCompareWhere .= " OR ";

		$strCompareWhere .= $compare_value . $strOperation;

        if(count($relevance_search)) {
            foreach($relevance_search AS $relevance_term) {
                $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_value . ") = 1, 0, 1)";
            }
        }
	}
} 
elseif (strlen($compare)) 
{
	$strCompareWhere .= $compare . $strOperation;
    if(count($relevance_search)) {
        foreach($relevance_search AS $relevance_term) {
            $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare . ") = 1, 0, 1)";
        }
    }
}

if (is_array($compare_having)) 
{
	foreach ($compare_having AS $compare_value) 
	{
		if (!strlen($compare_value))
			continue;

		if (strlen($strCompareHaving))
			$strCompareHaving .= " OR ";

		$strCompareHaving .= $compare_value . $strOperation;

        if(count($relevance_search)) {
            foreach($relevance_search AS $relevance_term) {
                $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_value . ") = 1, 0, 1)";
            }
        }
    }
} 
elseif (strlen($compare_having)) 
{
	$strCompareHaving .= $compare_having . $strOperation;

    if(count($relevance_search)) {
        foreach($relevance_search AS $relevance_term) {
            $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_having . ") = 1, 0, 1)";
        }
    }
}

if (!strlen($strCompareHaving) && !strlen($strCompareWhere)) 
{
	$wizard_field = substr($actex_sql, strpos(strtoupper($actex_sql), "SELECT") + 7, strrpos(strtoupper($actex_sql), "FROM") - (strpos(strtoupper($actex_sql), "SELECT") + 7));

	$arrWizardField = explode(" AS ", $wizard_field);
	if (is_array($arrWizardField) && count($arrWizardField)) 
	{
		$first = true;
		foreach ($arrWizardField AS $field_value) 
		{
			if (!strlen($field_value))
				continue;

			if ($first) 
			{
				$first = false;
				continue;
			}
            $field_wizard = "";

			if (strrpos(ltrim($field_value, "`"), "`") !== false) 
			{
				$field_wizard = substr(ltrim($field_value, "`"), 0, strrpos(ltrim($field_value, "`"), "`"));
			} 
			elseif (strpos(ltrim($field_value, ","), ",") !== false) 
			{
				$field_wizard = substr(ltrim($field_value, ","), 0, strpos(ltrim($field_value, ","), ","));
			} 
			elseif (strpos(ltrim($field_value), " ") !== false) 
			{
				$field_wizard = substr(ltrim($field_value), 0, strpos(ltrim($field_value), " "));
			} 

            if($field_wizard) {
                if (strlen($strCompareHaving))
                    $strCompareHaving .= " OR ";

                $strCompareHaving .= $field_wizard . $strOperation;

                if(count($relevance_search)) {
                    foreach($relevance_search AS $relevance_term) {
                        $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $field_wizard . ") = 1, 0, 1)";
                    }
                }
            }
		}
	}
}

if (strlen($actex_field) && strlen($father_value))
{

    if($strCompareWhere)
        $strCompareWhere = "(" . $strCompareWhere . " AND ";
    if($strCompareHaving)
        $strCompareHaving = "(" . $strCompareHaving . " AND ";

    switch($operation_sign)
    {
        case "IN":
            if(strlen($father_value)) 
            {
                if($strCompareWhere)
                    $strCompareWhere .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
                if($strCompareHaving)
                    $strCompareHaving .= " FIND_IN_SET(" . $db->toSql(new ffData($father_value), "Text", false) . ", $actex_field)"; 
            } 
            else 
            {
                if($strCompareWhere)
                    $strCompareWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
                if($strCompareHaving)
                    $strCompareHaving .= " $actex_field = " . $db->toSql(new ffData($father_value));
            }
            break;

        case "LIKE":
            if($strCompareWhere)
                $strCompareWhere .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
            if($strCompareHaving)
                $strCompareHaving .= " $actex_field LIKE '%(" . $db->toSql(new ffData($father_value), "Text", false) . "%'";
            break;
        case "<>":
            if($strCompareWhere)
                $strCompareWhere .= " $actex_field <> " . $db->toSql(new ffData($father_value));
            if($strCompareHaving)
                $strCompareHaving .= " $actex_field <> " . $db->toSql(new ffData($father_value));
            break;
        case "=":
        default:
            if($strCompareWhere)                
                $strCompareWhere .= " $actex_field = " . $db->toSql(new ffData($father_value));
            if($strCompareHaving)
                $strCompareHaving .= " $actex_field = " . $db->toSql(new ffData($father_value));
    }

    if (strpos($strCompareWhere, "(") === 0)
    {
            $strCompareWhere .= ")";
    }
    if (strpos($strCompareHaving, "(") === 0)
    {
            $strCompareHaving .= ")";
    }
}

if (strlen($strCompareWhere)) 
{
	$bFindWhereTag = preg_match("/\[WHERE\]/", $actex_sql);
	$bFindWhereOptions = preg_match("/(\[AND\]|\[OR\])/", $actex_sql);

	if (!$bFindWhereOptions)
		$sSqlWhere .= " WHERE ";

	$sSqlWhere .= " ( " . $strCompareWhere . ") ";
}
if (strlen($strCompareHaving)) 
{
	$bFindHavingTag = preg_match("/\[HAVING\]/", $actex_sql);
	$bFindHavingOptions = preg_match("/(\[HAVING_AND\]|\[HAVING_OR\])/", $actex_sql);

	if (!$bFindHavingOptions)
		$sSqlHaving .= " HAVING ";

	$sSqlHaving .= " ( " . $strCompareHaving . ") ";
}

$sSQL = $actex_sql;
if ($sSqlWhere) 
{
	$sSQL = str_replace("[AND]", "AND", $sSQL);
	$sSQL = str_replace("[OR]", "OR", $sSQL);
	$sSQL = str_replace("[WHERE]", $sSqlWhere, $sSQL);
} 
else 
{
	$sSQL = str_replace("[AND]", "", $sSQL);
	$sSQL = str_replace("[OR]", "", $sSQL);
	$sSQL = str_replace("[WHERE]", "", $sSQL);
}

if ($sSqlHaving) 
{
	$sSQL = str_replace("[HAVING_AND]", "AND", $sSQL);
	$sSQL = str_replace("[HAVING_OR]", "OR", $sSQL);
	$sSQL = str_replace("[HAVING]", $sSqlHaving, $sSQL);
} 
else 
{
	$sSQL = str_replace("[HAVING_AND]", "", $sSQL);
	$sSQL = str_replace("[HAVING_OR]", "", $sSQL);
	$sSQL = str_replace("[HAVING]", "", $sSQL);
}

if(count($relevance)) {
    $sSQL = str_replace("[ORDER]", " ORDER BY " . implode(", ", $relevance), $sSQL);
    $sSQL = str_replace("[COLON]", ", ", $sSQL);
} else {
	if(preg_match("/(\[COLON\])/", $sSQL))
		$sSQL = str_replace("[ORDER]", " ORDER BY ", $sSQL); 
	else
		$sSQL = str_replace("[ORDER]", "", $sSQL); 

    $sSQL = str_replace("[COLON]", "", $sSQL);
}
    
if($limit > 0)
	$sSQL = str_replace("[LIMIT]", " LIMIT " . $limit, $sSQL);
else
	$sSQL = str_replace("[LIMIT]", "", $sSQL);

if($_REQUEST["type"] == "actex") { 
    $ID_name = "value";
    $value = "desc";
    $group = "group";
} else {
    $ID_name = "value";
    $value = "label";
    $group = "cat";
}

$db->query($sSQL);
$i = -1;
if ($db->nextRecord())
{ 
    do
    {
        $i++;
        $php_array[$i][$ID_name] = ffCommon_charset_encode($db->getField($db->fields_names[0], "Text", true));
        $php_array[$i][$value] = ffCommon_charset_encode($db->getField($db->fields_names[1], "Text", true));
        if($actex_group && array_search($actex_group, $db->fields_names))
        { 
            $php_array[$i][$group] = ffCommon_charset_encode($db->getField($actex_group, "Text", true));
        }
        if(is_array($actex_attr) && count($actex_attr)) 
        {
            foreach($actex_attr AS $actex_attr_key => $actex_attr_value) 
            {
                if(is_array($actex_attr_value)) 
                {
                    if(strlen($actex_attr_value["field"]) && array_search($actex_attr_value["field"], $db->fields_names))
                        $php_array[$i]["attr"][$actex_attr_key] = ffCommon_charset_encode($db->getField($actex_attr_value["field"], "Text", true));

                    $php_array[$i]["attr"][$actex_attr_key] = $actex_attr_value["prefix"] . $php_array[$i]["attr"][$actex_attr_key] . $actex_attr_value["postfix"];
                } 
                else if(strlen($actex_attr_value) && array_search($actex_attr_value, $db->fields_names)) 
                {
                    $php_array[$i]["attr"][$actex_attr_key] = ffCommon_charset_encode($db->getField($actex_attr_value, "Text", true));
                }
            }
        }
    } while ($db->nextRecord());
}

if($_REQUEST["type"] == "actex") { 
    cm::jsonParse(array(
		"success" => true
		, "widget" => array(
			"actex" => array(
				"D$data_src" => $php_array
			)
		)
	)
);
    
} else {
    echo ffCommon_jsonenc($php_array, true);
}
exit;