<?php

namespace Backend\Modules\Faq\Domain\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('overviewNumItemsPerCategory', IntegerType::class, ['attr' => ['min' => 1, 'max' => 30]])
            ->add('mostReadNumItems', IntegerType::class, ['attr' => ['min' => 1, 'max' => 20]])
            ->add('relatedNumItems', IntegerType::class, ['attr' => ['min' => 1, 'max' => 10]])
            ->add('allowMultipleCategories', CheckboxType::class, ['required' => false])
            ->add('allowFeedback', CheckboxType::class, ['required' => false])
            ->add('allowOwnQuestion', CheckboxType::class, ['required' => false])
            ->add('sendEmailOnNewFeedback', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SaveSettings::class]);
    }
}
