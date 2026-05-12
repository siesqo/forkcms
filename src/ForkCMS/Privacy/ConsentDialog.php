<?php

namespace ForkCMS\Privacy;

use Common\Core\Cookie;
use Common\ModulesSettings;

class ConsentDialog
{
    const LEVEL_AD_STORAGE = 'ad_storage';
    const LEVEL_AD_USER_DATA = 'ad_user_data';
    const LEVEL_AD_PERSONALIZATION = 'ad_personalization';
    const LEVEL_ANALYTICS_STORAGE = 'analytics_storage';
    const LEVEL_FUNCTIONALITY_STORAGE = 'functionality_storage';
    const LEVEL_PERSONALIZATION_STORAGE = 'personalization_storage';
    const LEVEL_SECURITY_STORAGE = 'security_storage';

    /**
     * @var ModulesSettings
     */
    private $settings;

    /**
     * @var Cookie
     */
    private $cookie;

    public function __construct(ModulesSettings $settings, Cookie $cookie)
    {
        $this->settings = $settings;
        $this->cookie = $cookie;
    }

    public static function getConsentLevels(): array
    {
        return [
            self::LEVEL_AD_STORAGE,
            self::LEVEL_AD_USER_DATA,
            self::LEVEL_AD_PERSONALIZATION,
            self::LEVEL_ANALYTICS_STORAGE,
            self::LEVEL_FUNCTIONALITY_STORAGE,
            self::LEVEL_PERSONALIZATION_STORAGE,
            self::LEVEL_SECURITY_STORAGE,
        ];
    }

    public function isDialogEnabled(): bool
    {
        return $this->settings->get('Core', 'show_consent_dialog', false);
    }

    public function shouldDialogBeShown(): bool
    {
        if (!$this->settings->get('Core', 'show_consent_dialog', false)) {
            return false;
        }

        if (empty($this->getLevels())) {
            return false;
        }

        if ($this->cookie->get('privacy_consent_hash', '') === $this->getLevelsHash()) {
            return false;
        }

        return true;
    }

    public function getLevels(bool $includeFunctionality = false): array
    {
        $configured = array_filter(
            $this->settings->get('Core', 'privacy_consent_levels', []),
            function (string $level): bool {
                return in_array($level, self::getConsentLevels(), true)
                    && $level !== self::LEVEL_FUNCTIONALITY_STORAGE;
            }
        );

        if ($includeFunctionality) {
            return array_values(array_merge([self::LEVEL_FUNCTIONALITY_STORAGE], $configured));
        }

        return array_values($configured);
    }

    public function getLevelsHash(): string
    {
        $levels = $this->getLevels(true);
        sort($levels);

        return md5(implode('|', $levels));
    }

    public function getVisitorChoices(): array
    {
        $choices = [
            self::LEVEL_FUNCTIONALITY_STORAGE => true,
        ];

        foreach ($this->getLevels(false) as $level) {
            $choices[$level] = $this->cookie->get('privacy_consent_level_' . $level . '_granted', '0') === '1';
        }

        return $choices;
    }

    public function getJsData(): array
    {
        return [
            'possibleLevels' => $this->getLevels(true),
            'levelsHash' => $this->getLevelsHash(),
            'visitorChoices' => $this->getVisitorChoices(),
        ];
    }

    public function hasAgreedTo(string $level): bool
    {
        $choices = $this->getVisitorChoices();
        if (!array_key_exists($level, $choices)) {
            return false;
        }

        return $choices[$level];
    }
}
