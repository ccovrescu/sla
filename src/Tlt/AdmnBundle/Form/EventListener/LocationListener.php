<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class LocationListener implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $em;

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
        $this->user = $user;
        $this->showAll = $showAll;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
	
    private function addLocationForm($form, $department_id = null, $service_id = null, $zone_id = null, $zoneLocation_id = null )
    {
        $formOptions = array(
						'class'         => 'TltAdmnBundle:ZoneLocation',
						'label'         => 'Locatia',
						'attr'          => array(
							'class' => 'location_selector',
						)
        );

        if ($this->showAll=='view')
        {
            $formOptions['required'] = false;
            $formOptions['empty_value'] = '-- Toate --';
        }

        $formOptions['query_builder']	=	function (EntityRepository $repository) use ($zone_id, $service_id, $department_id) {
												$qb = $repository->createQueryBuilder('zl')
													->innerJoin('zl.location', 'l')
													->innerJoin('zl.equipments', 'eq')
													->where('zl.branch = :branch')
													->andWhere('eq.isActive = :isActive')
													->setParameter('branch', $zone_id)
													->setParameter('isActive', true)
													->orderBy('l.name', 'ASC');
													
												if ($service_id) {
													$qb->andWhere('eq.service = :service')
														->setParameter('service', $service_id);
												} elseif ($department_id) {
													$qb->innerJoin('eq.service', 'sv')
														->andWhere('sv.department = :department')
														->setParameter('department', $department_id);
												}
								 
												return $qb;
											};

        if (strlen($zoneLocation_id)>0) {
            $zoneLocation = $this->em
                ->getRepository('TltAdmnBundle:ZoneLocation')
                ->find($zoneLocation_id);

            if ($zoneLocation != null)
                $formOptions['data'] = $zoneLocation;
        } else {
            $formOptions['data'] = null;
        }

        $form->add('zoneLocation', 'entity', $formOptions);
    }
	
	public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
		
        $accessor	= PropertyAccess::createPropertyAccessor();

        $department_id	=	($accessor->getValue($data, 'department')) ? $accessor->getValue($data, 'department')->getId() : null;
        $service_id	=	($accessor->getValue($data, 'service')) ? $accessor->getValue($data, 'service')->getId() : null;
        $zone_id	=	($accessor->getValue($data, 'branch')) ? $accessor->getValue($data, 'branch')->getId() : null;
        $zoneLocation_id	=	($accessor->getValue($data, 'zoneLocation')) ? $accessor->getValue($data, 'zoneLocation')->getId() : null;
        
        $this->addLocationForm($form, $department_id, $service_id, $zone_id, $zoneLocation_id );
    }
	
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
		$department_id = array_key_exists('department', $data) ? $data['department'] : null;
		$service_id	= array_key_exists('service', $data) ? $data['service'] : null;
        $zone_id	= array_key_exists('branch', $data) ? $data['branch'] : null;
        $zoneLocation_id	= array_key_exists('branch', $data) ? $data['branch'] : null;
 
        $this->addLocationForm($form, $department_id, $service_id, $zone_id, $zoneLocation_id);
    }	
}