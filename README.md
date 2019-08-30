# Spaceman

Give namespace to unnamespaced lagacy php code.

## Example

```php
<?php

use Koriym\Spaceman\Convert;

require dirname(__DIR__) . '/vendor/autoload.php';

$sourcePath = __DIR__ . '/service/protected/controllers';
(new Convert('application'))($sourcePath);
```
