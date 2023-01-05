<?php

namespace Backend\Modules\Policies\Domain\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Backend\Modules\Policies\Domain\Settings\Command\SaveSettings;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

final class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'companyName',
            TextType::class,
            [
                'label' => 'lbl.CompanyName',
            ]
        );
        $builder->add(
            'streetName',
            TextType::class,
            [
                'label' => 'lbl.StreetName',
            ]
        );
        $builder->add(
            'streetNumber',
            TextType::class,
            [
                'label' => 'lbl.StreetNumber',
            ]
        );
        $builder->add(
            'postalCode',
            IntegerType::class,
            [
                'label' => 'lbl.PostalCode',
            ]
        );
        $builder->add(
            'city',
            TextType::class,
            [
                'label' => 'lbl.City',
            ]
        );
        $builder->add(
            'country',
            CountryType::class,
            [
                'label' => 'lbl.Country',
                'preferred_choices' => ['BE'],
            ]
        );
        $builder->add(
            'telephone',
            TelType::class,
            [
                'label' => 'lbl.Telephone',
            ]
        );
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'lbl.Email',
            ]
        );
        $builder->add(
            'registeredOffice',
            TextType::class,
            [
                'label' => 'lbl.RegisteredOffice',
                'required' => false,
            ]
        );
        $builder->add(
            'streetNameRegisteredOffice',
            TextType::class,
            [
                'label' => 'lbl.StreetName',
                'required' => false,
            ]
        );
        $builder->add(
            'streetNumberRegisteredOffice',
            TextType::class,
            [
                'label' => 'lbl.StreetNumber',
                'required' => false,
            ]
        );
        $builder->add(
            'postalCodeRegisteredOffice',
            IntegerType::class,
            [
                'label' => 'lbl.PostalCode',
                'required' => false,
            ]
        );
        $builder->add(
            'cityRegisteredOffice',
            TextType::class,
            [
                'label' => 'lbl.City',
                'required' => false,
            ]
        );
        $builder->add(
            'countryRegisteredOffice',
            CountryType::class,
            [
                'label' => 'lbl.Country',
                'preferred_choices' => ['BE'],
                'required' => false,
            ]
        );
        $builder->add(
            'enterpriseNumber',
            TextType::class,
            [
                'label' => 'lbl.VATNumber',
            ]
        );
        $builder->add(
            'supervisoryAuthority',
            TextType::class,
            [
                'label' => 'lbl.SupervisoryAuthority',
                'required' => false,
            ]
        );
        $builder->add(
            'district',
            TextType::class,
            [
                'label' => 'lbl.District',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SaveSettings::class]);
    }
}
