<?php

namespace Backend\Modules\Policies\Installer;

use Backend\Core\Installer\ModuleInstaller;
use Backend\Modules\Policies\Config;
use Common\ModuleExtraType;

final class Installer extends ModuleInstaller
{
    public function install(): void
    {
        $this->addModule(Config::MODULE_NAME);

        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->configureBackendNavigation();
        $this->configureBackendRights();
        $this->configureFrontendExtras();
    }

    private function configureBackendNavigation(): void
    {
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, $this->getModule(), 'policies/settings');
    }

    private function configureBackendRights(): void
    {
        $this->setModuleRights(1, $this->getModule());
        $this->setActionRights(1, $this->getModule(), 'Settings');
    }

    private function configureFrontendExtras(): void
    {
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'Disclaimer', 'Disclaimer', ['edit_url' => '/private/nl/policies/settings']);
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'PrivacyPolicy', 'PrivacyPolicy', ['edit_url' => '/private/nl/policies/settings']);
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'CookiePolicy', 'CookiePolicy', ['edit_url' => '/private/nl/policies/settings']);
    }
}
