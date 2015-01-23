<?php

/**
 * configuration (ini-file) reader class
 *
 * PHP version 5.3
 *
 * @package SULB_OAS
 * @author  Dr. Robert Kolatzek <r.koaltzek@sulb.uni-saarland.de>
 * @version "GIT: <git_id>"
 *
 *
 */

namespace SULB_OAS;

require_once 'Exception.php';
require_once 'StdoutDataHandler.php';

/**
 * ConfigParser parses given file path and gets configuration values.
 *
 * @author Dr. Robert Kolatzek
 */
class ConfigParser
{
    private $dbHost = 'localhost';
    private $dbName;
    private $dbUser;
    private $dbPassword;
    private $dbPort = 3306;
    private $dbSocket = '/var/run/mysqld/mysqld.sock';
    private $dbTable = "gbv_stat";
    private $apiUrl = 'https://oase.gbv.de:443/api/v1/reports/basic';
    private $prefix;
    private $format = 'json';
    private $from = '-3 days';
    private $until;
    private $granularity = 'day';
    private $content = 'counter';
    private $addEmptyRecords = false;
    private $summarized = false;
    private $informational = false;
    private $callMethod = 'curl';
    private $apiUser;
    private $apiPassword;
    private $target = 'stdout';

    public function __construct($filePath, $section = '')
    {
        if (!file_exists($filePath) || !file_get_contents($filePath)) {
            throw new Exception('Given configuration file path does not exists: ' . $filePath);
        }
        $iniCont = parse_ini_file($filePath, true);
        if (!key_exists('common', $iniCont)) {
            throw new Exception('Given configuration file has no section: common');
        } else {
            foreach ($iniCont['common'] as $key => $value) {
                $n = "$key";
                if ($key == 'content') {
                    $value = $this->contentKeyConvert($value);
                }
                if ($key == "getMethod" && $value == "curl" && !function_exists("curl_init")) {
                    throw new Exception('Curl is configured as callMethod but php is missing curl extension.');
                } elseif ($key == 'target') {
                    $this->checkTargetMethod($value);
                }
                $this->$n = $value;
            }
        }
        if (!empty($section)) {
            if (!key_exists($section, $iniCont)) {
                throw new Exception('Given configuration file has no section: ' . $section);
            } else {
                foreach ($iniCont[$section] as $key => $value) {
                    $n = "$key";
                    if ($key == 'content') {
                        $value = $this->contentKeyConvert($value);
                    } elseif ($key == "getMethod" && $value == "curl" && !function_exists("curl_init")) {
                        throw new Exception('Curl is configured as callMethod but php is missing curl extension.');
                    } elseif ($key == 'target') {
                        $this->checkTargetMethod($value);
                    }
                    $this->$n = $value;
                }
            }
        }
    }

    private function checkTargetMethod($value)
    {
        try {
            require_once ucfirst($value) . 'DataHandler.php';
        } catch (\Exception $ex) {
            throw new Exception(
                'Unknown DataHandler was set:' . $value . '. Could not open SULB_OAS/' .
                ucfirst($value) . 'DataHandler.php.'
            );
        }
    }

    private function contentKeyConvert($str)
    {
        return preg_replace("/[^_a-z]+/", "%2C", $str);
    }

    public function __set($name, $value)
    {
        if (!($name == 'from' || $name == 'until' || $name == 'target' || $name == 'addEmptyRecords')) {
            throw new Exception('Can not change config value: ' . $name);
        } elseif ($name == 'from' || $name == 'until') {
            if (
                !preg_match(
                    '/^(2[0-9]{3}\-(1[0-2]{1}|[0]{0,1}[1-9]{1})\-([0]{0,1}[1-9]{1}|[1]{1}[0-9]{1}|[2]{1}[0-9]{1}|[3]{1}[0-1]{1}))$/',
                    $value
                )
                &&
                !preg_match('/^(\-[0-9]+\sdays)$/', $value)) {
                throw new Exception('Bad date value: ' . $value);
            } else {
                $this->$name = $value;
            }
        } elseif ($name == 'target') {
            $this->checkTargetMethod($value);
            $this->target = $value;
        }
    }

    public function getDbHost()
    {
        return $this->dbHost;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function getDbUser()
    {
        return $this->dbUser;
    }

    public function getDbPassword()
    {
        return $this->dbPassword;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getUntil()
    {
        return $this->until;
    }

    public function getGranularity()
    {
        return $this->granularity;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getAddEmptyRecords()
    {
        return $this->addEmptyRecords;
    }

    public function getSummarized()
    {
        return $this->summarized;
    }

    public function getInformational()
    {
        return $this->informational;
    }

    public function getCallMethod()
    {
        return $this->callMethod;
    }

    public function getApiUser()
    {
        return $this->apiUser;
    }

    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getDbPort()
    {
        return $this->dbPort;
    }

    public function getDbSocket()
    {
        return $this->dbSocket;
    }

    public function getDbTable()
    {
        return $this->dbTable;
    }
}
