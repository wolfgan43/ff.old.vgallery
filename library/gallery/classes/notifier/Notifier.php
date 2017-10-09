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
require_once(__DIR__ . "/../vgCommon.php");

class Notifier extends vgCommon
{
    static $singleton                   = null;

    protected $type                     = "";                   //es pool ecc da gestire
    protected $unique                   = true;                //notifica unica. Incrementa l'hit ad ogni insert
    protected $expire                   = 0;                    //scadenza della notifica
    protected $schedule                 = "";                   //pospone l'invio della notifica
    protected $referer                  = null;                 //Referral di cosa ha generato la notifica (se un int e una notifica altrimenti e un servizio esterno)
    protected $hit                      = "1";
    
    protected $services                 = array(                //servizi per la scrittura o lettura della notifica
                                            "server"                        => false
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
                                                    , "sql"                 => null
                                                )
                                            )
                                            , "email"                       => array(
                                                "default"                   => "localhost"
                                                , "services"                => null
                                                , "storage"                 => array(
                                                    "nosql"                 => "mongodb"
                                                    , "sql"                 => null
                                                )
                                            )
                                            , "push"                        => array(
                                                "default"                   => null
                                                , "services"                => null
                                                , "storage"                 => array(
                                                    "nosql"                 => "mongodb"
                                                    , "sql"                 => null
                                                )
                                            )
                                            , "sms"                         => array(
                                                "default"                   => null
                                                , "services"                => null
                                                , "storage"                 => array(
                                                    "nosql"                 => "mongodb"
                                                    , "sql"                 => null
                                                )
                                            )
                                        );
    protected $controllers_rev          = array();
    protected $struct                   = array(
                                            "connectors"                    => array(
                                                "sql"                       => array(
                                                    "host"                  => null
                                                    , "name"                => null
                                                    , "username"            => null
                                                    , "password"            => null
                                                    , "table"               => "trace_notify"
                                                    , "key"                 => "ID"
                                                )
                                                , "nosql"                   => array(
                                                    "host"                  => null
                                                    , "name"                => null
                                                    , "username"            => null
                                                    , "password"            => null
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
                                            , "fields"                      => array(
                                               "users"                      => "uid"  //ID_dest
                                                , "groups"                  => "gid"
                                                , "type"                    => "type"
                                                , "title"                   => "title"
                                                , "message"                 => "message"
                                                , "attach"                  => "attach" //media
                                                , "service"                 => "service" //reader
                                                , "actions"                 => "actions"
                                                , "referer"                 => "referer"
                                                , "expire"                  => "expire"
                                                , "schedule"                => "schedule" //time_from
                                                , "hit"                     => "hit"
                                            )
                                            , "email"                       => array(
                                                "struct"                    => array(
                                                    "table"                 => "email"
                                                    , "key"                 => "ID"
                                                    , "fields"              => array(
                                                        "ID"                => "ID"
                                                        , "name"            => "name"
                                                        , "subject"         => "subject"
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
    protected $title                    = null;
    protected $message                  = null;
    protected $attach                   = null;
    protected $actions                  = null;
    protected $users                    = array();
    protected $groups                   = array();
    protected $timer                    = 10000;
    
    private $notify                     = array();
    private $result                     = array();
    
    


    public static function getInstance($params)
	{
		if (self::$singleton === null)
			self::$singleton = new Notifier($params);
        else
            self::$singleton->setParams($params);

		return self::$singleton;
	}    
    
    
    public function __construct($params = null)
    {
            
        $this->setParams($params);
        $this->setReferer(true);
        
        $this->loadControllers(__DIR__);        
        $this->loadConfig();
         //da aggiungere inizializzazioni classe necessarie come anagraph
    }
    public function read($where = null, $connectors = null, $fields = null)
    {
        $this->clearResult();

        $service = "server";
        if(!$this->isError()) 
        {
            if(!$where)
            {
                /*
                $user = get_session("user_permission");
                
                $where["fields"][$this->getField("users")] = array($user["ID"], "0");
                $where["fields"][$this->getField("groups")] = array($user["primary_gid"], "0");
                $where["groups"]["target"] = array($this->getField("users"), $this->getField("groups"));
                
                if(is_array($exclude) && count($exclude)) {
                    foreach($exclude AS $notify) {
                        $arrID[] = $notify["ID"];

                    }
                    $sSQL_where .= " AND trace_notify.ID NOT IN(" . $db->toSql(implode(",", $arrID), "Text", false). ")";
                }

                if(0 && strlen($path)) {
                    $sSQL_where .= " AND (actions = " . $db->toSql($path) . " OR actions = '')";
                }
                "FIND_IN_SET('server', trace_notify.reader) " . $sSQL_where . "
                                AND (expire >= " . $db->toSql(time(), "Number") . " OR expire = 0)
                                AND time_from <= " . $db->toSql(time(), "Number") ;
                */
            } else {
                $notify = $this->getNotify(
                    $where
                    , array(
                        "service" => $service
                    )
                );
            }

            if(!$connectors)
                $connectors = $this->controllers[$service]["storage"];

            if(!is_array($connectors))
            {
                if($this->controllers[$service]["storage"][$connectors])
                    $connectors = array($connectors => $this->controllers[$service]["storage"][$connectors]);
                else
                    $connectors = $this->controllers[$service]["storage"];
            }
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

            $storage = Storage::getInstance($connectors);			

            $this->result = $storage->read($notify);
            
            return array(
                "result" => $this->result
                , "timer" => (is_array($this->result) 
                    ? $this->timer 
                    : false
                )
            );
        }
    }
    
    public function send($message = null, $users = null, $groups = null, $actions = null, $title = null, $attach = null, $referer = null)
    {
        $this->clearResult();

        switch($this->type) {
            case "pool":
                if(!$this->actions)
                    $this->isError("trace_notify_action_required");
                break;
            default:
        }

        if(!$this->isError()) 
        {
            $this->setNotify(array(
                "users"             => ($users
                                        ? $users
                                        : $this->users
                                    )
                , "groups"          => ($groups
                                        ? $groups
                                        : $this->groups
                                    )
                , "type"            => $this->type
                , "title"           => ($title
                                        ? $title
                                        : $this->title
                                    )
                , "message"         => ($message
                                        ? $message
                                        : $this->message
                                    )
                , "attach"          => ($attach
                                        ? $attach
                                        : $this->attach
                                    )
                , "service"         => null
                , "actions"         => ($actions
                                        ? $actions
                                        : $this->actions
                                    )
                , "referer"         => ($referer
                                        ? $referer
                                        : $this->referer
                                    )
                , "expire"          => $this->expire
                , "schedule"        => $this->schedule
                , "hit"             => $this->hit
            ));

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
            $this->send($params["message"], $params["users"], $params["groups"], $params["actions"], $params["title"], $params["attach"], $params["referer"]);
        }
        return $this->getResult();
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }    
    public function setMessage($message) 
    {
        $this->message = $message;
    }
    public function setReferer($referer) 
    {
        if($referer === true)
            $referer = $_SERVER["HTTP_REFERER"];

        $this->referer = $referer;
    }
    public function addAttach($attach) 
    { //da fare con oggetto
        $this->attach[] = $attach;
    }
    public function addActions($actions) 
    {
        $this->actions[] = $actions;
    }
    public function addUsers($users) 
    {
        $this->addTo($users, "users");
    }
    
    public function addGroups($groups) 
    {
        $this->addTo($groups, "groups");
    }
    
    public function setFields($fields, $type = "fields")
    {
        $this->struct[$type] = array_replace($this->struct[$type], $fields);
    }
    public function setTimer($timer) 
    {
        $this->timer = $timer;
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
    private function getField($name) 
    {
        return $this->struct["fields"][$name];
    }
    private function setNotify($data) 
    {
        $this->getNotify($data, null,true);
    }
    private function getNotify($data, $fixed = array(), $save = false)
    {
        if(is_array($data))
        {
            foreach($data AS $param => $value)
            {
                $notify[$this->struct["fields"][$param]] = $data[$param];
            }            
        }
        else 
        {
            foreach($this->struct["fields"] AS $param => $field)
            {
                if(isset($fixed[$param]))
                    $notify[$field] = $fixed[$param];
                else
                    $notify[$field] = $this->$param;
            }        
        }


        if($save)
            $this->notify = $notify;

        return $notify;
    }
    private function sliceNotify($slice, $notify = null) 
    {
        if(!$notify)
            $notify = $this->notify;
        
        foreach($slice AS $field)
        {
            $fields[$this->struct["fields"][$field]] = $field;
        }

        return array_intersect_key($notify, $fields);
    }
    private function storage($service) 
    {
        $notify = $this->notify;
        if(isset($notify[$this->struct["fields"]["service"]]))
            $notify[$this->struct["fields"]["service"]] = $service;

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

        $storage = Storage::getInstance($connectors);
        if($this->unique)
        {
            $res = $storage->update(array(
                        $this->struct["fields"]["hit"]                          => "~" . $this->struct["fields"]["hit"] . " + 1"
                    ), $this->sliceNotify(array(
                        "users"
                        , "groups"
                        , "message"
                        , "service"
                        , "actions"
                    ), $notify)

                );

/*            $res = $storage->read($this->sliceNotify(array(
                        "users"
                        , "groups"
                        , "message"
                        , "service"
                        , "actions"
                    ), $notify)
                    , $connectors["sql"]["connector"]["key"]
                );
            if($res)
            {
                $res = $storage->write(array(
                    $this->struct["fields"]["hit"]                          => "~" . $this->struct["fields"]["hit"] . " + 1"
                ), $res[0]);
            }
  */
        } else {
            $res = $storage->write($notify);
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
            Mailer::getInstance();

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
    private function getResult()
    {
        return ($this->isError()
            ? $this->isError()
            : $this->result
        );
    }
    private function loadConfig()
    {
        require_once($this->getAbsPathPHP("/storage/Storage", true));
        require_once($this->getAbsPathPHP("/mailer/Mailer", true));

        if(is_file($this->getAbsPathPHP("/conf/gallery/config/notifier"))) {
            require_once($this->getAbsPathPHP("/conf/gallery/config/notifier"));
            
            if(defined("NOTIFY_SQL_NAME") && NOTIFY_SQL_NAME)
            {
                $this->struct["connectors"]["sql"] = array(
                    "host"          => (defined("NOTIFY_SQL_HOST")
                                        ? NOTIFY_SQL_HOST
                                        : "localhost"
                                    )
                    , "name"        => (defined("NOTIFY_SQL_NAME") //database name alt
                                        ? NOTIFY_SQL_NAME
                                        : ""
                                    )
                    , "username"    => (defined("NOTIFY_SQL_USER") //database username alt
                                        ? NOTIFY_SQL_USER
                                        : ""
                                    )
                    , "password"    => (defined("NOTIFY_SQL_PASSWORD") //database password alt
                                        ? NOTIFY_SQL_PASSWORD
                                        : ""
                                    )
                    , "table"       => (defined("NOTIFY_SQL_TABLE")
                                        ? NOTIFY_SQL_TABLE
                                        : "trace_notify"
                                    )
                    , "key"         => (defined("NOTIFY_SQL_KEY")
                                        ? NOTIFY_SQL_KEY 
                                        : "ID"
                                    )
                );
            }

            if(defined("NOTIFY_NOSQL_NAME") && NOTIFY_NOSQL_NAME)
            {
                $this->struct["connectors"]["nosql"] = array(
                    "host"          => (defined("NOTIFY_NOSQL_HOST")
                                        ? NOTIFY_NOSQL_HOST
                                        : "localhost"
                                    )
                    , "name"        => (defined("NOTIFY_NOSQL_NAME") //database name alt
                                        ? NOTIFY_NOSQL_NAME
                                        : "localhost"
                                    )
                    , "username"    => (defined("NOTIFY_NOSQL_USER") //database username alt
                                        ? NOTIFY_NOSQL_USER
                                        : "localhost"
                                    )
                    , "password"    => (defined("NOTIFY_NOSQL_PASSWORD") //database password alt
                                        ? NOTIFY_NOSQL_PASSWORD
                                        : "localhost"
                                    )
                    , "table"       => (defined("NOTIFY_NOSQL_TABLE")
                                        ? NOTIFY_NOSQL_TABLE
                                        : "trace_notify"
                                    )
                    , "key"         => (defined("NOTIFY_NOSQL_KEY") 
                                        ? NOTIFY_NOSQL_KEY 
                                        : "ID"
                                    )
                );
            }
        }
    }



    private function get_users($users = null, $groups = null)
    {

    }
}