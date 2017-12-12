# IRP Class / GNIB

A simple PHP API extension for retrive available dates from Ireland GNIB / IRP. I transform in [Carbon](http://carbon.nesbot.com) dates after retrive dates from Website.

Very simple to use:

```php
$params = [];
$params['cat'] = 'Work';
$params['sbcat'] = 'All';
$params['typ'] = 'Renewal';

$response = new Irpclass($params);

if($response['success'])
{
    if(!empty($response['results'])) {
        //Results
    }else{
        //empty
    }
}
```

## Installation

### With Composer

```
$ composer require tschope/irp-class
```

```json
{
    "require": {
        "tschope/irp-class": "~1.0"
    }
}
```

```php
<?php
require 'vendor/autoload.php';

use Irpclass;

$params = [];
$params['cat'] = 'Work';
$params['sbcat'] = 'All';
$params['typ'] = 'Renewal';

$response = new Irpclass($params);

if($response['success'])
{
    if(!empty($response['results'])) {
        //Results
    }else{
        //empty
    }
}
```

<a name="install-nocomposer"/>
### Without Composer

Why are you not using [composer](http://getcomposer.org/)? Download [Irpclass.php](https://github.com/tschope/irp-class/blob/master/src/Irpclass.php) from the repo and save the file into your project path somewhere. Don't forgot the dependencies, Carbon and Guzzlehttp.



