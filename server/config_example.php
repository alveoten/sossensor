<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 15/09/17
 * Time: 17.33
 */


$db = "db_name"; //db name
$mongoUri = "mongodb://user:password@localhost/{$db}";
$user_id = "user_id_into_collection";
$collection = 'events'; //collections name
$collection_stats  = "wc_stats"; //collections for aggregate stats


define("_ROOT_PATH",realpath(dirname(__DIR__."/../../../")));