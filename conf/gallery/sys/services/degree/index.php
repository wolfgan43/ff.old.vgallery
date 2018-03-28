<?php

// ----------------------------------------
//          FRAMEWORK FORMS vAlpha
//              PLUGIN EXTRAS (activecomboex)
//               by Samuele Diella
// ----------------------------------------
// impedisce a google d'indicizzare il servizio
if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") !== false) { 
    die('<html>
			<head>
				<title>no resource</title>
				<meta name="robots" content="noindex,nofollow" />
				<meta name="googlebot" content="noindex,nofollow" />
			</head>
		</html>');
}

// impedisce l'accesso diretto ai browser
if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false */) {
    http_response_code(404);
    exit;
}

//require_once("../../../../../../ff/main.php");
//require_once("../../../../../../modules/security/common.php");
//if ($plgCfg_ActiveComboEX_UseOwnSession)
//else
//    mod_security_check_session();

$php_array = array();

if(isset($_REQUEST["term"]) && strlen($_REQUEST["term"]))
{
    $search_value = $_REQUEST["term"];
    $search_value = str_replace("%", "\%", $search_value);
    $search_value = str_replace(" ", "%", $search_value);
    $search_value = str_replace("*", "%", $search_value);

    $compare_value = "name";

    $relevance = array();
    $relevance_search = array();
    if($search_value)
            $relevance_search = explode("%", $search_value);

    if(count($relevance_search)) {
        foreach($relevance_search AS $relevance_term) {
            $relevance[] = "IF(LOCATE(" . $db->toSql($relevance_term) . ", " . $compare_value . ") = 1, 0, 1)";
        }
    }
    
    $sSQL_where_autocomplete = " AND `" . $compare_value . "` LIKE '%" . $db->toSql($search_value, "Text", false) . "%'";
    $sSQL_order_autocomplete = (count($relevance) ? implode(", ", $relevance) . "," : "");
}

$data_src = $_REQUEST["data_src"];
//$selected_value = $_REQUEST["sel_val"];

$actex_main_db = false;
if ($actex_main_db)
    $db = mod_security_get_main_db();
else
    $db = ffDB_Sql::factory();




$queryString = "SELECT anagraph_role.ID
                        , (anagraph_role.name) AS description
                    FROM anagraph_role
                    WHERE " . (strlen($sSQL_where_autocomplete)
                        ? $sSQL_where_autocomplete
                        : "1"
                    ) . "
                    ORDER BY " . $sSQL_order_autocomplete . " description";




$actex_sql = $queryString;
$actex_operation = "";
$actex_skip_empty = false;
$actex_group = null;
$actex_attr = null;
$hide_result_on_query_empty = false;
$actex_preserve_field = null;

if (!strlen($father_value) && $hide_result_on_query_empty)
    die(cm::jsonParse(array()));

$sSQL = $actex_sql;

$db->query($sSQL);
$i = -1;
if ($db->nextRecord()) {
    do {
        $i++;
        $php_array[$i]["value"] = ffCommon_charset_encode($db->getField($db->fields_names[0], "Text", true));
        $php_array[$i]["desc"] = ffCommon_charset_encode($db->getField($db->fields_names[1], "Text", true));
        if ($actex_group && array_search($actex_group, $db->fields_names)) {
            $php_array[$i]["group"] = ffCommon_charset_encode($db->getField($actex_group, "Text", true));
        }
        if (is_array($actex_attr) && count($actex_attr)) {
            foreach ($actex_attr AS $actex_attr_key => $actex_attr_value) {
                if (is_array($actex_attr_value)) {
                    if (strlen($actex_attr_value["field"]) && array_search($actex_attr_value["field"], $db->fields_names))
                        $php_array[$i]["attr"][$actex_attr_key] = ffCommon_charset_encode($db->getField($actex_attr_value["field"], "Text", true));

                    $php_array[$i]["attr"][$actex_attr_key] = $actex_attr_value["prefix"] . $php_array[$i]["attr"][$actex_attr_key] . $actex_attr_value["postfix"];
                }
                else if (strlen($actex_attr_value) && array_search($actex_attr_value, $db->fields_names)) {
                    $php_array[$i]["attr"][$actex_attr_key] = ffCommon_charset_encode($db->getField($actex_attr_value, "Text", true));
                }
            }
        }
        //$php_array[$i]["value"] = ffCommon_charset_encode($db->getResult(NULL, 0)->getValue());
        //$php_array[$i]["desc"] = ffCommon_charset_encode($db->getResult(NULL, 1)->getValue());
    } while ($db->nextRecord());
}


if($_REQUEST["type"] == "selection" || $_REQUEST["type"] == "actex") {
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