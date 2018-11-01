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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;

class AnnouncerType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('branch', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Branch',
                    'property'		=>	'name',
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
                    'max_length' => 128,
                    'label' => 'Prenume',
                )
            )
            ->add('lastname',
                TextType::class,
                array(
                    'max_length' => 64,
                    'label' => 'Nume',
                )
            )
            ->add('compartment',
                TextType::class,
                array(
                    'max_length' => 128,
                    'label' => 'Compartiment',
                    'required' => false
                )
            )
            ->add('active', ChoiceType::class, array(
                'choices'  => array('0' => 'Inactiv', '1' => 'Activ'),
                    'label' => 'Status'
            ))
            ->add('salveaza', SubmitType::class)
            ->add('reseteaza', ResetType::class, array());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\AdmnBundle\Entity\Announcer',
            ));
    }

    public function getBlockPrefix()
    {
        return 'announcer';
    }
}