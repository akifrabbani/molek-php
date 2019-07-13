<?php
// Run this example in terminal / console
use AkifRabbani\Molek\Molek;

require 'src/Molek.php';

$bench_time_start = microtime(true);

if (!isset($argv[1])) die('Please provide start date!');
if (!isset($argv[2])) die('Please provide end date!');

$start_at = new DateTime($argv[1]);
$end_at = new DateTime($argv[2]);

// Complete ruleset
// All rules are optional
$ruleset = [
	'base_price' => 0,
	'operation_hours' => [
		'start' => '08:00',
		'end' => '20:00'
	],
	'first' => [
		[
			'type' => 'minute',
			'duration' => 15,
			'price' => 0
		],
		[
			'type' => 'hour',
			'duration' => 1,
			'price' => 4,
			'days' => ['sat', 'sun'],
			'dates' => [
				'2019-07-11'
			]
		],
		[
			'type' => 'hour',
			'duration' => 1,
			'price' => 2,
			'days' => ['mon', 'tue', 'wed', 'thu', 'fri']
		]	],
	'normal' => [
		[
			'type' => 'hour',
			'interval' => 1,
			'price' => 1,
			'days' => ['mon', 'tue', 'wed', 'thu', 'fri']
		],
		[
			'type' => 'hour',
			'interval' => 1,
			'price' => 1.5,
			'days' => ['sat', 'sun'],
			'dates' => [
				'2019-07-11'
			]
		]
	],
	'max' => [
		[
			'type' => 'hour',
			'duration' => 8,
			'price' => 10,
			'days' => ['mon', 'tue', 'wed', 'thu', 'fri']
		],
		[
			'type' => 'hour',
			'duration' => 8,
			'price' => 15,
			'days' => ['sat', 'sun'],
			'dates' => [
				'2019-07-11'
			]
		]
	]
];

$molek = new Molek();
$molek->setRuleset($ruleset);

$result = $molek->calculate($start_at, $end_at, true);

var_dump($result);

echo 'Ran in ' . number_format(microtime(true) - $bench_time_start, 3) . ' seconds';