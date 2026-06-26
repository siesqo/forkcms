<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Translation;

use Backend\Core\Engine\Model;
use Backend\Form\Type\MetaType;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\CreateFaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\UpdateFaqQuestion;
use Symfony\Component\Form\AbstractType;
use Backend\Form\Type\EditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FaqQuestionTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, ['label' => 'lbl.Question'])
            ->add(
                'answer',
                EditorType::class,
                [
                    'label' => 'lbl.Answer',
                    'required' => false,
                    'attr' => ['class' => 'inputEditor'],
                ]
            )
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    /** @var CreateFaqQuestion|UpdateFaqQuestion $formData */
                    $formData = $event->getForm()->getParent()->getParent()->getData();

                    $event->getForm()->add(
                        'meta',
                        MetaType::class,
                        [
                            'detail_url' => Model::getUrlForBlock('Faq', 'Detail'),
                            'base_field_name' => 'question',
                            'generate_url_callback_class' => FaqQuestionTranslationRepository::class,
                            'generate_url_callback_method' => 'self::getUrl',
                            'generate_url_callback_parameters' => [
                                $event->getData()->getLocale(),
                                $formData->hasExistingFaqQuestion()
                                    ? $formData->getFaqQuestionEntity()->getId() : null,
                            ],
                        ]
                    );
                }
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FaqQuestionTranslationDataTransferObject::class]);
    }
}
