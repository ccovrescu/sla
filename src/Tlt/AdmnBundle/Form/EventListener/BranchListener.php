<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Tlt\ProfileBundle\Entity\User;

class BranchListener implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var User
     */
	private $user;

    /**
     * @var bool
     */
    private $showAll;

    /**
     * @param ObjectManager $em
     * @param User $user
     * @param bool $showAll
     */
    public function __construct(ObjectManager $em, User $user = null, $showAll = true)
    {
        $this->em = $em;
		$this->user	=	$user;
        $this->showAll = $showAll;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addBranchForm($form, $department_id = null, $service_id = null, $zone_id = null)
    {
        $formOptions = array(
            'class'         => 'TltAdmnBundle:Branch',
            'label'         => 'Agentia/Centrul',
            'attr'          => array(
                'class' => 'branch_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['placeholder'] = '-- Toate --';
        }

        $userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();
		
        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $service_id, $userBranches, $userDepartments) {
													// filtram doar dupa echipamentele active si zonele userului.
													$qb = $repository->createQueryBuilder('br')
													
														->innerJoin('br.zoneLocations', 'zl')
														->innerJoin('zl.equipments', 'eq')
														->innerJoin('eq.service', 'sv')
														->where('eq.isActive = :isActive')
														->andWhere('br.id IN (:userBranches)')
														->andWhere('sv.department IN (:userDepartments)')
														->setParameter('isActive', true)
														->setParameter('userBranches', $userBranches)
														->setParameter('userDepartments', $userDepartments)
														->orderBy('br.name', 'ASC');
													
													if ($service_id)
													{
														$qb->andWhere('eq.service = :service')
															->setParameter('service', $service_id);
													} elseif ($department_id)
													{
														$qb->andWhere('sv.department = :department')
															->setParameter('department', $department_id);
													}
													
													return $qb;
												};
		
 
         if (strlen($zone_id)>0) {
             $zone = $this->em
                 ->getRepository('TltAdmnBundle:Branch')
                 ->find($zone_id);

             if ($zone != null)
                 $formOptions['data'] = $zone;
         } else {
             $formOptions['data'] = null;
         }
 
        $form->add('branch', EntityType::class, $formOptions);
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor = PropertyAccess::createPropertyAccessor();

        $zone_id	=	($accessor->getValue($data, 'branch')) ? $accessor->getValue($data, 'branch')->getId() : null;
        $service_id	=	($accessor->getValue($data, 'service')) ? $accessor->getValue($data, 'service')->getId() : null;
        $department_id	=	($accessor->getValue($data, 'department')) ? $accessor->getValue($data, 'department')->getId() : null;

        $this->addBranchForm($form, $department_id, $service_id, $zone_id);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
		$department_id	= array_key_exists('department', $data) ? $data['department'] : null;
        $service_id		= array_key_exists('service', $data) ? $data['service'] : null;
        $zone_id		= array_key_exists('branch', $data) ? $data['branch'] : null;
		
		$this->addBranchForm($form, $department_id, $service_id, $zone_id);
    }
}