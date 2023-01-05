<?php

namespace Backend\Modules\Policies\Domain\Settings\Command;

use Common\ModulesSettings;
use Backend\Modules\Policies\Config;

final class SaveSettingsHandler
{
    /** @var ModulesSettings */
    private $modulesSettings;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;
    }

    public function handle(SaveSettings $saveSettings): void
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
