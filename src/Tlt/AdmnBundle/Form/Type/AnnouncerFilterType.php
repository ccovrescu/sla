<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;

class AnnouncerFilterType extends AbstractType
{
    private $securityContext;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $em, SecurityContext $securityContext)
    {
        $this->em   = $em;
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('branch','entity',array(
                    'class' => 'Tlt\AdmnBundle\Entity\Branch',
                    'label' => 'Agentie/Centru',
                    'required' => false,
                    'empty_value' => 'Toate',
                    'query_builder'	=>	function (EntityRepository $repository) {
                                            $qb = $repository->createQueryBuilder('b')
                                                        ->where('b.id IN (:branches)')
                                                        ->setParameter('branches', array_values($this->securityContext->getToken()->getUser()->getBranches()->toArray()))
                                                        ->orderBy('b.name');

                                                        return $qb;
                                        },
                ));

        $builder
            ->add('Arata', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tlt\AdmnBundle\Entity\AnnouncerFilter',
            ));
    }

    public function getName()
    {
        return 'announcer_filter';
    }
}