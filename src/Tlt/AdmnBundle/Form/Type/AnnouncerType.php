<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;

class AnnouncerType extends AbstractType
{
    private $securityContext;

/*    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }
*/

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityContext = $options['securityContext'];
        $this->securityContext = $securityContext;
        $builder
            ->add('id', HiddenType::class)
            ->add('branch', EntityType::class, array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Branch',
                    'choice_label'		=>	'name',
                    'label'			=>	'Agentie/Centru',
                    'query_builder'	=>	function (EntityRepository $repository) {
                        $qb = $repository->createQueryBuilder('b')
                            ->where('b.id IN (:branches)')
                            ->setParameter('branches', array_values($this->securityContext->getToken()->getUser()->getBranches()->toArray()))
                            ->orderBy('b.name');
                        return $qb;
                    },
                )
            )
            ->add('firstname',
                TextType::class,
                array(
                    'attr' => array('maxlength' => 128),
                    'label' => 'Prenume',
                )
            )
            ->add('lastname',
                TextType::class,
                array(
                    'attr' => array('maxlength' => 64),
                    'label' => 'Nume',
                )
            )
            ->add('compartment',
                TextType::class,
                array(
                    'attr' => array('maxlength' => 128),
                    'label' => 'Compartiment',
                    'required' => false
                )
            )
            ->add('active', ChoiceType::class, array(
                'choices'  => array('Inactiv'=>'0', 'Activ'=>'1'),
                    'label' => 'Status',
                'choices_as_values'=>true
            ))
            ->add('salveaza', SubmitType::class)
            ->add('reseteaza', ResetType::class, array());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\AdmnBundle\Entity\Announcer',
            'securityContext'=>false,
            ))
            ->setRequired(array( 'securityContext' ));
    }

    public function getBlockPrefix()
    {
        return 'announcer';
    }
}