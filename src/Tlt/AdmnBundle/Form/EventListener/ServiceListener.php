<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Tlt\ProfileBundle\Entity\User;

class ServiceListener implements EventSubscriberInterface
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
        $this->em   = $em;
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
	
    private function addServiceForm($form, $department_id = null, $service_id = null)
    {
		$userBranches		=	$this->user->getBranchesIds();
		$userDepartments	=	$this->user->getDepartmentsIds();
   //     echo "<script>alert($department_id);</script>";

        $formOptions = array(
            'class'         => 'TltAdmnBundle:Service',
            'label'         => 'Serviciul',
            'attr'          => array(
                'class' => 'service_selector',
            )
        );

        if ($this->showAll)
        {
            $formOptions['required'] = false;
            $formOptions['placeholder'] = '-- Toate --';
        }

        $formOptions['query_builder'] = function (EntityRepository $repository) use ($department_id, $userBranches, $userDepartments) {
                $qb = $repository->createQueryBuilder('sv')
										->innerJoin('sv.equipments', 'eq')
										->innerJoin('eq.zoneLocation', 'zl')
										->where('eq.isActive = :isActive')
										->andWhere('zl.branch IN (:userBranches)')
										->andWhere('sv.department IN (:userDepartments)')
										->setParameter('isActive', true)
										->setParameter('userBranches', $userBranches)
										->setParameter('userDepartments', $userDepartments)
										->orderby('sv.name', 'ASC');
										
										// if ($department_id)
											$qb->andWhere('sv.department = :department')
                                                ->setParameter('department', $department_id);

                return $qb;
            };

        if (strlen($service_id)>0) {
            $service = $this->em
                ->getRepository('TltAdmnBundle:Service')
                ->find($service_id);

            if ($service != null)
                $formOptions['data'] = $service;
        } else {
            $formOptions['data'] = null;
        }

        $form->add('service', EntityType::class, $formOptions);
    }
	
	public function preSetData(FormEvent $event)
    {
    //         echo "<script>alert('Preset Data');</script>";
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor	= PropertyAccess::createPropertyAccessor();
        $department_id	=	($accessor->getValue($data, 'department')) ? $accessor->getValue($data, 'department')->getId() : null;
        $service_id	=	($accessor->getValue($data, 'service')) ? $accessor->getValue($data, 'service')->getId() : null;

        $this->addServiceForm($form, $department_id, $service_id);
    }
	
    public function preSubmit(FormEvent $event)
    {
       // echo "<script>alert('PreSUBMIT Data');</script>";

        $data = $event->getData();
        $form = $event->getForm();

        $department_id = array_key_exists('department', $data) ? $data['department'] : null;
        $service_id = array_key_exists('service', $data) ? $data['service'] : null;

        $this->addServiceForm($form, $department_id, $service_id);
    }
}