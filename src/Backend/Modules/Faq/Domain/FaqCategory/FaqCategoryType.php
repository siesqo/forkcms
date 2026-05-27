<?php

namespace Backend\Modules\Faq\Domain\FaqCategory;

use Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslationType;
use Common\Form\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class FaqCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'translations',
            CollectionType::class,
            [
                'entry_type' => FaqCategoryTranslationType::class,
                'error_bubbling' => false,
                'constraints' => [new Valid()],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FaqCategoryDataTransferObject::class]);
    }
}
