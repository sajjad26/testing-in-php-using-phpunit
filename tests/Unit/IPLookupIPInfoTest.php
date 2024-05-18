<?php

namespace Tests\Unit;

use App\Domain\Model\IPAddress;
use App\Services\IPLookup\Exceptions\InvalidAuthTokenException;
use App\Services\IPLookup\Exceptions\InvalidRequestException;
use App\Services\IPLookup\IPLookupIPInfo;
use ipinfo\ipinfo\IPinfo;
use ipinfo\ipinfo\IPinfoException;
use PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\MockObject\MockObject;

class IPLookupIPInfoTest extends TestCase
{
    public function testGetIPInformationWithValidToken()
    {
        $ipAddressMock = $this->getMockedIpAddress();
        $ipInfoMock = $this->createMock(IPinfo::class);
        $ipInfoMock->method('getDetails')->willReturn((object) ['city' => 'London']);
        $ipLookupIPInfoMock = $this->getIpLookupIPInfoMock($ipInfoMock);

        $this->assertEquals('London', $ipLookupIPInfoMock->getIPInformation($ipAddressMock));
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

        $ipInfoMock = $this->createMock(IPinfo::class);
        $ipInfoMock->method('getDetails')->willThrowException(new IPinfoException(json_encode(['status' => $status])));

        $ipLookupMock = $this->getIpLookupIPInfoMock($ipInfoMock);
        $ipLookupMock->getIPInformation($ipAddressMock);
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

    /**
     * @param string $ip
     * @return IPAddress|MockObject
     */
    private function getMockedIpAddress(string $ip = '8.8.8.8'): IPAddress
    {
        $ipAddressMock = $this->createMock(IPAddress::class);
        $ipAddressMock->method('getIP')->willReturn($ip);
        return $ipAddressMock;
    }

    /**
     * @param IpInfo|MockObject
     * 
     * @return IPLookupIPInfo|MockObject
     */
    private function getIpLookupIPInfoMock($ipInfoMock): IPLookupIPInfo
    {
        /** @var IPLookupIPInfo|MockObject $ipLookupMock */
        $ipLookupMock = $this->getMockBuilder(IPLookupIPInfo::class)
            ->setConstructorArgs(['valid_token'])
            ->onlyMethods(['createIPinfoClient'])
            ->getMock();

        $ipLookupMock->expects($this->once())
            ->method('createIPinfoClient')
            ->willReturn($ipInfoMock);

        return $ipLookupMock;
    }
}
