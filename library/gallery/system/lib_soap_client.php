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
function system_lib_soap_client($url, $headers, $action, $data = null, $auth = null)
{
	/*
		//example for autentication standard in soap or other options in call soalClient
		$auth = array('login' => ''			//string
					, 'password' => ''		//string
				)

		$headers = array('nameSpace' => ''	//string
						, 'name' => '' 		//string
						, 'params' => '' 	//mixed data
				);		
	
	*/

    if(!is_array($data))
        $data = array($data);

    $clientParams = array(
			"trace"      => 1,        // enable trace to view what is happening
			"exceptions" => 0,        // disable exceptions
			"cache_wsdl" => 0         // disable any caching on the wsdl, encase you alter the wsdl server
    );

    if(is_array($auth) && count($auth))
    {
	    $clientParams = array_merge($clientParams, $auth);
    }
    
    // Create the SoapClient instance 
    $client = new SoapClient($url, $clientParams);

    // Create the header 
    $header     = new SoapHeader($headers["nameSpace"], $headers["name"], $headers["params"], false);

    // Call wsdl function 
    $result = $client->__soapCall($action, $data, NULL, $header); 
    

    //The result
	if (is_soap_fault($result)) {
	    return array("code" => $result->faultcode
	    			 , "msg" => $result->faultstring);
	} else {
		return $result->{$action . "Result"};
	}

}
   