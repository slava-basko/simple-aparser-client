Simple A-Parser Client
======================

Requirements
------------
 
- PHP version >= 5.4
- curl

Usage
-----

```php
<?php
require_once 'aparser-api-php-client.php';

$aparser = new Aparser('http://127.0.0.1:9091/API', 'pass');

$aparser->ping();

$aparser->addTask('default', 'default', 'text', array('keyboard','usb keyboard'));

$aparser->getTaskConf($taskUid);

```


License
-------

MIT License.