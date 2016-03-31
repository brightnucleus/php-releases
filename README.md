# Bright Nucleus PHP Releases Database

[![Latest Stable Version](https://poser.pugx.org/brightnucleus/php-releases/v/stable)](https://packagist.org/packages/brightnucleus/php-releases)
[![Total Downloads](https://poser.pugx.org/brightnucleus/php-releases/downloads)](https://packagist.org/packages/brightnucleus/php-releases)
[![Latest Unstable Version](https://poser.pugx.org/brightnucleus/php-releases/v/unstable)](https://packagist.org/packages/brightnucleus/php-releases)
[![License](https://poser.pugx.org/brightnucleus/php-releases/license)](https://packagist.org/packages/brightnucleus/php-releases)

This is a Composer plugin that provides an automated list of PHP releases.

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Contributing](#contributing)
* [License](#license)

## Installation

The only thing you need to do to make this work is adding this package as a dependency to your project:

```BASH
composer require brightnucleus/php-releases
```

## Basic Usage

On each `composer install` or `composer update`, the list of PHP releases will be rebuilt.

Usage is pretty straight-forward. Just use one of the two provided static methods:

```PHP
<?php

$releases = new PHPReleases();

// Check whether a specific version exists.
$exists = $releases->exists( '7.0.0' ); // Returns true.

// Get the release date of a specific version.
$date = $releases->getReleaseDate( '7.0.0' ); // Returns DateTime object for 2015-12-03.

// Get all the release data.
$array = $releases->getAll(); // Returns an array in the format: '<version>' => '<release date>'
```

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

This code is released under the MIT license. For the full copyright and license information, please view the LICENSE file distributed with this source code.
