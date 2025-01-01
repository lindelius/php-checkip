<?php

namespace Lindelius\CheckIp\Tests;

use Lindelius\CheckIp\Exception\InvalidIpException;
use Lindelius\CheckIp\IpAddress;
use Lindelius\CheckIp\IpAddressType;
use PHPUnit\Framework\TestCase;

final class IpAddressTest extends TestCase
{
    /** @dataProvider provideFromString */
    public function testFromString(string $value, ?IpAddress $expected): void
    {
        if ($expected === null) {
            $this->expectException(InvalidIpException::class);
        }

        $ipAddress = IpAddress::fromString($value);
        $this->assertEquals($ipAddress, $expected);
    }

    public static function provideFromString(): array
    {
        return [

            // Invalid addresses
            "Empty string" => ["", null],
            "Only whitespace" => [" ", null],
            "Missing IPv4 segment" => ["..2.3.4", null],
            "Out of bounds IPv4 address" => ["256.256.256.256", null],
            "Incorrectly shortened IPv6 address" => [":::", null],

            // Valid IPv4 addresses
            "Example IPv4 address" => ["1.2.3.4", new IpAddress("1.2.3.4", IpAddressType::IPV4)],
            "Lower IPv4 bounds" => ["0.0.0.0", new IpAddress("0.0.0.0", IpAddressType::IPV4)],
            "Upper IPv4 bounds" => ["255.255.255.255", new IpAddress("255.255.255.255", IpAddressType::IPV4)],

            // Valid IPv6 addresses
            "Example IPv6 address" => ["2001:db8:3333:4444:CCCC:DDDD:EEEE:FFFF", new IpAddress("2001:db8:3333:4444:CCCC:DDDD:EEEE:FFFF", IpAddressType::IPV6)],
            "IPv6 dual address" => ["2001:db8:3333:4444:5555:6666:1.2.3.4", new IpAddress("2001:db8:3333:4444:5555:6666:1.2.3.4", IpAddressType::IPV6)],
            "Shortened IPv6 (zeroed beginning)" => ["::1234:5678", new IpAddress("::1234:5678", IpAddressType::IPV6)],
            "Shortened IPv6 (zeroed middle)" => ["2001:db8::1234:5678", new IpAddress("2001:db8::1234:5678", IpAddressType::IPV6)],
            "Shortened IPv6 (zeroed ending)" => ["2001:db8::", new IpAddress("2001:db8::", IpAddressType::IPV6)],
            "Shortened IPv6 (all zeroes)" => ["::", new IpAddress("::", IpAddressType::IPV6)],

        ];
    }

    /** @dataProvider provideJsonSerialize */
    public function testJsonSerialize(IpAddress $ipAddress, string $expected): void
    {
        $this->assertSame($expected, json_encode($ipAddress));
    }

    public static function provideJsonSerialize(): array
    {
        return [
            "IPv4" => [new IpAddress("1.2.3.4", IpAddressType::IPV4), "{\"value\":\"1.2.3.4\",\"type\":\"ipv4\"}"],
            "IPv6" => [new IpAddress("2001:db8::1234:5678", IpAddressType::IPV6), "{\"value\":\"2001:db8::1234:5678\",\"type\":\"ipv6\"}"],
        ];
    }
}
