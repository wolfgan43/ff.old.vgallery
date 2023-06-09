<?php
function analytics($type, $params) {
    $res = null;

    switch($type)
    {
        case "event":
            break;
        default:
    }


    return $res;
}

function analytics_set_event($user_path, $title, $code = null, $domain = null) {
    $cm = cm::getInstance();

    if(!$code)
        $code = analytics_get_code_by_webservices();

    if($code) {
        if(!$domain)
            $domain = DOMAIN_INSET;

        ga_send_pageview($domain, $user_path, $title, $code);
    }

    $cm->doEvent("vg_on_analytics_event", array($domain, $user_path, $title, $code));
}

function analytics_get_code_by_webservices() {
    if(check_function("get_webservices")) {
        $service = get_webservices();
        if($service["analytics.google"]["enable"] && $service["analytics.google"]["code"]) {
            $code = $service["analytics.google"]["code"];
        } elseif($service["analytics.google.universal"]["enable"] && $service["analytics.google.universal"]["code"]) {
            $code = $service["analytics.google.universal"]["code"];
        } elseif($service["google.tagmanager"]["enable"] && $service["google.tagmanager"]["analytics.code"]) {
            $code = $service["google.tagmanager"]["analytics.code"];
        }
    }

    return $code;
}

function analytics_parse_ecommerce_tracking($trans, $items= null, $code = null) {
    if(!$code) {
        $code = analytics_get_code_by_webservices();
    }

    if($code) {
        if($trans) {
            $js = "ga('require', 'ecommerce');";
            $js .= analytics_parse_ecommerce_transaction($trans);
            if(is_array($items) && count($items)) {
                foreach ($items as &$item) {
                    $js .= analytics_parse_ecommerce_transaction_item($trans['id'], $item);
                }
            }

            $js .= "ga('ecommerce:send');";

            return '<script type="text/javascript">' . $js . '</script>';
        }
    }
}

// Function to return the JavaScript representation of a TransactionData object.
function analytics_parse_ecommerce_transaction(&$trans) {
  return <<<HTML
  
ga('ecommerce:addTransaction', {
  'id': '{$trans['id']}',
  'affiliation': '{$trans['affiliation']}',
  'revenue': '{$trans['revenue']}',
  'shipping': '{$trans['shipping']}',
  'tax': '{$trans['tax']}'
});

HTML;
}

// Function to return the JavaScript representation of an ItemData object.
function analytics_parse_ecommerce_transaction_item(&$transId, &$item) {
  return <<<HTML
  
ga('ecommerce:addItem', {
  'id': '$transId',
  'name': '{$item['name']}',
  'sku': '{$item['sku']}',
  'category': '{$item['category']}',
  'price': '{$item['price']}',
  'quantity': '{$item['quantity']}'
});

HTML;
}

function gaParseCookie() {
	if (isset($_COOKIE['_ga'])) {
		list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
		$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
		$cid = $contents['cid'];
	} else {
		$cid = gaGenerateUUID();
	}
	return $cid;
}

//Generate UUID
//Special thanks to stumiller.me for this formula.
function gaGenerateUUID() {
	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0x0fff) | 0x4000,
		mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	);
}

//Send Data to Google Analytics
//https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
function gaSendData($data) {
	$getString = 'https://ssl.google-analytics.com/collect';
	$getString .= '?payload_data&';
	$getString .= http_build_query($data);
	$result = file_get_contents($getString);
	return $result;
}

//Send Pageview Function for Server-Side Google Analytics
function ga_send_pageview($hostname=null, $page=null, $title=null, $code = "") {
	if(strlen($code)) {
	$data = array(
		'v' => 1,
		'tid' => $code, //@TODO: Change this to your Google Analytics Tracking ID.
		'cid' => gaParseCookie(),
		't' => 'pageview',
		'dh' => $hostname, //Document Hostname "gearside.com"
		'dp' => $page, //Page "/something"
		'dt' => $title //Title
	);
	gaSendData($data);
	}
}

//Send Event Function for Server-Side Google Analytics
function ga_send_event($category=null, $action=null, $label=null, $code = "") {
	if(strlen($code)) {
	$data = array(
		'v' => 1,
		'tid' => $code, //@TODO: Change this to your Google Analytics Tracking ID.
		'cid' => gaParseCookie(),
		't' => 'event',
		'ec' => $category, //Category (Required)
		'ea' => $action, //Action (Required)
		'el' => $label //Label
	);
	gaSendData($data);
	}
}