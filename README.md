php-curl wrapper
================
Simple curl wrapper with REST methods support:

 - GET
 - HEAD
 - POST
 - PUT
 - PATCH
 - DELETE
 
Requirements
------------
 - PHP 5.4+
 - curl php extension 

Installation
------------

The preferred way to install this wrapper is through [composer](http://getcomposer.org/download/).

```bash
php composer.phar require genxoft/curl "*"
```

or

```bash
composer require genxoft/curl "*"
```

Quick usage
-----------

### Quick get request

```php
require_once __DIR__ . '/vendor/autoload.php';
use genxoft\curl\Curl;

$result = Curl::QuickGet("http://example.com", ["text_param" => "text_value"]);

```

You can see also Curl::QuickPost and Curl::QuickJson quick methods

Basic usage
-----------

### Post request with Json data

Performing post request with Json data and query params

```php
require_once __DIR__ . '/vendor/autoload.php';
use genxoft\curl\Curl;
use genxoft\curl\Request;

$request = new Request("http://example.com");
$request->addGetParam("action", "save")
    ->setJsonBody([
        "name" => "John Smith",
        "age" => 23
    ]);
$curl = new Curl($request);
$response = $curl->post();

if ($response === null) {
    echo $curl->getLastError();
} else {
    if ($response->isSuccess()) {
        echo "Data saved";
    } else {
        echo "HTTP Error: ".$response->getStatusMessage();
    }
}

```

## Donate
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2PURUX2SHUD9E"><img src="https://www.paypalobjects.com/en_US/RU/i/btn/btn_donateCC_LG.gif"></a>

## LICENSE

This curl wrapper is released under the [MIT license](https://github.com/walkor/workerman/blob/master/MIT-LICENSE.txt).