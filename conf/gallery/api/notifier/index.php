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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
/*
$mailer = Mailer::getInstance();
$mailer->send("ciao", "ciao", "wolfgan@gmail.com");
die();

Jobs::getInstance()->run();
die();*/


/*
$pippo = Stats::getInstance("user")->read(array("user_vars.follow-user-101719" => "1"));
print_r($pippo);
die();*/
/*
$notifier = Notifier::getInstance("sms");
$notifier->send("You Welcome2AHAHAH!", "+393397324389");
print_r($notifier);*/

/*

$stats = Stats::getInstance("page");
$stat = $stats->sum(array("owner" => 205096));

$stat = $stats->range("2018-03", "hits", array("owner" => 205096));

print_r($stat);
die();*/

$ciao = Cms::getUrl("/asd/asd");


$anagraph = Anagraph::getInstance("access");
$lists = $anagraph->get(array("ID" => "9"), array("access.users.username", "name", "anagraph.email", "anagraph_type.name", "anagraph_type.email"));



$lists->getAccess("username");


$lists = array(
    "9" => array(
        "name" => "asD"
        //[...]
        , "access" => array(
            "username" => "ASD"
            //[...]
        )
        , "doctor_info" => array(
        )
        , "doctor_studi" => array(
        )
    )

);
die();

//Cache::log("ciao", "ww");
switch($cm->real_path_info) {
	case "/user":
		$type = "user";
		$twhere = "src";
		break;
	case "/page":
	default:
		$type = "page";
		$twhere = "url";
}

$stats = Stats::getInstance("page");

/*


//print_R($res) ;
die();
*/

$res = $stats->range("2017-02", "hits", array("url" => "/medici-online/ippocrate-di-coo", "domain" => DOMAIN_INSET));
print_r($res);
echo "------------------";
$res = $stats->range("2017-02", "hits", array("owner" => "9"));
print_r($res);
die("AZD");

echo "<br>" . "Inizializzazione della classe:";
echo "<br>" . '$stats = Stats::getInstance("' . $type . '");';
echo "<br>";
echo "<br>";
echo "<br>";



echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le informazioni di ippocrate ($type)";
echo "<br>" . '$res = $stats->read(array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->read(array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res);

echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le informazioni e aggiunge una variabile di ambiente 'pippo' ad ippocrate ($type)";
echo "<br>" . '$stats->update(array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"), array("pippo" => 2));' . "<br>";
$res = $stats->update(array($twhere => "/medici-online/ippocrate-di-coo"), array("pippo" => 2));
print_R($res);

echo "<br>";
echo "<br>********************************************************************";
echo "<br>recupera tutte le variabili di ambiente relative a ippocrate ($type)";
echo "<br>" . '$res = $stats->get(null, array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->get(null, array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res);

echo "<br>";
echo "<br>********************************************************************";
echo "<br>Incrementa la variabile hits ad ippocrate ($type)";
echo "<br>" . '$res = $stats->set(array("hits" => "++"), array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->set(array("hits" => "++"), array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res);

echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera la variabile hits da ippocrate ($type)";
echo "<br>" . '$res = $stats->get("hits", array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->get("hits", array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res);

echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al set di regexp passate. da ippocrate ($type)";
echo "<br>" . '$res = $stats->like(array("hits-2018-*", "P*p*", "follow-*"), array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->like(array("hits-2018-*", "P*p*", "follow-*"), array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;

/*
echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al periodo richiesto. Supporta (anno, anno-mese, o exact match) da ippocrate ($type)";
echo "<br>" . '$res = $stats->getByTime("2018-02",array("hits", "follow"), array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->getByTime("2018-02",array("hits", "follow"), array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;*/




echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al periodo richiesto. Supporta (anno, anno-mese, o exact match o array(year => xxxx, month => xx, day => xx) da ippocrate ($type)";
echo "<br>" . '$res = $stats->range("2018-02",array("hits", "follow"), array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->range("2018-02",array("hits", "follow"), array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;

echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al periodo richiesto. Supporta (anno, anno-mese, o exact match o array(year => xxxx, month => xx, day => xx) da ippocrate ($type)";
echo "<br>" . '$res = $stats->range(array("year" => "2018", "month" => "2"),array("hits", "follow"), array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->range(array("year" => "2018", "month" => "2"),array("hits", "follow"), array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;

echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al periodo richiesto. Supporta (anno, anno-mese, o exact match o array(year => xxxx, month => xx, day => xx) da ippocrate ($type)";
echo "<br>" . '$res = $stats->range("2018-02-13",array("hits", "follow"), array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->range("2018-02-13",array("hits", "follow"), array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;


echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al periodo richiesto. Supporta (anno, anno-mese, o exact match o array(year => xxxx, month => xx, day => xx) da ippocrate ($type)";
echo "<br>" . '$res = $stats->range("2017-02", "hits", array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->range("2017-02", "hits", array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;


echo "<br>";
echo "<br>********************************************************************";
echo "<br>Recupera tutte le variabili che corrispondono al periodo richiesto. Supporta (anno, anno-mese, o exact match o array(year => xxxx, month => xx, day => xx) da ippocrate ($type)";
echo "<br>" . '$res = $stats->range("2017", "hits", array("' . $twhere . '" => "/medici-online/ippocrate-di-coo"));' . "<br>";
$res = $stats->range("2017", "hits", array($twhere => "/medici-online/ippocrate-di-coo"));
print_R($res) ;

/*
$year = "2017";
for($i = 1; $i <=12; $i++) {
	$days = cal_days_in_month(CAL_GREGORIAN, $i, $year);

	$month = str_pad($i, 2, "0", STR_PAD_LEFT);

	$arrNoData = array();
	for($r = 0; $r <=rand(0,$days); $r++) {
		$arrNoData[rand(0,$days)] = true;
	}

	$total = 0;
	for($d = 1; $d<= $days; $d++) {
		$day = str_pad($d, 2, "0", STR_PAD_LEFT);
		if($arrNoData[$d]) {
			$res = $stats->set(array("hits-$year-$month-$day" => "0"), array($twhere => "/medici-online/ippocrate-di-coo"));
			continue;
		}
		$hits = $skip = rand(10,1000);
		$res = $stats->set(array("hits-$year-$month-$day" => $hits), array($twhere => "/medici-online/ippocrate-di-coo"));

		$total += $hits;
	}
	$res = $stats->set(array("hits-$year-$month" => $total), array($twhere => "/medici-online/ippocrate-di-coo"));


}
	*/

die();













//todo: ffCommon_crossDomains
$notifier = Notifier::getInstance("sms");
$notifier->send("You Welcome2!", "+393397324389");
die("ASDASD");


$notifier = Notifier::getInstance(array("sms", "email"));
$notifier->send("You Welcome!", array(
		"tukulka" => array(
			"email" => "tukulka@gmail.com"
			, "phone" => "+393285656546516"
		)
		, "wolfgan" => array(
			"email" => "wolfgan@gmail.com"
			, "phone" => "+393397324389"
		)
	)
);
die();
/*
   $mail = Mailer::getInstance("User Registration", "sparkpost");
   echo $mail->send("Ciaone!", "ASD", array("wolfgan@gmail.com", "tukulka@gmail.com"));
die();*/

    $notifier = Notifier::getInstance(null, array(
    	"unique" => true
		, "display_in" => "/"
	));
	$res = $notifier->send(array(
	"title" => "titolo"
	, "description" => "desc"
	, "media" => array(
		"cover" => "/img"
		, "video" => "asdad.avi"
	)
	), "9");

	print_r($res);
die();





    $res = $notifier->sendMail("ci55555ao", array(22,22,22));
    print_R($res);
    
    /*$res = $notifier->read(array(
        "users" => array(22,22,22)
        , "expire" => "0"
    ), "sql");
    print_r($res);*/
    exit;