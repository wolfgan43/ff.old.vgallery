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

$php_array = array();
$db = ffDB_Sql::factory();

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


    

if ($_REQUEST["voce"])
    $real_value = $_REQUEST["voce"];
else
    $real_value = "ID";

if (isset($_REQUEST["data-limit"]) && isset($_REQUEST["data-source"])) {
    if (check_function("get_schema_def")) {
        $service_schema = get_schema_def();
        if (is_array($service_schema["schema"]["search_tags"]["relationship"][$_REQUEST["data-source"]]) && count($service_schema["schema"]["search_tags"]["relationship"][$_REQUEST["data-source"]])) {
            if ($service_schema["schema"]["search_tags"]["relationship"][$_REQUEST["data-source"]]["multi"])
                $sSQL_where = " AND FIND_IN_SET(" . $db->toSql($_REQUEST["data-limit"], "Text", false) . ", " . "search_tags" . "." . $db->toSql($service_schema["schema"]["search_tags"]["relationship"][$_REQUEST["data-source"]]["key"], "Text", false) . ")";
            else
                $sSQL_where = " AND search_tags." . $db->toSql($service_schema["schema"]["search_tags"]["relationship"][$_REQUEST["data-source"]]["key"], "Text", false) . " = " . $db->toSql($_REQUEST["data-limit"], "Text", false);
        }
    }
}
$queryString = "SELECT " . $db->toSql($real_value, "Text", false) . " AS ID, name AS description
                    FROM search_tags
                    WHERE 1 " . $sSQL_where . "
                        AND `" . $compare_value . "` LIKE '%" . $db->toSql($search_value, "Text", false) . "%'
                    ORDER BY " . (count($relevance) ? implode(", ", $relevance) . ", " : "") . " name
                    LIMIT 10";

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


echo ffCommon_jsonenc($php_array, true);
	exit;
/*
echo ffCommon_jsonenc($php_array, true);
exit;
*/