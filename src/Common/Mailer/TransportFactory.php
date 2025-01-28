<?php

namespace Common\Mailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * This class will create the right mailer transport based on some parameters
 */
class TransportFactory
{
    /**
     * Create The right transport instance based on some settings
     *
     * @param string $type
     * @param string $server
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $encryption
     *
     * @return TransportInterface
     */
    public static function create(
        string $type = 'sendmail',
        string $server = null,
        int $port = 25,
        string $user = null,
        string $pass = null,
        string $encryption = null
    ): TransportInterface {
        if ($type === 'smtp') {
            return self::getSmtpTransport($server, $port, $user, $pass, $encryption);
        }

        return self::getMailTransport();
    }

    private static function getSmtpTransport(
        string $server = null,
        string $port = null,
        string $user = null,
        string $pass = null,
        string $encryption = null
    ): TransportInterface {
        // Create the DSN string
        $dsn = sprintf(
            'smtp://%s:%s@%s:%d',
            urlencode($user),
            urlencode($pass),
            $server,
            $port
        );

        // Add encryption if specified
        if ($encryption) {
            $dsn = sprintf('%s?encryption=%s', $dsn, $encryption);
        }

        // Create the transport from the DSN
        return Transport::fromDsn($dsn);
    }

    private static function getMailTransport(): SendmailTransport
    {
        return new SendmailTransport();
    }
}
