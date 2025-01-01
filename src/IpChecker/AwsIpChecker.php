<?php

namespace Lindelius\CheckIp\IpChecker;

use Lindelius\CheckIp\Exception\ClientException;
use Lindelius\CheckIp\IpAddress;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class AwsIpChecker implements IpCheckerInterface
{
    public const ENDPOINT_URL = "https://checkip.amazonaws.com";

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function checkIp(): IpAddress
    {
        $this->logger->debug("Checking the current IP address via AWS...");

        try {
            $response = $this->httpClient->sendRequest(
                $this->requestFactory->createRequest("GET", self::ENDPOINT_URL)
            );

            $stream = $response->getBody();

            // The AWS endpoint should respond with nothing but the IP address
            // and some trailing whitespace.
            $responseBody = trim($stream->getContents());
        } catch (ClientExceptionInterface $exception) {
            throw new ClientException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $exception) {
            throw new ClientException("Unable to check the current IP address via AWS.", 0, $exception);
        } finally {
            if (isset($stream)) {
                $stream->close();
            }
        }

        $this->logger->debug("Received an IP address response from AWS.", [
            "responseCode" => $response->getStatusCode(),
            "responseBody" => $responseBody,
        ]);

        $ipAddress = IpAddress::fromString($responseBody);

        $this->logger->debug("Found the current IP address via AWS.", [
            "value" => $ipAddress->value,
            "type" => $ipAddress->type->value,
        ]);

        return $ipAddress;
    }
}
