<?php

/**
 * php-curl call method class
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

require_once 'ConfigParser.php';
require_once 'CallMethodInterface.php';

/**
 * Class with curl (php-curl) call method
 */
class CallMethodCurl implements \SULB_OAS\CallMethodInterface
{
    /**
     * URL to call
     * @var String
     */
    private $url;
    /**
     * Array of error messages
     * @var array
     */
    private $errorMessages;
    /**
     * Configuration
     * @var \SULB_OAS\ConfigParser
     */
    private $config;
    
    /**
     * Create a client for aggregated OAS data
     * @param \SULB_OAS\ConfigParser $config configuration
     * @throws Exception bad user-password values, data format is missing, from parameter is missing
     */
    public function __construct(\SULB_OAS\ConfigParser $config)
    {
        if (empty($config->getApiUrl())
                || (!empty($config->getApiPassword()) && empty($config->getApiUser()))
                || (empty($config->getApiPassword()) && !empty($config->getApiUser()))) {
            throw new Exception('API url or API user/password is missing.');
        }
        if (empty($config->getFormat())) {
            throw new Exception('API format is missing');
        }
        if (!in_array($config->getFormat(), array('json', 'csv'))) {
            throw new Exception('Bad format for API: ' . $config->getFormat());
        }
        if (empty($config->getFrom())) {
            throw new Exception('Empty parameter: from.');
        }
        $this->url = $config->getApiUrl() . '.' . $config->getFormat();
        $this->config = clone $config;
    }

    /**
     * Start a call and retrive data
     * @param Integer $oaiId id (number) of requested work. Can be null or 0 for whole prefix (from ini) usage
     * @return String HTTP-Body of request
     * @throws Exception on unexpected server answer - not a "HTTP/1.1 200 OK"
     */
    public function getData($oaiId = null)
    {
        $url = $this->url . '?';
        $oai = $this->config->getPrefix();
        if ($oaiId > 0) {
            $oai = preg_replace('/:$/', '', preg_replace("/%$/", '', $oai)) . ':' . intval($oaiId);
        }
        $url .= "identifier=" . str_replace(array('%3A', '%25'), array(':', '%'), \rawurlencode($oai));
        $url .= "&from=" . \rawurlencode($this->config->getFrom());
        if (!empty($this->config->getUntil())) {
            $url .= "&until=" . \rawurlencode($this->config->getUntil());
        } else {
            $url .= "&until=" . \rawurlencode(date('Y-m-d', time()));
        }
        $url .= "&granularity=" . $this->config->getGranularity();
        $url .= "&content=" . $this->config->getContent();
        $url .= "&addemptyrecords=" . ($this->config->getAddEmptyRecords() ? 'true' : 'false');
        $url .= "&summarized=" . ($this->config->getSummarized() ? 'true' : 'false');
        $url .= "&informational=" .
                (($this->config->getInformational() && $oaiId != 0 && $this->config->getFormat() == 'json') ?
                'true' : 'false');
        /* on debug show url */
        if(DEBUG) echo $url."\n";
        $curlConnection = curl_init($url);
        $this->errorMessages[] = $url;
        curl_setopt($curlConnection, CURLOPT_AUTOREFERER, true);
        curl_setopt($curlConnection, CURLOPT_HEADER, true);
        curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlConnection, CURLOPT_TIMEOUT, 60);
        $usrPwd = $this->config->getApiUser() . ':' . $this->config->getApiPassword();
        curl_setopt($curlConnection, CURLOPT_USERPWD, $usrPwd);
//    curl_setopt($ch, CURLOPT_NOBODY, true);
        $ret = curl_exec($curlConnection);
        curl_close($curlConnection);
        $parts = preg_split("/(\r\n\r\n|\n\r\n\r|\n\n|\r\r)/", $ret);
        $head = $parts[0];
        $body = $parts[1];
        if (preg_split("/[\n\r]+/", $head)[0] == "HTTP/1.1 200 OK") {
            return $body;
        } else {
            $this->errorMessages[] = preg_split("/[\n\r]+/", $head)[0];
            $this->errorMessages[] = $body;
            throw new Exception('Unexpected server answer: ' . preg_split("/[\n\r]+/", $head)[0]);
        }
    }

    /**
     * Get a readable description of all error messages (HTTP header and body)
     * @return String stingified array of errorMessages
     */
    public function errorReport()
    {
        return var_export($this->errorMessages, true);
    }
}
