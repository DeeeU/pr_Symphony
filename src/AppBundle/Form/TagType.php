<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('name', TextType::class, [
          'label' => 'タグ名',
          'attr' => [
            'class' => 'form-control',
            'placeholder' => 'タグ名を入力してください',
            'maxlength' => 50
          ]
        ])
        ->add('color', ColorType::class, [
          'label' => '色',
          'attr' => [
            'class' => 'form-control',
          ]
        ])
        ->add('save', SubmitType::class, [
          'label' => '保存',
          'attr' => [
            'class' => 'btn btn-primary mt-3',
          ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setDefaults([
        'data_class' => 'AppBundle\Entity\Tag',
        'csrf_protection' => true,
        'csrf_field_name' => '_token',
        'csrf_token_id'   => 'tag_item',
      ]);
    }

    public function getBlockPrefix()
    {
      return 'appbundle_tag';
    }
}
