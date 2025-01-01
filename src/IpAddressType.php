<?php

namespace Lindelius\CheckIp;

enum IpAddressType: string
{
    case IPV4 = "ipv4";
    case IPV6 = "ipv6";
}
