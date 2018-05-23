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


class mailerSparkpost
{
    const TYPE                                              = "email";

    private $device                                         = null;
    private $config                                         = null;
    private $mailer                                         = null;

    /**
     * mailerSparkpost constructor.
     * @param $mailer
     */
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
        $this->setConfig();
    }

    /**
     * @return null
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

    /**
     * @todo: da togliere
     */
    private function setConfig()
    {
        $this->config = $this->mailer->getConfig($this::TYPE);

        if (!$this->config["password"])
        {
            if (is_file($this->mailer->getAbsPathPHP("/config")))
            {
                require_once($this->mailer->getAbsPathPHP("/config"));
				if(!$this->mailer->issetFrom() && defined("A_FROM_EMAIL") && A_FROM_EMAIL) {
					$this->mailer->addAddress(array(
						"name" 		=> A_FROM_NAME
					, "email" 	=> A_FROM_EMAIL
					), "from");
				}

                $this->config["host"] = (defined("A_SMTP_HOST")
                    ? A_SMTP_HOST
                    : "smtp.sparkpostmail.com"
                );
                $this->config["name"] = (defined("FF_DATABASE_NAME")
                    ? FF_DATABASE_NAME
                    : "SMTP_Injection"
                );
                $this->config["username"] = (defined("A_SMTP_USER")
                    ? A_SMTP_USER
                    : ""
                );
                $this->config["password"] = (defined("A_SMTP_PASSWORD")
                    ? A_SMTP_PASSWORD
                    : ""
                );

                $this->config["auth"] = (defined("SMTP_AUTH")
                    ? SMTP_AUTH
                    : true
                );
                $this->config["port"] = (defined("A_SMTP_PORT")
                    ? A_SMTP_PORT
                    : "587"
                );
                $this->config["secure"] = (defined("A_SMTP_SECURE")
                    ? A_SMTP_SECURE
                    : "tls"
                );
            }
        }
    }
}