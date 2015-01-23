<?php

/**
 * Console application for harvesting data from GBV
 *
 * PHP version 5.3
 *
 * @package SULB_OAS
 * @author  Dr. Robert Kolatzek <r.koaltzek@sulb.uni-saarland.de>
 * @version "GIT: <git_id>"
 *
 * Arguments:
 * 1 = ini file name (without extension "ini")
 * 2 = section in this file
 * 3 = id for oai identifier
 * 4 = from-date
 * 5 = until-date
 * 6 = what to do with data? 'stdout' (for debug), 'db' (store in db),
 * 'save' (store on disk)
 * 7 = file name to append data
 */
set_include_path(get_include_path() . PATH_SEPARATOR . 'SULB_OAS' . PATH_SEPARATOR);

require_once 'SULB_OAS/ConfigParser.php';
require_once 'SULB_OAS/CallMethodCurl.php';

if (count($argv) == 1) {
    echo "ini file name as 1st parameter is missing. ";
    echo "take a look in config directory or read help (append '-h').\n". 
    "exit.\n";
    exit();
}

if ($argv[1] == '-h') {
$args = <<<EOT

Arguments:
    1 = ini file name (without extension "ini")
    2 = section in this file (default: common)
    3 = id for oai identifier (default: %)
    4 = from-date (default: -3 days)
    5 = until-date (default: empty)
    6 = what to do with data? 'stdout' (for debug), 'db' (store in db),
        'save' (store on disk)
    7 = file name to append data

EOT;
    echo "Usage 'php app.php' [argument 1 [argument 2 [argument 3...]]]";
    echo $args;
    exit();
}

/**
 * Set internal constant 'DEBUG' to true if it is set on console (like "export debug=true")
 */
if (getenv('debug')) {
    define('DEBUG', true);
}
else {
    define('DEBUG', false);
}

$configname = $argv[1];
$section = '';
if (count($argv) > 2) {
    $section = $argv[2];
}

$id = null;
if (count($argv) > 3 && intval($argv[3]) > 0) {
    $id = $argv[3];
}

$c = new \SULB_OAS\ConfigParser('config/' . $configname . '.ini', $section);

if (count($argv) > 4 && strlen(trim($argv[4])) > 0) {
    $c->from = $argv[4];
}

if (count($argv) > 5 && strlen(trim($argv[5])) > 0) {
    $c->until = $argv[5];
}

/* on debug add also empty records */
if (DEBUG) $c->addEmptyRecords = true;

$data = '';
if ($c->getCallMethod() == 'curl') {
    $a = new \SULB_OAS\CallMethodCurl($c);
    try {
        $data = $a->getData($id) . "\n";
    } catch (SULB_OAS\Exception $e) {
        echo $e->getMessage() . "\n";
        echo $a->errorReport() . "\n";
    }
}

if (count($argv) > 6 && strlen(trim($argv[6])) > 0) {
    $c->target = trim($argv[6]);
}

if ($c->getTarget() == 'stdout') {
    SULB_OAS\StdoutDataHandler::write($data);
} elseif ($c->getTarget() == 'db') {
    SULB_OAS\DbDataHandler::connect($c->getDbTable(), $c);
    SULB_OAS\DbDataHandler::write($data);
    SULB_OAS\DbDataHandler::disconnect('');
} elseif ($c->getTarget() == 'save') {
    if (count($argv) > 7 && strlen(trim($argv[7])) > 0) {
        $filename = trim($argv[7]);
    }

    SULB_OAS\SaveDataHandler::connect($filename, $c);
    SULB_OAS\SaveDataHandler::write($data);
    SULB_OAS\SaveDataHandler::disconnect('');
}
