<?php

namespace Tlt\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', RepeatedType::class, array(
                'type'            => 'password',
                'required'        => true,
                'first_options'   => array('label' => 'Parola'),
                'second_options'  => array('label' => 'Reintroduceti parola'),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\ProfileBundle\Entity\User',
                'intention'  => 'reset',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'tlt_profile_reset';
    }
}