<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 2/26/2015
 * Time: 8:19 AM
 */

namespace Tlt\ProfileBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType {

    private $action;

    public function __construct($action = 'view')
    {
        $this->action = $action;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'label' => 'Utilizator',
                'required' => true,
//                'disabled' => true
            ))
            ->add('status', 'choice',array(
                'choices'  => array(
                    '0' => 'Inactiv',
                    '1' => 'Activ'
                ),
                'required' => true,
                'empty_value' => '-- Alegeti o optiune --'
            ))
            ->add('lastname', 'text', array(
                'label' => 'Nume',
                'required' => true
            ))
            ->add('firstname', 'text', array(
                'label' => 'Prenume',
                'required' => true
            ))
            ->add('compartment', 'text', array(
                'label' => 'Compartiment',
                'required' => true
            ))
            ->add('departments','entity',array(
                'class'     => 'Tlt\AdmnBundle\Entity\Department',
                'property'  => 'name',
                'label'		=> 'Tip serviciu',
                'expanded'  => true,
                'multiple' => true
            ))
            ->add('branches','entity',array(
                'class'     => 'Tlt\AdmnBundle\Entity\Branch',
                'property'  => 'name',
                'label'		=> 'Zona',
                'expanded'  => true,
                'multiple' => true,
//                'read_only' => true
            ))
            ->add('owners','entity',array(
                    'class'     => 'Tlt\AdmnBundle\Entity\Owner',
                    'property'  => 'name',
                    'label'		=> 'Entitatea',
                    'expanded'  => true,
                    'multiple' => true,
//                'read_only' => true
            ))
            ->add('roluri','entity',array(
                'class'     => 'Tlt\ProfileBundle\Entity\Role',
                'property'  => 'name',
                'label'		=> 'Roluri',
                'expanded'  => true,
                'multiple' => true,
//                'read_only' => true
            ))
            ->add('email', 'text', array(
                'label' => 'Adresa de e-mail',
            ))
            ->add('emailNotification', 'choice', array(
                'label' => 'Notificare pe email',
                'choices' => array(
                    '0' => 'Nu',
                    '1' => 'Da'
                ),
                'multiple' => false,
                'expanded' => true,
                'required' => true
            ))
            ->add('salveaza', 'submit')
            ->add('reseteaza', 'reset', array());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tlt\ProfileBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'changePassword';
    }
}