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
    const TYPE                                              = "sql";

    private $device                                         = null;
    private $config                                         = null;
    private $storage                                        = null;

    public function __construct($storage)
    {
        $this->storage = $storage;
        $this->setConfig();

        $this->device = new ffDB_Sql();
        $this->device->on_error = "ignore";

        if ($this->device->connect($this->config["name"], $this->config["host"], $this->config["username"], $this->config["password"])) {
            $this->config["key"] = "ID";
        } else {
            $this->device = false;
        }
        $storage->convertData("_id", "ID");
        $storage->convertWhere("_id", "ID");
    }

	public function convertFields($fields, $flag = false)
	{
		$res 																		= array();
		$struct 																	= $this->storage->getParam("struct");
		if(is_array($fields) && count($fields))
		{
			foreach($fields AS $name => $value)
			{
				if ($name == "key") {
					$name 															= $this->config["key"];
				} elseif(strpos($name, "key") === 1) {
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
					$res[$name] 													= "`" . $name ."` " . ($value == "DESC"
																						? "DESC"
																						: "ASC"
																					);
					continue;
				}

				$field 																= $this->storage->normalizeField($name, $value);

				//if($field["res"])
				//	$result															= $field["res"];

				switch($flag) {
					case "select":
						$res[$name]         										= $field["name"];
						break;
					case "insert":
						$res["head"][$name]         								= $field["name"];
						if(is_array($field["value"])) {
							if($this->storage->isAssocArray($field["value"]))														//array assoc to string
								$res["body"][$name] 								= "'" . str_replace("'", "\\'", json_encode($field["value"])) . "'";
							else																				//array seq to string
								$res["body"][$name] 								= $this->device->toSql(implode(",", array_unique($field["value"])));
						} else {
							$res["body"][$name]         							= $this->device->toSql($field["value"]);
						}
						break;
					case "update":
						if(is_array($field["value"])) {
							if($this->storage->isAssocArray($field["value"]))														//array assoc to string
								$res[$name] 										= "`" . $field["name"] . "` = " . "'" . str_replace("'", "\\'", json_encode($field["value"])) . "'";
							else																				//array seq to string
								$res[$name] 										= "`" . $field["name"] . "` = " . $this->device->toSql(implode(",", array_unique($field["value"])));
						} else {
							$res[$name]         									= "`" . $field["name"] . "` = " . $this->device->toSql($field["value"]);
						}
						break;
					case "where":
						if(!is_array($value))
							$value 													= $field["value"];

						if($field["name"] == $this->config["key"]) {
							$value 													= $this->convertID($value);
						}

						if(is_array($struct[$field["name"]])) {
							$struct_type 											= "array";
						} else {
							$arrStructType 											= explode(":", $struct[$field["name"]], 2);
							$struct_type 											= $arrStructType[0];
						}

						switch ($struct_type) {
							case "arrayOfNumber":                                                                         //array
							case "array":                                                                                //array
								if (is_array($value) && count($value)) {
									foreach($value AS $i => $item) {
										$res[$name][] 								= ($field["not"] ? "NOT " : "") . "FIND_IN_SET(" . $this->device->toSql($item) . ", `" . $field["name"] . "`)";
									}
									$res[$name] 									= "(" . implode(($field["not"] ? " AND " : " OR "), $res[$name]) . ")";
								}
								break;
							case "boolean":
							case "date":
							case "number":
                            case "primary":
							case "string":
                            case "text":
							default:
								if (is_array($value)) {
									if(count($value))
										$res[$name] 								= "`" . $field["name"] . "` " . ($field["not"] ? "NOT " : "") . "IN('" . implode("','", array_unique($value)) . "')";
								} else {
									if ($field["op"]) {                                                //< > <= >=
										$op = "";
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
										}
										if($op) {
											$res[$name] 							= "`" . $field["name"] . "` " . $op . " " . $this->device->toSql($value);
										}
									} else {
										$res[$name]     							= "`" . $field["name"] . "` " . ($field["not"] ? "<>" : "=") . " " . $this->device->toSql($value);
									}
								}
						}
						break;
					default:
				}
			}

			if(is_array($res)) {
				switch ($flag) {
					case "select":
						$result["select"] 											= "`" . implode("`, `", $res) . "`";
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
					case "sort":
						$result["sort"]												= implode(", ", $res);
						break;
					default:
				}
			}
		}

		return $result;
	}
    public function getDevice()
    {
        return $this->device;
    }
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
    }
	private function convertID($keys) {
		if(is_array($keys))
			$res = array_filter($keys, "is_numeric");
		elseif(!is_numeric($keys))
			$res = null;

		return $res;
	}
}