<?php

include_once(dirname(__FILE__).'/_core.php');

$period = $_GET['period'] ?? '';
// バリデーションして使う

if($period === 'month') {
    $end = date('Y/m/d', strtotime('today'));
    $start = date('Y/m/d', strtotime('-29 day'));
} else {
    $end = date('Y/m/d', strtotime('today'));
    $start = date('Y/m/d', strtotime('-6 day'));
}

$result = specifiedPeriodDatas::getIineDatas($start, $end);

if(!$result) {
echo false;
return;
}

$labels = [];
$counts = [];

$current = strtotime($start);
$endTime = strtotime($end);

while ($current <= $endTime) {
    $ymd = date('Y/m/d', $current);
    $labels[] = $ymd;

    if(key_exists($ymd, $result)) {
        $sum = 0;
        foreach($result[$ymd] as $item) {
            foreach ($item as $val) {
                $sum += $val[5];
            }
        }
        $counts[] = $sum;
    } else {
        $counts[] = 0;
    }

    $current = strtotime('+1 day', $current);
}

$ret = [
    'labels' => $labels,
    'counts' => $counts
];

header('Content-Type: application/json');
echo json_encode($ret);