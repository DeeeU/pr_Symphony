<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'カテゴリ名',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'カテゴリ名を入力してください',
                    'maxlength' => 100
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => '説明',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'カテゴリの説明を入力してください（任意）'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => '保存',
                'attr' => [
                    'class' => 'btn btn-primary mt-3'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Category',

            // CSRFプロテクション有効化
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'category_item',

            // バリデーション有効化
            'validation_groups' => ['Default'],
            'allow_extra_fields' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'appbundle_category';
    }
}
