# Spaceman

Dealing with old unnamaspaced code does not seem fun. Something you had to do at work?

<img src="https://user-images.githubusercontent.com/529021/64026400-a6b6f400-cb79-11e9-9fd0-f14dcf424e67.png" width=250>

## Installation

```
composer require koriym/spaceman dev-master --dev
```

## Usage

```php
<?php

use Koriym\Spaceman\Convert;

require dirname(__DIR__) . '/vendors/autoload.php';

$sourcePath = __DIR__ . '/service/protected/controllers';

// Rewrite php file with adding namespace declaration starting `$packageName` on directory basis
$packageName = 'application';
(new Convert($packageName))($sourcePath);
```
