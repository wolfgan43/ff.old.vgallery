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

class AuthCertificate {
    const PROTOCOL                                                  = "https";
    const APPID                                                     = APPID;
    const ID_LENGTH                                                 = "7";
    const KEY_LENGTH                                                = "11";
    const PRECISION                                                 = "2";
    const EXPIRE                                                    = "365";
    const ALG                                                       = "sha256";

    private $domain                                                 = null;

    private $valid_from                                             = null;
    private $valid_to                                               = null;

    private $serial_number                                          = null;
    private $countryName                                            = null;
    private $stateOrProvinceName                                    = null;
    private $localityName                                           = null;
    private $organizationName                                       = null;
    private $organizationalUnitName                                 = null;
    private $commonName                                             = null;
    private $emailAddress                                           = null;

    private $indexes                                                = null;

    public function __construct($domain)
    {
        $this->domain                                               = $domain;
    }

    public function get($secret) {
        if($this->domain) {
            if(!$this->invalidVPN("csr") && !$this->invalidVPN("pkey")) {
                $pem                                                    = $this->getContent("csr");
                $key                                                    = $this->getContent("pkey");

                if($pem && $key) {
                    //$dd = $this->createCertificate();   //provvisorio
                    //$pem = $dd["cert"];                 //provvisorio
                   // $key = $dd["pkey"];                 //provvisorio
                    $keyCheckData                                       = array(0 => $key, 1 => $secret);
                    $result                                             = openssl_x509_check_private_key($pem, $keyCheckData);

                    unset($secret);                                     //remove var from memory permanently
                    unset($keyCheckData);                               //remove var from memory permanently

                    if($result) {
                        $cert                                           = openssl_x509_read($pem); ///??? serve veramente?
                        $cert_data                                      = openssl_x509_parse($pem);

                        openssl_free_key($key);
                        openssl_x509_free($cert);                       ////??? serve veramente?

                        $this->valid_from                               = date('m-d-Y H:i:s', strval($cert_data['validFrom_time_t']));
                        $this->valid_to                                 = date('m-d-Y H:i:s', strval($cert_data['validTo_time_t']));
                        if($this->isExpired()) {
                            $res["status"]                              = "401";
                            $res["error"]                               = "Certificate Expired";
                        } else {
                            $this->serial_number                        = $cert_data['serialNumber'];
                            $this->countryName                          = $cert_data['subject']['C'];
                            $this->stateOrProvinceName                  = $cert_data['subject']['ST'];
                            $this->localityName                         = $cert_data['subject']['L'];
                            $this->organizationName                     = $cert_data['subject']['O'];
                            $this->organizationalUnitName               = $cert_data['subject']['OU'];
                            $this->commonName                           = $cert_data['subject']['CN'];
                            $this->emailAddress                         = $cert_data['subject']['emailAddress'];

                            $res["certificate"]                         = array(
                                                                            "countryName"               => $this->countryName
                                                                            , "stateOrProvinceName"     => $this->stateOrProvinceName
                                                                            , "localityName"            => $this->localityName
                                                                            , "organizationName"        => $this->organizationName
                                                                            , "commonName"              => $this->commonName
                                                                            , "emailAddress"            => $this->emailAddress
                                                                            , "kd"                      => $this->index("kd")
                                                                            , "kp"                      => $this->index("kp")
                                                                            , "kl"                      => $this->index("kl")
                                                                            , "secret"                  => $this->getDomain("secret")
                                                                        );
                            $res["status"]                              = "0";
                            $res["error"]                               = "";
                        }
                    } else {
                        $res["status"]                                  = "410";
                        $res["error"]                                   = "Certificate invalid";
                    }
                } else {
                    $res["status"]                                      = "410";
                    $res["error"]                                       = "Certificate Files Missing" . (DEBUG_MODE === true
                                                                            ? ": " . $this->getContent()
                                                                            : ""
                                                                        );
                }
            } else {
                unset($secret);                                         //remove var from memory permanently

                $res["status"]                                          = "406";
                $res["error"]                                           = "VPN Broken";
            }
        } else {
            $res["status"]                                              = "500";
            $res["error"]                                               = "Domain Missing";
        }

        return $res;
    }
    public function getDomain($key = null) {
        return ($key
            ? $this->domain[$key]
            : $this->domain
        );
    }
    private function isExpired() {
        // Convert to timestamp
        $start_ts                                           = strtotime($this->valid_from);
        $end_ts                                             = strtotime($this->valid_to);
        $user_ts                                            = time();

        // Check that user date is between start & end
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }

    private function decodeIndexes() {
        $app_precision                                      = ($this->domain["security"]["cert_id_length"]
                                                                ? $this->domain["security"]["cert_precision"]
                                                                : AuthCertificate::PRECISION
                                                            );
        $arrKL                                              = explode("ver: ", $this->organizationalUnitName);

        return array(
            "kl"    => $arrKL[1]                                            //"002010008005"   //Domain_precision * 6
            , "kd"  => substr($this->serial_number, strlen($app_precision))             //"846315270"      //$id_length + App_precision 1.000.000
            , "kp"  => substr($this->serial_number, 0, strlen($app_precision))      //"2"              //App_precision
        );
    }
    private function invalidVPN($type) {

        $server_ip                                          = $this->domain["security"][$type . "_ip"];
        if($server_ip) {
            $arrUrl                                         = explode("/", $this->domain["security"][$type . "_url"], 2);
            $host                                           = ($arrUrl[0]
                                                                ? $arrUrl[0]
                                                                : $_SERVER["HTTP_HOST"]
                                                            );

            $ip                                             = gethostbyname($host);
            if($ip != $server_ip) {
                $res                                        = true;
            }
        }
        return $res;
    }
    private function getContent($type = null) {
        $name                                               = $this->domain["name"];
        $ext                                                = "." . ($type == "csr"
                                                                ? "pem"
                                                                : ($type == "pkey"
                                                                    ? "key"
                                                                    : ""
                                                                )
                                                            );
        $protocol                                           = ($this->domain["security"][$type . "_protocol"]
                                                                ? $this->domain["security"][$type . "_protocol"]
                                                                : $this::PROTOCOL
                                                            );
        if($type) {
            switch ($protocol) {
                case "http":
                case "https":
                    $res                                    = file_get_contents($protocol . "://" . $this->domain["security"][$type . "_url"] . "/" . $name . $ext);
                    break;
                case "ssl":                                 //todo: da finire
                    break;
                case "socket":                              //todo: da finire
                    break;
                default:
            }
        } else {
            $res                                            = $protocol . "://" . $this->domain["security"]["csr_url"] . "/" . $name . ".pem";
        }
        return $res;
    }

    public function index($key) {
        if(!$this->indexes)
            $this->indexes = $this->decodeIndexes();

        return $this->indexes[$key];
    }

    public function createCertificate($secret) {
        $csrout                                                     = null;
        $certout                                                    = null;
        $pkeyout                                                    = null;

        if($secret) {
            $id_length                                              = ($this->domain["security"]["cert_id_length"]
                                                                        ? $this->domain["security"]["cert_id_length"]
                                                                        : AuthCertificate::ID_LENGTH
                                                                    );
            $key_length                                             = ($this->domain["security"]["cert_key_length"]
                                                                        ? $this->domain["security"]["cert_key_length"]
                                                                        : AuthCertificate::KEY_LENGTH
                                                                    );
            $app_precision                                          = ($this->domain["security"]["cert_precision"]
                                                                        ? $this->domain["security"]["cert_precision"]
                                                                        : AuthCertificate::PRECISION
                                                                    );

            $expire                                                 = ($this->domain["security"]["cert_expire"]
                                                                        ? $this->domain["security"]["cert_expire"]
                                                                        : AuthCertificate::EXPIRE
                                                                    );
            $digest_alg                                             = ($this->domain["security"]["cert_alg"]
                                                                        ? $this->domain["security"]["cert_alg"]
                                                                        : AuthCertificate::ALG
                                                                    );

            $arrKD                                                  = array("9", "8", "7", "6", "5", "4", "3", "2", "1", "0");
            shuffle($arrKD);
            $kd                                                     = substr(implode("", $arrKD), 0, $id_length + $app_precision);
            $kp                                                     = $app_precision;

            for($i = 1; $i <= $app_precision * 2; $i++) {
                $coord                                              = rand(1, $key_length);
                $arrKL[]                                            = str_pad($coord, 3, "0", STR_PAD_LEFT) ;
            }
            $kl                                                     = implode("", $arrKL);
            if($kp && $kd && $kl) {
                $extraattribs                                       = array(
                                                                        "kp" => $kp,
                                                                        "kd" => $kd,
                                                                        "kl" => $kl
                                                                    );
                // for SSL server certificates the commonName is the domain name to be secured
                // for S/MIME email certificates the commonName is the owner of the email address
                // location and identification fields refer to the owner of domain or email subject to be secured
                $dn                                                 = array(
                                                                        "organizationalUnitName"    => ($this->domain["company_description"]
                                                                                                        ? $this->domain["company_description"]
                                                                                                        : "Powered by " . Auth::AUTHOR
                                                                                                    ) . " ver: " . $extraattribs["kl"]
                                                                        , "commonName"              => $this->domain["name"]
                                                                    );

                if($this->domain["company_state"])                  $dn["countryName"]              = $this->domain["company_state"];
                if($this->domain["company_province"])               $dn["stateOrProvinceName"]      = $this->domain["company_province"];
                if($this->domain["company_city"])                   $dn["localityName"]             = $this->domain["company_city"];
                if($this->domain["company_name"])                   $dn["organizationName"]         = $this->domain["company_name"];
                if($this->domain["company_email"])                  $dn["emailAddress"]             = $this->domain["company_email"];

                // Generate a new private (and public) key pair
                $privkey                                            = openssl_pkey_new(array(
                                                                        "private_key_bits"          => 2048,
                                                                        "private_key_type"          => OPENSSL_KEYTYPE_RSA,
                                                                    ));

                // Generate a certificate signing request
                $csr                                                = openssl_csr_new(
                                                                        $dn,
                                                                        $privkey,
                                                                        array('digest_alg'          => $digest_alg)
                                                                    );

                // Generate a self-signed cert, valid for 365 days
                $x509                                               = openssl_csr_sign(
                                                                        $csr,
                                                                        null,
                                                                        $privkey,
                                                                        $expire,
                                                                        array('digest_alg'          => $digest_alg),
                                                                        $extraattribs["kp"] . $extraattribs["kd"]
                                                                    );

                // Save your private key, CSR and self-signed cert for later use
                openssl_csr_export($csr, $csrout);
                openssl_x509_export($x509, $certout);
                openssl_pkey_export($privkey, $pkeyout, $secret) ;

        // Show any errors that occurred here

                if(openssl_error_string() === false) {
                    $res                                            = array(
                                                                        "csr"                       => $csrout,
                                                                        "pem"                       => $certout,
                                                                        "key"                       => $pkeyout,
                                                                    );
                    if(Auth::DEBUG === true) {
                        $res["debug"]                               = array(
                                                                        "expire"                    => $expire,
                                                                        "alg"                       => $digest_alg,
                                                                        "id_length"                 => $id_length,
                                                                        "key_length"                => $key_length,
                                                                        "precision"                 => $app_precision,
                                                                        "dn"                        => $dn,
                                                                        "indexes"                   => $extraattribs
                                                                    );
                    }
                    $res["status"]                                  = "0";
                    $res["error"]                                   = "";
                } else {
                    $res["status"]                                  = "410";
                    $res["error"]                                   = "Certificate Broken";
                }
            } else {
                $res["status"]                                      = "410";
                $res["error"]                                       = "Index Broken";
            }
        } else {
            $res["status"]                                          = "400";
            $res["error"]                                           = "Secret Missing";
        }
        return $res;
    }
}