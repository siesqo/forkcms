<?php

namespace Backend\Modules\Policies\Domain\Settings\Command;

use Backend\Modules\Policies\Config;
use Common\ModulesSettings;
use Symfony\Component\Validator\Constraints as Assert;

final class SaveSettings
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $companyName;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $streetName;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $streetNumber;

    /**
     * @var int|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $postalCode;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $city;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $country;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $telephone;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $email;

    /**
     * @var string|null
     */
    public $registeredOffice;

    /**
     * @var string|null
     */
    public $streetNameRegisteredOffice;

    /**
     * @var string|null
     */
    public $streetNumberRegisteredOffice;

    /**
     * @var int|null
     */
    public $postalCodeRegisteredOffice;

    /**
     * @var string|null
     */
    public $cityRegisteredOffice;

    /**
     * @var string|null
     */
    public $countryRegisteredOffice;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $enterpriseNumber;

    /**
     * @var string|null
     */
    public $supervisoryAuthority;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $district;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $settings = $modulesSettings->getForModule(Config::MODULE_NAME);

        $this->companyName = $settings['companyName'] ?? null;
        $this->streetName = $settings['streetName'] ?? null;
        $this->streetNumber = $settings['streetNumber'] ?? null;
        $this->postalCode = $settings['postalCode'] ?? null;
        $this->city = $settings['city'] ?? null;
        $this->country = $settings['country'] ?? null;
        $this->telephone = $settings['telephone'] ?? null;
        $this->email = $settings['email'] ?? null;
        $this->registeredOffice = $settings['registeredOffice'] ?? null;
        $this->streetNameRegisteredOffice = $settings['streetNameRegisteredOffice'] ?? null;
        $this->streetNumberRegisteredOffice = $settings['streetNumberRegisteredOffice'] ?? null;
        $this->postalCodeRegisteredOffice = $settings['postalCodeRegisteredOffice'] ?? null;
        $this->cityRegisteredOffice = $settings['cityRegisteredOffice'] ?? null;
        $this->countryRegisteredOffice = $settings['countryRegisteredOffice'] ?? null;
        $this->enterpriseNumber = $settings['enterpriseNumber'] ?? null;
        $this->supervisoryAuthority = $settings['supervisoryAuthority'] ?? null;
        $this->district = $settings['district'] ?? null;
    }
}
