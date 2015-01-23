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

/**
 * Interface for call classes like curl, curl-bin, fopen 
 */
interface CallMethodInterface
{

    public function __construct(ConfigParser $config);

    public function getData($oaiId);

    public function errorReport();
}
