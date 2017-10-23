<?php
$user_path = $cm->real_path_info;

check_function("Anagraph");
//todo: ffCommon_crossDomains
switch(basename($user_path))
{
    case "verify":
        check_function("get_schema_fields_by_type");

        $arPathInfo = explode("/", $user_path);
        $username = $arPathInfo[2];
        $action = $arPathInfo[1];

        $user = Anagraph::getInstance($username);
        $user->verify($code);

        break;
    case "register":

        break;
    default:
}
