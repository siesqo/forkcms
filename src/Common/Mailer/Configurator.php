<?php

namespace Common\Mailer;

use Common\ModulesSettings;
use Symfony\Component\Mailer\Transport\TransportInterface;

class Configurator
{
    /**
     * @var ModulesSettings
     */
    private $modulesSettings;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;
    }

    public function getTransport(): TransportInterface
    {
        return TransportFactory::create(
            (string) $this->modulesSettings->get('Core', 'mailer_type', 'sendmail'),
            $this->modulesSettings->get('Core', 'smtp_server'),
            (int) $this->modulesSettings->get('Core', 'smtp_port', 25),
            $this->modulesSettings->get('Core', 'smtp_username'),
            $this->modulesSettings->get('Core', 'smtp_password'),
            $this->modulesSettings->get('Core', 'smtp_secure_layer')
        );
    }
}
