<?php

namespace App\Services\IPLookup\Exceptions;

use Exception;

class InvalidAuthTokenException extends Exception
{
    public static function NoAuthTokenProvided()
    {
        return new Self("No auth was provided");
    }

    public static function InvalidAuthTokenProvided(string $accessToken)
    {
        return new Self("Invalid access token provided {$accessToken}");
    }
}
