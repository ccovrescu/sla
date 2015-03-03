<?php

namespace Tlt\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('oldPassword', 'password', array(
            'label' => 'Parola curenta',
            'label_attr' => array(
                'class' => 'control-label'
            )
        ));
        $builder->add('newPassword', 'repeated', array(
            'type' => 'password',
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
        $builder->add('salveaza', 'submit');
        $builder->add('reseteaza', 'reset', array());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tlt\ProfileBundle\Form\Model\ChangePassword',
        ));
    }

    public function getName()
    {
        return 'change_passwd';
    }
}