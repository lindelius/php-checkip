<?php

namespace Lindelius\CheckIp\IpChecker;

use Lindelius\CheckIp\Exception\IpCheckerException;
use Lindelius\CheckIp\IpAddress;

interface IpCheckerInterface
{
    /**
     * Get the current IP address of the host.
     *
     * @return IpAddress
     * @throws IpCheckerException
     */
    public function checkIp(): IpAddress;
}
