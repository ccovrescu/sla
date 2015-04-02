<?php
namespace Tlt\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnexaFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', 'choice', array(
                    'choices' => array(
                        '2014' => '2014',
                        '2015' => '2015',
                        '2016' => '2016'
                    ),
                    'label' => 'Anul'
                )
            )
            ->add('owner', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
                    'property'		=>	'name',
                    'label'			=>	'Entitatea',
                    'empty_value'   => 'Toate',
                    'empty_data'    => null,
                    'required'      => false
                )
            )
            ->add('department', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'property'		=>	'name',
                    'label'			=>	'Departamentul',
                    'empty_value'   => 'Toate',
                    'empty_data'    => null,
                    'required'      => false
                )
            )
            ->add('Arata', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\MainBundle\Form\Model\AnexaFilters',
            ));
    }

    public function getName()
    {
        return 'branch';
    }
}