<?php

/**
 * Interface for call methods
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
 * Use MySQL as storage for data, gbv_stats table (dbTable-value) by using mysqli
 */
class DbDataHandler implements \SULB_OAS\DataHandlerInterface
{
    /**
     * db connection
     * @var \mysqli connection object
     */
    private static $connection;
    /**
     * table name
     * @var String destination table
     */
    private static $destination;

    /**
     * Create and test connection and existence of destination table (but not it structure)
     *
     * @param String $dest table name in MySQL db
     * @param \SULB_OAS\ConfigParser $config Configuration
     * @return boolean true
     * @throws Exception if connection error
     * @throws Exception if table does not exist
     */
    public static function connect($dest, ConfigParser $config)
    {
        self::$connection = new \mysqli(
            $config->getDbHost(),
            $config->getDbUser(),
            $config->getDbPassword(),
            $config->getDbName(),
            $config->getDbPort(),
            $config->getDbSocket()
        );
        if (self::$connection->connect_error) {
            throw new Exception(
                'Connect Error (' . self::$connection->connect_errno . ') ' . self::$connection->connect_error
            );
        }
        self::$destination = $dest;
        if ($res = self::$connection->query('show tables like ' . self::$destination) && $res->num_rows != 1) {
            throw new Exception('Table ' . self::$destination . ' is missing in DB ' . $config->getDbName());
        }
        return true;
    }

    /**
     * Close connection to MySQL
     *
     * @param String $dest empty table name
     * @return boolean true
     */
    public static function disconnect($dest)
    {
        self::$connection->close();
        return true;
    }

    /**
     * Write data to MySQL table (destination)
     * @param String $data JSON string with data
     * @throws Exception prepare failed, bad oai-id, writing failed or bad rows count written
     */
    public static function write($data)
    {
        $data = json_decode($data);
        if ($data->granularity == 'day') {
            // ALTER TABLE `rk_gbv_stat_test` ADD `robots_abstract` INT UNSIGNED NULL COMMENT 'neu seit 15.12.2014' AFTER `robots` 
            $stmt = self::$connection->prepare(
                "REPLACE INTO "
                . "" . self::$destination . " (identnum, identrep, date, counter, counter_abstract, robots, robots_abstract, country) "
                . "VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . self::$connection->errno . ") " . self::$connection->error);
            }
            foreach ($data->entries as $value) {
                $a['counter'] = $value->counter + 0;
                $a['counter_abstract'] = $value->counter_abstract + 0;
                $a['robots'] = $value->robots + 0;
                $a['robots_abstract'] = $value->robots_abstract + 0;
                if (preg_match('/^oai:(.+):(\d+)$/', $value->identifier, $oai_id_parts) && count($oai_id_parts) != 3) {
                    var_dump($oai_id_parts);
                    throw new Exception('This identifier is not readable: ' . $value->identifier);
                }
                $a['identnum'] = $oai_id_parts[2];
                $a['repo'] = $oai_id_parts[1];
                $a['date'] = $value->date;
                $a['country'] = '';
                if (
                    !$stmt->bind_param(
                        'issiiiis',
                        $a['identnum'],
                        $a['repo'],
                        $a['date'],
                        $a['counter'],
                        $a['counter_abstract'],
                        $a['robots'],
                        $a['robots_abstract'],
                        $a['country']
                    )
                ) {
                    throw new Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
                }
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                }
                if ($stmt->affected_rows > 2) {
                    throw new Exception(
                        'Unexpected insert/update count: ' . $stmt->affected_rows . ' by ' . $value->identifier .
                        ' on ' . $value->date
                    );
                }
            }
            $stmt->close();
        }
    }
}
