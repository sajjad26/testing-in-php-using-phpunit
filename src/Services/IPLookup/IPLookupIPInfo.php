<?php

namespace App\Services\IPLookup;

use App\Domain\Model\IPAddress;
use App\Services\IPLookup\Exceptions\InvalidAuthTokenException;
use App\Services\IPLookup\Exceptions\InvalidRequestException;
use ipinfo\ipinfo\IPinfo;
use ipinfo\ipinfo\IPinfoException;

use function json_decode;

class IPLookupIPInfo implements IPLookupInterface
{
    private ?string $accessToken;

    public function __construct(?string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getIPInformation(IPAddress $ipAddress): string
    {
        if (!$this->accessToken) {
            throw InvalidAuthTokenException::NoAuthTokenProvided();
        }

        $client = new IPinfo($this->accessToken);

        try {
            $ipInfo = $client->getDetails($ipAddress->getIP());
        } catch (IPinfoException $exception) {
            $exceptionData = json_decode($exception->getMessage());

            switch ($exceptionData->status ?? 0) {
                case 406: // Access key wrong format
                case 403: // Incorrect Access Key
                case 101: // Documented Invalid Access Key
                    throw InvalidAuthTokenException::InvalidAuthTokenProvided($this->accessToken);
            }

            throw new InvalidRequestException('Invalid IP Info request', 0, $exception);
        }

        return $ipInfo->city;
    }
}
