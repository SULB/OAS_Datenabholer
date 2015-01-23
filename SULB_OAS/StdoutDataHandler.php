<?php

/**
 * DataHandler for stdout target
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

require_once 'DataHandlerInterface.php';

/**
 * Class for returning data on stdOut (console)
 */
class StdoutDataHandler implements \SULB_OAS\DataHandlerInterface
{
    /**
     * Dummy function
     * @param String $dest dummy/empty
     * @param \SULB_OAS\ConfigParser $config Configuration
     * @return boolean true
     */
    public static function connect($dest, ConfigParser $config)
    {
        return true;
    }

    /**
     * Dummy function
     * @param String $dest
     * @return boolean
     */
    public static function disconnect($dest)
    {
        return true;
    }

    /**
     * Print out given data string. Usable for debugging.
     * @param String $data response body to print out
     */
    public static function write($data)
    {
        echo $data;
    }
}
