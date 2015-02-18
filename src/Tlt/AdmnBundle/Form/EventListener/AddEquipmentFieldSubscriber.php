<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;
use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Equipment;
 
class AddEquipmentFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToEquipment;
 
    public function __construct($propertyPathToEquipment)
    {
        $this->propertyPathToEquipment = $propertyPathToEquipment;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        );
    }
 
    private function addEquipmentForm($form, $location_id)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Equipment',
            'empty_value'   => 'Alegeti o optiune',
            'label'         => 'Echipament',
            'attr'          => array(
                'class' => 'equipment_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($location_id) {
                $qb = $repository->createQueryBuilder('equipment')
                    ->where('equipment.location = :location')
                    ->setParameter('location', $location_id)
                ;
 
                return $qb;
            }
        );
 
        $form->add($this->propertyPathToEquipment, 'entity', $formOptions);
    }
 
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor    = PropertyAccess::createPropertyAccessor();
 
        $equipment		= $accessor->getValue($data, $this->propertyPathToEquipment);
		$location_id	= ($equipment && $equipment->getLocation()) ? $equipment->getLocation()->getId() : null;
 
        $this->addEquipmentForm($form, $location_id);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $location_id = array_key_exists('location', $data) ? $data['location'] : null;
 
        $this->addEquipmentForm($form, $location_id);
    }
}