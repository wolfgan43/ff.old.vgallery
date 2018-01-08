<?php 
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
		$js = 'var om' . $actual_srv["code"] . ',om' . $actual_srv["code"] . '_poll=function(){var r=0;return function(n,l){clearInterval(r),r=setInterval(n,l)}}();!function(e,t,n){if(e.getElementById(n)){om' . $actual_srv["code"] . '_poll(function(){if(window["om_loaded"]){if(!om' . $actual_srv["code"] . '){om' . $actual_srv["code"] . '=new OptinMonsterApp();return om' . $actual_srv["code"] . '.init({"s":"' . $actual_srv["subcode"]  . '.' . $actual_srv["code"] . '","staging":0,"dev":0,"beta":0});}}},25);return;}var d=false,o=e.createElement(t);o.id=n,o.src="//a.optnmnstr.com/app/js/api.min.js",o.onload=o.onreadystatechange=function(){if(!d){if(!this.readyState||this.readyState==="loaded"||this.readyState==="complete"){try{d=om_loaded=true;om' . $actual_srv["code"] . '=new OptinMonsterApp();om' . $actual_srv["code"] . '.init({"s":"' . $actual_srv["subcode"]  . '.' . $actual_srv["code"] . '","staging":0,"dev":0,"beta":0});o.onload=o.onreadystatechange=null;}catch(t){}}}};(document.getElementsByTagName("head")[0]||document.documentElement).appendChild(o)}(document,"script","omapi-script");';

		$oPage->tplAddJs("om", array(
	   		"embed" => $js
	   	));
    }