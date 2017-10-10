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


class notifierTwilio
{
    const TYPE                                              = "sms";

    private $device                                         = null;
    private $config                                         = null;
    private $data                                           = null;
    private $notifier                                       = null;

    public function __construct($notifier, $data = null, $config = null)
    {
        $this->notifier = $notifier;
        $this->setConfig($config);
        $this->setData($data);

        $notify = $this->data;
    }

    public function getDevice()
    {
        return $this->device;
    }
    public function getConfig()
    {
        return $this->config;
    }
    public function getData()
    {
        return $this->data;
    }
    private function setConfig($config = null)
    {
        $this->config = $this->notifier->getConfig($this::TYPE);

        if (!$this->config["app_id"] || $this->config["app_key"])
        {
            if (is_file($this->notifier->getAbsPath("/conf/gallery/config/other." . FF_PHP_EXT)))
            {
                require_once($this->notifier->getAbsPath("/conf/gallery/config/other." . FF_PHP_EXT));

                $this->config["app_id"] = (defined("NOTIFY_PUSH_ONESIGNAL_APP_ID")
                    ? NOTIFY_PUSH_ONESIGNAL_APP_ID
                    : ""
                );
                $this->config["app_key"] = (defined("NOTIFY_PUSH_ONESIGNAL_APP_ID")
                    ? NOTIFY_PUSH_ONESIGNAL_APP_ID
                    : ""
                );
            }
        }
    }
    private function setData($data = null)
    {
        $this->data = $this->notifier->getData($this::TYPE, $data);
    }
}