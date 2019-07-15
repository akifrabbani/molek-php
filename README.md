# Molek

[![Latest Version](https://img.shields.io/github/release/akifrabbani/molek.svg?style=flat-square)](https://github.com/akifrabbani/molek/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/akifrabbani/molek/master.svg?style=flat-square)](https://travis-ci.org/akifrabbani/molek)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/akifrabbani/molek.svg?style=flat-square)](https://scrutinizer-ci.com/g/akifrabbani/molek/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/akifrabbani/molek.svg?style=flat-square)](https://scrutinizer-ci.com/g/akifrabbani/molek)
[![Total Downloads](https://img.shields.io/packagist/dt/league/skeleton.svg?style=flat-square)](https://packagist.org/packages/league/skeleton)

Payment amount calculator based on two dates and ruleset.

## Install

Via Composer

``` bash
$ composer require akifrabbani/molek
```

## Usage

``` php
$start = new DateTime("2019-07-20 08:00:00");
$end = new DateTime("2019-09-20 23:00:00");

// Rule for every hour is RM 1.
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


$molek = new AkifRabbani\Molek\Molek($ruleset);
echo "Price is RM " . $molek->calculate($start, $end);
```

## Credits

- Akif Rabbani (https://github.com/akifrabbani)
- Mohd Hafizuddin M Marzuki (https://github.com/apih)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
