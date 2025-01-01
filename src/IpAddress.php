<?php

namespace Lindelius\CheckIp;

use Lindelius\CheckIp\Exception\InvalidIpException;

final class IpAddress
{
    public function __construct(
        public readonly string $value,
        public readonly IpAddressType $type,
    ) {
    }

    /**
     * Create an IpAddress from a given string.
     *
     * @param string $value
     * @return IpAddress
     * @throws InvalidIpException
     */
    public static function fromString(string $value): IpAddress
    {
        $value = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);

        if ($value === false) {
            throw new InvalidIpException("The given value is not a valid IP address.");
        }

        return new IpAddress($value, str_contains($value, ":") ? IpAddressType::IPV6 : IpAddressType::IPV4);
    }
}
