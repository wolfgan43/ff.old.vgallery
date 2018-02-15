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

class Mailer extends vgCommon
{
    static $singleton                   = null;

    const THEME_DEFAULT                 = THEME_INSET;

    protected $name                     = null;
    protected $domain                   = DOMAIN_NAME;
    protected $lang                     = null;
    protected $notify                   = null;
    protected $debug_data               = array(
											"email"         => null
											, "fields"      => null
											, "owner"       => null
											, "enable"      => false
										);
    protected $send_copy                = false; //invert mail
    protected $storage                  = array(
											"ID"           	=> 0
											, "obj"         => null
										);

    protected $theme                    = FRONTEND_THEME;
    protected $template                 = null;

    //header
    protected $smtp                     = null;
    protected $subject                  = null;
    protected $from                     = null;
    protected $to                       = null;
    protected $cc                       = null;
    protected $bcc                      = null;

    //body
    protected $prefix                   = array(
        "subject"                       => null
    , "group"                       => null
    , "label"                       => null
    , "field"                       => null
    );
    protected $pre                      = null;
    protected $post                     = null;
    protected $content                  = null;
    protected $fields                   = array();

    protected $attach                   = null;
    protected $actions                  = null;

    //dest
    protected $users                    = array();
    protected $groups                   = array();
    protected $referer                  = null;
    protected $owner                    = null;

    protected $services                 = array(                //servizi per la scrittura o lettura della notifica
        "phpmailer"                         => null
    );
    protected $controllers              = array(
        "email"                     => array(
            "default"                   => "localhost"
			, "services"                => null
			, "storage"                 => array(
				"sql" 					=> null
			)
        )
    );
    protected $controllers_rev          = array();
    protected $struct                   = array(
        "connectors"                    => array(
            "email"                     => array(
                "host"                  => null
				, "username"            => null
				, "password"            => null
				, "auth"                => false
				, "port"                => null
				, "secure"              => null

            )
        , "sql"                       => array(
                "host"                  => null
				, "name"                => null
				, "username"            => null
				, "password"            => null
				, "table"               => "email"
				, "key"                 => "name"
            )
        , "nosql"                   => array(
                "host"                  => null
				, "name"                => null
				, "username"            => null
				, "password"            => null
				, "table"               => "email"
				, "key"                 => "name"
            )
        )
    , "storage"                     => array(
            "struct"                    => array(
                "table"                 => "email"
				, "key"                 => "name"
				, "fields"              => array(
                    "ID"                => "ID"
					, "name"            => "name"
					, "subject"         => "subject"
					, "notify"          => "enable_notify"
					, "from_name"       => "from_name"
					, "from_email"      => "from_email"
					, "template"        => "tpl_email_path"
					, "fields_debug"     => "fields_example"
					, "owner_debug"     => "owner_example"
					, "email_debug"     => "email_debug"
                )
            )
        , "address"                 => array(
                "table"                 => "email_address"
				, "key"                 => "ID_email"
				, "fields"              => array(
                    "ID"                => "ID"
					, "ID_email"        => "ID_email"
					, "name"            => "name"
					, "email"           => "email"
					, "type"            => "type"
                )
            )
        )
    );


    private $tpl_html_path              = null;
    private $tpl_html                   = null;

    private $tpl_text_path              = null;
    private $tpl_text                   = null;
    private $result                     = array();
    private $exTime						= 0;

    public static function getInstance($params, $controller = null)
    {
        if (self::$singleton === null) {
            self::$singleton = new Mailer($params);
        } else {
            if($params && !is_array($params))
                $params = array("name" => $params);

            self::$singleton->setParams($params);
        }
		if($controller)
			self::$singleton->setController("email", $controller);

        return self::$singleton;
    }


    public function __construct($params = null)
    {
        if($params && !is_array($params))
            $params = array("name" => $params);

        $this->setParams($params);
        $this->loadControllers(__DIR__);
    }

    /**
     * @param $message
     * @param null $to
     * @param null $from
     * @param null $cc
     * @param null $bcc
     * @param null $subject
     * @param null $actions
     * @param null $attach
     * @param null $settings
     * @return array|null
     */
    public function send($message = null, $subject = null, $to = null, $from = null, $cc = null, $bcc = null, $actions = null, $attach = null, $referer = null)
    {
		$start = Stats::stopwatch();

        $this->clearResult($from);
//todo: $notify da fare e $send_copy e $actions e $users e $groups e $referer
		$this->setMessage($message, $subject, $actions, $attach, $referer);

		$this->loadConfig();
		$this->loadTemplate();

        if(!$this->isError())
        {
            $this->addAddress($from             , "from");
            $this->addAddress($to               , "to");
            $this->addAddress($cc               , "cc");
            $this->addAddress($bcc              , "bcc");

            if(!$this->isError()) {
                foreach ($this->services AS $controller => $services) {
                    $funcController = "controller_" . $controller;
                    if (is_array($services) && count($services)) {
                        foreach (array_filter($services) AS $service) {
                            $this->$funcController($service);
                        }
                    } else {
                        $this->$funcController($services);
                    }
                }
            }
        }

        $this->exTime = Stats::stopwatch($start);

        cache_writeLog($this->debug_backtrace(__FILE__) . "\n"
			. "URL: " . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . " REFERER: " . $_SERVER["HTTP_REFERER"] . "\n"
			. "name (struct from db): " . $this->name . "\n"
			. "tpl: " . $this->template . " (" . $this->tpl_html_path . ")" . "\n"
			. " subject: " . $this->subject . "\n"
			. " from: " . print_r($this->from, true)
			. " to: " . print_r($this->to, true)
			. " cc: " . print_r($this->cc, true)
			. " bcc: " . print_r($this->bcc, true)
			. " Result: " . print_r($this->getResult(), true)
		, "email" . ($this->isError() ? "_error" : ""));

        return $this->getResult();
    }

    public function setTitle($title)
    {
        $this->title                            = $title;
    }
    public function setMessage($message, $subject, $actions = null, $attach = null, $referer = null)
    {
        if(is_array($message))
        {
			$this->name                    		= $message["name"];
			if($message["theme"])
				$this->theme                    	= $message["theme"];

			$this->template                 	= $message["template"];
			$this->pre                      	= $message["pre"];
			$this->post                     	= $message["post"];
			$this->content                  	= $message["content"];
			$this->fields                   	= $message["fields"];
        } else {
			if(strpos($message, " ") === false)
				$this->name                     = $message;
			else
            	$this->content                  = $message;

            $message 							= array();
        }

		$this->subject							= ($subject
													? $subject
													: $message["subject"]
												);

		$this->setAttach(			 	 $attach
													? $attach
													: $message["attach"]
												);
		$this->setActions(						$actions
													? $actions
													: $message["actions"]
												);
		$this->setReferer(						$referer
			? $referer
			: $message["referer"]
		);
    }

    public function setReferer($referer)
    {
    	if($referer) {
			$this->referer                   	= $referer;
		} else {
			$stack 								= debug_backtrace();
			$firstFrame 						= $stack[count($stack) - 1];

			$this->referer 						= str_replace($this->getDiskPath(), "", $firstFrame['file']);
		}
    }
	public function setAttach($attach, $reset = true)
	{
		if($reset)
			$this->attach = array();

		if(is_array($attach)) {
			foreach($attach AS $name => $value) {
				$this->addAttach($value, $name);
			}
		} elseif(strlen($attach)) {
			$this->addAttach($attach);
		}
	}
	public function addAttach($attach, $name = null)
	{
		if(!$name)
			$name 								= $attach;

		$this->attach[$name]                    = $attach;
	}
	public function setActions($actions, $reset = true)
	{//todo: da fare con le calltoactions
		if($reset)
			$this->actions = array();

		if(is_array($actions)) {
			foreach($actions AS $action) {
				$this->addAction($action);
			}
		} elseif($actions) {
			$this->debug("action missing data:" . $actions);
		}
	}
	public function addAction($action)
	{//todo: da fare con le calltoactions
		$this->actions[] = $action;
	}
	public function addAddress($address, $type = "to", $name = null)
    { //da fare con oggetto
        if(is_array($address))
        {
            if(array_key_exists("email", $address))
            {
				$this->setAddress(array(
                    "email" => $address["email"]
                	, "name" => ($name ? $name : ($address["name"] ? $address["name"] : $address["email"]))
                ), $type);
            } elseif(array_key_exists("0", $address)) {
                foreach($address AS $addr)
                {
                    if(array_key_exists("email", $addr))
                    {
                        $this->setAddress(array(
                            "email" => $addr["email"]
                        	, "name" => ($addr["name"] ? $addr["name"] : $addr["email"])
                        ), $type);
                    } elseif(strlen($addr)) {
						$this->setAddress(array(
							"email" => $addr
							, "name" => $addr
						), $type);
					} else {
                        $this->debug("wrong_email", $addr);
                    }
                }
            }
        } elseif($address) {
			$this->setAddress(array(
                "email" => $address
            	, "name" => ($name ? $name : $address)
            ), $type);
        } else {
			$this->debug("email_address_empty");
		}
    }
    public function setFields($fields, $type = "struct")
    {
        $this->struct["storage"][$type]["fields"] = array_replace($this->struct["storage"][$type]["fields"], $fields);
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

    public function getService($type, $prop = null)
    {
        return ($prop
            ? $this->services[$type][$prop]
            : $this->services[$type]
        );
    }

    public function getHeaders()
    { //TODO: da sistemare tutto


        /**
         * Process Preview
         */
        $headers_mail = NULL;
        $headers_tag = NULL;

        $headers_mail["settings"]["name"]                                                   = $this->name;
        $headers_mail["settings"]["theme"]                                                  = $this->theme;
        $headers_mail["settings"]["template"]                                               = $this->template;
        $headers_mail["settings"]["notify"]                                                 = ($this->notify
            ? ffTemplate::_get_word_by_code("yes")
            : ffTemplate::_get_word_by_code("no")
        );
        $headers_mail["settings"]["debug"]                                                  = $this->debug_data;
        $headers_mail["settings"]["lang"]                                                   = $this->lang;
        $headers_mail["settings"]["domain"]                                                 = $this->domain;
        $headers_mail["settings"]["prefix"]                                                 = $this->prefix;



        $headers_mail["attach"]                                                             = $this->attach;
        $headers_mail["actions"]                                                            = $this->actions;



        $headers_mail["headers"]["from"]                                                    = $this->from;
        $headers_mail["headers"]["to"]                                                      = $this->to;
        $headers_mail["headers"]["cc"]                                                      = $this->cc;
        $headers_mail["headers"]["bcc"]                                                     = $this->bcc;
        $headers_mail["headers"]["subject"]                                                 = $this->subject;

        $headers_mail["body"]["owner"]                                                      = $this->owner;
        $headers_mail["body"]["fields"]                                                     = $this->fields;
        $headers_mail["body"]["pre"]                                                        = $this->pre;
        $headers_mail["body"]["post"]                                                       = $this->post;
        $headers_mail["body"]["content"]                                                    = $this->content;




/*
        if (is_array($arrAddress) && count($arrAddress)) {
            foreach ($arrAddress AS $arrAddress_type => $arrAddress_value) {
                if (is_array($arrAddress_value) && count($arrAddress_value)) {
                    $count_address = 0;
                    foreach ($arrAddress_value AS $address_email => $address_name) {
                        $headers_tag['tags_' . $arrAddress_type]['{' . $arrAddress_type . "_email_" . $count_address . '}'] = $address_email . " ( example )";
                        $headers_tag['tags_' . $arrAddress_type]['{' . $arrAddress_type . "_name_" . $count_address . '}'] = $address_name . " ( example )";
                        $count_address++;
                    }
                }
            }
        }

        if (is_array($struct["body"]["fields"]) && count($struct["body"]["fields"]))
        {
            foreach ($struct["body"]["fields"] AS $fields_key => $fields_value)
            {
                $field_type = $fields_value["settings"]["type"];
                if (is_array($fields_value) && count($fields_value))
                {
                    $count_row = 0;
                    foreach ($fields_value AS $fields_value_key => $fields_value_value)
                    {
                        if (strtolower($fields_value_key) == "settings")
                            continue;

                        if (is_array($fields_value_value) && count($fields_value_value))
                        {
                            $field_sect = ($fields_value["settings"]["type"]
                                ?  $fields_value["settings"]["type"]
                                : ucfirst($fields_key) . ucfirst($fields_value_key)
                            );
                            foreach ($fields_value_value AS $fields_value_value_key => $fields_value_value_value)
                            {
                                if (strtolower($fields_value_value_key) == "settings")
                                    continue;

                                $headers_tag['tags_row [' . ffCommon_specialchars('<!--BeginSezRow' . $field_sect . '--><!--EndSezRow' . $field_type . '-->') . ']']['{' . process_mail_field($fields_value_value_key, $struct["body"]["prefix"]["label"]) . '}'] = $fields_value_value_value . " ( example )";
                            }
                        } else {
                            $headers_tag['tags']['{' . process_mail_field($fields_value_key, $struct["body"]["prefix"]["label"]) . '}'] = $fields_value_value . " ( example )";
                        }
                    }
                }
            }
        }

        if ($headers_mail !== NULL && is_array($headers_mail) && is_array($struct["template"]["default"])) {
            $tpl_header = ffTemplate::factory(FF_DISK_PATH . FF_THEME_DIR . "/" . $struct["template"]["default"]["theme"] . $struct["template"]["default"]["path"]);
            $tpl_header->load_file("email_header.html", "main");

            foreach ($headers_mail AS $headers_key => $headers_value) {
                $tpl_header->set_var("SezHeader", "");
                if (is_array($headers_value)) {
                    foreach ($headers_value AS $headers_value_key => $headers_value_value) {
                        if (is_array($headers_value_value)) {
                            $headers_value_value = $headers_value_value["name"] . "[" . $headers_value_value["mail"] . "]";
                            $tpl_header->set_var("headers_label", "");
                        } else {
                            if (!is_int($headers_value_key)) {
                                if (substr($headers_key, 0, 1) == "_")
                                    $tpl_header->set_var("headers_label", ffTemplate::_get_word_by_code("email_" . ltrim($headers_key, "_") . "_" . $headers_value_key));
                                else
                                    $tpl_header->set_var("headers_label", $headers_value_key);
                            } else {
                                $tpl_header->set_var("headers_label", "");
                            }
                        }
                        $tpl_header->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', "email_" . $headers_key . "_" . $headers_value_key)));
                        $tpl_header->set_var("headers_class", "data");
                        $tpl_header->set_var("headers_value", $headers_value_value);
                        $tpl_header->parse("SezHeader", true);
                    }
                } elseif (strlen($headers_value)) {
                    $tpl_header->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', "email_" . $headers_key . "_" . $headers_value)));
                    $tpl_header->set_var("headers_label", "");
                    $tpl_header->set_var("headers_class", "data");
                    $tpl_header->set_var("headers_value", $headers_value);
                    $tpl_header->parse("SezHeader", true);
                }
                $tpl_header->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $struct["prefix"] . "_" . $headers_key)));
                $tpl_header->set_var("group_name", ffTemplate::_get_word_by_code("email_" . ltrim($headers_key, "_")));
                $tpl_header->set_var("group_class", preg_replace('/[^a-zA-Z0-9\-]/', '', "email-" . ltrim($headers_key, "-")));
                $tpl_header->parse("SezHeadersGroups", true);
            }
            $tpl_header->parse("SezHeaders", false);

            $tpl_header->set_var("email_template_title", ffTemplate::_get_word_by_code("email_template_explanation_header"));
            $preview_header_mail = $tpl_header->rpparse("main", false);
        }

        if ($headers_tag !== NULL && is_array($headers_tag) && is_array($struct["template"]["default"])) {
            $tpl_header = ffTemplate::factory(FF_DISK_PATH . FF_THEME_DIR . "/" . $struct["template"]["default"]["theme"] . $struct["template"]["default"]["path"]);
            $tpl_header->load_file("email_header.html", "main");

            foreach ($headers_tag AS $headers_key => $headers_value) {
                $tpl_header->set_var("SezHeader", "");
                if (is_array($headers_value)) {
                    foreach ($headers_value AS $headers_value_key => $headers_value_value) {
                        if (is_array($headers_value_value)) {
                            $headers_value_value = $headers_value_value["name"] . "[" . $headers_value_value["mail"] . "]";
                            $tpl_header->set_var("headers_label", "");
                        } else {
                            if (!is_int($headers_value_key)) {
                                if (substr($headers_key, 0, 1) == "_")
                                    $tpl_header->set_var("headers_label", ffTemplate::_get_word_by_code("email_" . ltrim($headers_key, "_") . "_" . $headers_value_key));
                                else
                                    $tpl_header->set_var("headers_label", $headers_value_key);
                            } else {
                                $tpl_header->set_var("headers_label", "");
                            }
                        }
                        $tpl_header->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', "email_" . $headers_key . "_" . $headers_value_key)));
                        $tpl_header->set_var("headers_class", "example");
                        $tpl_header->set_var("headers_value", $headers_value_value);
                        $tpl_header->parse("SezHeader", true);
                    }
                } elseif (strlen($headers_value)) {
                    $tpl_header->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', "email_" . $headers_key . "_" . $headers_value)));
                    $tpl_header->set_var("headers_label", "");
                    $tpl_header->set_var("headers_class", "example");
                    $tpl_header->set_var("headers_value", $headers_value);
                    $tpl_header->parse("SezHeader", true);
                }
                $tpl_header->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $struct["prefix"] . "_" . $headers_key)));
                $tpl_header->set_var("group_name", ffTemplate::_get_word_by_code("email_" . ltrim($headers_key, "_")));
                $tpl_header->set_var("group_class", preg_replace('/[^a-zA-Z0-9\-]/', '', "email-" . ltrim($headers_key, "-")));
                $tpl_header->parse("SezHeadersGroups", true);
            }
            $tpl_header->parse("SezHeaders", false);

            $tpl_header->set_var("email_template_title", ffTemplate::_get_word_by_code("email_template_explanation_tag"));
            $preview_header_tag = $tpl_header->rpparse("main", false);
        }*/
    }
    private function setAddress($addr, $type)
    {
        if($addr) {
            switch ($type) {
                case "to":
					$this->to[$addr["email"]] 			= $addr;
                    break;
                case "from":
                    if(is_array($this->from) && count($this->from) > 0)
                        $this->isError("mailer_from_must_be_one");
                    else
                        $this->from[$addr["email"]] 	= $addr;
                    break;
                case "cc":
                    $this->cc[$addr["email"]] 			= $addr;
                    break;
                case "bcc":
                    $this->bcc[$addr["email"]] 			= $addr;
                    break;
            }
        }
    }
    private function getField($name, $type = "struct")
    {
        return $this->struct["storage"][$type]["fields"][$name];
    }
    private function getResult()
    {
        return ($this->isError()
            ? array(
				"error" => $this->isError()
				, "exTime" => $this->exTime
			)
            : array(
				"result" => $this->result
				, "exTime" => $this->exTime
			)
        );
    }
    private function loadConfig($service = "email")
    {
        if($this->name) {
			$connectors = $this->controllers[$service]["storage"];
			foreach ($connectors AS $type => $data) {
				if (!$data) {
					$connectors[$type] = array(
						"service" => null
					, "connector" => $this->struct["connectors"][$type]
					);

				}
			}

			$this->storage["obj"] = Storage::getInstance($connectors);
			$struct = $this->storage["obj"]->lookup($this->struct["storage"]["struct"]["table"]
				, array(
					$this->getField($this->struct["storage"]["struct"]["key"], "struct") => $this->name
				));

			if (!$struct)
				$struct = $this->makeAccount(); //TODO: da implementare
		}

        if($struct)
        {
        	if(!$this->subject)
            	$this->subject              = $struct[$this->struct["storage"]["struct"]["fields"]["subject"]];

			if(!$this->template)
				$this->template             = $struct[$this->struct["storage"]["struct"]["fields"]["template"]];

			if(!$this->notify)
				$this->notify               = $struct[$this->struct["storage"]["struct"]["fields"]["notify"]];

			if(!$this->from)
				$this->from[$struct[$this->struct["storage"]["struct"]["fields"]["from_email"]]] = array(
                "name" => $struct[$this->struct["storage"]["struct"]["fields"]["from_name"]]
				, "email" => $struct[$this->struct["storage"]["struct"]["fields"]["from_email"]]
            );

            $this->debug_data["fields"]      = $struct[$this->struct["storage"]["struct"]["fields"]["fields_debug"]];
            $this->debug_data["owner"]       = $struct[$this->struct["storage"]["struct"]["fields"]["owner_debug"]];
            $this->debug_data["email"]       = $struct[$this->struct["storage"]["struct"]["fields"]["email_debug"]];

            $this->storage["ID"]        = $struct["ID"];

            //TODO: da sviluppare la join method
            /*$addresses = $this->storage["obj"]->read(array(
                $this->getField($this->struct["storage"]["address"]["key"], "address") => $data["ID"]
            ), $this->struct["storage"]["address"]["table"]);

            if(is_array($addresses) && count($addresses))
            {
                foreach($addresses AS $address)
                {
                    $this->addAddress(
                        $address[$this->struct["storage"]["address"]["email"]]
                        , $address[$this->struct["storage"]["address"]["name"]]
                        , $address[$this->struct["storage"]["address"]["type"]]
                    );
                }
            }*/
        }

        // $this->result[$service] = $res;

    }

    private function loadTemplate()
    {
        /*
         * Resolve template Path
         */

		if ($this->template) {
			if (is_file($this->getAbsPath($this->template))) {
				$this->tpl_html_path = $this->getAbsPath($this->template);
			} elseif (strpos($this->template, "/") === false && $this->theme && is_file($this->getAbsPath(FF_THEME_DIR . "/" . $this->theme . "/contents/email/" . $this->template . "/email.tpl"))) {
				$this->tpl_html_path = $this->getAbsPath(FF_THEME_DIR . "/" . $this->theme . "/contents/email/" . $this->template . "/email.tpl");
			} elseif ($this->theme && is_file($this->getAbsPath(FF_THEME_DIR . "/" . $this->theme . "/" . $this->template))) {
				$this->tpl_html_path = $this->getAbsPath(FF_THEME_DIR . "/" . $this->theme . "/" . $this->template);
			}
		}

		if($this->content) {
			if($this->tpl_html_path) {
				$this->fields["pre"] .= $this->content;
				$this->content = null;
			} else {
				$this->tpl_html = ffTemplate::factory();
				$this->tpl_html->load_content($this->content, "main");
				$this->tpl_text = ffTemplate::factory();
				$this->tpl_text->load_content($this->content, "main");
			}
		}

		if(!$this->tpl_html_path) {
			if ($this->theme && is_file($this->getAbsPath(FF_THEME_DIR . "/" . $this->theme . "/contents/email/email.tpl"))) {
				$this->tpl_html_path = $this->getAbsPath(FF_THEME_DIR . "/" . $this->theme . "/contents/email/email.tpl");
			} else {
				$this->tpl_html_path = $this->getAbsPath(FF_THEME_DIR . "/" . $this::THEME_DEFAULT . "/contents/email/email.tpl");
			}
		}

		if($this->tpl_html_path) {
			$this->tpl_html = ffTemplate::factory(ffCommon_dirname($this->tpl_html_path));
			$this->tpl_html->load_file(basename($this->tpl_html_path), "main");
			if(is_file(ffCommon_dirname($this->tpl_html_path) . "/email.txt"))
				$this->tpl_text_path = ffCommon_dirname($this->tpl_html_path) . "/email.txt";
		}

		if($this->tpl_html) {
            $this->tpl_html->set_var("site_path", FF_SITE_PATH);
            $this->tpl_html->set_var("site_updir", SITE_UPDIR);
            $this->tpl_html->set_var("domain_inset", $this->domain);
            $this->tpl_html->set_var("theme_inset", $this::THEME_DEFAULT);
            $this->tpl_html->set_var("theme", $this->theme);
            $this->tpl_html->set_var("email_name", $this->name);
            $this->tpl_html->set_var("language_inset", FF_LOCALE);

            if($this->tpl_text_path) {
                $this->tpl_text = ffTemplate::factory(ffCommon_dirname($this->tpl_text_path));
                $this->tpl_text->load_file(basename($this->tpl_text_path), "main");
                $this->tpl_text->set_var("site_path", FF_SITE_PATH);
                $this->tpl_text->set_var("site_updir", SITE_UPDIR);
                $this->tpl_text->set_var("domain_inset", $this->domain);
                $this->tpl_text->set_var("theme_inset", $this::THEME_DEFAULT);
                $this->tpl_text->set_var("theme", $this->theme);
                $this->tpl_text->set_var("email_name", $this->name);
                $this->tpl_text->set_var("language_inset", FF_LOCALE);
            }
        } else {
            $this->isError("mailer_template_notfound");
        }
    }

    /**
     * @param null $service
     */
    private function controller_phpmailer($service = null)
    {
        $type                                                           = "email";
        if(!$service)
            $service                                                    = $this->controllers[$type]["default"];

        if($service)
        {
            $controller                                                 = "mailer" . ucfirst($service);
            require_once($this->getAbsPathPHP("/mailer/services/" . $type . "_" . $service, true));

            $driver                                                     = new $controller($this);

            $this->smtp                                                 = $driver->getConfig();
            $lang                                                       = ($this->lang
                ? $this->lang
                : FF_LOCALE
            );

            $mail                                                       = new phpmailer();
            $mail->SetLanguage(strtolower(substr($lang, 0, -1)), $this->getAbsPath("/library/phpmailer/language/"));
            $mail->Subject                                              = $this->process_mail_subject();
            $mail->CharSet                                              = strtolower(FF_DEFAULT_CHARSET);
            $mail->Encoding                                             = "quoted-printable";

            if($this->smtp["auth"]) {
                $mail->IsSMTP();
            } else {
                $mail->IsMail();
            }

            $mail->Host                                                 = $this->smtp["host"];
            $mail->SMTPAuth                                             = $this->smtp["auth"];
            $mail->Username                                             = $this->smtp["username"];
            $mail->Port                                                 = $this->smtp["port"];
            $mail->Password                                             = $this->smtp["password"];
            $mail->SMTPSecure                                           = $this->smtp["secure"];
            $mail->SMTPAutoTLS                                          = false;

            $froms 														= array_values($this->from);
			$from 														= $froms[0];

            $mail->FromName                                             = $from["name"];

            if (strpos($this->smtp["username"], "@") === false)
                $mail->From = $from["email"];
            else
                $mail->From = $this->smtp["username"];

            if ($this->smtp["username"] != $from["email"])
                $mail->AddReplyTo($from["email"], $from["name"]);

            if(is_array($this->to) && count($this->to))
            {
                foreach($this->to AS $to)
                {
                    $mail->addAddress($to["email"], $to["name"]);
                }
            }

            if(is_array($this->cc) && count($this->cc))
            {
                foreach($this->cc AS $cc)
                {
                    $mail->addCC($cc["email"], $cc["name"]);
                }
            }

            if(is_array($this->bcc) && count($this->bcc))
            {
                foreach($this->bcc AS $bcc)
                {
                    $mail->addBCC($bcc["email"], $bcc["name"]);
                }
            }

            /**
             * Process Owner
             */
            $this->tpl_html->set_var("SezOwner", "");
            if (is_array($this->owner)) {
                foreach ($this->owner AS $owner_label => $owner_value) {
                    if (!strlen($owner_value))
                        continue;

                    $this->tpl_html->set_var("owner_label", $owner_label);
                    $this->tpl_html->set_var("owner", $owner_value);

                    $this->tpl_html->set_var("owner_" . $owner_label, $owner_value);
                    $this->tpl_html->set_var("owner_" . $owner_label . "_label", $owner_label);
                    $this->tpl_html->parse("SezOwnerLabel", false);
                    $this->tpl_html->parse("SezOwner", true);
                }
            } elseif (strlen($this->owner)) {
                $this->tpl_html->set_var("owner", $this->owner);
                $this->tpl_html->parse("SezOwner", false);
            }

            /**
             * Process Fields
             */
            if (is_array($this->fields))
            {
                $count_group = 0;
                $group_type = array("Table" => true);
                foreach ($this->fields AS $fields_key => $fields_value)
                {
                    $field_type = $fields_value["settings"]["type"];
                    if (is_array($fields_value) && count($fields_value))
                    {
                        $count_row = 0;
                        foreach ($fields_value AS $fields_value_key => $fields_value_value)
                        {
                            if (strtolower($fields_value_key) == "settings")
                                continue;

                            switch ($field_type)
                            {
                                case "Table":
                                    if (is_array($fields_value_value) && count($fields_value_value))
                                    {
                                        foreach ($fields_value_value AS $fields_value_value_key => $fields_value_value_value)
                                        {
                                            if (strtolower($fields_value_value_key) == "settings")
                                                continue;

                                            $this->parse_mail_field($fields_value_value_value, $fields_value_value_key, $field_type, $count_row);
                                        }

                                        $this->parse_mail_row($field_type, true);
                                    } else {
                                        $this->parse_mail_field($fields_value_value, $fields_key . "_" . $fields_value_key, $field_type);
                                        $this->parse_mail_row($field_type);
                                    }
                                    break;
                                default:
                                    if (is_array($fields_value_value) && count($fields_value_value))
                                    {
                                        foreach ($fields_value_value AS $fields_value_value_key => $fields_value_value_value) {
                                            if (strtolower($fields_value_value_key) == "settings")
                                                continue;

                                            $this->parse_mail_field($fields_value_value_value, $fields_value_value_key, $field_type, $count_row);

                                        }

                                        $this->parse_mail_row($field_type, true);
                                    } else {
                                        $this->parse_mail_field($fields_value_value, $fields_key . "_" . $fields_value_key, $field_type);
                                        $this->parse_mail_row($field_type);
                                    }
                            }
                            $count_row++;
                        }
                    } else {
                        $this->tpl_html->set_var($fields_key, $fields_value); //custom vars
                        if($this->tpl_text)
                            $this->tpl_text->set_var($fields_key, $fields_value); //custom vars
                    }

                    $this->parse_mail_group($fields_key, $group_type, $field_type);

                    $count_group++;
                }

                $this->tpl_html->parse("SezFields", false);
                if($this->tpl_text)
                    $this->tpl_text->parse("SezFields", false);
            }

            $this->tpl_html->set_var("pre_body", $this->pre);
            $this->tpl_html->set_var("post_body", $this->post);
            $this->tpl_html->set_var("real_name", $this->process_mail_field($this->name, null, "smart_url"));

            if($this->tpl_text)
            {
                $this->tpl_text->set_var("pre_body", $this->pre);
                $this->tpl_text->set_var("post_body", $this->post);
                $this->tpl_text->set_var("real_name", $this->process_mail_field($this->name, null, "smart_url"));
            }

            if($this->tpl_html) {
                $mail->IsHTML(true);
                $mail->Body = $this->tpl_html->rpparse("main", false);
                if($this->tpl_text)
                    $mail->AltBody = $this->tpl_text->rpparse("main", false);
            } else {
                $mail->IsHTML(false);
                $mail->Body = $this->tpl_text->rpparse("main", false);
            }

            /*
             * Images
             */
            if (is_dir(ffCommon_dirname($this->tpl_html_path) . "/images")) {
                $arrEmailImages = glob(ffCommon_dirname($this->tpl_html_path) . "/images/*");
                if (is_array($arrEmailImages) && count($arrEmailImages)) {
                    foreach ($arrEmailImages AS $email_image) {
                        $mail->AddEmbeddedImage($email_image, basename($email_image), basename($email_image), 'base64', ffMimeContentType($email_image));
                    }
                }
            }

            /*
             * Attachment
             */
            if (is_array($this->attach) && count($this->attach)) {
                foreach ($this->attach AS $attach_key => $attach_value) {
                    if (is_file(DISK_UPDIR . $attach_value))
                        $mail->AddAttachment(DISK_UPDIR . $attach_value, $attach_key);
                }
            }

            if (is_dir(ffCommon_dirname($this->tpl_html_path) . "/attach")) {
                $arrEmailAttach = glob(ffCommon_dirname($this->tpl_html_path) . "/attach/*");
                if (is_array($arrEmailAttach) && count($arrEmailAttach)) {
                    foreach ($arrEmailAttach AS $email_attach) {
                        $mail->AddAttachment($email_attach, basename($email_attach));
                    }
                }
            }


            $rc = $mail->Send();
            if (!$rc)
                $this->isError($mail->ErrorInfo);
        }

        $this->result = $rc;
    }

    private function clearResult($from = null)
    {
        if($from)
            $this->from = null;

        $this->to       = null;
        $this->cc       = null;
        $this->bcc      = null;
        $this->result   = array();

        $this->isError("");
    }

    private function makeAccount()
    {
    	return;
		$email_name = $email;
		$enable_notify = false;

		if(!(strlen($tpl_email_path) && is_dir(FF_DISK_PATH . $tpl_email_path))) {
			$tpl_email_path = clone_template_mail($email_name);
		}

		$default_from["name"] = A_FROM_NAME;
		$default_from["mail"] = A_FROM_EMAIL;

		$sSql = "INSERT 
	                    INTO `email` 
	                    (
	                        ID
	                        , name
	                        , tpl_email_path
	                        , from_name
	                        , from_email
	                        , enable_notify
	                        , owner
	                    ) 
	                    VALUES 
	                    (
	                        ''
	                        , " . $dbtemp->toSql($email_name, "Text") . "
	                        , " . $dbtemp->toSql($tpl_email_path, "Text") . "
	                        , " . $dbtemp->toSql($default_from["name"], "Text") . "
	                        , " . $dbtemp->toSql($default_from["mail"], "Text") . "
	                        , " . $dbtemp->toSql($enable_notify, "Number") . "
	                        , " . $dbtemp->toSql(0, "Number") . "
	                    )";
		$dbtemp->execute($sSql);
		$ID_mail = $dbtemp->getInsertID(true);

		if(!verifyMailbox(CC_FROM_EMAIL)) {
			$sSql = "SELECT * FROM `email_address` WHERE email = " . $dbtemp->toSql(CC_FROM_EMAIL, "Text");
			$dbtemp->query($sSql);
			if($dbtemp->nextRecord()) {
				$ID_CC = $dbtemp->getField("ID")->getValue();
				$default_cc = array(
					"name" => $dbtemp->getField("name", "Text", true)
				, "mail" => $dbtemp->getField("email", "Text", true)
				);
			} else {
				$sSql = "INSERT 
		                        INTO `email_address` 
		                        (
		                            ID
		                            , name
		                            , email
		                            , uid
		                        ) 
		                        VALUES 
		                        (
		                            ''
		                            , " . $dbtemp->toSql(CC_FROM_NAME, "Text") . "
		                            , " . $dbtemp->toSql(CC_FROM_EMAIL, "Text") . "
		                            , " . $dbtemp->toSql(0, "Number") . "
		                        )";
				$dbtemp->execute($sSql);
				$ID_CC = $dbtemp->getInsertID(true);
				$default_cc = array(
					"name" => CC_FROM_NAME
				, "mail" => CC_FROM_EMAIL
				);
			}

			if($ID_CC) {
				$sSql = "SELECT * 
			                FROM `email_rel_address` 
			                WHERE 
			                    ID_email = " . $dbtemp->toSql($ID_mail, "Number") . "
			                    AND ID_address = " . $dbtemp->toSql($ID_CC, "Number") . "
			                    AND type = " . $dbtemp->toSql("cc", "Text");
				$dbtemp->query($sSql);
				if(!$dbtemp->nextRecord()) {
					$sSql = "INSERT 
			                        INTO `email_rel_address` 
			                        (
			                            ID
			                            , ID_email
			                            , ID_address
			                            , type
			                        ) 
			                        VALUES 
			                        (
			                            ''
			                            , " . $dbtemp->toSql($ID_mail, "Number") . "
			                            , " . $dbtemp->toSql($ID_CC, "Number") . "
			                            , " . $dbtemp->toSql("cc", "Text") . "
			                        )";
					$dbtemp->execute($sSql);
				}
			}
		}
		if(!verifyMailbox(BCC_FROM_EMAIL)) {
			$sSql = "SELECT * FROM `email_address` WHERE email = " . $dbtemp->toSql(BCC_FROM_EMAIL, "Text");
			$dbtemp->query($sSql);
			if($dbtemp->nextRecord()) {
				$ID_BCC = $dbtemp->getField("ID")->getValue();
				$default_bcc = array(
					"name" => $dbtemp->getField("name", "Text", true)
				, "mail" => $dbtemp->getField("email", "Text", true)
				);
			} else {
				$sSql = "INSERT 
		                        INTO `email_address` 
		                        (
		                            ID
		                            , name
		                            , email
		                            , uid
		                        ) 
		                        VALUES 
		                        (
		                            ''
		                            , " . $dbtemp->toSql(BCC_FROM_NAME, "Text") . "
		                            , " . $dbtemp->toSql(BCC_FROM_EMAIL, "Text") . "
		                            , " . $dbtemp->toSql(0, "Number") . "
		                        )";
				$dbtemp->execute($sSql);
				$ID_BCC = $dbtemp->getInsertID(true);
				$default_bcc = array(
					"name" => BCC_FROM_NAME
				, "mail" => BCC_FROM_EMAIL
				);
			}

			if($ID_BCC) {
				$sSql = "SELECT * 
			                FROM `email_rel_address` 
			                WHERE 
			                    ID_email = " . $dbtemp->toSql($ID_mail, "Number") . "
			                    AND ID_address = " . $dbtemp->toSql($ID_BCC, "Number") . "
			                    AND type = " . $dbtemp->toSql("bcc", "Text");
				$dbtemp->query($sSql);
				if(!$dbtemp->nextRecord()) {
					$sSql = "INSERT 
			                        INTO `email_rel_address` 
			                        (
			                            ID
			                            , ID_email
			                            , ID_address
			                            , type
			                        ) 
			                        VALUES 
			                        (
			                            ''
			                            , " . $dbtemp->toSql($ID_mail, "Number") . "
			                            , " . $dbtemp->toSql($ID_BCC, "Number") . "
			                            , " . $dbtemp->toSql("bcc", "Text") . "
			                        )";
					$dbtemp->execute($sSql);
				}
			}
		}
    }
    function process_mail_field($value, $prefix = null, $type = null, $language = null)
    {
        if($prefix)
            $prefix = $prefix . " ";

        if($language === null)
            $language = $this->lang;

        if($language)
        {
            $res = ffTemplate::_get_word_by_code($prefix . $value, $language);
        } else {
            $res = $prefix . $value;
        }
        switch($type)
        {
            case "link":
                check_function("transmute_inlink");

                $link = $value;
                if(strpos($value, "http") === 0)
                {
                    $link = "http" . ($_SERVER["HTTPS"] ? "s" : "") . "://" . $_SERVER["HTTP_HOST"] . FF_SITE_PATH . substr($link, 4);
                }

                $res = transmute_inlink($link, $res);
                break;
            case "smart_url":
                $res = ffCommon_url_rewrite($res);
                break;
            default:
        }

        return $res;
    }

    private function process_mail_subject()
    {
        return $this->process_mail_field(
            ($this->subject
                ? $this->subject
                : $this->name
            )
            , $this->prefix["subject"]
        );
    }

    private function parse_mail_group($value, $groups, $type = null)
    {
        /*
         * Parse field html(label, value, real_name)
         */
        $this->tpl_html->parse("SezStyle" . $type, false);
        foreach ($groups AS $group_key => $group_value)
        {
            if ($group_key != $type) {
                $this->tpl_html->set_var("SezStyle" . $group_key, "");
            }
        }
        if ($type)
            $this->tpl_html->set_var("SezStyle", "");

        $this->tpl_html->set_var("real_name", $this->process_mail_field($value, null, "smart_url"));
        $this->tpl_html->set_var("group_name", $this->process_mail_field($value, $this->prefix["group"]));
        $this->tpl_html->parse("SezGroups", true);

        $this->tpl_html->set_var("SezFieldLabel", "");
        $this->tpl_html->set_var("SezField", "");

        foreach ($groups AS $group_key => $group_value)
        {
            $this->tpl_html->set_var("Sez" . $group_key . "FieldLabel", "");
            $this->tpl_html->set_var("Sez" . $group_key . "Field", "");
            $this->tpl_html->set_var("Sez" . $group_key . "Row", "");
        }

        /*
         * Parse field text(label, value, real_name)
         */
        if($this->tpl_text)
        {
            $this->tpl_text->parse("SezStyle" . $type, false);
            foreach ($groups AS $group_key => $group_value) {
                if ($group_key != $type) {
                    $this->tpl_text->set_var("SezStyle" . $group_key, "");
                }
            }
            if ($type)
                $this->tpl_text->set_var("SezStyle", "");

            $this->tpl_text->set_var("real_name", $this->process_mail_field($value, null, "smart_url"));
            $this->tpl_text->set_var("group_name", $this->process_mail_field($value));
            $this->tpl_text->parse("SezGroups", true);

            $this->tpl_text->set_var("SezFieldLabel", "");
            $this->tpl_text->set_var("SezField", "");

            foreach ($groups AS $group_key => $group_value)
            {
                $this->tpl_text->set_var("Sez" . $group_key . "FieldLabel", "");
                $this->tpl_text->set_var("Sez" . $group_key . "Field", "");
                $this->tpl_text->set_var("Sez" . $group_key . "Row", "");
            }
        }
    }

    private function parse_mail_field($value, $name, $type = null, $skip_label = false)
    {
        /*
         * Parse field html(label, value, real_name)
         */
        if (!$skip_label)
        {
            $this->tpl_html->set_var("fields_label", $this->process_mail_field($name, $this->prefix["label"]));
            $this->tpl_html->parse("Sez" . $type . "FieldLabel", true);
        }

        $this->tpl_html->set_var("real_name", $this->process_mail_field($name, $this->prefix["field"], "smart_url"));
        $this->tpl_html->set_var("fields_value", $this->process_mail_field($value, $this->prefix["field"], $name));
        $this->tpl_html->parse("Sez" . $type . "Field", true);

        $this->tpl_html->set_var(                      //custom vars
            $this->process_mail_field($name, $this->prefix["label"])
            , $this->process_mail_field($value, $this->prefix["field"])
        );

        /*
         * Parse field text(label, value, real_name)
         */
        if($this->tpl_text)
        {
            if (!$skip_label)
            {
                $this->tpl_text->set_var("fields_label", $this->process_mail_field($name, $this->prefix["label"]));
                $this->tpl_text->parse("Sez" . $type . "FieldLabel", true);
            }

            $this->tpl_text->set_var("fields_value", $this->process_mail_field($value, $this->prefix["field"], $name));
            $this->tpl_text->parse("SezField", true);

            $this->tpl_text->set_var(                  //custom vars
                $this->process_mail_field($name, $this->prefix["label"])
                , $this->process_mail_field($value, $this->prefix["field"])
            );
        }
    }

    private function parse_mail_row($type = null, $reset_field = false)
    {
        $this->tpl_html->parse("Sez" . $type . "Row", false);
        $this->tpl_html->parse("SezRow" . $type, false); //custom vars

        if($reset_field) {
            $this->tpl_html->set_var("Sez" . $type . "Field", "");
            $this->tpl_html->set_var("Sez" . $type . "FieldLabel", "");
        }

        if($this->tpl_text) {
            $this->tpl_text->parse("Sez" . $type . "Row", false);
            $this->tpl_text->parse("SezRow" . $type, false); //custom vars
            if ($reset_field) {
                $this->tpl_text->set_var("Sez" . $type . "Field", "");
                $this->tpl_text->set_var("Sez" . $type . "FieldLabel", "");
            }
        }
    }

}