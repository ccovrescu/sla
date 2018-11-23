<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 2/26/2015
 * Time: 8:19 AM
 */

namespace Tlt\ProfileBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType {

    private $action;

    public function __construct($action = 'view')
    {
        $this->action = $action;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'label' => 'Utilizator',
                'required' => true,
//                'disabled' => true
            ))
            ->add('status', ChoiceType::class,array(
                'choices'  => array(
                    'Inactiv'=>'0',
                    'Activ'=>'1'
                ),
                'required' => true,
                'placeholder' => '-- Alegeti o optiune --',
                'choices_as_values'=>true,
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'Nume',
                'required' => true
            ))
            ->add('firstname', TextType::class, array(
                'label' => 'Prenume',
                'required' => true
            ))
            ->add('compartment', TextType::class, array(
                'label' => 'Compartiment',
                'required' => true
            ))
            ->add('departments','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
                'class'     => 'Tlt\AdmnBundle\Entity\Department',
                'choice_label'  => 'name',
                'label'		=> 'Tip serviciu',
                'expanded'  => true,
                'multiple' => true,
            ))
            ->add('branches','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
                'class'     => 'Tlt\AdmnBundle\Entity\Branch',
                    'query_builder' => function(EntityRepository $repository) {
                        return $repository->createQueryBuilder('b')->orderBy('b.name', 'ASC');
                    },
//                'property'  => 'name',
                'label'		=> 'Zona',
                'expanded'  => true,
                'multiple' => true,
//                'read_only' => true
            ))
            ->add('owners','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
                    'class'     => 'Tlt\AdmnBundle\Entity\Owner',
                    'query_builder' => function(EntityRepository $repository) {
                        return $repository->createQueryBuilder('o')->orderBy('o.name', 'ASC');
                    },
//                    'property'  => 'name',
                    'label'		=> 'Entitatea',
                    'expanded'  => true,
                    'multiple' => true,
//                'read_only' => true
            ))
            ->add('roluri','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
                'class'     => 'Tlt\ProfileBundle\Entity\Role',
                'choice_label'  => 'name',
                'label'		=> 'Roluri',
                'expanded'  => true,
                'multiple' => true,
//                'read_only' => true
            ))
            ->add('email', TextType::class, array(
                'label' => 'Adresa de e-mail',
            ))
            ->add('emailNotification', ChoiceType::class, array(
                'label' => 'Notificare pe email',
                'choices' => array(
                    'Nu'=>'0',
                    'Da'=>'1'
                ),
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'choices_as_values'=>true
            ))
            ->add('salveaza', SubmitType::class)
            ->add('reseteaza', ResetType::class, array());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tlt\ProfileBundle\Entity\User',
        ));
    }

    public function getBlockPrefix()
    {
        return 'changePassword';
    }
}