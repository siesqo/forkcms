<?php

namespace Common\Tests\Mailer;

use Common\Mailer\TransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Tests for our module settings
 */
class TransportFactoryTest extends TestCase
{
    public function testCreatesMailTransportByDefault(): void
    {
        self::assertInstanceOf(
            SendmailTransport::class,
            TransportFactory::create()
        );
    }

    public function testCreatesSmtpTransportIfWanted(): void
    {
        self::assertInstanceOf(
            TransportInterface::class,
            TransportFactory::create('smtp', 'localhost', 25)
        );
    }

    public function testEncryptionCanBeSet(): void
    {
        $transport = TransportFactory::create('smtp', 'localhost', 465, null, null, 'ssl');
        self::assertInstanceOf(TransportInterface::class, $transport);

        $transport = TransportFactory::create('smtp', 'localhost', 587, null, null, 'tls');
        self::assertInstanceOf(TransportInterface::class, $transport);
    }
}
