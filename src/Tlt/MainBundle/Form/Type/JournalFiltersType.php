<?php
namespace Tlt\MainBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Security\Core\SecurityContext;

class JournalFiltersType extends AbstractType
{
    /**
     * @param SecurityContext $securityContext
     */
    private $securityContext;

    /**
     * @param ObjectManager $objectManager
     */
    protected $objectManager;

/*    public function __construct(SecurityContext $securityContext, ObjectManager $objectManager)
    {
        $this->securityContext = $securityContext;
        $this->objectManager = $objectManager;
    }
*/
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityContext = $options['securityContext'];
        $this->securityContext = $securityContext;
        $objectManager = $options['objectManager'];
        $this->objectManager = $objectManager;
        $userOwners = $this->securityContext->getToken()->getUser()->getOwners();
        $userDepartments = $this->securityContext->getToken()->getUser()->getDepartments();

        $builder
            ->add('owner', EntityType::class, array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
                    'label'			=>	'Entitatea',
                    'query_builder' => function (EntityRepository $repository) use ($userOwners) {
                        $qb = $repository->createQueryBuilder('ow')
                            ->andWhere('ow.id IN (:userOwners)');
//                            ->setParameter('userOwners', $userOwners->toArray())

                        if (substr($this->securityContext->getToken()->getUser()->getCompartment(), 0, strlen('TEL'))=='TEL') {
                            $qb = $qb->setParameter('userOwners', $userOwners->toArray());
                        } else {
                            $qb->setParameter('userOwners', $this->getEquipmentsOwnerIds());
                        }

                        $qb->orderBy('ow.name', 'ASC');

                        return $qb;
                    },
                )
            )
            ->add('department', EntityType::class, array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'label'			=>	'Tip Serviciu',
                    'placeholder'   => '-- Toate --',
                    'empty_data'    => null,
                    'query_builder' => function (EntityRepository $repository) use ($userDepartments) {
                        $qb = $repository->createQueryBuilder('dp')
                            ->andWhere('dp.id IN (:userDepartments)')
                            ->setParameter('userDepartments', $userDepartments->toArray())
                            ->orderby('dp.name', 'ASC');

                        return $qb;
                    },
                    'required'      => false
                )
            )
            ->add('start', DateType::class, array(
                    'widget' => "choice",
                    'format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016',
                        '2017',
						'2018'
                    ),
                    'label' => 'De la:'
                )
            )
            ->add('end', DateType::class, array(
                    'widget' => "choice",
                    'format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016',
                        '2017',
						'2018'
                    ),
                    'label' => 'Pana la:'
                )
            )
            ->add('Arata', SubmitType::class);
    }

    protected function getEquipmentsOwnerIds()
    {
        $owners = $this->objectManager->getRepository('TltAdmnBundle:Equipment')->createQueryBuilder('e')
            ->select('distinct o.id')
            ->leftJoin('e.zoneLocation', 'z')
            ->leftJoin('z.branch', 'b')
            ->leftJoin('e.service', 's')
            ->leftJoin('s.department', 'd')
            ->leftJoin('e.owner', 'o')
            ->where('b.id IN (:branches)')
            ->andWhere('d.id IN (:departments)')
            ->setParameter('branches',$this->securityContext->getToken()->getUser()->getBranches()->toArray())
            ->setParameter('departments',$this->securityContext->getToken()->getUser()->getDepartments()->toArray());

        return $owners->getQuery()->getResult();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tlt\MainBundle\Form\Model\JournalFilters',
            'securityContext'=>false,
            'objectManager'=>false,
            ))
            ->setRequired(array(
                'objectManager','securityContext',
            ));
    }

    public function getBlockPrefix()
    {
        return 'journal_filters';
    }
}