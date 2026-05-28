<?php

namespace Backend\Modules\Policies\Domain\Settings\Command;

use Backend\Modules\Policies\Config;
use Common\ModulesSettings;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SaveSettingsHandler
{
    public function __construct(private readonly ModulesSettings $modulesSettings)
    {
    }

    public function __invoke(SaveSettings $saveSettings): void
    {
        $this->modulesSettings->set(Config::MODULE_NAME, 'companyName', $saveSettings->companyName);
        $this->modulesSettings->set(Config::MODULE_NAME, 'streetName', $saveSettings->streetName);
        $this->modulesSettings->set(Config::MODULE_NAME, 'streetNumber', $saveSettings->streetNumber);
        $this->modulesSettings->set(Config::MODULE_NAME, 'postalCode', $saveSettings->postalCode);
        $this->modulesSettings->set(Config::MODULE_NAME, 'city', $saveSettings->city);
        $this->modulesSettings->set(Config::MODULE_NAME, 'country', $saveSettings->country);
        $this->modulesSettings->set(Config::MODULE_NAME, 'telephone', $saveSettings->telephone);
        $this->modulesSettings->set(Config::MODULE_NAME, 'email', $saveSettings->email);
        $this->modulesSettings->set(Config::MODULE_NAME, 'registeredOffice', $saveSettings->registeredOffice);
        $this->modulesSettings->set(Config::MODULE_NAME, 'streetNameRegisteredOffice', $saveSettings->streetNameRegisteredOffice);
        $this->modulesSettings->set(Config::MODULE_NAME, 'streetNumberRegisteredOffice', $saveSettings->streetNumberRegisteredOffice);
        $this->modulesSettings->set(Config::MODULE_NAME, 'postalCodeRegisteredOffice', $saveSettings->postalCodeRegisteredOffice);
        $this->modulesSettings->set(Config::MODULE_NAME, 'cityRegisteredOffice', $saveSettings->cityRegisteredOffice);
        $this->modulesSettings->set(Config::MODULE_NAME, 'countryRegisteredOffice', $saveSettings->countryRegisteredOffice);
        $this->modulesSettings->set(Config::MODULE_NAME, 'enterpriseNumber', $saveSettings->enterpriseNumber);
        $this->modulesSettings->set(Config::MODULE_NAME, 'supervisoryAuthority', $saveSettings->supervisoryAuthority);
        $this->modulesSettings->set(Config::MODULE_NAME, 'district', $saveSettings->district);
    }
}
