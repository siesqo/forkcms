<?php

namespace Common\Tests\Mailer;

use Common\Mailer\Configurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class ConfiguratorTest extends TestCase
{
    public function testGetTransportReturnsSendmailByDefault(): void
    {
        $modulesSettingsMock = $this->getModulesSettingsMock();
        $modulesSettingsMock
            ->method('get')
            ->will($this->returnValue(null));

        $configurator = new Configurator($modulesSettingsMock);

        $this->assertInstanceOf(TransportInterface::class, $configurator->getTransport());
        $this->assertInstanceOf(SendmailTransport::class, $configurator->getTransport());
    }

    public function testGetTransportReturnsSmtpTransport(): void
    {
        $modulesSettingsMock = $this->getModulesSettingsMock();
        $modulesSettingsMock
            ->method('get')
            ->will($this->onConsecutiveCalls(
                'smtp',
                'test.server.com',
                25,
                'test@server.com',
                'testpass',
                null
            ));

        $configurator = new Configurator($modulesSettingsMock);

        $this->assertInstanceOf(TransportInterface::class, $configurator->getTransport());
    }

    private function getModulesSettingsMock(): MockObject
    {
        return $this->createMock('Common\ModulesSettings');
    }
}
