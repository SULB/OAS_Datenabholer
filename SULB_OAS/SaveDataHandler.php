<?php

/**
 * DataHandler for file storage target (save)
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
 * Class for file writing of retriven data
 */
class SaveDataHandler implements DataHandlerInterface
{
    /**
     * File pointer to write in.
     * @var filepointer
     */
    private static $connection;
    /**
     * File name / path to open and write
     * @var String file name
     */
    private static $destination;

    /**
     * Open file for writing at the end (append mode)
     * @param String $dest file name
     * @param \SULB_OAS\ConfigParser $config Configuration, cann be null (dummy)
     * @throws \SULB_OAS\Exception if file is not readable/writable
     * @return boolean true
     */
    public static function connect($dest, ConfigParser $config)
    {
        self::$destination = $dest;
        try {
            self::$connection = fopen($dest, 'a+');
        } catch (\Exception $e) {
            throw new Exception('Not readable/writable file: '.self::$destination);
        }
        return true;
    }

    /**
     * Close file pointer
     * @param String $dest dummy/empty
     * @return boolean true
     */
    public static function disconnect($dest)
    {
        fclose(self::$connection);
        return true;
    }

    /**
     * Write given String into the file(pointer)
     * @param String $data JSON or CSV data from HTTP response body
     * @return boolean true
     * @throws Exception if data could not be written
     */
    public static function write($data)
    {
        try {
            fwrite(self::$connection, $data);
        } catch (\Exception $e) {
            throw new Exception('Data could not be written into: '.self::$destination);
        }
        return true;
    }
}
