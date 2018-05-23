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

class storageMongodb {
    const TYPE                                              = "nosql";

	private $device                                         = null;
    private $config                                         = null;
    private $storage                                        = null;

    /**
     * storageMongodb constructor.
     * @param $storage
     */
    public function __construct($storage)
    {
        $this->storage = $storage;
        $this->setConfig();

        $this->device = new ffDB_MongoDB();
        $this->device->on_error = "ignore";
        if ($this->device->connect($this->config["name"], $this->config["host"], $this->config["username"], $this->config["password"])) {
            $this->config["key"] = "_id";
        } else {
            $this->device = false;
        }
        $storage->convertData("ID", "_id");
        $storage->convertWhere("ID", "_id");
    }

    /**
     * @param $fields
     * @param bool $flag
     * @return array
     */
    public function convertFields($fields, $flag = false)
	{
		$res 																		= array();
    	$struct 																	= $this->storage->getParam("struct");

		if(is_array($fields) && count($fields)) {
			foreach ($fields AS $name => $value) {
				if ($name == "key") {
					$name 															= $this->config["key"];
				} elseif(0 && strpos($name, "key") === 1) {
					$name 															= substr($name, 0,1) . $this->config["key"];
				} elseif ($flag == "select" && strpos($value, ".") > 0) {
					$name 															= substr($value, 0, strpos($value, "."));
					$value 															= true;
				}
				if($flag == "select" && !is_array($value)) {
					$arrValue 														= explode(":", $value, 2);
					$value 															= ($arrValue[0] ? $arrValue[0] : true);
				}

				if($flag == "sort") {
					$res["sort"][$name] 												= ($value == "DESC"
																						? -1
																						: 1
																					);
					continue;
				}

				$field 																= $this->storage->normalizeField($name, $value);

				if($field["res"]) {
					$res 															= array_replace_recursive($res, $field["res"]);
					continue;
				}
				switch($flag) {
					case "select":
						$res["select"][$field["name"]] 								= ($field["value"]
																						? true
																						: false
																					);
						break;
					case "insert":
						$res["insert"][$field["name"]] 								= $field["value"];
						break;
					case "update":
						$res["update"]['$set'][$field["name"]] 						= $field["value"];
						break;
					case "where":
						if(!is_array($value))
							$value 													= $field["value"];

						if($field["name"] == $this->config["key"])
							$value 													= $this->convertID($value);

						$res["where"][$field["name"]] 								= $value;

						if(is_array($struct[$field["name"]])) {
							$struct_type 											= "array";
						} else {
							$arrStructType 											= explode(":", $struct[$field["name"]], 2);
							$struct_type 											= $arrStructType[0];
						}

						switch ($struct_type) {
							case "arrayOfNumber":                                                                         //array
							case "array":                                                                                //array
								//search
								if (is_array($value) && count($value)) {
									if(!$this->storage->isAssocArray($value)) {
										$res["where"][$field["name"]] 				= array(
																						($field["not"] ? '$nin' : '$in') => $value
																					);
									}
                                } elseif(is_array($value) && !count($value)) {
                                    $res["where"][$field["name"]]                   = array();
								} else {
									unset($res["where"][$field["name"]]);
								}
								break;
							case "boolean":
								if ($field["not"] && $value !== null) {                                                //not
									$res["where"][$field["name"]] 					= array(
																						'$ne' => $value
																					);
								}
								break;
							case "date":
							case "number":
                            case "primary":
								if($value !== null) {
									if ($field["op"] && $value !== null) {                                                //< > <= >=
										$op = "";
										switch($field["op"]) {
											case ">":
												$op 								= ($field["not"] ? '$lt' : '$gt');
												break;
											case ">=":
												$op 								= ($field["not"] ? '$lte' : '$gte');
												break;
											case "<":
												$op 								= ($field["not"] ? '$gt' : '$lt');
												break;
											case "<=":
												$op 								= ($field["not"] ? '$gte' : '$lte');
												break;
											default:
										}
										if($op) {
											$res["where"][$field["name"]] 			= array(
												"$op" => $value
											);
										}
									} elseif ($field["not"]) {                                                //not
										$res["where"][$field["name"]] 				= array(
																						'$ne' => $value
																					);
									}
								}
								if (is_array($value) && count($value)) {
									$res["where"][$field["name"]] 					= array(
																						($field["not"] ? '$nin' : '$in') => (($field["name"] == $this->config["key"])
																							? $value
																							: array_map('intval', $value)
																						)
																					);
								}
								break;
							case "string":
                            case "char":
                            case "text":
							default:
								if (is_array($value) && count($value)) {
									$res["where"][$field["name"]] 					= array(
																						($field["not"] ? '$nin' : '$in') => (($field["name"] == $this->config["key"])
																							? $value
																							: array_map('strval', $value)
																						)
																					);
								} elseif ($field["not"]) {                                                        //not
									if($value) {
										$res["where"][$field["name"]] 				= array(
																						'$ne' => $value
																					);
									} else {
										unset($res["where"][$field["name"]]);
									}
								}
							//string
						}
						break;
					default:
				}
			}

            if(is_array($res)) {
                switch ($flag) {
                    case "select":
                        $res["select"][$this->config["key"]]                        = true;
                        break;
                    case "insert":
                        break;
                    case "update":
                        break;
                    case "where":
                        break;
                    case "sort":
                        break;
                    default:
                }
            }
        } elseif($flag == "select") {

        }

		return $res;
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

	}


    public function down() {

	}

    /**
     * @todo: da togliere
     */
    private function setConfig()
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
					:  ""
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

	}

    /**
     * @param $value
     * @return bool|\MongoDB\BSON\ObjectID
     */
    private function getObjectID($value)
	{
		if ($value instanceof \MongoDB\BSON\ObjectID) {
			$res = $value;
		} else {
			try {
				$res = new \MongoDB\BSON\ObjectID($value);
			} catch (\Exception $e) {
				return false;
			}
		}
		return $res;
	}

    /**
     * @param $keys
     * @return array|bool|\MongoDB\BSON\ObjectID
     */
    private function convertID($keys) {
		if(is_array($keys)) {
			foreach($keys AS $subkey => $subvalue) {
				if(is_array($subvalue)) {
					foreach($subvalue AS $i => $key) {
						$ID = $this->getObjectID($key);
						if($ID)
							$res[$subkey][] = $ID;
					}
				} else {
					$ID = $this->getObjectID($subvalue);
					if($ID)
						$res[] = $ID;
				}
			}
		} else {
			$ID = $this->getObjectID($keys);
			if($ID)
				$res = $ID;
		}

		return $res;
	}
}