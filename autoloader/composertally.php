<?php

include 'commonbase.php';

$writer = new \App\Tools\CSV\FileWriter('autoloader.csv', false);
foreach (glob(PUBLIC_ROOT.'/*.csv') as $file)
	{
	$csvReader = new \App\Tools\CSV\FileReader($file, false);
	$basename = str_replace('.csv', '', basename($file));
	$times = [];
	foreach ($csvReader as $row)
		{
		$memory = (int)$row[1];
		$times[] = (float)$row[2];
		}

	sort($times);
	array_pop($times);
	array_pop($times);
	$avg = 0.0;
	foreach ($times as $time)
		{
		$avg += $time;
		}
	$avg /= count($times);
	$row = ['type' => $basename, 'memory' => $memory, 'time' => $avg];
	$writer->outputRow($row);
	}

