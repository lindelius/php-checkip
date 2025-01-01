<?php

namespace Lindelius\CheckIp\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class ClientException extends RuntimeException implements IpCheckerException, ClientExceptionInterface
{
}
