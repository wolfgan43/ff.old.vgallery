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

class Stats extends vgCommon
{
	static $singleton                   = null;
	private $service					= null;
    protected $controllers              = null;
    protected $controllers_rev          = null;
    protected $connectors               = array();
    protected $struct                   = array();
    private $driver						= null;
    private $result						= null;

    /**
     * @param $service
     * @return null|Stats
     */
    public static function getInstance($service)
	{
		if (self::$singleton === null)
			self::$singleton = new Stats($service);
		else {
			self::$singleton->service = $service;
		}
		return self::$singleton;
	}
    public static function registerErrors() {
        declare(ticks=1);

        register_tick_function(function() {
            $GLOBALS["backtrace"]       = debug_backtrace();
        });

        register_shutdown_function(function() {
            $error = error_get_last();

            switch ($error['type']) {
                case E_NOTICE:
                case E_USER_NOTICE:
                    Cache::log($error, "notice");
                    break;
                case E_WARNING:
                case E_DEPRECATED:
                    header("Content-Type: text/html");

                    echo "<br /><br /><b>Warning</b>: " . $error["message"] . " in <b>" . $error["file"] . "</b> on line <b>" . $error["line"] . "</b>";

                case E_ERROR:
                case E_RECOVERABLE_ERROR:

                case E_PARSE:
                case E_STRICT:


                case E_CORE_ERROR:
                case E_CORE_WARNING:
                case E_COMPILE_ERROR:

                case E_COMPILE_WARNING:
                case E_USER_ERROR:
                case E_USER_WARNING:

                case E_USER_DEPRECATED:


                    Stats::dump($GLOBALS["backtrace"]);

                    if(function_exists("cache_sem_remove"))
                        cache_sem_remove($_SERVER["PATH_INFO"]);

                    break;
                default:
            }

        });



        //exit;
    }

    public static function dump($backtrace = null) {
        $html                               = "";
        $disk_path                          = (defined("FF_DISK_PATH")
                                                ? FF_DISK_PATH
                                                : str_replace(vgCommon::BASE_PATH, "", __DIR__)
                                            );
        if(!$backtrace)                     $backtrace = debug_backtrace();

        //die(self::_getDiskPath());
        foreach($backtrace AS $i => $trace) {
            if($i) {
                unset($trace["object"]);
                if (is_array($trace["args"]) && count($trace["args"])) {
                    foreach ($trace["args"] AS $key => $value) {
                        if (is_object($value)) {
                            $trace["args"][$key] = "Object: " . get_class($value);
                        } elseif(is_array($value)) {
                            foreach($value AS $subkey => $subvalue) {
                                if(is_object($subvalue)) {
                                    $trace["args"][$key][$subkey] = "Object: " . get_class($subvalue);
                                } elseif(is_array($subvalue)) {
                                    $trace["args"][$key][$subkey] = $subvalue;
                                } else {
                                    $trace["args"][$key][$subkey] = $subvalue;
                                }
                            }

                        }
                    }
                    if($trace["file"]) {
                        $label = 'Line in: ' . '<b>' . str_replace($disk_path, "", $trace["file"])  . '</b>';
                        $list_start = '<ol start="' . $trace["line"] . '">';
                        $list_end = '</ol>';
                    } else {
                        $label = 'Func: ' . '<b>' .  $trace["function"] . '</b>';
                        $list_start = '<ul>';
                        $list_end = '</ul>';

                    }

                    $html .=  $list_start . '<li><a style="text-decoration: none;" href="javascript:void(0);" onclick="this.nextSibling.style = \'display:none\';">' . $label . '</a><pre>' . print_r($trace, true). '</pre></li>' . $list_end;
                }
                $res[] = $trace;
            }
        }
        echo "<hr />";
        echo "<center>BACKTRACE</center>";
        echo "<hr />";
        echo $html;
    }

    /**
     * @param bool $end
     * @param bool $isXHR
     * @return mixed
     */
    public static function benchmark($end = false, $isXHR = false) {
        static $res;

        if(function_exists("getrusage"))
        {
            $ru = getrusage();
            if ($end) {
                $res["mem"] 			= number_format(memory_get_usage(true) - $res["mem"], 0, ',', '.');
                $res["mem_peak"] 		= number_format(memory_get_peak_usage(true) - $res["mem_peak"], 0, ',', '.');
                $res["cpu"] 			= number_format(abs(($ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec']) - $res["cpu"]), 0, ',', '.');
                $res["includes"] 		= get_included_files();
                $res["classes"] 		= get_declared_classes();
                $res["db"] 				= ffDB_Sql::$_objProfile;
                $res["exTime"] 			= microtime(true) - $res["exTime"];

                if (extension_loaded('xhprof') && is_dir(FF_DISK_PATH . "/xhprof_lib")) {
                    $profiler_namespace = str_replace(".", ",", "[" . round($res["exTime"], 2) . "s] " . ($isXHR
                            ? str_replace("/", "_", trim(parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH), "/")) . " (" . str_replace("/", "_", trim($_SERVER["REQUEST_URI"], "/")) . ")"
                            : str_replace("/", "_", trim($_SERVER["REQUEST_URI"], "/"))
                        )) . " - " . $end;

                    $xhprof_data = xhprof_disable();
                    $xhprof_runs = new XHProfRuns_Default();
                    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
                    $profiler_url = sprintf('https://www.paginemediche.info/xhprof_html/index.php?run=%s&source=%s', $run_id, $profiler_namespace);

                    //  printf('nbsp;<a href="%s" target="_blank">Profiler output</a><br>', $profiler_url);
                }

                Cache::log("URL: " . $_SERVER["REQUEST_URI"] . " (" . $end . ") Benchmark: " . print_r($res, true) . "Profiler: " . $profiler_url, "benchmark" .  ($isXHR ? "_xhr" : ""));
                return $res;
            } else {
                $res["mem"]             = memory_get_usage(true);
                $res["mem_peak"]        = memory_get_peak_usage(true);
                $res["cpu"]             = $ru['ru_utime.tv_usec'] + $ru['ru_stime.tv_usec'];
                $res["exTime"] 			= microtime(true);

                if (extension_loaded('xhprof') && is_dir(FF_DISK_PATH . "/xhprof_lib")) {
                    include_once FF_DISK_PATH . '/xhprof_lib/utils/xhprof_lib.php';
                    include_once FF_DISK_PATH . '/xhprof_lib/utils/xhprof_runs.php';

                    xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
                }
            }
        }

    }

    /**
     * @param null $start
     * @return mixed|string
     */
    public static function stopwatch($start = null) {
		if(!$start)
			return microtime(true);

		$duration = microtime(true) - $start;
		return number_format($duration, 2, '.', '');
	}

    /**
     * Stats constructor.
     * @param $service
     */
    public function __construct($service)
	{
	    $this->loadControllers(__DIR__);

		$this->service = $service;
	}

    /**
     * @param $where
     * @param null $fields
     * @return null
     */
    public function read($where, $fields = null) {
		$this->clearResult();

		$this->result[$this->service] = $this->getDriver()->get_stats($where, null, $fields);
		$this->result = $this->result[$this->service];

		return $this->getResult();
	}

    /**
     * @param $where
     * @param null $set
     * @return null
     */
    public function update($where, $set = null) {
		$this->clearResult();

		$this->result[$this->service] = $this->getDriver()->get_stats($where, $set);
		$this->result = $this->result[$this->service];

		return $this->getResult();
	}

    /**
     * @param null $insert
     * @param null $update
     * @return null
     */
    public function write($insert = null, $update = null) {
		$this->clearResult();

		$this->result[$this->service] = $this->getDriver()->write_stats($insert, $update);
		$this->result = $this->result[$this->service];

		return $this->getResult();
	}

    /**
     * @param $where
     * @return null
     */
    public function delete($where) {
		$this->clearResult();

		return $this->getResult();
	}

    /**
     * @param null $fields
     * @param null $where
     * @return null
     */
    public function get($fields = null, $where = null) {
		$this->clearResult();
		if(!$where) {
			$where = $fields;
			$fields = null;
		}

		$this->result = $this->getDriver()->get_vars($where, $fields);
		return $this->getResult();
	}

    /**
     * @param $rules
     * @param $where
     * @return null
     */
    public function like($rules, $where) {
		$this->clearResult();

		$match = array();
		if($rules) {
			if (!is_array($rules))
				$rules = array($rules);

			$data = $this->getDriver()->get_vars($where);
			if (is_array($data) && count($data)) {
				foreach ($rules AS $rule) {
					$match = $match + (array)preg_grep("/^" . str_replace(array("\*", "\?"), array("(.+)", "(.?)"), preg_quote($rule)) . "$/i", array_keys($data));
				}
			}

			if (count($match))
				$this->result = array_intersect_key($data, array_fill_keys($match, true));
		}

		return $this->getResult();
	}

    /**
     * @param $rules
     * @param $where
     * @return null
     */
    public function sum($rules, $where) {
		$this->clearResult();

		$this->result = $this->getDriver()->sum_vars($where, $rules);

		return $this->getResult();
	}

    /**
     * @param $when
     * @param $fields
     * @param null $where
     * @return null
     * @throws Exception
     */
    public function range($when, $fields, $where = null) {
		$this->clearResult();
        $range                                                              = null;
        $model                                                              = null;
        $pos                                                                = null;
        $operation                                                          = null;
		if(!$where) {
			$this->isError("class not support method. todo: we need to create average stats. Please try later. ^_^");
		} else {
			if ($when) {
			    if($fields) {
                    if (!is_array($fields))
                        $fields                                             = array($fields);
                } else {
			        $this->isError("fields empty");
                }

				if (is_array($when)) {
                    $operation                                              = "get";
					$range                                                  = $when;
                    if (is_array($fields) && count($fields)) {
                        foreach ($range as $key => $value) {
                            foreach ($fields AS $field) {
                                $rules[]                                    = $field . "-" . $value->format('Y-m-d');

                                $res[$field]                                = array();
                            }
                        }
                    }
				} elseif(strpos($when, " ") !== false) {
                    $operation                                              = "get";
                    $arrPeriod                                              = explode(" ", $when);
                    $arrPeriod["start"]                                     = new DateTime($arrPeriod[0]);
                    $arrPeriod["end"]                                       = new DateTime($arrPeriod[1]);
                    $arrPeriod["end"]                                       = $arrPeriod["end"]->modify("+1 day");
                    $arrPeriod["interval"]                                  = new DateInterval('P1D');
                    $range                                                  = new DatePeriod(
                                                                                $arrPeriod["start"]
                                                                                , $arrPeriod["interval"]
                                                                                , $arrPeriod["end"]
                                                                            );
                    if (is_array($fields) && count($fields)) {
                        foreach ($range as $key => $value) {
                            foreach ($fields AS $field) {
                                $rules[]                                    = $field . "-" . $value->format('Y-m-d');

                                $res[$field]                                = 0;
                            }
                        }
                    }
                } else {
                    $operation                                              = "sum";
                    $time                                                   = strtotime($when . str_repeat("-01", 2 - substr_count($when, "-")));
                    if ($time) {
                        $arrTime                                            = getdate($time);

                        $range                                              = array(
                                                                                "year"          => $arrTime["year"]
                                                                                , "month"       => (substr_count($when, "-") >= 1
                                                                                                    ? str_pad($arrTime["mon"], 2, "0", STR_PAD_LEFT)
                                                                                                    : "0"
                                                                                                )
                                                                                , "day"         => (substr_count($when, "-") == 2
                                                                                                    ? str_pad($arrTime["mday"], 2, "0", STR_PAD_LEFT)
                                                                                                    : "0"
                                                                                                )
                                                                            );
                    }

                    if (is_numeric($range["year"]) && is_numeric($range["month"]) && $range["day"]) {
                        $model                                              = "0";
                    } elseif ($range["year"] && $range["month"]) {
                        $model                                              = array_fill(0, cal_days_in_month(CAL_GREGORIAN, $range["month"], $range["year"]), "0");
                        $pos                                                = 3;
                    } elseif ($range["year"]) {
                        $model                                              = array_fill(0, 12, "0");
                        $pos                                                = 2;
                    } else {
                        $this->isError("invalid range time");
                    }

                    if (!$this->isError()) {
                        $tRule                                              = $range["year"];
                        if ($range["month"]) {
                            $tRule                                          .= "-" . str_pad($range["month"], 2, "0", STR_PAD_LEFT);
                            if ($range["day"]) {
                                $tRule                                      .= "-" . str_pad($range["day"], 2, "0", STR_PAD_LEFT);
                            } else {
                                $tRule                                      .= "-*";
                            }
                        } else {
                            $tRule                                          .= "-??";
                        }

                        if (is_array($fields) && count($fields)) {
                            foreach ($fields AS $field) {
                                $rules[]                                    = $field . "-" . $tRule;

                                $res[$field]                                = $model;
                            }
                        }

                    }
                }

                if($rules) {
                    $data                                                   = $this->$operation($rules, $where);
                    if (is_array($data) && count($data)) {
                        if($this->isAssocArray($data)) {
                            $res                                            = $this->getRangeValue($data, $pos, $res);
                        } else {
                            foreach ($data AS $key => $value) {
                                $res                                        = $this->getRangeValue($value, $pos, $res);
                            }
                        }
                    }

                    $this->result                                           = (count($fields) > 1
                                                                                ? $res
                                                                                : $res[$fields[0]]
                                                                            );
                }
			} else {
				$this->isError("when empty");
			}
		}

		return $this->getResult();
	}

    /**
     * @param $set
     * @param null $where
     * @param null $old
     * @return null
     */
    public function set($set, $where = null, $old = null) {
		$this->clearResult();

		$this->result = $this->getDriver()->set_vars($set, $where, $old);

		return $this->getResult();
	}

    /**
     * @param $type
     * @param null $config
     * @return array|mixed|null
     */
    public function getConfig($type, $config = null) {
		if(!$config)
			$config = $this->services[$type]["connector"];

		if(is_array($config))
			$config = array_replace($this->connectors[$type], array_filter($config));
		else
			$config = $this->connectors[$type];

		return $config;
	}

    /**
     * @param $set
     * @param null $old
     * @return null
     */
    public function normalize_fields($set, $old = null) {
        if(is_array($set) && count($set)) {
            if($old)
                $res = $old;

            foreach ($set AS $key => $value) {
                switch ((string) $value) {
                    case "++":
                        $res[$key] = ($old[$key]
                            ? ++$old[$key]
                            : 1
                        );
                        break;
                    case "--":
                        $res[$key] = ($old[$key]
                            ? --$old[$key]
                            : 0
                        );
                        break;
                    default:
                        $res[$key] = $value;
                }
            }
        }
        return $res;
    }

    /**
     * @return mixed
     */
    private function getDriver() {
		return $this->controller();
	}
	/**
	 * @param null $service
	 */
	private function controller()
	{
		$type                                                           	= $this->service;

		if(!$this->driver[$type]) {
			$controller                                                 	= "stats" . ucfirst($type);
			//require_once($this->getAbsPathPHP("/stats/services/" . $type, true));

			$driver                                                     	= new $controller($this);
			//$db                                                         	= $driver->getDevice();

			$this->driver[$type] 											= $driver;
		}

		return $this->driver[$type];
	}

    /**
     *
     */
    private function clearResult()
	{
		$this->result = array();
		$this->isError("");
	}

    /**
     * @return null
     */
    private function getResult()
	{
		return ($this->isError()
			? $this->isError()
			: $this->result
		);
	}

    /**
     * @param $data
     * @param null $pos
     * @param array $res
     * @return array
     */
    private function getRangeValue($data, $pos = null, $res = array()) {
        foreach ($data AS $key => $value) {
            $arrKey                                                         = explode("-", $key);
            if ($pos)
                $res[$arrKey[0]][(int)$arrKey[$pos] - 1]                    = $value;
            else
                $res[$arrKey[0]]                                            += $value;
        }

        return $res;
    }
}

