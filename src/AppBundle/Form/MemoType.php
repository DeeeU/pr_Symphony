<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MemoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'タイトル',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'メモのタイトルを入力してください'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => '内容',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'メモの内容を入力してください'
                ]
            ])
            ->add('category', EntityType::class, [
              'class' => Category::class,
              'choice_label' => 'name',
              'placeholder' => 'カテゴリを選択してください',
              'required' => false,
              'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => '保存',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
        // createdAtは自動設定なので除外
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Memo'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_memo';
    }
}
