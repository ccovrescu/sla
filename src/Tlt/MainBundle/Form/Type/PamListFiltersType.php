<?php
namespace Tlt\MainBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use JsonSchema\Constraints\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
                            ->andWhere('ow.id IN (:userOwners)')
                            ->setParameter('userOwners', $this->getEquipmentsOwnerIds())
                            ->orderby('ow.name', 'ASC');

                        return $qb;
                    },
                )
            )
            ->add('department', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'label'			=>	'Tip Serviciu',
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
            ->add('Arata', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\MainBundle\Form\Model\PamListFilters',
            ));
    }

    public function getName()
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