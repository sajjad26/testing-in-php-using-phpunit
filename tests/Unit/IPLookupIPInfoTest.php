<?php

namespace Tests\Unit;

use App\Domain\Model\IPAddress;
use App\Services\IPLookup\Exceptions\InvalidAuthTokenException;
use App\Services\IPLookup\Exceptions\InvalidRequestException;
use App\Services\IPLookup\IPLookupIPInfo;
use ipinfo\ipinfo\IPinfoException;
use PHPUnit\Framework\TestCase;
use Mockery;

class IPLookupIPInfoTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetIPInformationWithValidToken()
    {
        $ipAddressMock = $this->getMockedIpAddress();
        $ipInfoMock = Mockery::mock('overload:ipinfo\ipinfo\IPinfo');
        $ipInfoMock->shouldReceive('getDetails')
            ->andReturn((object)['city' => 'London']);

        $ipLookup = new IPLookupIPInfo('valid_token');
        $this->assertEquals('London', $ipLookup->getIPInformation($ipAddressMock));
    }

    public function testGetIPInformationWithInvalidToken()
    {
        $this->expectException(InvalidAuthTokenException::class);

        $ipAddressMock = $this->createMock(IPAddress::class);

        $ipLookup = new IPLookupIPInfo(null);
        $ipLookup->getIPInformation($ipAddressMock);
    }

    /**
     * @dataProvider invalidRequestDataProvider
     */
    public function testGetIPInformationThrowsInvalidRequestException($status, $expectedException)
    {
        $this->expectException($expectedException);

        $ipAddressMock = $this->getMockedIpAddress();
        $ipInfoMock = Mockery::mock('overload:ipinfo\ipinfo\IPinfo');
        $ipInfoMock->shouldReceive('getDetails')
            ->andThrow(new IPinfoException(json_encode(['status' => $status])));

        $ipLookup = new IPLookupIPInfo('invalid_token');
        $ipLookup->getIPInformation($ipAddressMock);
    }

    public function invalidRequestDataProvider()
    {
        return [
            'status 406' => [406, InvalidAuthTokenException::class],
            'status 403' => [403, InvalidAuthTokenException::class],
            'status 101' => [101, InvalidAuthTokenException::class],
            'status other' => [500, InvalidRequestException::class],
        ];
    }

    private function getMockedIpAddress(string $ip = '8.8.8.8')
    {
        $ipAddressMock = $this->createMock(IPAddress::class);
        $ipAddressMock->method('getIP')->willReturn($ip);
        return $ipAddressMock;
    }
}
