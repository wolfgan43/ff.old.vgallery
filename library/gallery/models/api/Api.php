<?php
/**
 * Created by PhpStorm.
 * User: wolfgan
 * Date: 25/04/2018
 * Time: 14:10
 */

class Api {


    public static function send($data = null, $type = "json") {
        if($data) {
            switch($type) {
                case "json":
                    header("Content-type: application/json");
                    echo json_encode($data);
                    break;
                case "xml":
                    header("Content-type: application/xml");
                    echo $data;
                    break;
                case "soap":
                    header("Content-type: application/soap+xml");
                    //echo system_lib_soap_client($data);

                    break;
                default:
            }
        }

        exit;
    }


}