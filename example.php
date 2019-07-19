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
// All top rules are optional
// Top rules: base_price, operation_hours, first, normal, max
$ruleset = [
	'base_price' => 0,
	'operation_hours' => [
		'start' => '08:00',
		'end' => '20:00'
	],

	// First rules
	'first' => [
		[
			'duration' => [
				'type' => 'minute',
				'value' => 15
			],
			'prices' => [
				[
					'value' => 0
				]
			]
		],
		[
			'duration' => [
				'type' => 'hour',
				'value' => 1
			],
			// Put the highest priority price first
			// Since the first price that meets the conditions will be selected
			'prices' => [
				[
					'value' => 4,
					'dates' => [
						'2019-07-11'
					]
				],
				[
					'value' => 4,
					'days' => ['sat', 'sun']
				],
				[
					'value' => 2,
					'days' => ['mon', 'tue', 'wed', 'thu', 'fri']
				]
			]
		]
	],

	// Normal rules
	'normal' => [
		[
			'per_block' => true,
			'interval' => [
				'type' => 'hour',
				'value' => 1,
			],
			'prices' => [
				[
					'value' => 4,
					'dates' => [
						'2019-07-11'
					]
				],
				[
					'value' => 4,
					'days' => ['sat', 'sun']
				],
				[
					'value' => 2,
					'days' => ['mon', 'tue', 'wed', 'thu', 'fri']
				]
			]
		]
	],


	// Max rules
	'max' => [
		[
			'duration' => [
				'type' => 'hour',
				'value' => 6,
			],
			'prices' => [
				[
					'value' => 20,
					'dates' => [
						'2019-07-11'
					]
				],
				[
					'value' => 20,
					'days' => ['sat', 'sun']
				],
				[
					'value' => 10,
					'days' => ['mon', 'tue', 'wed', 'thu', 'fri']
				]
			]
		],
		[
			'duration' => [
				'type' => 'hour',
				'value' => 30,
			],
			'prices' => [
				[
					'value' => 100
				]
			]
		]
	]
];

$molek = new Molek();
$molek->setRuleset($ruleset);

$result = $molek->calculate($start_at, $end_at, true);

var_dump($result);

echo 'Ran in ' . number_format(microtime(true) - $bench_time_start, 3) . ' seconds';