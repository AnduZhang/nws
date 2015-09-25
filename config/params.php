<?php

return [
    'adminEmail' => 'admin@example.com',
    'projectName' => ' - NREStormTracker',
    'googleAPIKey' =>'AIzaSyB_BgHnfwBhEQVpy9l0y8OBYXvJamzTR9E',
    'timePeriodForRecentPreAlerts' =>12, //In hours
    'timePeriodForRecentPostAlerts' =>162, //In hours
    'postStormDefaultRadius'=>1, // In km
    'alertsPageUpdateTime'=>60000, // In miliseconds
    'enableLsrParser'=>1,
    'AtomFeedParser'=>[
        'enabled'=> 1,
        'url'=>'http://alerts.weather.gov/cap/us.php?x=1',
        'filter'=>['hurricane','tornado','flood warning'],
    ],
    'LsrParser'=>[
        'enabled'=>1,
        'filter'=>['hurricane','tornado','freezing rain'],
        'ftpHost' => 'tgftp.nws.noaa.gov',
        'ftpLogin' => 'anonymous',
        'ftpPassword' => '',
        'lsrFilesPath' => '/SL.us008001/DF.c5/DC.textf/DS.lsrnw',
        'lsrUrl' => '',
        'fileNameFormat' =>'/sn.[0-9]{4}.txt/',
        'force'=>1,
        'files'=> [],
    ],
];
