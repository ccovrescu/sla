<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tlt\AdmnBundle\Entity\System;
 
class AddSystemFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToSystem;
 
    public function __construct($propertyPathToSystem)
    {
        $this->propertyPathToSystem = $propertyPathToSystem;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
 
    private function addSystemForm($form, $equipment_id)
    {
        $formOptions = array(
			'label'	=> false,
            // 'class'         => 'Tlt\AdmnBundle\Entity\System',
            // 'empty_value'   => 'Alegeti o optiune',
            // 'label'         => 'System',
            // 'property'      => 'name',
            // 'property_path' => '[name]', # in square brackets!
            // 'multiple'      => true,
            // 'expanded'      => true,
            // 'attr'          => array(
                // 'class' => 'system_selector',
            // ),
            // 'query_builder' => function (EntityRepository $repository) use ($equipment_id) {
                // $qb = $repository->createQueryBuilder('system')
					// ->leftJoin('system.mappings', 'mapping')
                    // ->where('mapping.equipment = :equipment')
					// ->orderBy('system.name', 'ASC')
                    // ->setParameter('equipment', $equipment_id)
                // ;
 
                // return $qb;
            // }
        );
 
        $form->add($this->propertyPathToSystem, new \Tlt\TicketBundle\Form\Type\TTicketFixSystemType(), $formOptions);
    }
 
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor    = PropertyAccess::createPropertyAccessor();
 
        $system			= $accessor->getValue($data, $this->propertyPathToSystem);
		$equipment_id	= ($system) ? $system->getEquipment()->getId() : null;
 
        $this->addSystemForm($form, $equipment_id);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $equipment_id = array_key_exists('equipment', $data) ? $data['equipment'] : null;
 
        $this->addSystemForm($form, $equipment_id);
    }
}