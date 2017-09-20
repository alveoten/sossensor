<?php
header("content-type: text/plain; charset=utf8");

ini_set("display_errors","on");
error_reporting(E_ALL|E_STRICT);

chdir(realpath(__DIR__."/../"));

require_once "config.php";
require_once _ROOT_PATH."/vendor/autoload.php";

if(isset($_GET["debug"])){
    echo json_encode(["status" => "free" ]);
    exit;
}


$mongo = new MongoDB\Client($mongoUri);

$query = [
    "user_id" => new \MongoDB\BSON\ObjectID($user_id),
    "date" => ['$gte' => new \MongoDB\BSON\UTCDateTime(new \DateTime("-5 minutes",new DateTimeZone("Europe/Rome")))]
];

$num = $mongo->{$db}->{$collection}->count(
    $query,
    [ ["sort" => -1 ] ]
);

//no events in 5 minutes
if($num == 0){
    echo json_encode(["status" => "free" ]);
    exit;
}

$count = $mongo->{$db}->{$collection}->count(
    [
        "user_id" => new \MongoDB\BSON\ObjectID($user_id),
        "date" => ['$gte' => new \MongoDB\BSON\UTCDateTime(new \DateTime("-21 seconds"))]
    ]
);

//at least 1 events younger then 21 seconds
if($count > 0){
    echo json_encode(["status" => "occupied" ]);
    exit;
}

//no events younger then 30 seconds
echo json_encode(["status" => "warning" ]);
