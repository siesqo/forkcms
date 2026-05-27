<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslationType;
use Common\Form\CollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class FaqQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'category',
                EntityType::class,
                [
                    'label' => 'lbl.Category',
                    'class' => FaqCategory::class,
                    'choice_label' => 'getBackendTitle',
                    'choice_translation_domain' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->join('c.translations', 'ct')
                            ->addOrderBy('ct.title', 'ASC');
                    },
                ]
            )
            ->add(
                'hidden',
                CheckboxType::class,
                [
                    'label' => 'lbl.Hidden',
                    'required' => false,
                ]
            )
            ->add(
                'translations',
                CollectionType::class,
                [
                    'entry_type' => FaqQuestionTranslationType::class,
                    'error_bubbling' => false,
                    'constraints' => [new Valid()],
                ]
            );

        if (BackendModel::isModuleInstalled('Tags')) {
            $builder->add(
                'tags',
                TextType::class,
                [
                    'label' => 'lbl.Tags',
                    'required' => false,
                    'attr' => ['class' => 'form-control js-tags-input'],
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FaqQuestionDataTransferObject::class]);
    }
}
