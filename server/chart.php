<?php
header("content-type: text/html; charset=utf8");

ini_set("display_errors","on");
error_reporting(E_ALL|E_STRICT);

chdir(realpath(__DIR__."/../"));

require_once "config.php";
require_once _ROOT_PATH."/vendor/autoload.php";

$mongo = new MongoDB\Client($mongoUri);
$res = $mongo->{$db}->{$collection_stats}->aggregate([
   ['$group' =>
       [
           '_id' => [ "id"=>  '$wc_id', "hour"=> '$local_hour'],
           'wc_id' => ['$first' => '$wc_id'],
           'hour' => ['$first' => '$local_hour'],
           'avg_use' => ['$avg' => '$number_of_use' ],
            'avg_time' => ['$avg' => '$average_time']
       ],
   ],
   ['$sort' => [
           'wc_id' => 1,
           'hour' => 1
       ]
   ]
]);

$data_times = [];
$avg_times = [];
$labels = [];
foreach ($res as $r) {
    $labels[] = $r->hour;
    $data_times[] = round($r->avg_use,2);
    $avg_times[] = round($r->avg_time/60,2);
}


if(isset($_GET['json'])){
    echo json_encode([
        "labels" => $labels,
        "data_times" => $data_times,
        "avg_times" => $avg_times
      ]
    );
    exit;
}

?><!DOCTYPE html>
<html>
    <head>

    </head>
    <body>
        <canvas id="myChart" width="300" height="200"></canvas>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js" type="text/javascript"></script>
        <script>
            var ctx = document.getElementById("myChart").getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [<?= implode(",", $labels); ?>],
                    datasets: [{
                        label: '# utilizzo medio del wc',
                        data: [<?= implode(",", $data_times); ?>],
                        backgroundColor: [
                            <?php for($i=0; $i<count($labels); $i++): ?>
                            'rgba(54, 162, 235, 0.2)',
                            <?php endfor; ?>
                        ],
                        borderColor: [
                            <?php for($i=0; $i<count($labels); $i++): ?>
                            'rgba(54, 162, 235, 1)',
                            <?php endfor; ?>
                        ],
                        borderWidth: 1
                    },
                        {
                            label: 'durata media di utilizzo',
                            data: [<?= implode(",", $avg_times); ?>],
                            type: 'line'
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });


        </script>
    </body>
</html>

