<?php
namespace Tlt\MainBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use JsonSchema\Constraints\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Symfony\Component\Security\Core\SecurityContext;
use Tlt\AdmnBundle\Form\EventListener\ServiceListener;
use Tlt\AdmnBundle\Form\EventListener\SystemListenerPam;

class PamListFiltersType extends AbstractType
{
    /**
     * @param SecurityContext $securityContext
     */
    private $securityContext;

    /**
     * @param ObjectManager $objectManager
     */
    protected $objectManager;

    public function __construct(SecurityContext $securityContext, ObjectManager $objectManager)
    {
        $this->securityContext = $securityContext;
        $this->objectManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userOwners = $this->securityContext->getToken()->getUser()->getOwners();
        $userDepartments = $this->securityContext->getToken()->getUser()->getDepartments();

        $builder
            ->add('owner', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
                    'label'			=>	'Entitatea',
                    'query_builder' => function (EntityRepository $repository) use ($userOwners) {
                        $qb = $repository->createQueryBuilder('ow')
                            ->andWhere('ow.id IN (:userOwners)');

                        if (substr($this->securityContext->getToken()->getUser()->getCompartment(), 0, strlen('TEL'))=='TEL') {
                            $qb = $qb->setParameter('userOwners', $userOwners->toArray());
//                            echo "<script>alert('varianta 1 !!');</script>";
                        } else {
                            $qb->setParameter('userOwners', $this->getEquipmentsOwnerIds());
//                            echo "<script>alert('varianta 2 !!');</script>";
                        }

                        $qb->orderby('ow.name', 'ASC');

                        return $qb;
                    },
                )
            )
            ->add('department', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'label'			=>	'Departament',
                    'query_builder' => function (EntityRepository $repository) use ($userDepartments) {
                        $qb = $repository->createQueryBuilder('dp')
                            ->andWhere('dp.id IN (:userDepartments)')
                            ->setParameter('userDepartments', $userDepartments->toArray())
                            ->orderby('dp.name', 'ASC');

                        return $qb;
                    },
                    'required'      => true
                )
            )
// introdus 19.10.2018 , la cererea dlui Dianu
            ->addEventSubscriber(new ServiceListener( $this->objectManager, $this->securityContext->getToken()->getUser() ))

/*           ->add(	'service',
              'entity',
                array(
                    'class' => 'Tlt\AdmnBundle\Entity\Service',
                    'property' => 'name',
                    'label'	=> 'Serviciu',
                    'group_by' => 'department.name',
                    'required'	 	=>	false,
                    'empty_value'=>'--Toate--',
                )
            )

*/
            ->addEventSubscriber(new SystemListenerPam( $this->objectManager, $this->securityContext->getToken()->getUser() ))
// sfarsit introdus 19.10.2018
            ->add('Arata', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\MainBundle\Form\Model\PamListFilters',
            ));
    }

    public function getBlockPrefix()
    {
        return 'pam_list_filters';
    }

    /**
     *
     */
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
}