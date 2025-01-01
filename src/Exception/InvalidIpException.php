<?php

namespace Lindelius\CheckIp\Exception;

use RuntimeException;

class InvalidIpException extends RuntimeException implements IpCheckerException
{
}
