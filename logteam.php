<?php

date_default_timezone_set('Africa/Johannesburg');

$stage = 2;

if (isset($argv[1]) 
		&& in_array($argv[1], ['1','2','3','4'])) {
	$stage = (int)$argv[1];
}

define('LOADSHEDDING_STAGE', $stage);

$today=date('d');

$teamZones = [
    '2' => ['Yaseen'],
    '5' => ['Nadeem'],
    '7' => ['Saeed', 'Akeeda'], // 'Bert', 
    '10' => ['Tiaan'], // Briehan - 10th Nov
    '11' => ['Kamil'],
    '12' => ['Tohir'],
    '14' => ['Briehan'],
    '15' => ['Elvis', 'Mncedi', 'Lisa'],

];

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));

require_once 'vendor/autoload.php';

$csv = new parseCSV('stage'.LOADSHEDDING_STAGE.'.csv');

$data = $csv->data;

// Create an Array of Date Columns
$datesColumn = [];
for ($i=2; $i<=17; $i++)
{
    $datesColumn[$i] = [$data[0][$i]];
    
    if (!empty($data[1][$i])) {
        $datesColumn[$i][] = $data[1][$i];
    }
}


// Find Load Shedding
$loadShedding = [];

for ($i=2; $i<=count($data)-1; $i++)
{
    $startTime = $data[$i][0];
    $endTime = $data[$i][1]; 
    
    
    for ($j=2; $j<=17; $j++)
    {
        // Ignore if not today
        if (!in_array($today, $datesColumn[$j])) {
            continue;
        }
        
        // Get Zones
        $zones = explode(',', $data[$i][$j]);
        
        foreach ($zones as $zone)
        {
            $zone = (int)$zone;
            
            // Check whether it affects someone in team
            if (isset($teamZones[$zone])) {
                if (!isset($loadShedding[$startTime])) {
                    $loadShedding[$startTime] = [
                        'date'=>$datesColumn[$j],
                        'zone'=>[$zone],
                        'starttime'=>$startTime,
                        'endtime'=>$endTime,
                        'affects'=>$teamZones[$zone]
                    ];
                } else {
                    $loadShedding[$startTime]['zone'][] = $zone;
                    $loadShedding[$startTime]['affects'] = array_merge($loadShedding[$startTime]['affects'], $teamZones[$zone]);
                }
                
            }
        }
    }
}

echo 'Loadshedding in Merchant Team for '.date('l, j F Y').' - Stage '.LOADSHEDDING_STAGE.PHP_EOL.PHP_EOL;

foreach ($loadShedding as $time)
{
    echo sprintf('Zone %s', str_pad(implode(',', $time['zone']), 7));
    
    echo sprintf(' - %s till %s for: ', $time['starttime'], $time['endtime']);
    
    echo implode(', ', $time['affects']);
    
    echo PHP_EOL;
}
