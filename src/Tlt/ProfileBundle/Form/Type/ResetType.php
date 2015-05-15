<?php

namespace Tlt\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
                'type'            => 'password',
                'required'        => true,
                'first_options'   => array('label' => 'Parola'),
                'second_options'  => array('label' => 'Reintroduceti parola'),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\ProfileBundle\Entity\User',
                'intention'  => 'reset',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tlt_profile_reset';
    }
}