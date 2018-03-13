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


class Anagraph extends vgCommon
{
    static $singleton                   = null;

    protected $storage                 	= "mysql";
    protected $services                 = array(
											"anagraph" => null
											, "access" => null
										);
    protected $controllers              = array(
                                            "anagraph"                      => array(
                                                "default"                   => false
                                                , "services"                => false
                                                , "storage"                 => array(
                                                    "nosql"                 => null
                                                    , "sql"                 => null
                                                )
												, "struct"					=> "anagraph"
                                            )
											, "access"                      => array(
                                                "default"                   => false
                                                , "services"                => false
                                                , "storage"                 => array(
                                                    "sql"                 	=> null
                                                )
												, "struct"					=> "access"
                                            )
                                        );
    protected $controllers_rev          = array();
    protected $struct              		= array(
											"connectors"                    => array(
												"sql"                       => array(
													"prefix"				=> "ANAGRAPH_DATABASE_"
                                                )
                                                , "nosql"                   => array(
													"prefix"				=> "ANAGRAPH_MONGO_DATABASE_"
                                                )
											)
											, "table" => array(
												/*
												 * quelli che permettono l'identificazione diretta, come i dati anagrafici (ad esempio: nome e cognome), le immagini, ecc.;
												 */
												"identification " => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"access" 			=> "uid"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)
												, "general" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph_ext"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"identification" 	=> "ID_anagraph"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)

												/*
												 * quelli che possono rivelare l'origine razziale ed etnica, le convinzioni religiose, filosofiche o di altro genere, le opinioni politiche, l'adesione a partiti, sindacati, associazioni od organizzazioni a carattere religioso, filosofico, politico o sindacale, lo stato di salute e la vita sessuale;
												 */
												, "sensitivity" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph_ext"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"identification" 	=> "ID_anagraph"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)
												/*
												 * quelli che possono rivelare l'esistenza di determinati provvedimenti giudiziari soggetti ad iscrizione nel casellario giudiziale (ad esempio, i provvedimenti penali di condanna definitivi, la liberazione condizionale, il divieto od obbligo di soggiorno, le misure alternative alla detenzione) o la qualità di imputato o di indagato
												 */
												, "legal" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph_ext"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"identification" 	=> "ID_anagraph"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)
												/*
												 * i dati personali relativi alle caratteristiche genetiche ereditarie o acquisite di una persona fisica che forniscono informazioni univoche sulla fisiologia o sulla salute di detta persona fisica, e che risultano in particolare dall'analisi di un campione biologico della persona fisica in questione
												 */
												, "genetic" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph_ext"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"identification" 	=> "ID_anagraph"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)
												/*
												 * i dati personali ottenuti da un trattamento tecnico specifico relativi alle caratteristiche fisiche, fisiologiche o comportamentali di una persona fisica che ne consentono o confermano l'identificazione univoca, quali l'immagine facciale o i dati dattiloscopic
												 */
												, "biometric" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph_ext"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"identification" 	=> "ID_anagraph"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)
												/*
												 * : i dati personali attinenti alla salute fisica o mentale di una persona fisica, compresa la prestazione di servizi di assistenza sanitaria, che rivelano informazioni relative al suo stato di salut
												 */
												, "health" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													, "table" 				=> "anagraph_ext"
													, "key" 				=> "ID"
													, "rel" 				=> array(
														"identification" 	=> "ID_anagraph"
													)
													, "crypt" => array(
														"data" => false //metodo di criptazione applicato es: sha256
														, "transfert" => false  //metodo di criptazione applicato es: ssl
													)
												)
												/*
												 * i Dati per effettuare l'accesso all'interno del sistema.
												 */
												, "access" => array(
													"prefix"				=> null //prefisso usato per le costanti di connessione db
													,"table" 				=> CM_TABLE_PREFIX . "mod_security_users"
													, "key" 				=> "ID"
													, "crypt" 				=> array(
														"data" 				=> false //metodo di criptazione applicato es: sha256
														, "transfert" 		=> false  //metodo di criptazione applicato es: ssl
													)
												)

											)
										);
    protected $struct_default                   = "access";

    protected $query                            = "";
    protected $users                            = null;
    protected $groups                           = null;
    protected $fields                           = array();
    private $session							= null;

	public static function getInstance($services = null, $params = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Notifier($services, $params);
		else {
			if($services)
				self::$singleton->setServices($services);

			self::$singleton->setParams($params);
		}
		return self::$singleton;
	}

	public function __construct($services = null, $params = null)
	{
		$this->setServices($services);
		$this->setParams($params);

		//$this->loadControllers(__DIR__);
		$this->loadSession();
		//da aggiungere inizializzazioni classe necessarie come anagraph
	}

    private function loadSession()
	{
		if(!$this->session) {
			$this->session = get_session("user_permissions");
		}
	}
	private function getAnagraphByUser($uid = null) {
    	if(!$uid)
			$uid = $this->session["ID"];

	}


    public function get($where = null, $fields = null)
    {
		$service = "server";
		$connectors = $this->controllers[$service]["storage"];
		foreach($connectors AS $type => $data)
		{
			if(!$data)
			{
				$connectors[$type] = array(
					"service" => null
				, "connector" => $this->struct["connectors"][$type]
				);
			}
		}
print_r($connectors);
		die();
		$storage = Storage::getInstance($connectors);
		$this->result = $storage->read($where, $fields);
		$res = ($this->result
			? $this->result
			: array(
				"result" => array()
			)
		);

        //$anagraph = get


    }



    public function addFields($fields)
    {
        if(is_array($fields))
        {
            foreach($fields AS $name => $type)
            {
                $this->addField($name, $type);
            }
        } else {
            $this->addField($fields);
        }
    }


    public function get_user($dest = array(), $to, $fields = null, $service = null) {
        $db = ffDB_Sql::factory();

        if(is_array($fields) && count($fields)) {
            foreach($fields AS $field_name) {
                $query["select"][] = CM_TABLE_PREFIX . "mod_security_users.`" . $field_name . "`";
            }
        } elseif($fields) {
            $query["select"][] = CM_TABLE_PREFIX . "mod_security_users.`" . $fields . "`";
        }

        if($service) {
            $query["select"][] = CM_TABLE_PREFIX . "mod_security_token.`token`";
            $query["join"][] = CM_TABLE_PREFIX . "mod_security_token.`token` ON " . CM_TABLE_PREFIX . "mod_security_token.`token`.ID_user = " . CM_TABLE_PREFIX . "mod_security_users.ID";
        }

        if(is_array($to)) {
            if(is_array($to["uid"])) {
                $dest["uid"] = implode(",", $to["uid"]);
                $query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID IN(" . $dest["uid"] . ")";
            } elseif(is_numeric($to["uid"])) {
                $dest["uid"] = $to["uid"];
                $query["where"][] = CM_TABLE_PREFIX . "mod_security_users.ID = " . $dest["uid"];
            }
        }

        if($query) {
            $dest = array();
            $sSQL = "SELECT DISTINCT " . implode(", ", $query["select"]) . "
                    FROM " . CM_TABLE_PREFIX . "mod_security_users
                        " . (is_array($query["join"])
                            ? " INNER JOIN " . implode(" INNER JOIN ", $query["join"])
                            : ""
                        ) . "
                    WHERE " . implode(" AND ", $query["where"]);
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $dest["uid"][] = $db->record;
                } while($db->nextRecord());
            }
        }

        return $dest;
    }

    private function addField($name, $type = null)
    {
        $this->fields[$name] = $type;
    }

    private function makeSelect()
    {
        if(is_array($this->fields))
        {
            foreach($this->fields AS $name => $type)
            {
                if(!$this->struct[$type])
                    $type = $this->struct_default;

                $this->query["select"][] = $this->struct[$type]["table"] . "." . $name;
            }
        }
    }
    private function addWhere($value, $field = null, $type = null)
    {
        if(!$field)
            $field = $value;

        $this->fields[$field] = $value;
    }
}