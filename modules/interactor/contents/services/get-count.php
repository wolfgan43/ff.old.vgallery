<?php
$db = ffDB_Sql::factory();
$arrCounter = array();

if(isset($_POST["params"]) && count($_POST["params"]))
{
    $array_url = json_decode($_POST["params"], true);
    foreach($array_url AS $key => $value) {
        if(strlen($list_url))
            $list_url .= ",";
        $list_url .= "'" . $db->toSql($value, "Text", false) . "'";
    }
}

$domain_path = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];
$domain_path = "http://www.paginemediche.it";
$array_url = array("/news-ed-eventi/app-e-dintorni-quando-la-tecnologia-aiuta-a-curarci");
if(strlen($list_url))
{
    
    $sSQL = "SELECT ID, hits, path
                FROM trace_url
                WHERE path IN ( " . $list_url . ")";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $arrCounter[$db->getField("path", "Text", true)]["hits"] = $db->getField("hits", "Text", true);
        } while ($db->nextRecord());
    }
}


if (1 || global_settings("MOD_INTERACTOR_FACEBOOK_SHARE_COUNT")) {
    foreach($array_url AS $index => $url)
    {
        $arrCounter[$url]["facebook"] = facebook_like_share_count($domain_path . $url);
    }   
}

if (0 && global_settings("MOD_INTERACTOR_TWITTER_SHARE_COUNT")) {
    foreach($array_url AS $index => $url)
    {
        $arrCounter[$url]["twitter"] = twitter_tweet_count($domain_path . $url);
    }   
}

if (1 || global_settings("MOD_INTERACTOR_GPLUS_SHARE_COUNT")) {
    foreach($array_url AS $index => $url)
    {
        $arrCounter[$url]["gplus"] = gplus_count($domain_path . $url);
    }   
}

if (1 || global_settings("MOD_INTERACTOR_LINKEDIN_SHARE_COUNT")) {
    foreach($array_url AS $index => $url)
    {
        $arrCounter[$url]["linkedin"] = linkedin_count($domain_path . $url);
    }   
}

print_r($arrCounter);


function url(){
    $pu = parse_url($_SERVER['REQUEST_URI']);
    return $pu["scheme"] . "://" . $pu["host"];
}

function facebook_like_share_count( $url ) {
    $api = file_get_contents( 'http://graph.facebook.com/?id=' . $url );
    $count = json_decode( $api );
    if( isset( $count->shares ) ) return (int) $count->shares;
    return 0;
};


function pinterest_pins ( $url ) {
    $api = file_get_contents( 'http://api.pinterest.com/v1/urls/count.json?callback%20&url=' . $url );
    $body = preg_replace( '/^receiveCount\((.*)\)$/', '\\1', $api );
    $count = json_decode( $body );
    if( isset( $count->count ) ) return (int) $count->count;
    return 0;
};
function gplus_count( $url ) {
    $contents = file_get_contents( 'https://plusone.google.com/_/+1/fastbutton?url=' .  $url );
    preg_match( '/window\.__SSR = {c: ([\d]+)/', $contents, $matches );
    if( isset( $matches[0] ) ) 
        return (int) str_replace( 'window.__SSR = {c: ', '', $matches[0] );
    return 0;
}
function linkedin_count($url) { 
    $contents = file_get_contents("http://www.linkedin.com/countserv/count/share?url=" .  $url . "&format=json");
    $json = json_decode($contents, true);
    return isset($json['count'])?intval($json['count']):0;
}

