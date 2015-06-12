<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityRepository;

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
            ->add('id', 'hidden')
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
                'text',
                array(
                    'max_length' => 128,
                    'label' => 'Prenume',
                )
            )
            ->add('lastname',
                'text',
                array(
                    'max_length' => 64,
                    'label' => 'Nume',
                )
            )
            ->add('compartment',
                'text',
                array(
                    'max_length' => 128,
                    'label' => 'Compartiment',
                )
            )
            ->add('active', 'choice', array(
                'choices'  => array('0' => 'Inactiv', '1' => 'Activ'),
                    'label' => 'Status'
            ))
            ->add('salveaza', 'submit')
            ->add('reseteaza', 'reset', array());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\AdmnBundle\Entity\Announcer',
            ));
    }

    public function getName()
    {
        return 'announcer';
    }
}