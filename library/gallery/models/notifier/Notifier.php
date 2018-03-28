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

class Notifier extends vgCommon
{
    static $singleton                   = null;

    protected $services                 = array(                //servizi per la scrittura o lettura della notifica
                                            "server"                        => null
                                        );      
    protected $controllers              = array(
                                            "server"                        => array(
                                                "default"                   => false
                                                , "services"                => false
                                                , "storage"                 => array(
                                                    "nosql"                 => null/*array(
                                                        "service"           => "mongodb"
                                                        , "connector"       => array(
                                                            "name"          => "dbname mongo" //database name alt
                                                            , "username"    => "dbusername mongo" //database username alt
                                                            , "password"    => "dbpassword mongo" //database password alt
                                                            , "table"       => "dbtable mongo"
                                                            , "key"         => "ID mongo"
                                                        )
                                                    )*/
                                                    //, "sql"                 => null
                                                )
												, "struct"					=> "db"
                                            )
                                            , "email"                       => array(
                                                "default"                   => "localhost"
                                                , "services"                => null
                                                , "storage"                 => array(
                                                    "nosql"                 => "mongodb"
                                                    , "sql"                 => null
                                                )
												, "struct"					=> "email"
                                            )
                                            , "push"                        => array(
                                                "default"                   => null
                                                , "services"                => null
                                                , "storage"                 => array(
                                                    "nosql"                 => "mongodb"
                                                    , "sql"                 => null
                                                )
												, "struct"					=> "push"
                                            )
                                            , "sms"                         => array(
                                                "default"                   => null
                                                , "services"                => null
                                                , "storage"                 => array(
                                                    "nosql"                 => "mongodb"
                                                    , "sql"                 => null
                                                )
												, "struct"					=> "push"
                                            )
                                        );
    protected $controllers_rev          = array();
    protected $struct                   = array(
                                            "connectors"                    => array(
                                                "sql"                       => array(
													"prefix"				=> "NOTIFY_DATABASE_"
                                                    , "table"               => "trace_notify"
                                                    , "key"                 => "ID"
                                                )
                                                , "nosql"                   => array(
													"prefix"				=> "NOTIFY_MONGO_DATABASE_"
                                                    , "table"               => "trace_notify"
                                                    , "key"                 => "ID"
                                                )
                                                , "fs"                      => array(
                                                    "path"                  => "/cache/notify"
                                                    , "name"                => "title"
                                                )
                                                , "push"                    => array(
                                                    "app_id"                => null
                                                    , "app_key"             => null
                                                )
                                                , "email"                   => array(
                                                    "host"                  => null
                                                    , "username"            => null
                                                    , "password"            => null
                                                    , "auth"                => false
                                                    , "port"                => null
                                                    , "secure"              => null
                                                    , "storage"             => array(
                                                        "sql"               => null
                                                    )
                                                )
                                            )
											, "table" => array(
												"db" => array(
													"visitor"				=> "visitor"
													, "url"                 => "url"
													, "get"                 => "get"
													, "domain"              => "domain"

													, "users"               => "uid"
													, "groups"              => "gid"
													, "type"                => "type" //tipologia esempio pool, alert, ecc
													, "title"               => "title"
													, "message"             => "message"
													, "media"               => "media" //contiene cover, embed o altro come fields custom
													, "reader"              => "reader" //mail, server
													, "actions"             => "actions"
													, "referer"             => "referer" //chi ha triggerato la notifica, api, url, nome custom
													, "expire"              => "expire"
													, "schedule"            => "schedule" //time_from
													, "hit"                 => "hit"

													, "created"				=> "created"
													, "last_update"			=> "last_update"
													, "delivered"			=> "delivered"
													, "display_in"			=> "display_in"
												)
											)
											, "type" => array(
												"visitor"				=> "string"
												, "url"                 => "string"
												, "get"                 => "string"
												, "domain"              => "string"
												, "users"               => "arrayOfNumber"
												, "groups"              => "arrayOfNumber"
												, "type"                => "string" //tipologia esempio pool, alert, ecc
												, "title"               => "string"
												, "message"             => "string"
												, "media"               => array(
													"default"			=> "string"
													, "cover"			=> "string:toImage"
													, "video"			=> "string:toEembed"
												) //contiene cover, embed o altro come fields custom
												, "reader"              => "string" //mail, server
												, "actions"             => "array"
												, "referer"             => "string" //chi ha triggerato la notifica, api, url, nome custom
												, "expire"              => "number"
												, "schedule"            => "number" //time_from
												, "hit"                 => "number"

												, "created"				=> "number:toTimeElapsed"
												, "last_update"			=> "number:toDateTime"
												, "delivered"			=> "boolean"
												, "display_in"			=> "string"
											)
                                            , "email"                       => array(
                                                "struct"                    => array(
                                                    "table"                 => "email"
                                                    , "key"                 => "ID"
                                                    , "fields"              => array(
                                                        "ID"                => "ID"
                                                        , "name"            => "name"
                                                        , "subject"         => "subject"
														, "fields"         	=> "media"
                                                        , "notify"          => "enable_notify"
                                                        , "from_name"       => "from_name"
                                                        , "from_email"      => "from_email"
                                                        , "tpl_email_path"  => "tpl_email_path"
                                                        , "fields_example"  => "fields_example"
                                                        , "owner_example"   => "owner_example"
                                                        , "email_debug"     => "email_debug"
                                                    )
                                                )
                                                , "address"                 => array(
                                                    "table"                 => "email_address"
                                                    , "key"                 => "ID"
                                                    , "fields"              => array(
                                                        "ID"                => "ID"
                                                        , "name"            => "name"
                                                        , "email"           => "email"
                                                        , "type"            => "type"
                                                    )
                                                )
                                            )
                                        );
	protected $visitor                  = null;
	protected $url                    	= null;
	protected $get                    	= null;
	protected $domain                   = null;

	protected $users                    = array();
	protected $groups                   = array();

	protected $type                    	= null;
	protected $title                    = null;
    protected $message                  = null;
    protected $media                   	= null;
	protected $reader                  	= null;
    protected $actions                  = null;
	protected $referer                  = null;                 //Referral di cosa ha generato la notifica (se un int e una notifica altrimenti e un servizio esterno)
	protected $expire                   = 0;                    //scadenza della notifica
	protected $schedule                 = "";                   //pospone l'invio della notifica
	protected $hit                      = "1";

	protected $unique                   = false;                //notifica unica. Incrementa l'hit ad ogni insert
    protected $timer                    = 300000;
	protected $delivered               	= false;
    protected $display_in               = "/";

    private $page						= null;
    private $notify                     = array();
    private $result                     = array();
    
    


    public static function getInstance($services = null, $params = null)
	{
		if (self::$singleton === null)
			self::$singleton = new Notifier($services, $params);
        else {
            self::$singleton->setServices($services);
			self::$singleton->setParams($params);
		}
		return self::$singleton;
	}    
    
    public function __construct($services = null, $params = null)
    {
        $this->loadControllers(__DIR__);

		$this->setServices($services);
		$this->setParams($params);


         //da aggiungere inizializzazioni classe necessarie come anagraph
    }

    public static function response($is_delivered, $keys = null) {
		if(DEBUG_PROFILING === true)
			$start 								= Stats::stopwatch();

    	$notifier = Notifier::getInstance();

		if($is_delivered && is_array($keys) && count($keys)) {
			$return = null;

			$notifier->update(array(
				"delivered" => true
			), array(
				"key" => $keys
				, "delivered" => false
			));
		} else {
			$globals = ffGlobals::getInstance("gallery");
			check_function("get_user_data");
			//todo: da implementare la classe anagraph
			$anagraph = user2anagraph();

			$limit_path[] = $globals->page["user_path"];
			if($globals->page["user_path"] != "/")
				$limit_path[] = "/";

			$return = $notifier->read(array(
				"uid" 				=> $anagraph["ID"]
				, "display_in" 		=> $limit_path
				, "!key" 			=> $keys
			), array(
				"title" 			=> true
				, "message" 		=> true
				, "cover"			=> "media.cover"
				, "video"			=> "media.video"
				, "class"			=> "media.class"
				, "type"			=> true
				, "created" 		=> true
				, "delivered" 		=> ":toString"
				)
			, array(
				 "created" 			=> "DESC"
			));

			if(DEBUG_PROFILING === true) {
				$return["exTime"] = Stats::stopwatch($start);
			}
		}
		return $return;
	}

    public function read($where = null, $fields = null, $sort = null)
    {
        $this->clearResult();

        if(!$this->isError())
        {
			$service = "server";
			$struct = $this->controllers[$service]["struct"];
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

			$storage = Storage::getInstance($connectors, array(
				"struct" => $this->getTypeOf($struct)
			));
            $this->result = $storage->read($where, $fields, $sort);
            $res = ($this->result
				? $this->result
				: array(
					"result" => array()
				)
			);
			$res["timer"] = $this->timer;
            return $res;
        }
    }
	public function update($set, $where)
	{
		$this->clearResult();

		if(!$this->isError())
		{
			$service = "server";

			$last_update = time();
			$struct = $this->controllers[$service]["struct"];

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

			$query["set"] = $this->getfields($set);
			$query["where"] = $this->getfields($where);

			if($query["set"] && $query["where"]) {
				$query["set"][$this->getField("last_update")] = $last_update;

				$storage = Storage::getInstance($connectors, array(
					"struct" => $this->getTypeOf($struct)
				));

				$this->result = $storage->update($query["set"], $query["where"]);
			} else {
				$this->isError("set or where missing");
			}
		}

		return $this->getResult();
	}
    public function send($message = null, $users = null, $groups = null, $title = null, $type = null, $media = null, $actions = null, $schedule = null, $expire = null, $referer = null)
    {
        $this->clearResult();

        $this->traceByVisitor();
		$this->setMessage($message, $title, $type, $schedule, $expire, $media, $actions, $referer);

		switch($this->type) {
            case "pool":
                if(!$this->actions)
                    $this->isError("notify_action_required");
                break;
            default:
        }

        if(!$this->isError()) 
        {
			$this->addUsers($users);
			$this->addGroups($groups);

            foreach($this->services AS $controller => $services)
            {
                $funcController = "controller_" . $controller;
                if(is_array($services) && count($services))
                {
                    foreach(array_filter($services) AS $service)
                    {
                        $this->$funcController($service);
                    }
                } else {
                    $this->$funcController($services);
                }
            }
        }

        return $this->getResult();
    }
    public function sendMail($params)
    {
        if(!isset($this->services["email"]))
            $this->services["email"] = false;

        if(is_array($params))
        {
            $this->send($params["message"], $params["users"], $params["groups"], $params["actions"], $params["title"], $params["media"], $params["referer"]);
        }
        return $this->getResult();
    }

	public function getPage()
	{
		if(!$this->page)
			$this->setPage();

		return $this->page;
	}
	public function setPage($url = null)
	{
		if($url)
			$this->page = $url;
		elseif($_SERVER["HTTP_REFERER"])
			$this->page = $_SERVER["HTTP_REFERER"];
		else
			$this->page = $_SERVER["REQUEST_URI"];

		return $this->page;
	}
	public function setVisitor($visitor = null)
	{
		if($visitor) {
			$this->visitor 						= $visitor;
		} else {
			//todo: da recuperare con la classe trace
			require_once($this->getAbsPathPHP("/library/gallery/system/trace"));
			$visitor 							= system_trace_get_visitor();
			$this->visitor 						= $visitor["unique"];
		}

		return $this->visitor;
	}
	public function setUrl($url = null)
	{
		if($url) {
			$this->url 							= $url;
		} else {
			$this->url 							= parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH);
		}

		return $this->url;
	}
	public function setGet($get = null)
	{
		if($get) {
			$this->get 							= $get;
		} else {
			$this->get 							= parse_url($_SERVER["HTTP_REFERER"], PHP_URL_QUERY);
		}

		return $this->get;
	}
	public function setDomain($domain = null)
	{
		if($domain) {
			$this->domain 						= $domain;
		} else {
			$this->domain 						= ($_SERVER["HTTP_REFERER"]
													? parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST)
													: $_SERVER["HTTP_HOST"]
												);
		}

		return $this->domain;
	}
	public function setUsers($users, $reset = true)
	{
		if($reset)
			$this->users = array();

		$this->addUsers($users);

		return $this->users;
	}
	public function setGroups($groups, $reset = true)
	{
		if($reset)
			$this->groups = array();

		$this->addGroups($groups);

		return $this->groups;
	}
	public function setType($type)
	{
		$this->type = $type;

		return $this->type;
	}
	public function setTitle($title)
	{
		$this->title = $title;

		return $this->title;
	}
	public function setMessage($message, $title, $type = null, $schedule = null, $expire = null, $media = null, $actions = null, $referer = null)
	{
		if(is_array($message))
		{
			$this->message                  	= $message["message"];
		} else {
			$this->message                      = $message;
			$message 							= array();
		}

		$this->title							= ($title
			? $title
			: $message["title"]
		);
		$this->type								= ($type
			? $type
			: $message["type"]
		);
		$this->schedule							= ($schedule
			? $schedule
			: $message["schedule"]
		);
		$this->expire							= ($expire
			? $expire
			: $message["expire"]
		);

		$this->setMedia(			 	 		$media
			? $media
			: $message["media"]
		);
		$this->setActions(						$actions
			? $actions
			: $message["actions"]
		);
		$this->setReferer(						$referer
			? $referer
			: $message["referer"]
		);

		return $this->message;
	}
	public function setMedia($media, $reset = true)
	{
		if($reset)
			$this->media = array();

		$this->addMedia($media);

		return $this->media;
	}
	public function setReader($reader)
	{
		$this->reader = $reader;
	}
	public function setActions($actions, $reset = true)
	{//todo: da fare con le calltoactions
		if($reset)
			$this->actions = array();

		$this->addActions($actions);

		return $this->actions;
	}
	public function setReferer($referer)
	{
		if($referer) {
			$this->referer                   	= $referer;
		} else {
			$this->referer 						= $this->debug_backtrace(__FILE__);
		}

		return $this->referer;
	}
	public function setExpire($expire)
	{
		$this->expire = $expire;

		return $this->expire;
	}
	public function setSchedule($schedule)
	{
		$this->schedule = $schedule;

		return $this->schedule;
	}
	public function setTimer($timer)
	{
		$this->timer = $timer;

		return $this->timer;
	}
	public function setDelivered($delivered)
	{
		$this->delivered = $delivered;

		return $this->delivered;
	}
	public function setDisplayIn($path)
	{
		$this->display_in = $path;

		return $this->display_in;
	}
	public function addMedia($media)
	{
		if(is_array($media)) {
			foreach($media AS $name => $value) {
				$this->media[$name] = $value;
			}
		} elseif($media) {
			$this->debug("media missing data:" . $media);
		}
	}
    public function addActions($actions)
    {//todo: da fare con le calltoactions
		if(is_array($actions)) {
			foreach($actions AS $action) {
				$this->actions[] = $action;
			}
		} elseif($actions) {
			$this->debug("action missing data:" . $actions);
		}
    }
    public function addUsers($users)
    { //todo: da fare con oggetto anagraph
		if($users) {
			if(!is_array($users))
				$users = array($users);

			if(is_array($users) && count($users)) {
				foreach($users AS $user) {
					if (is_numeric($user)) {
						$this->addTo($user, "users");
					} elseif (strpos($user, "@")) {
//da fare con la mail

					} else {
//da fare con username
					}
				}
			}
		} else {
			$this->debug("users_empty");
		}
    }
	public function addGroups($groups)
	{ //todo: da fare con oggetto anagraph
		if($groups) {
			if(!is_array($groups))
				$groups = array($groups);

			if(is_array($groups) && count($groups)) {
				foreach($groups AS $group) {
					if (is_numeric($group)) {
						$this->addTo($group, "groups");
					} else {
//da fare con groups
					}
				}
			}
		} else {
			$this->debug("groups_empty");
		}
	}

    public function setFields($fields, $type = "fields")
    {
        $this->struct[$type] = array_replace($this->struct[$type], $fields);
    }
    public function getConfig($type, $config = null)
    {
        if(!$config)
			$config = $this->services[$type]["connector"];

        if(is_array($config))
            $config = array_replace($this->struct["connectors"][$type], array_filter($config));
        else
            $config = $this->struct["connectors"][$type];

        return $config;
    }
    public function getData($type, $data = null)
    {
        if(is_array($data))
            $data = array_replace($this->storage($type), array_filter($data));
        else
            $data = $this->storage($type);

        return $data;
    }
    public function getService($type, $prop = null)
    {
        return ($prop
            ? $this->services[$type][$prop]
            : $this->services[$type]
        );
    }
	private function traceByVisitor($visitor = null, $url = null) {
		$this->setVisitor($visitor);
		$this->setUrl($url);
		$this->setGet($url);
		$this->setDomain($url);
	}
	private function getFields($fields, $type = "db")
	{
		if(is_array($fields) && count($fields)) {
			foreach($fields AS $name => $value) {
				$res[$this->getField($name, $type)] = $value;
			}
		}
		return $res;
	}
    private function getField($name, $type = "db")
    {
        return ($name == "key"
        	? "key"
			: $this->struct["table"][$type][$name]
		);
    }
	private function getTypeOf($type) {
		foreach($this->struct["table"][$type] AS $name => $field) {
			$res[$field] = ($this->struct["type"][$name]
				? $this->struct["type"][$name]
				: "string"
			);
		}

		return $res;
	}
    private function getNotify($fields = "db", $data = array())
    {
        if(!$this->notify[$fields])
    		$this->setNotify($data, $fields);

        return $this->notify[$fields];
    }
    private function setNotify($data = array(), $fields = "db")
    {
		foreach($this->struct["table"][$fields] AS $param => $field)
		{
			if(isset($data[$param])) {
				$notify[$field] = $data[$param];
			} else {
				$notify[$field] = $this->$param;
			}
		}
		$this->notify[$fields] = $notify;

        return $notify;
    }
    private function sliceNotify($slice, $type = "db")
    {
		$notify = $this->notify[$type];

        foreach($slice AS $field)
        {
            $fields[$this->struct["table"][$type][$field]] = $field;
        }

        return array_intersect_key($notify, $fields);
    }
    private function storage($service) 
    {
    	$created = time();
        $struct = $this->controllers[$service]["struct"];
    	$notify = $this->getNotify($struct, array($this->struct["table"][$struct]["created"] => $created));

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

        $storage = Storage::getInstance($connectors, array(
        	"struct" => $this->getTypeOf($struct)
		));
        if($this->unique)
        {
			$res = $storage->write(
				$notify
				, array(
					"set" => array(
						$this->struct["table"][$struct]["hit"]                          => $this->struct["table"][$struct]["hit"] . "++"
						, $this->struct["table"][$struct]["last_update"]                => $created
					)
					, "where" => $this->sliceNotify(array(
						"users"
						, "groups"
						, "message"
						, "title"
						, "media"
						, "actions"
					))
				)
			);

        } else {
            $res = $storage->insert($notify);
        }

        $this->result[$service] = $res;
        
        return $notify;
    }

    /**
     * @param null $service
     */
    private function controller_server($service = null)
    {
        $service = "server";
		$this->setReader($service);


        $notify = $this->storage($service);
    }

    /**
     * @param null $service
     */
    private function controller_email($service = null)
    {
        $type                                                           = "email";
        if(!$service)
            $service                                                    = $this->controllers["email"]["default"];

        if($service)
        {
			$this->setReader($service);

            $mailer = Mailer::getInstance();
/*
			$mailer->send($message = null
				, $subject = null
				, $to = null
				, $from = null
				, $cc = null
				, $bcc = null
				, $actions = null
				, $attach = null
				, $referer = null
			);*/


        }
    }

    /**
     * @param null $service
     */
    private function controller_push($service = null)
    {
        $type                                                           = "push";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
			$this->setReader($service);

            $controller                                                 = "notifier" . ucfirst($service);
            require_once($this->getAbsPathPHP("/notifier/services/" . $type . "_" . $service, true));

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
            $config                                                     = $driver->getConfig();
        }
    }

    /**
     * @param null $service
     */
    private function controller_sms($service = null)
    {
        $type                                                           = "sms";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
			$this->setReader($service);

            $controller                                                 = "notifier" . ucfirst($service);
            require_once($this->getAbsPathPHP("/notifier/services/" . $type . "_" . $service, true));

            $driver                                                     = new $controller($this);
            $db                                                         = $driver->getDevice();
            $config                                                     = $driver->getConfig();
        }
    }
    
    private function addTo($IDs, $type = "users") 
    {
        if(is_array($IDs))
            $this->$type = array_merge($this->$type, $IDs);
        elseif($IDs)
            array_push($this->$type, $IDs);        
    }
    private function clearResult() 
    {
        $this->notify = array();
        $this->result = array();
        $this->isError("");
    }
	private function getResultKeys() {
		$keys = array();
		if(is_array($this->result) && count($this->result)) {
			foreach ($this->result AS $service => $storage) {
				if(is_array($storage) && count($storage)) {
					foreach($storage AS $connector => $result) {
						if(is_array($result["keys"]) && count($result["keys"]))
							$keys = array_replace($keys, $result["keys"]);
					}
				}
			}
		}
		return $keys;
	}
    private function getResult($onlyKey = false)
    {
        return ($this->isError()
            ? $this->isError()
            : ($onlyKey
				? $this->getResultKeys()
				: $this->result
			)
        );
    }

    private function get_users($users = null, $groups = null)
    {

    }
}