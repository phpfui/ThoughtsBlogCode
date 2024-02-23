<?php

$file = $_GET['type'] ?? 'default';

$startMemory = memory_get_usage();
$startTime = microtime(true);

//include '../vendor/autoload.php';
include '../commonbase.php';

$endTime = microtime(true);
$finalMemory = memory_get_usage();

$memoryUsed = $finalMemory - $startMemory;
$microSeconds = $endTime - $startTime;

echo "Composer autoload takes {$memoryUsed} bytes of memory<br>";
echo "and took {$microSeconds} to load<br>";

$csv = \fopen($file . 'Plain.csv', 'a+');

\fputcsv($csv, [0, $memoryUsed, $microSeconds]);
