# Spaceman

Give namespace to unnamespaced lagacy php code.

<img src="https://user-images.githubusercontent.com/529021/64026400-a6b6f400-cb79-11e9-9fd0-f14dcf424e67.png" width=250>

## Example

```php
<?php

use Koriym\Spaceman\Convert;

require dirname(__DIR__) . '/vendor/autoload.php';

$sourcePath = __DIR__ . '/service/protected/controllers';
(new Convert('application'))($sourcePath);
```
