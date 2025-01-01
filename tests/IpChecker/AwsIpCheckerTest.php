<?php

namespace Lindelius\CheckIp\Tests\IpChecker;

use GuzzleHttp\Exception\TransferException;
use Lindelius\CheckIp\Exception\ClientException;
use Lindelius\CheckIp\Exception\InvalidIpException;
use Lindelius\CheckIp\IpAddressType;
use Lindelius\CheckIp\IpChecker\AwsIpChecker;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

final class AwsIpCheckerTest extends TestCase
{
    public function testCheckIp(): void
    {
        $value = "2001:db8:3333:4444:5555:6666:1.2.3.4";
        $type = IpAddressType::IPV6;

        // Set up required mocks
        $psrRequest = $this->createMock(RequestInterface::class);

        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $psrRequestFactory->expects($this->once())->method("createRequest")
            ->with("GET", AwsIpChecker::ENDPOINT_URL)
            ->willReturn($psrRequest);

        $psrResponseStream = $this->createMock(StreamInterface::class);
        $psrResponseStream->expects($this->once())->method("getContents")
            ->willReturn("{$value}\n");

        $psrResponse = $this->createMock(ResponseInterface::class);
        $psrResponse->expects($this->once())->method("getBody")
            ->willReturn($psrResponseStream);

        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())->method("sendRequest")
            ->with($psrRequest)
            ->willReturn($psrResponse);

        // Run assertions for the IpCheckerInterface implementation
        $ipAddress = (new AwsIpChecker($psrClient, $psrRequestFactory))->checkIp();

        $this->assertSame($value, $ipAddress->value);
        $this->assertSame($type, $ipAddress->type);
    }

    public function testCheckIpWithInvalidResponse(): void
    {
        $this->expectException(InvalidIpException::class);

        // Set up required mocks
        $psrRequest = $this->createMock(RequestInterface::class);

        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $psrRequestFactory->expects($this->once())->method("createRequest")
            ->with("GET", AwsIpChecker::ENDPOINT_URL)
            ->willReturn($psrRequest);

        $psrResponseStream = $this->createMock(StreamInterface::class);
        $psrResponseStream->expects($this->once())->method("getContents")
            ->willReturn("ERROR\n");

        $psrResponse = $this->createMock(ResponseInterface::class);
        $psrResponse->expects($this->once())->method("getBody")
            ->willReturn($psrResponseStream);

        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())->method("sendRequest")
            ->with($psrRequest)
            ->willReturn($psrResponse);

        // Run assertions for the IpCheckerInterface implementation
        (new AwsIpChecker($psrClient, $psrRequestFactory))->checkIp();
    }

    /** @dataProvider provideCheckIpWithClientException */
    public function testCheckIpWithClientException(Throwable $exception, string $expectedException, string $expectedMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        // Set up required mocks
        $psrRequest = $this->createMock(RequestInterface::class);

        $psrRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $psrRequestFactory->expects($this->once())->method("createRequest")
            ->with("GET", AwsIpChecker::ENDPOINT_URL)
            ->willReturn($psrRequest);

        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())->method("sendRequest")
            ->with($psrRequest)
            ->willThrowException($exception);

        // Run assertions for the IpCheckerInterface implementation
        (new AwsIpChecker($psrClient, $psrRequestFactory))->checkIp();
    }

    public static function provideCheckIpWithClientException(): array
    {
        return [
            "PSR client exception" => [
                new TransferException("The PSR request timed out."),
                ClientException::class,
                "The PSR request timed out.",
            ],
            "Unexpected exception" => [
                new RuntimeException(),
                ClientException::class,
                "Unable to check the current IP address via AWS.",
            ],
        ];
    }
}
