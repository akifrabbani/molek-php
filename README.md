# League Skeleton

[![Latest Version](https://img.shields.io/github/release/akifrabbani/molek.svg?style=flat-square)](https://github.com/akifrabbani/molek/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/akifrabbani/molek/master.svg?style=flat-square)](https://travis-ci.org/akifrabbani/molek)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/akifrabbani/molek.svg?style=flat-square)](https://scrutinizer-ci.com/g/akifrabbani/molek/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/akifrabbani/molek.svg?style=flat-square)](https://scrutinizer-ci.com/g/akifrabbani/molek)
[![Total Downloads](https://img.shields.io/packagist/dt/league/skeleton.svg?style=flat-square)](https://packagist.org/packages/league/skeleton)

**Note:** Replace `skeleton` with the correct package name in the above URLs, then delete this line.

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

## Install

Via Composer

``` bash
$ composer require akifrabbani/molek
```

## Usage

``` php
$start = new DateTime("2019-07-20 08:00:00");
$end = new DateTime("2019-07-20 23:00:00");
$ruleset = ['interval' => ['hour' => 1]];

$molek = new AkifRabbani\Molek($ruleset);
echo "Duration in seconds is " . $molek->duration($start, $end);
```

## Credits

- Akif Rabbani (https://github.com/akifrabbani)
- Mohd Hafizuddin M Marzuki (https://github.com/apih)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
