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

class storageMysql {
    const TYPE                                                                      = "sql";
    const KEY                                                                       = "ID";


    private $device                                                                 = null;
    private $config                                                                 = null;
    private $storage                                                                = null;

    /**
     * storageMysql constructor.
     * @param $storage
     */
    public function __construct($storage)
    {
        $this->storage                                                              = $storage;
       // $this->setConfig();

        $this->config                                                               = $this->storage->getConfig($this::TYPE);

        $this->device                                                               = new ffDB_Sql();
        $this->device->on_error                                                     = "ignore";

        if ($this->device->connect(
            $this->config["name"]
            , $this->config["host"]
            , $this->config["username"]
            , $this->config["password"]
        )) {
            $this->config["key"]                                                    = $this::KEY;
        } else {
            $this->device                                                           = false;
        }

        $storage->convertData("_id", $this::KEY);
        $storage->convertWhere("_id", $this::KEY);
    }

    /**
     * @param $fields
     * @param bool $flag
     * @return mixed
     */
    public function convertFields($fields, $flag = false)
	{
		$res 																		= array();
		$struct 																	= $this->storage->getParam("struct");
		if(is_array($fields) && count($fields))
		{
		    if($flag == "where" && is_array($fields['$or'])) {
                $res['$or']                                                         = $this->convertFields($fields['$or'], "where_OR");
            }

		    unset($fields['$or']);
			foreach($fields AS $name => $value)
			{
			    if($name == "*") {
			        if($flag == "select") {
			            $res                                                        = null;
                        $result["select"]                                           = "*";
			            break;
                    } else {
			            continue;
                    }
                }

			    $name                                                               = str_replace("`", "", $name);
                $value                                                              = str_replace("`", "", $value);

				if ($name == "key") {
					$name 															= $this->config["key"];
				} elseif(0 && strpos($name, "key") === 1) { //todo: esplode se hai un campo tipo pkey chediventa pID. da verificare perche esiste questa condizione
					$name 															= substr($name, 0,1) . $this->config["key"];
				} elseif ($flag == "select" && strpos($value, ".") > 0) { //todo: da valutare con il ritorno degl array che nn funziona es: read("campo_pippo => ciao.te = ["ciao"]["te"])
					$name 															= substr($value, 0, strpos($value, "."));
					$value 															= true;
				}
				if($flag == "select" && !is_array($value)) {
					$arrValue 														= explode(":", $value, 2);
					$value 															= ($arrValue[0] ? $arrValue[0] : true);
				}

				if($flag == "sort") {
					$res[$name] 													= "`" . str_replace(".", "`.`", $name) ."` " . ($value === "-1" || $value === "DESC"
																						? "DESC"
																						: "ASC"
																					);
					continue;
				}

				$field 																= $this->storage->normalizeField($name, $value);
                if($field == "special") {
                    if($flag == "where" || $flag == "where_OR") {
                        foreach($value AS $op => $subvalue) {
                            switch($op) {
                                case '$gt':
                                    $res[$name . '-' . $op] 				        = "`" . $name . "`" . " > " . $this->device->toSql($subvalue, "Number");
                                    break;
                                case '$gte':
                                    $res[$name . '-' . $op] 				        = "`" . $name . "`" . " >= " . $this->device->toSql($subvalue, "Number");
                                    break;
                                case '$lt':
                                    $res[$name . '-' . $op] 						= "`" . $name . "`" . " < " . $this->device->toSql($subvalue, "Number");
                                    break;
                                case '$lte':
                                    $res[$name . '-' . $op] 						= "`" . $name . "`" . " <= " . $this->device->toSql($subvalue, "Number");
                                    break;
                                case '$eq':
                                    $res[$name . '-' . $op] 						= "`" . $name . "`" . " = " . $this->device->toSql($subvalue);
                                    break;
                                case '$regex':
                                    $res[$name . '-' . $op] 						= "`" . $name . "`" . " LIKE " . $this->device->toSql(str_replace(array("(.*)", "(.+)", ".*", ".+", "*", "+"), "%", $subvalue));
                                    break;
                                case '$in':
                                    if(is_array($subvalue))
                                        $res[$name . '-' . $op] 					= "`" . $name . "`" . " IN('" . str_replace(", ", "', '", $this->device->toSql(implode(", ", $subvalue), "Text", false)) . "')";
                                    else
                                        $res[$name . '-' . $op] 					= "`" . $name . "`" . " IN('" . str_replace(",", "', '", $this->device->toSql($subvalue, "Text", false)) . "')";
                                    break;
                                case '$nin':
                                    if(is_array($subvalue))
                                        $res[$name . '-' . $op] 					= "`" . $name . "`" . " NOT IN('" . str_replace(", ", "', '", $this->device->toSql(implode(", ", $subvalue), "Text", false)) . "')";
                                    else
                                        $res[$name . '-' . $op] 					= "`" . $name . "`" . " NOT IN('" . str_replace(",", "', '", $this->device->toSql($subvalue, "Text", false)) . "')";
                                    break;
                                case '$ne':
                                    $res[$name . '-' . $op] 						= "`" . $name . "`" . " <> " . $this->device->toSql($subvalue);
                                    break;
                                case '$inset':
                                    $res[$name . '-' . $op] 						= " FIND_IN_SET(" . $this->device->toSql(str_replace(",", "','", $subvalue)) . ", `" . $name . "`)";
                                    break;
                                default:
                            }
                        }
                    }
                } else {
                    switch($flag) {
                        case "select":
                            $res[$name]         									= $field["name"];
                            break;
                        case "insert":
                            $res["head"][$name]         							= $field["name"];
                            if(is_array($field["value"])) {
                                if($this->storage->isAssocArray($field["value"]))														//array assoc to string
                                    $res["body"][$name] 							= "'" . str_replace("'", "\\'", json_encode($field["value"])) . "'";
                                else																				//array seq to string
                                    $res["body"][$name] 							= $this->device->toSql(implode(",", array_unique($field["value"])));
                            } else {
                                $res["body"][$name]         						= $this->device->toSql($field["value"]);
                            }
                            break;
                        case "update":
                            if(is_array($field["value"])) {
                                switch($field["op"]) {
                                    case "++":
                                        //skip
                                        break;
                                    case "--":
                                        //skip
                                        break;
                                    case "+":
                                        if($this->storage->isAssocArray($field["value"])) {                                                        //array assoc to string
                                            //skip
                                        } else {																				//array seq to string
                                            $res[$name] 							= "`" . $field["name"] . "` = " . "CONCAT(`"  . $field["name"] . "`, IF(`"  . $field["name"] . "` = '', '', ','), " . $this->device->toSql(implode(",", array_unique($field["value"]))) . ")";
                                        }
                                        break;
                                    default:
                                        if($this->storage->isAssocArray($field["value"]))														//array assoc to string
                                            $res[$name] 							= "`" . $field["name"] . "` = " . "'" . str_replace("'", "\\'", json_encode($field["value"])) . "'";
                                        else																				//array seq to string
                                            $res[$name] 							= "`" . $field["name"] . "` = " . $this->device->toSql(implode(",", array_unique($field["value"])));
                                }
                            } else {
                                $res[$name]         								= "`" . $field["name"] . "` = " . $this->device->toSql($field["value"]);
                                switch($field["op"]) {
                                    case "++":
                                        $res[$name] = $res[$name] . " + 1";
                                        break;
                                    case "--":
                                        $res[$name] = $res[$name] . " - 1";
                                        break;
                                    case "+":
                                        $res[$name] 								= "`" . $field["name"] . "` = " . "CONCAT(`"  . $field["name"] . "`, IF(`"  . $field["name"] . "` = '', '', ','), " . $this->device->toSql($field["value"]) . ")";
                                        break;
                                    default:
                                }
                            }
                            break;
                        case "where":
                        case "where_OR":
                            if(!is_array($value))
                                $value 												= $field["value"];

                            if($field["name"] == $this->config["key"]) {
                                $value 												= $this->convertID($value);
                            }

                            if(is_array($struct[$field["name"]])) {
                                $struct_type 										= "array";
                            } else {
                                $arrStructType 										= explode(":", $struct[$field["name"]], 2);
                                $struct_type 										= $arrStructType[0];
                            }

                            switch ($struct_type) {
                                case "arrayIncremental":                                                                     //array
                                case "arrayOfNumber":                                                                        //array
                                case "array":                                                                                //array
                                    if (is_array($value) && count($value)) {
                                        foreach($value AS $i => $item) {
                                            $res[$name][] 							= ($field["not"] ? "NOT " : "") . "FIND_IN_SET(" . $this->device->toSql($item) . ", `" . $field["name"] . "`)";
                                        }
                                        $res[$name] 								= "(" . implode(($field["not"] ? " AND " : " OR "), $res[$name]) . ")";
                                    }
                                    break;
                                case "boolean":
                                case "date":
                                case "number":
                                case "primary":
                                case "string":
                                case "char":
                                case "text":
                                default:
                                    if (is_array($value)) {
                                        if(count($value))
                                            $res[$name] 							= "`" . $field["name"] . "` " . ($field["not"] ? "NOT " : "") . "IN(" . $this->valueToFunc($value, $struct_type) . ")";
                                    } else {
                                        switch($field["op"]) {
                                            case ">":
                                                $op 								= ($field["not"] ? '<' : '>');
                                                break;
                                            case ">=":
                                                $op 								= ($field["not"] ? '<=' : '>=');
                                                break;
                                            case "<":
                                                $op 								= ($field["not"] ? '>' : '<');
                                                break;
                                            case "<=":
                                                $op 								= ($field["not"] ? '>=' : '<=');
                                                break;
                                            default:
                                                $op                                 = ($field["not"] ? "<>" : "=");
                                        }
                                        $res[$name] 							    = "`" . $field["name"] . "` " . $op . " " . $this->valueToFunc($value, $struct_type);
                                    }
                            }
                            break;
                        default:
                    }
                }
			}

			if(is_array($res)) {
				switch ($flag) {
					case "select":
                        $result["select"] = "`" . implode("`, `", $res) . "`";
					    if($result["select"] != "*") {
                            $key_name                                               = $this->storage->getFieldAlias($this->config["key"]);
                            if(!$res[$key_name])                                    $result["select"] .= ", `" . $key_name . "`";
                        }
						break;
					case "insert":
						$result["insert"]["head"] 									= "`" . implode("`, `", $res["head"]) . "`";
						$result["insert"]["body"] 									= implode(", ", $res["body"]);
						break;
					case "update":
						$result["update"] 											= implode(", ", $res);
						break;
					case "where":
						$result["where"] 											= implode(" AND ", $res);
						break;
                    case "where_OR":
                        $result["where"] 											= implode(" OR ", $res);
                        break;
					case "sort":
						$result["sort"]												= implode(", ", $res);
						break;
					default:
				}
			}
		} else {
		    switch($flag) {
                case "select":
                    $result["select"] = "*";
                    break;
                case "where":
                case "where_OR":
                    $result["where"] = " 1 ";
                    break;
                default:
            }
        }

		return $result;
	}

    /**
     * @return bool|null
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @return null
     */
    public function getConfig()
    {
        return $this->config;
    }
    public function up() {
		$struct 																	= $this->storage->getParam("struct");

	}
	public function down() {
		$struct 																	= $this->storage->getParam("struct");

	}

    /**
     * @todo: da togliere
     */
    /*private function setConfig()
    {
        $this->config = $this->storage->getConfig($this::TYPE);
		if(!$this->config["name"])
		{
			$prefix = ($this->config["prefix"] && defined($this->config["prefix"] . "NAME") && constant($this->config["prefix"] . "NAME")
				? $this->config["prefix"]
				: vgCommon::getPrefix($this::TYPE)
			);

			if (is_file($this->storage->getAbsPathPHP("/config")))
			{
				require_once($this->storage->getAbsPathPHP("/config"));
				$this->config["prefix"] = $prefix;
				$this->config["host"] = (defined($prefix . "HOST")
					? constant($prefix . "HOST")
					: "localhost"
				);
				$this->config["name"] = (defined($prefix . "NAME")
					? constant($prefix . "NAME")
					: ""
				);
				$this->config["username"] = (defined($prefix . "USER")
					? constant($prefix . "USER")
					: ""
				);
				$this->config["password"] = (defined($prefix . "PASSWORD")
					? constant($prefix . "PASSWORD")
					: ""
				);
			}
		}
    }*/

    /**
     * @param $keys
     * @return array|int|null|string
     */
    private function convertID($keys) {
		if(is_array($keys))
			$res = array_filter($keys, "is_numeric");
		elseif(!is_numeric($keys))
			$res = null;
		else
            $res = $keys;

		return $res;
	}

    /**
     * @param $value
     * @param $func
     * @param array $opt
     * @return string
     */
    private function valueToFunc($value, $func, $opt = array()) {
        $res                                                                        = "";
        $uFunc                                                                      = strtoupper($func);
        switch ($uFunc) {
            case "ASCII":
            case "CHAR_LENGTH":
            case "CHARACTER_LENGTH":
            case "LCASE":
            case "LENGTH":
            case "LOWER":
            case "LTRIM":
            case "REVERSE":
            case "RTRIM":
            case "TRIM":
            case "UCASE":
            case "UPPER":
            case "ENCRYPT":
            case "MD5":
            case "OLD_PASSWORD":
            case "PASSWORD":
                if(is_array($value)) {
                    foreach($value AS $i => $v) {
                        $res[$i]                                                    = $uFunc . "(" . $this->device->toSql($value) . ")";
                    }
                    $res                                                            = implode(",", $res);
                } else {
                    $res                                                            = $uFunc . "(" . $this->device->toSql($value) . ")";
                }

                break;
            case "REPLACE";
            case "CONCAT";
            //todo: da fare altri metodi se servono
                break;
            case "AES256":
                $res = openssl_encrypt ($value, "AES-256-CBC", time()/*$this->getCertificate()*/);
                break;
            default:
                if(is_array($value)) {
                    $res                                                            = "'" . implode("','", array_unique($value)) . "'";
                } else {
                    $res                                                            = $this->device->toSql($value);
                }
        }

        return $res;
    }
}