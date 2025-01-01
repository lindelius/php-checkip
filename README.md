# php-checkip

[![CircleCI](https://circleci.com/gh/lindelius/php-checkip.svg?style=shield)](https://circleci.com/gh/lindelius/php-checkip)

A minimal, PSR based library for checking the public IP of the host machine.

## Installation

If you are using Composer, you may install the latest version of this library by running the following command from your project's root folder:

```
composer require lindelius/php-checkip
```

You may also manually download the library by navigating to the "Releases" page and then expanding the "Assets" section of the latest release.

## Usage

The included implementation(s) of [IpCheckerInterface](src/IpChecker/IpCheckerInterface.php) are all PSR based, meaning they all rely on PSR compatible HTTP clients and request factories. One example of such a library is the popular [Guzzle](https://github.com/guzzle/guzzle) library, which also happens to be used in the code snippet below:

```php
use Lindelius\CheckIp\Exception\IpCheckerException;
use Lindelius\CheckIp\IpAddress;
use Lindelius\CheckIp\IpChecker\AwsIpChecker;

$ipChecker = new AwsIpChecker(
    new \GuzzleHttp\Client(["verify" => false]),
    new \GuzzleHttp\Psr7\HttpFactory(),
    // <-- Optional PSR compatible logger
);

try {
    /** @var IpAddress $ipAddress */
    $ipAddress = $ipChecker->checkIp();
    
    echo $ipAddress->value; // A valid IPv4 or IPv6 address
    echo $ipAddress->type->value; // ipv4 | ipv6
} catch (IpCheckerException $ex) {
    // TODO: Handle potential errors
}
```

### Logging

You may optionally include a PSR compatible logger when instantiating an IP checker in order to get access to internal debug information. Please note, though, that this information will only be available if the logger has been configured to include `debug` messages.
