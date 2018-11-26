<?php

namespace Tlt\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oldPassword', PasswordType::class, array(
            'label' => 'Parola curenta',
            'label_attr' => array(
                'class' => 'control-label'
            )
        ));
        $builder->add('newPassword', RepeatedType::class, array(
            'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
            'invalid_message' => 'Parolele trebuie sa coincida.',
            'required' => true,
            'first_options' => array(
                'label' => 'Noua parola',
                'label_attr' => array(
                    'class' => 'control-label'
                )
            ),
            'second_options' => array('label' => 'Confirmare parola'),
        ));
        $builder->add('salveaza', SubmitType::class);
        $builder->add('reseteaza', ResetType::class, array());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tlt\ProfileBundle\Form\Model\ChangePassword',
        ));
    }

    public function getBlockPrefix()
    {
        return 'change_passwd';
    }
}