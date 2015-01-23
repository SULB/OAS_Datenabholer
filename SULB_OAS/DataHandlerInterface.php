<?php

/**
 * DataHander interface
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

/**
 * Interface for all DataHandler (transform, store etc)
 */
interface DataHandlerInterface
{

    public static function connect($dest, ConfigParser $config);

    public static function write($data);

    public static function disconnect($dest);
}
