<?php
$permission = check_attendance_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$UserNID = get_session("UserNID");
$db = ffDB_Sql::factory();
$db2 = ffDB_Sql::factory();

if(((isset($_REQUEST["frmAction"]) && ($_REQUEST["frmAction"] == "approve" || $_REQUEST["frmAction"] == "discard")) || isset($_REQUEST["status"])) && $_REQUEST["keys"]["ID"] > 0) {
	
	
    if(array_key_exists("status", $_REQUEST)) {
            $status = $_REQUEST["status"];
    } elseif($_REQUEST["frmAction"] == "approve") {
            $status = "1";
    } elseif($_REQUEST["frmAction"] == "discard") {
            $status = "0";
    }
		
    $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request 
                    SET " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.status = " . $db->toSql($status, "Number") . "
                    WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->execute($sSQL);
	
    $db3 = ffDB_Sql::factory();
    $sSQL3 = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.* 
                    , (IF(" . CM_TABLE_PREFIX . "mod_attendance_type.ID_force_type > 0
                        ," . CM_TABLE_PREFIX . "mod_attendance_type.ID_force_type
                        , " . CM_TABLE_PREFIX . "mod_attendance_type.ID
                    )) AS ID_type
                    , " . (check_function("get_user_data")
                        ? get_user_data("reference", "anagraph", null, false)
                        : "''"
                    ) . " AS anagraph_name
                    , IF(employee.uid > 0
                        , IF(employee.billreference = ''
                            , IF(CONCAT(employee.name, '', employee.surname) <> ''
                                , CONCAT(employee.name, ' ', employee.surname)
                                , IF(account_employee.username = '', account_employee.email, account_employee.username)
                            )
                            , CONCAT(employee.name, ' ', employee.surname)
                        )
                        , IF(employee.billreference = ''
                            , CONCAT(employee.name, ' ', employee.surname)
                            , employee.billreference
                        )
                    ) AS employee
                    , " . CM_TABLE_PREFIX . "mod_attendance_office.ID AS ID_office
                    , IF(anagraph.email = '' , " . CM_TABLE_PREFIX . "mod_security_users.email, anagraph.email) AS anagraph_email
                    , " . CM_TABLE_PREFIX . "mod_attendance_office.name_director AS name_director
                    , " . CM_TABLE_PREFIX . "mod_attendance_office.email_director AS email_director
                    , " . CM_TABLE_PREFIX . "mod_attendance_office.email_manager AS email_manager
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.name AS `type`
                    , (SELECT IF(owner_office.email = '' , owner_office_account.email, owner_office.email) AS owner_office_email
                            FROM anagraph AS owner_office
                                LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users AS owner_office_account ON owner_office_account.ID = owner_office.uid
                            WHERE owner_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office.ID_owner
                    ) AS owner_office_email
                    , " . CM_TABLE_PREFIX . "mod_attendance_office_employee.role AS employee_role
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.um AS um_type
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.mail_response_employee AS mail_response_employee
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.mail_response_customer AS mail_response_customer
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.mail_response_office AS mail_response_office
                FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request
                    INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_type ON " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_type
                    INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_user
                    INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
                    INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office_employee ON " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = anagraph.ID
                    INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office
                    LEFT JOIN anagraph AS employee ON employee.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_employee
                    LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users AS account_employee ON account_employee.ID = employee.uid
                WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID = " . $db3->toSql($_REQUEST["keys"]["ID"], "Number") . "
                    AND cm_mod_attendance_office.ID IN 
                                    (SELECT  " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office
                                            FROM  " . CM_TABLE_PREFIX . "mod_attendance_sheet 
                                            INNER JOIN  " . CM_TABLE_PREFIX . "mod_attendance_sheet_request ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_user = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user
                                            WHERE  " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_since <=  " . CM_TABLE_PREFIX . "mod_attendance_sheet.day
                                                AND IF (" . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_to = " . '0000-00-00' . " , " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_since , " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.date_to) >= " . CM_TABLE_PREFIX . "mod_attendance_sheet.day
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID = " . $db3->toSql($_REQUEST["keys"]["ID"], "Number") . 
                                    ")";
    $db3->query($sSQL3); 
    if($db3->nextRecord()) { 
        $z=1;
        $to = array();
        $to[0]["name"] = $db3->getField("anagraph_name", "Text", true);
        $to[0]["mail"] = $db3->getField("anagraph_email", "Text", true);

        $from = array();
    //		$arrDomainName = explode(".", DOMAIN_NAME);
    //		$domain_name = $arrDomainName[1] . "." . $arrDomainName[2];
        if(substr_count(str_replace("www.", "", DOMAIN_NAME), ".") > 1) {
                $arrDomainName = explode(".", str_replace("www.", "", DOMAIN_NAME));
                $domain_name = $arrDomainName[count($arrDomainName) - 2] . "." . $arrDomainName[count($arrDomainName) - 1];
        } else {
                $domain_name = str_replace("www.", "", DOMAIN_NAME);
        }		
        $from["name"] = "noreply";  
        $from["mail"] = "noreply@" . $domain_name;  
		
        do 
        { 

            $cc = null;
            $fields = array();

            $type_name = $db3->getField("type", "Text", true);
            $um_type = $db3->getField("um_type", "Text", true);
            $ID_user_request = $db3->getField("ID_user", "Text", true);

            $anagraph_name = $db3->getField("anagraph_name", "Text", true);
            $employee_name = $db3->getField("employee", "Text", true);

            $ID_type = $db3->getField("ID_type", "Number", true);
            $ID_office = $db3->getField("ID_office", "Number", true);

            $mail_response_employee = $db3->getField("mail_response_employee", "Text", true);
            $mail_response_customer = $db3->getField("mail_response_customer", "Text", true);
            $mail_response_office = $db3->getField("mail_response_office", "Text", true);

            if($status) {
                $str_status = "request_approved";
                $to_sedi[0]["name"] = $db3->getField("name_director", "Text", true);
                $to_sedi[0]["mail"] = $db3->getField("email_director", "Text", true);

                if(strlen($db3->getField("email_manager", "Text", true))) 
                {
                    $arrManager = explode(",", $db3->getField("email_manager", "Text", true));
                    if(is_array($arrManager) && count($arrManager)) 
                    {
                        foreach($arrManager AS $arrManager_key => $arrManager_value) 
                        {
                            if(strlen($arrManager_value)) 
                            {
                                $cc[] = array(
                                                "name" => trim($arrManager_value)
                                                , "mail" => trim($arrManager_value)
                                        );
                            }
                        }
                    }
                }
                if(strlen($db3->getField("owner_office_email", "Text", true)))
                {
                    $to_owner[0]["name"] = $db3->getField("owner_office_email", "Text", true);
                    $to_owner[0]["mail"] = $db3->getField("owner_office_email", "Text", true);
                }
            } else {
                $str_status = "request_discarded";
            }

            if(strlen($db3->getField("anagraph_name", "Text", true))) {
                $fields["info"]["user"] = $db3->getField("anagraph_name", "Text", true);
                $fields["info"]["user_role"] = $db3->getField("employee_role", "Text", true);
            }
            if(strlen($db3->getField("type", "Text", true))) {
                $fields["info"]["type"] = $db3->getField("type", "Text", true);
            }

            if($db3->getField("date_since", "Text", true) != "0000-00-00") {
                $date_change = $db3->getField("date_since", "Text", true);
                $fields["info"]["date_since"] = $db3->getField("date_since", "Date")->getValue("Date", FF_LOCALE);
            }
            if($db3->getField("date_to", "Text", true) != "0000-00-00") {
                $date_change_to = $db3->getField("date_to", "Text", true);
                $fields["info"]["date_to"] = $db3->getField("date_to", "Date")->getValue("Date", FF_LOCALE);
            }
            $date_since = $db3->getField("date_since", "Date")->getValue("Timestamp");
            if($db3->getField("date_to", "Date")->getValue("Timestamp") > 0) 
            {
                $date_to = $db3->getField("date_to", "Date")->getValue("Timestamp");
            } else 
            {
                $date_to = $date_since;
            }

            if(strlen($db3->getField("note", "Text", true))) {
                $fields["info"]["note"] = $db3->getField("note", "Text", true);
            }
            //DEBUG MODE
            /*
            if($UserNID == 1)
            {
                    echo $um_type;
            }
            */
            if($db3->getField("ID_employee", "Number", true) > 0) 
            {
                $ID_employee = $db3->getField("ID_employee", "Number", true);
                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_office_employee.role
                                , " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user 
                                , CONCAT(anagraph.name, ' ', anagraph.surname) AS name
                                , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from, '%H:%i') AS user_interval_from
                                , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to, '%H:%i') AS user_interval_to
                                , " . CM_TABLE_PREFIX . "mod_attendance_sheet.day
                            FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet ON " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet
                                INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office_employee ON " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user
                                INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user 
                            WHERE 1
                                AND (" . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_employee, "Number") . " OR " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user_request, "Number") . ")
                            ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from";
                $db->query($sSQL);
                if($db->nextRecord()) 
                {
                    $j = $k = 0;
                    do
                    {
                        if($db->getField("ID_user", "Number", true) == $ID_employee)
                        {
                            $fields["info"]["employee"] = $db->getField("name", "Text", true);
                            $fields["info"]["employee_role"] = $db->getField("role", "Text", true);
                            if($db->getField("day", "Text", true) == $date_change)
                            {
                                if($j > 0)
                                {
                                        $fields["interval2_time_interval_change" . $j]["divisor"] = " , ";
                                } 
                                $fields["interval2" . $j]["time_interval_change_from"] = $db->getField("user_interval_from", "Text", true);
                                $fields["interval2" . $j]["time_interval_change_to"] = $db->getField("user_interval_to", "Text", true);
                                $fields["interval2" . $j]["time_interval_change_turn"] = $db->getField("user_interval_from", "Text", true) . "/" . $db->getField("user_interval_to", "Text", true);
                                $j++;
                            }
                        } elseif($db->getField("ID_user", "Number", true) == $ID_user_request && $db->getField("day", "Text", true) == $date_change)
                        {
                            if($k > 0)
                            {
                                $fields["interval1_time_interval_change" . $k]["divisor"] = " , ";
                            }
                            $fields["interval1" . $k]["time_interval_change_from"] = $db->getField("user_interval_from", "Text", true);
                            $fields["interval1" . $k]["time_interval_change_to"] = $db->getField("user_interval_to", "Text", true);
                            $fields["interval1" . $k]["time_interval_change_turn"] = $db->getField("user_interval_from", "Text", true) . "/" . $db->getField("user_interval_to", "Text", true);
                            $k++;
                        }
                    } while($db->nextRecord());
                    if($j === 0)
                    {
                        $fields["interval2" . $j]["time_interval_change_turn"] = "Riposo";
                    }
                    if($k === 0)
                    {
                        $fields["interval1" . $k]["time_interval_change_turn"] = "Riposo";
                    }
                }
            }

            $sSQL = "SELECT DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from, '%H:%i') AS time_interval_from
                            , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to, '%H:%i') AS time_interval_to
                            , IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from = '00:00:00' 
                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to = '00:00:00' 
                                , ''
                                , CONCAT(
                                    DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from, '%H:%i')
                                    , ' / '
                                    , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to, '%H:%i')
                                )
                            ) AS `time`
                            FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval
                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID_request = " . $db2->toSql($_REQUEST["keys"]["ID"], "Number") . "
                            ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID";
            $db2->query($sSQL);
            if($db2->nextRecord()) 
            {
                $i = 1;
                do {
                    if($i > 1)
                    {
                            $fields["interval" . $i]["divisor"] = " , ";
                    }
                    $fields["info"]["time"][] = $db2->getField("time", "Text", true);
                    $fields["interval" . $i]["time_interval_from"] = $db2->getField("time_interval_from", "Text", true);
                    $fields["interval" . $i]["time_interval_to"] = $db2->getField("time_interval_to", "Text", true);
                    $fields["interval" . $i]["time_interval_cambio_orario"] = $db2->getField("time_interval_from", "Text", true) . "/" . $db2->getField("time_interval_to", "Text", true);
                    $i++;
                } while($db2->nextRecord());     
            }

            $fields["info_" . $str_status] = true;
            if($status) {

                $fields["info"]["request_admin_approved"] = ffTemplate::_get_word_by_code("sheet_" . $str_status);
                $date_diff = $date_to - $date_since;
                $count_day = floor($date_diff / 86400) + 1;
                if(!$count_day)
                    $count_day = 1;


                if($um_type == "st") //switch Time
                { 
                    $l = $m = 0;
                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                    , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from, '%H:%i') AS user_interval_day_from
                                    , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to, '%H:%i') AS user_interval_day_to
                                FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                    INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID
                                WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($date_change, "Text") . "
                                    AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user_request, "Number") . "
                                    AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number") . "
                                ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from";
                    $db->query($sSQL);
                    if($db->nextRecord()) 
                    {
                        $ID_sheet_since = $db->getField("ID", "Number", true);
                        do
                        {
                            if($m > 0)
                            {
                                    $fields["interval_change_day1" . $m]["divisor"] = " , ";
                            }
                            $fields["interval1" . $m]["time_interval_change_day_from"] = $db->getField("user_interval_day_from", "Text", true);
                            $fields["interval1" . $m]["time_interval_change_day_to"] = $db->getField("user_interval_day_to", "Text", true);
                            $fields["interval1" . $m]["time_interval_change_day"] = $db->getField("user_interval_day_from", "Text", true) . "/" . $db->getField("user_interval_day_to", "Text", true);
                            $m++;
                        } while ($db->nextRecord()); 
                    } else {
                        $fields["interval10"]["time_interval_change_day"] = "Riposo";
                    }

                    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                    , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from, '%H:%i') AS user_interval_day_from
                                    , DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_to, '%H:%i') AS user_interval_day_to
                                FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                    INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default ON " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID
                                WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($date_change_to, "Text") . "
                                    AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user_request, "Number") . "
                                    AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number") . "
                                ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.time_from";
                    $db->query($sSQL);
                    if($db->nextRecord()) 
                    {
                        $ID_sheet_to = $db->getField("ID", "Number", true);
                        do
                        {
                            if($l > 0)
                            {
                                $fields["interval_change_day2" . $l]["divisor"] = " , "; 
                            }
                            $fields["interval2" . $l]["time_interval_change_day_from"] = $db->getField("user_interval_day_from", "Text", true);
                            $fields["interval2" . $l]["time_interval_change_day_to"] = $db->getField("user_interval_day_to", "Text", true);
                            $fields["interval2" . $l]["time_interval_change_day"] = $db->getField("user_interval_day_from", "Text", true) . "/" . $db->getField("user_interval_day_to", "Text", true);
                            $l++;
                        } while ($db->nextRecord());
                    } else {
                        $fields["interval20"]["time_interval_change_day"] = "Riposo";
                    }

                    if($ID_sheet_since > 0 || $ID_sheet_to > 0) {
                        $arrDate["since"] = new ffData($date_since, "Timestamp");
                        $arrDate["to"] = new ffData($date_to, "Timestamp");

                        if($ID_sheet_to)
                        {
                            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET
                                            " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($arrDate["since"], "Date") . "
                                            , " . CM_TABLE_PREFIX . "mod_attendance_sheet.note = CONCAT(" . $db->toSql($type_name) . ", ' ', " . $db->toSql($arrDate["to"]->getValue("Date", FF_LOCALE) . " => " . $arrDate["since"]->getValue("Date", FF_LOCALE)) . ", ' ', " . $db->toSql(ffTemplate::_get_word_by_code("sheet_" . $str_status)) . ")
                                        WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_to, "Number") . "
                                            AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number");
                            $db->execute($sSQL);
                        }
                        if($ID_sheet_since)
                        {
                            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET
                                            " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($arrDate["to"], "Date") . "
                                            , " . CM_TABLE_PREFIX . "mod_attendance_sheet.note = CONCAT(" . $db->toSql($type_name) . ", ' ', " . $db->toSql($arrDate["since"]->getValue("Date", FF_LOCALE) . " => " . $arrDate["to"]->getValue("Date", FF_LOCALE)) . ", ' ', " . $db->toSql(ffTemplate::_get_word_by_code("sheet_" . $str_status)) . ")
                                        WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_since, "Number") . "
                                            AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number");
                            $db->execute($sSQL);	
                        }
                    }
                } else if($um_type == "se") //switch Employee
                { 
                    if($ID_employee > 0) 
                    { 
                        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                    FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                    WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($date_change, "Text") . "
                                        AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user_request, "Number") . "
                                        AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number");
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $ID_sheet_user = $db->getField("ID", "Number", true);
                        }
                        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                    FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                    WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql($date_change, "Text") . "
                                        AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_employee, "Number") . "
                                        AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number");
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            $ID_sheet_employee = $db->getField("ID", "Number", true);
                        }

                        if($ID_sheet_user > 0 || $ID_sheet_employee > 0) 
                        {
/*
                            if($ID_sheet_since)
                            {
                                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                            FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql(date("Y-m-d", $date_since)) . "
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_employee, "Number");
                                $db->query($sSQL);
                                if($db->nextRecord()) 
                                {
                                    $ID_sheet_old = $db->getField("ID", "Number", true);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($ID_sheet_old, "Number");
                                    $db->execute($sSQL);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_old, "Number");
                                    $db->execute($sSQL);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_old, "Number");
                                    $db->execute($sSQL);
                                }
                            }

                            if($ID_sheet_to)
                            {
                                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                        FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                        WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql(date("Y-m-d", $date_to)) . "
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user, "Number");
                                $db->query($sSQL);
                                if($db->nextRecord()) 
                                {
                                    $ID_sheet_old = $db->getField("ID", "Number", true);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($ID_sheet_old, "Number");
                                    $db->execute($sSQL);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_old, "Number");
                                    $db->execute($sSQL);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_old, "Number");
                                    $db->execute($sSQL);
                                }	
                            }*/
                            if($ID_sheet_employee)
                            {
                                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET
                                                " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user_request, "Number") . "
                                                , " . CM_TABLE_PREFIX . "mod_attendance_sheet.note = CONCAT(" . $db->toSql($type_name) . ", ' ', " . $db->toSql($employee_name) . ", ' ', " . $db->toSql(ffTemplate::_get_word_by_code("sheet_" . $str_status)) . ")
                                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_employee, "Number") . "
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number");
                                $db->execute($sSQL);
                            }
                            if($ID_sheet_user)
                            {
                                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET
                                                " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_employee, "Number") . "
                                                , " . CM_TABLE_PREFIX . "mod_attendance_sheet.note = CONCAT(" . $db->toSql($type_name) . ", ' ', " . $db->toSql($anagraph_name) . ", ' ', " . $db->toSql(ffTemplate::_get_word_by_code("sheet_" . $str_status)) . ")
                                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_user, "Number") . "
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_office = " . $db->toSql($ID_office, "Number");
                                $db->execute($sSQL);
                            }
                        }
                    }
                } else 
                {
                    for($i=0; $i < $count_day; $i++) {
                        $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet.* 
                                    FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                    WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.day = " . $db->toSql(date("Y-m-d", $date_since + (86400 * $i))) . "
                                        AND " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID_user = " . $db->toSql($ID_user_request, "Number");
                        $db->query($sSQL);
                        if($db->nextRecord()) {
                            do{
                                $ID_sheet[$db->getField("ID", "Number", true)] = $db->getField("ID", "Number", true);
                            } while ($db->nextRecord());
                        } else {
                            $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet
                                            (
                                                    ID 	
                                                    , ID_user
                                                    , ID_office
                                                    , day
                                                    , last_update
                                            ) VALUES (
                                                    null
                                                    , " . $db->toSql($ID_user_request, "Number") . "
                                                    , " . $db->toSql($ID_office, "Number") . "
                                                    , " . $db->toSql(date("Y-m-d", $date_since + (86400 * $i))) . "
                                                    , " . $db->toSql(time()) . "
                                            )";
                            $db->execute($sSQL);
                            $ID_sheet[$db->getInsertID(true)] = $db->getInsertID(true);
                        }
                        foreach($ID_sheet AS $ID_sheet_key => $ID_sheet_value)
                        {

                            $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet SET
                                                    " . CM_TABLE_PREFIX . "mod_attendance_sheet.note = CONCAT(" . $db->toSql($type_name) . ", ' ', " . $db->toSql(ffTemplate::_get_word_by_code("sheet_" . $str_status)) . ")
                                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet.ID = " . $db->toSql($ID_sheet_key, "Number");
                            $db->execute($sSQL);

                            if($um_type == "cd") { //change Day
                                $db4 = ffDB_Sql::factory();
                                $sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default SET
                                                ID_type = " . $db->toSql($ID_type, "Number") . "
                                            WHERE 1
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_key, "Number");
                                $db->execute($sSQL);

                                $sSQL4 = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval SET
                                                ID_type = " . $db4->toSql($ID_type, "Number") . "
                                            WHERE 1
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db4->toSql($ID_sheet_key, "Number");
                                $db4->execute($sSQL4);	
								
/*
                                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                                (
                                                        ID_type
                                                        , ID_sheet
                                                        , time_from
                                                        , time_to
                                                        , ID_request
                                                ) VALUES (
                                                        " . $db->toSql($ID_type, "Number") . "
                                                        , " . $db->toSql($ID_sheet_key, "Number") . "
                                                        , '09:00'
                                                        , '18:00'
                                                        , " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                                                )";
                                $db->execute($sSQL);
*/
                            } elseif($um_type == "th") {	//change Time Diff
                                $arrTime = array();
                                $arrTimeNew = array();

                                $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.*
                                            FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_key, "Number");
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    do {
                                        $arrTime[] = array("ID_type" => $db->getField("ID_type", "Number", true)
                                                                        , "ID_sheet" => $ID_sheet_key
                                                                        , "time_from" => $db->getField("time_from", "Text", true)
                                                                        , "time_to" => $db->getField("time_to", "Text", true)
                                                                        , "ID_request" => 0
                                                                );
                                    } while($db->nextRecord());
                                }
                                $sSQL = "SELECT time_from
                                                , time_to
                                            FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval
                                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID_request = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
                                $db->query($sSQL);
                                if($db->nextRecord()) {
                                    do {
                                        if(is_array($arrTime) && count($arrTime)) {
                                            foreach($arrTime AS $time_key => $time_value) {
                                                $time["from"]["before"] = new ffData($time_value["time_from"], "Time");
                                                $time["from"]["after"] = $db->getField("time_from", "Time");
                                                $time["to"]["before"] = new ffData($time_value["time_to"], "Time");
                                                $time["to"]["after"] = $db->getField("time_to", "Time");

                                                if($time["to"]["before"]->getValue("Timestamp") > $time["from"]["after"]->getValue("Timestamp")
                                                        && $time["to"]["before"]->getValue("Timestamp") < $time["to"]["after"]->getValue("Timestamp")
                                                ) {
                                                    $arrTime[$time_key]["time_to"] = $db->getField("time_from", "Text", true);
                                                }

                                                if($time["from"]["before"]->getValue("Timestamp") > $time["from"]["after"]->getValue("Timestamp")
                                                        && $time["from"]["before"]->getValue("Timestamp") < $time["to"]["after"]->getValue("Timestamp")
                                                ) {
                                                    $arrTime[$time_key]["time_from"] = $db->getField("time_to", "Text", true); 
                                                }
                                            }
                                        }

                                        $arrTimeNew[] = array("ID_type" => $ID_type
                                                                        , "ID_sheet" => $ID_sheet_key
                                                                        , "time_from" => $db->getField("time_from", "Text", true)
                                                                        , "time_to" => $db->getField("time_to", "Text", true)
                                                                        , "ID_request" => $_REQUEST["keys"]["ID"]
                                                                );
                                    } while($db->nextRecord());

                                    $arrTime = array_merge($arrTime, $arrTimeNew);
                                }

                                if(is_array($arrTime) && count($arrTime)) {
                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                                    WHERE 1
                                                            AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_key, "Number");
                                    $db->execute($sSQL);

                                    $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
                                                    WHERE 1
                                                            AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($ID_sheet_key, "Number");
                                    $db->execute($sSQL);

                                    foreach($arrTime AS $time_key => $time_value) {
                                        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                                        (
                                                                ID
                                                                , ID_type
                                                                , ID_sheet
                                                                , time_from
                                                                , time_to
                                                                , ID_request
                                                        ) 
                                                        VALUES
                                                        (
                                                                null
                                                                , " . $db->toSql($time_value["ID_type"], "Number") . "
                                                                , " . $db->toSql($time_value["ID_sheet"], "Number") . "
                                                                , " . $db->toSql($time_value["time_from"], "Time") . "
                                                                , " . $db->toSql($time_value["time_to"], "Time") . "
                                                                , " . $db->toSql($time_value["ID_request"], "Number") . "
                                                        )";
                                                                        $db->execute($sSQL);
										
                                        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
                                                        (
                                                                ID
                                                                , ID_type
                                                                , ID_sheet
                                                                , time_from
                                                                , time_to
                                                        ) 
                                                        VALUES
                                                        (
                                                                null
                                                                , " . $db->toSql($time_value["ID_type"], "Number") . "
                                                                , " . $db->toSql($time_value["ID_sheet"], "Number") . "
                                                                , " . $db->toSql($time_value["time_from"], "Time") . "
                                                                , " . $db->toSql($time_value["time_to"], "Time") . "
                                                        )";
                                        $db->execute($sSQL);
                                    }
                                }
                            } else { //change Time
                                $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                            WHERE 1
                                            AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default.ID_sheet = " . $db->toSql($ID_sheet_key, "Number");
                                $db->execute($sSQL);
                                //" . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_owner = " . $db->toSql(get_session("UserNID"), "Number") . "
                                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                                (
                                                        ID_type
                                                        , ID_sheet
                                                        , time_from
                                                        , time_to
                                                        , ID_request
                                                )
                                                (
                                                        SELECT 
                                                                " . $db->toSql($ID_type, "Number") . "
                                                                , " . $db->toSql($ID_sheet_key, "Number") . "
                                                                , time_from
                                                                , time_to
                                                                , " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                                                        FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval
                                                        WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID_request = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                                                )";
                                $db->execute($sSQL);



                                $sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
                                            WHERE 1
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($ID_sheet_key, "Number") . "
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.badgein = 0";
                                $db->execute($sSQL);

                                $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval
                                                (
                                                    ID_request
                                                    , ID_type
                                                    , ID_sheet
                                                    , time_from
                                                    , time_to
                                                )
                                                ( 
                                                    SELECT 
                                                        " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                                                        , " . $db->toSql($ID_type, "Number") . "
                                                        , " . $db->toSql($ID_sheet_key, "Number") . "
                                                        , time_from
                                                        , time_to
                                                    FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval
                                                    WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID_request = " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                                                )";
                                $db->execute($sSQL);
/*									
                                $sSQL4 = "UPDATE " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval SET
                                                ID_type = " . $db->toSql($ID_type, "Number") . "
                                            WHERE 1
                                                AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval.ID_sheet = " . $db->toSql($ID_sheet_key, "Number");
                                $db->execute($sSQL4);
                                if(!$db->affectedRows()) {
                                    $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_attendance_sheet_interval_default
                                                    (
                                                            ID_type
                                                            , ID_sheet
                                                            , time_from
                                                            , time_to
                                                            , ID_request
                                                    ) VALUES (
                                                            " . $db->toSql($ID_type, "Number") . "
                                                            , " . $db->toSql($ID_sheet_key, "Number") . "
                                                            , '00:00'
                                                            , '00:00'
                                                            , " . $db->toSql($_REQUEST["keys"]["ID"], "Number") . "
                                                    )";
                                    $db->execute($sSQL);
                                }
*/
                            }
                        }
                    }
                }
            } else 
            {
                $fields["info"]["request_admin_discarded"] = ffTemplate::_get_word_by_code("sheet_" . $str_status);
            }

            if(check_function("process_mail")) 
            {
                //DEBUG MODE
                /*
                if($UserNID == 1)
                {
                    $to = array();
                    $to_owner = array();
                    $to_sedi = array();
                    $cc = array();
                    $to[0]["name"] = "Giorgio Palermo";
                    $to[0]["mail"] = "pale619@hotmail.com";
                }
                */
                if($z === 1)
                {
                    //email dipendente
                    if(strlen($mail_response_employee)) {
                            $res_mail .= process_mail(email_system($mail_response_employee, MOD_ATTENDANCE_THEME), $to, ffTemplate::_get_word_by_code("mod_attendance_request_subjectemployee") . " " . ffTemplate::_get_word_by_code("sheet_" . $str_status), NULL, $fields, $from, null, null);
                    }
                }
                $z++;
                if($status) { 
                    //email owner office
                    if(strlen($mail_response_customer)) {
                            $res_mail .= process_mail(email_system($mail_response_customer, MOD_ATTENDANCE_THEME), $to_owner, ffTemplate::_get_word_by_code("mod_attendance_request_subjectcustomer") . " " . ffTemplate::_get_word_by_code("sheet_" . $str_status), NULL, $fields, $from, null, null);
                    }
                    //email sedi
                    if(strlen($mail_response_office)) {
                            $res_mail .= process_mail(email_system($mail_response_office, MOD_ATTENDANCE_THEME), $to_sedi, ffTemplate::_get_word_by_code("mod_attendance_request_subjectoffice") . " " . ffTemplate::_get_word_by_code("sheet_" . $str_status), NULL, $fields, $from, null, $cc);
                    }
                }
                if(strlen($res_mail) && check_function("write_notification"))
                {   
                    write_notification(ffTemplate::_get_word_by_code("mod_attendance_send_mail_error"), $res_mail, "information", "restricted", FF_SITE_PATH . MOD_ATTENDANCE_PATH . "/sheet");
                }
				
            }
        } while($db3->nextRecord());
    }

	if($_REQUEST["XHR_DIALOG_ID"]) {
	    die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("SheetRequestModify")), true));
	} else {
	    die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => false, "refresh" => true, "resources" => array("SheetRequestModify")), true));
	    //ffRedirect($_REQUEST["ret_url"]);
	}
}

if(isset($_REQUEST["keys"]["ID"]) && $_REQUEST["keys"]["ID"] > 0) {
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.*
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.um AS um_type
                    , " . CM_TABLE_PREFIX . "mod_attendance_type.mail_request AS mail_request
                FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request
                    INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_type ON " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID_type
                WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request.ID = " . $db->toSql($_REQUEST["keys"]["ID"], "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $ID_type = $db->getField("ID_type", "Number", true);
        $um_type = $db->getField("um_type", "Text", true);
        $mail_request = $db->getField("mail_request", "Text", true);
    }
} else {
    $ID_type = $_REQUEST["type"];
    if($ID_type > 0) {
            $sSQL = "SELECT * 
                            FROM " . CM_TABLE_PREFIX . "mod_attendance_type
                            WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.ID = " . $db->toSql($ID_type, "Number");
            $db->query($sSQL);
            if($db->nextRecord()) {
                    $um_type = $db->getField("um", "Text", true);
                    $mail_request = $db->getField("mail_request", "Text", true);
            }
    }
}

$sSQL = "SELECT 
			anagraph.ID
           , " . (check_function("get_user_data")
                ? get_user_data("reference", "anagraph", null, false)
                : "''"
            ) . " AS name
			, IF(anagraph.email = '' , " . CM_TABLE_PREFIX . "mod_security_users.email, anagraph.email) AS email
			, " . CM_TABLE_PREFIX . "mod_attendance_office.email_director AS email_director
			, " . CM_TABLE_PREFIX . "mod_attendance_office.email_manager AS email_manager
			, (SELECT 
					IF(owner_office.email = '' , owner_office_account.email, owner_office.email) AS owner_office_email
				FROM anagraph AS owner_office
					LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users AS owner_office_account ON owner_office_account.ID = owner_office.uid
				WHERE owner_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office.ID_owner
			) AS owner_office_email
 		FROM anagraph
			INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
			INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office_employee ON " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user = anagraph.ID
			INNER JOIN " . CM_TABLE_PREFIX . "mod_attendance_office ON " . CM_TABLE_PREFIX . "mod_attendance_office.ID = " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office
		WHERE " . CM_TABLE_PREFIX . "mod_security_users.status > 0
			AND " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db->toSql($UserNID, "Number") . "
		ORDER BY " . CM_TABLE_PREFIX . "mod_security_users.ID DESC";
$db->query($sSQL);
if($db->nextRecord()) {
	$ID_anagraph = $db->getField("ID", "Number", true);
	$anagraph_name = $db->getField("name", "Text", true);
	$anagraph_email = $db->getField("email", "Text", true);
	$office_email_director = $db->getField("email_director", "Text", true);
	$office_email_manager = $db->getField("email_manager", "Text", true);
	$owner_office_email = $db->getField("owner_office_email", "Text", true);
	//DEBUG MODE
	/*
	if($UserNID == 11)
	{
		$owner_office_email = "";
	}
	*/
}

$oRecord = ffRecord::factory($cm->oPage);
/*
if(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path) . "/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/" . basename($cm->oPage->page_path);
} elseif(file_exists(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm/ffRecord.html")) {
	$oRecord->template_dir = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents/clm";
}*/
$oRecord->id = "SheetRequestModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("sheet_request_modify_title");
$oRecord->src_table = CM_TABLE_PREFIX . "mod_attendance_sheet_request";
$oRecord->addEvent("on_do_action", "SheetRequestModify_on_do_action");
$oRecord->addEvent("on_done_action", "SheetRequestModify_on_done_action");
$oRecord->user_vars["from"]["name"] = $anagraph_name;
$oRecord->user_vars["from"]["mail"] = $anagraph_email;
$oRecord->user_vars["mail_request"] = $mail_request;
$oRecord->user_vars["um_type"] = $um_type;

if(strlen($office_email_director))
	$oRecord->user_vars["bcc"]["director"]["mail"] = $office_email_director;
if(strlen($office_email_manager))
	$oRecord->user_vars["bcc"]["manager"]["mail"] = $office_email_manager;
if(strlen($owner_office_email))
	$oRecord->user_vars["bcc"]["owner"]["mail"] = $owner_office_email;

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

if($ID_type > 0) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_type";
	$oField->container_class = "type";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_modify_type");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "SELECT ID, name 
							FROM " . CM_TABLE_PREFIX . "mod_attendance_type
							WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.approval > 0
							ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
								, " . CM_TABLE_PREFIX . "mod_attendance_type.name";
	$oField->default_value = new ffData($ID_type, "Number");
	$oField->control_type = "label";
	$oRecord->addContent($oField); 


	$oField = ffField::factory($cm->oPage);
	$oField->id = "date_since";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_date_since");
	$oField->base_type = "date";
	$oField->extended_type = "Date";
	$oField->app_type = "Date";
	$oField->default_value = new ffData(date("d-m-Y", time() + 86400), "Date", FF_LOCALE);
	$oField->widget = "datepicker";
	$oField->required = true;
	$oRecord->addContent($oField);  

	switch($um_type) {
		case "st":
		case "cd":
			$oField = ffField::factory($cm->oPage);
			$oField->id = "date_to";
			$oField->label = ffTemplate::_get_word_by_code("sheet_modify_date_to");
			$oField->base_type = "date";
			$oField->extended_type = "Date";
			$oField->app_type = "Date";
			$oField->default_value = new ffData(date("d-m-Y", time() + 172800), "Date", FF_LOCALE);
			$oField->widget = "datepicker";
			$oField->required = true;
			$oRecord->addContent($oField);  
			break;
		case "ch":
		case "th": 
			$oDetail = ffDetails::factory($cm->oPage, null, null, array("name" => "ffDetails_horiz"));
			$oDetail->id = "SheetRequestModifyInterval";
			$oDetail->title = ffTemplate::_get_word_by_code("sheet_modify_interval_title");
			$oDetail->src_table = CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval";
			$oDetail->order_default = "ID";
			//$oDetail->starting_rows = 2;
			$oDetail->min_rows = 1;
			$oDetail->force_min_rows = true;
			$oDetail->fields_relationship = array ("ID_request" => "ID");
			//$oDetail->addEvent("on_before_process_field", "SheetModifyInterval_on_before_process_field");
			//$oDetail->addEvent("on_after_process_row", "SheetModifyInterval_on_after_process_row");

			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID";
			$oField->data_source = "ID";
			$oField->base_type = "Number";
			$oDetail->addKeyField($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "time_from";
			$oField->container_class = "time-from";
			$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_time_from");
			$oField->extended_type = "Time";
			$oField->widget = "timepicker";
			$oField->required = true;
			$oDetail->addContent($oField);

			$oField = ffField::factory($cm->oPage);
			$oField->id = "time_to";
			$oField->container_class = "time-to";
			$oField->label = ffTemplate::_get_word_by_code("sheet_modify_interval_time_to");
			$oField->extended_type = "Time";
			$oField->widget = "timepicker";
			$oField->required = true;
			$oDetail->addContent($oField);

			$oRecord->addContent($oDetail);
			$cm->oPage->addContent($oDetail);
			break;
		case "se":
			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID_employee";
			$oField->label = ffTemplate::_get_word_by_code("sheet_modify_employee");
			$oField->extended_type = "Selection";
			$oField->base_type = "Number";
			$oField->source_SQL = "SELECT
			                            anagraph.ID
                                        , " . (check_function("get_user_data")
                                            ? get_user_data("reference", "anagraph", null, false)
                                            : "''"
                                        ) . " AS name
							    FROM anagraph
							    WHERE anagraph.uid IN (SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
					    								FROM " . CM_TABLE_PREFIX . "mod_security_users
					    									INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
					    									INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					    								WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_ATTENDANCE_GROUP_EMPLOYEE) . "
					    							)
					    			AND anagraph.ID <> " . $db->toSql($ID_anagraph, "Number") . "
					    			AND anagraph.ID IN 
					    				(
					    					SELECT " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user
											FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
											WHERE " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office IN 
													(
														SELECT " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_office 
														FROM " . CM_TABLE_PREFIX . "mod_attendance_office_employee
														WHERE " . CM_TABLE_PREFIX . "mod_attendance_office_employee.ID_user =" . $db->toSql($ID_anagraph, "Number") . "
													)
										)
							        [AND] [WHERE]
							    GROUP BY anagraph.ID
							    ORDER BY name";
			$oField->widget = "activecomboex";
			$oField->actex_update_from_db = true;
			$oField->resources[] = "AnagraphModify";
			$oField->required = true;
			$oRecord->addContent($oField);
			break;
	}
	$oField = ffField::factory($cm->oPage);
	$oField->id = "note";
	$oField->label = ffTemplate::_get_word_by_code("sheet_modify_note");
	$oField->base_type = "Text";
	$oField->extended_type = "Text";
	$oRecord->addContent($oField);  
} else {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_type";
	$oField->container_class = "type";
	$oField->label = ffTemplate::_get_word_by_code("sheet_request_modify_type");
	$oField->base_type = "Number";
	$oField->extended_type = "Selection";
	$oField->source_SQL = "
							(
								SELECT ID
									, name 
								FROM " . CM_TABLE_PREFIX . "mod_attendance_type
								WHERE " . CM_TABLE_PREFIX . "mod_attendance_type.approval > 0
								ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_type.default DESC
									, " . CM_TABLE_PREFIX . "mod_attendance_type.name
							)";
	$oField->required = true;
	$oRecord->addContent($oField); 
}
						

$oRecord->insert_additional_fields = array(
										"ID_user" =>  new ffData($ID_anagraph, "Number")
									);
$oRecord->additional_fields = array(
									"last_update" =>  new ffData(time(), "Number")
									);

$cm->oPage->addContent($oRecord);   

function SheetRequestModify_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();
	
    switch($action) {
		case "insert":
			if(!array_key_exists("type", $_REQUEST)) {
				ffRedirect($component->parent[0]->site_path . $component->parent[0]->page_path . "/modify?type=" . $component->form_fields["ID_type"]->getValue() . "&ret_url=" . urlencode($component->parent[0]->getRequestUri()));
			}
                case "update":
                    break;
    }
}

function SheetRequestModify_on_done_action($component, $action) {
	$db = ffDB_Sql::factory();
	$UserNID = get_session("UserNID");
    switch($action) {
		case "insert":
		case "update":
			$to = array();
		
			$sSQL = "SELECT 
                        " . (check_function("get_user_data")
                            ? get_user_data("reference", "anagraph", null, false)
                            : "''"
                        ) . " AS anagraph_name
						, IF(anagraph.email = '' , " . CM_TABLE_PREFIX . "mod_security_users.email, anagraph.email) AS anagraph_email
					FROM anagraph 
						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.uid = " . CM_TABLE_PREFIX . "mod_security_users.ID
						INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid
					WHERE " . CM_TABLE_PREFIX . "mod_security_groups.name = " . $db->toSql(MOD_ATTENDANCE_GROUP_ATTENDANCE);
			$db->query($sSQL);
			if($db->nextRecord()) {
				$i = 0;
				do {
					$to[$i]["name"] = $db->getField("anagraph_name", "Text", true);
					$to[$i]["mail"] = $db->getField("anagraph_email", "Text", true);

					$i++;
				} while($db->nextRecord());
			}		

//			$arrDomainName = explode(".", DOMAIN_NAME);
//			$domain_name = $arrDomainName[1] . "." . $arrDomainName[2];
			if(substr_count(str_replace("www.", "", DOMAIN_NAME), ".") > 1) {
				$arrDomainName = explode(".", str_replace("www.", "", DOMAIN_NAME));
				$domain_name = $arrDomainName[1] . "." . $arrDomainName[2];
			} else {
				$domain_name = str_replace("www.", "", DOMAIN_NAME);
			}

			
			//$from = $component->user_vars["from"];
			$from["name"] = "noreply";  
			$from["mail"] = "noreply@" . $domain_name;  

			//$to[0] = "pigroz@gmail.com";  //mail attendance
			$cc = null;
			//$cc = $component->user_vars["bcc"]["manager"]["mail"];

			$fields = array();

			if(strlen($component->user_vars["from"]["name"])) {
				$fields["info"]["user"] = $component->user_vars["from"]["name"];
			}
			if(isset($component->form_fields["ID_type"])) {
				$fields["info"]["type"] = $component->form_fields["ID_type"]->getDisplayValue();
			}
			if(isset($component->form_fields["date_since"])) {
				$fields["info"]["date_since"] = $component->form_fields["date_since"]->getValue("Date", FF_LOCALE);
			}
			if(isset($component->form_fields["date_to"])) {
				$fields["info"]["date_to"] = $component->form_fields["date_to"]->getValue("Date", FF_LOCALE);
			}
			if(isset($component->form_fields["ID_employee"])) {
				$fields["info"]["employee"] = $component->form_fields["ID_employee"]->getDisplayValue();
			}
			$sSQL = "SELECT 
		                    IF(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from = '00:00:00' 
		                        AND " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to = '00:00:00' 
		                        , ''
		                        , CONCAT(
	                        		DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_from, '%H:%i')
	                        		, ' / '
	                        		, DATE_FORMAT(" . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.time_to, '%H:%i')
		                        )
		                    ) AS `time`
		                FROM " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval
		                WHERE " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID_request = " . $db->toSql($component->key_fields["ID"]->value) . "
		                ORDER BY " . CM_TABLE_PREFIX . "mod_attendance_sheet_request_interval.ID";
		    $db->query($sSQL);
		    if($db->nextRecord()) {
		    	$i = 0;
				do {
					$fields["info"]["time"][] = $db->getField("time", "Text", true); 
					$i++;
				} while($db->nextRecord());
		    }
		
			if(isset($component->form_fields["note"])) {
				$fields["info"]["note"] = $component->form_fields["note"]->getValue();
			}
			
			if(strlen($component->user_vars["mail_request"]) && check_function("process_mail")) 
			{
				/*if(strlen($cc_request)) 
				{
					$arrManager = explode(",", $cc_request);
					if(is_array($arrManager) && count($arrManager)) 
					{
						foreach($arrManager AS $arrManager_key => $arrManager_value) 
						{
							if(strlen($arrManager_value)) 
							{
								$cc["manage" . $arrManager_key]["mail"] = trim($arrManager_value);
							}
						}
					}

				}*/
				//DEBUG MODE
				/*
				if($UserNID == 11)
				{
					$to = array();
					$cc = array();
					$to[0]["name"] = "Giorgio Palermo";
					$to[0]["mail"] = "pale619@hotmail.com";
				}
				*/
				$res_mail = process_mail(email_system($component->user_vars["mail_request"], MOD_ATTENDANCE_THEME), $to, ffTemplate::_get_word_by_code("mod_attendance_request_subject") . " " . $component->form_fields["ID_type"]->getDisplayValue(), NULL, $fields, $from, null, $cc);
			}
			
			break;
    }
} 
?>