<?php

/**
*
* Perf test between for and foreach
* @see phptester.net
*/


$elements = [];

////
// An array of 10,000 elements with random string values
////
for($i = 0; $i < 10000; $i++) {
       $elements[] = (string)rand(10000000, 99999999);
}



////
// for test with count outside
////

$time_start = microtime(true);
$len = count($elements);
for($i = 0; $i < $len; $i++) { }

$time_end = microtime(true);
$for_time_woc = $time_end - $time_start;

////
// for test with count inside
////

$time_start = microtime(true);
for($i = 0; $i < count($elements); $i++) { }

$time_end = microtime(true);
$for_time_wc = $time_end - $time_start;


////
// foreach test
////
$time_start = microtime(true);
foreach($elements as $element) { }

$time_end = microtime(true);
$foreach_time = $time_end - $time_start;

echo "For took (with count outside): " . number_format($for_time_woc * 1000, 3) . "ms\n";
echo "For took (with count inside): " . number_format($for_time_wc * 1000, 3) . "ms\n";
echo "Foreach took: " . number_format($foreach_time * 1000, 3) . "ms\n";
