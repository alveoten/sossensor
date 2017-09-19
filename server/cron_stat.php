<?php
//header("content-type: text/plain; charset=utf8");

ini_set("display_errors", "on");
error_reporting(E_ALL | E_STRICT);

chdir(realpath(__DIR__ . "/../"));

require_once "config.php";
require_once _ROOT_PATH."/vendor/autoload.php";

$mongo = new MongoDB\Client($mongoUri);

if(isset($argv[1]) OR isset($_GET["date"])){
    $date = $argv[1]??$_GET["date"];
}

$dates = [];
if( !isset($date) ){
    $dates[] = "yesterday";
}
elseif(!preg_match("/\d{4}-\d{2}-\d{2}|\d{4}-\d{2}-\d{2}/", $date)){
    echo "invalid date format";
    exit;
}
else{
    list($start,$end) = explode("|", $date);
    if($start > $end){
        echo "invalid date format";
        exit;
    }
    $period = new \DatePeriod(new \DateTime($start),new \DateInterval("P1D"),new \DateTime($end));
    foreach($period as $date){
        $dates[] = $date->format('Y-m-d');
    }
    $dates[] = $end;
}



foreach($dates as $date) {
    $mongo->{$db}->{$collection_stats}->deleteMany(
        [
            'local_day' => $date
        ]
    );

    $cursor = $mongo->{$db}->{$collection}->aggregate([
        ['$match' =>
            [
                "user_id" => new \MongoDB\BSON\ObjectID($user_id),
                '$and' => [
                    ['date' => ['$gte' => new \MongoDB\BSON\UTCDateTime((new \DateTime($date, new DateTimeZone('Europe/Rome')))->setTime(9, 0))]],
                    ['date' => ['$lte' => new \MongoDB\BSON\UTCDateTime((new \DateTime($date, new DateTimeZone('Europe/Rome')))->setTime(19, 0))]]
                ]
            ]
        ],
        ['$sort' => ['date' => 1]]
    ]);

    $hours = [];

    $pauses = [];
    $pause_start = '';
    $last_time = 0;
    $list_wc = [];
    foreach ($cursor as $r) {
        $date = $r->date;
        /**
         * @var $date UTCDateTime
         */
        $time = $date->toDateTime()->setTimezone(new \DateTimeZone('Europe/Rome'));
        if(!isset($list_wc[$r->wc_id])) {
            $list_wc[$r->wc_id] = [];
        }
        $list_wc[$r->wc_id][] = $time;
    }

    foreach($list_wc as $id => $times) {

        foreach ($times as $time) {

            if ($last_time === 0) {
                $last_time = $pause_start = $time;
                $pauses[$time->format('Y-m-d H:i:s')] = [
                    "start" => $time,
                    "end" => $time
                ];
                continue;
            }

            $interval = getDifferenceInSecond($time->diff($last_time));
            if ($interval < 20) {
                $pauses[$pause_start->format('Y-m-d H:i:s')]["end"] = $last_time;
                $last_time = $time;
                continue;
            }

            $last_time = $pause_start = $time;
            $pauses[$time->format('Y-m-d H:i:s')] = [
                "start" => $time,
                "end" => $time
            ];
        }


        $hours = [];
        foreach ($pauses as $pause) {
            if (!isset($hours[$pause["start"]->format("H")])) {
                $hours[$pause["start"]->format("H")] = [];
            }
            $hours[$pause["start"]->format("H")][] = $pause;
        }

        foreach ($hours as $hour) {
            $tot = 0;
            foreach ($hour as $pause) {
                $tot += getDifferenceInSecond($pause["end"]->diff($pause["start"]));
            }
            $mongo->{$db}->{$collection_stats}->insertOne([
                "local_day" => $pause["start"]->format('Y-m-d'),
                "local_hour" => (int)$pause["start"]->format("H"),
                "local_day_of_week" => (int)$pause["start"]->format("w"),
                "number_of_use" => (int)count($hour),
                "average_time" => $tot / count($hour),
                "wc_id" => $id
            ]);
        }

    }//id
}
/**
 * approssimative, valid for our purpose
 */
function getDifferenceInSecond(\DateInterval $interval)
{
    return ($interval->y * 3600 * 24 * 365) + ($interval->m * 31 * 24 * 3600) + ($interval->d * 24 * 3600) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
}