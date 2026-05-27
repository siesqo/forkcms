<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Translation;

use Backend\Core\Engine\Model;
use Backend\Form\Type\MetaType;
use Backend\Modules\Faq\Domain\FaqCategory\Command\CreateFaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\Command\UpdateFaqCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FaqCategoryTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'lbl.Title'])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    /** @var CreateFaqCategory|UpdateFaqCategory $formData */
                    $formData = $event->getForm()->getParent()->getParent()->getData();

                    $event->getForm()->add(
                        'meta',
                        MetaType::class,
                        [
                            'detail_url' => Model::getUrlForBlock('Faq', 'Category'),
                            'base_field_name' => 'title',
                            'generate_url_callback_class' => FaqCategoryTranslationRepository::class,
                            'generate_url_callback_method' => 'self::getUrl',
                            'generate_url_callback_parameters' => [
                                $event->getData()->getLocale(),
                                $formData->hasExistingFaqCategory()
                                    ? $formData->getFaqCategoryEntity()->getId() : null,
                            ],
                        ]
                    );
                }
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FaqCategoryTranslationDataTransferObject::class]);
    }
}
