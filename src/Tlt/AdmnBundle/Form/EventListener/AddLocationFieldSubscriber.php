<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Tlt\AdmnBundle\Entity\Branch;
use Tlt\AdmnBundle\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AddLocationFieldSubscriber implements EventSubscriberInterface
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
 
    private function addLocationForm($form, $branch_id, $location = null)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Location',
            'placeholder'   => 'Alegeti o optiune',
            'label'         => 'Locatia',
            'attr'          => array(
                'class' => 'location_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($branch_id) {
                $qb = $repository->createQueryBuilder('location')
                    ->where('location.branch = :branch')
                    ->setParameter('branch', $branch_id)
                ;
 
                return $qb;
            }
        );
		
		if ($location) {
            $formOptions['data'] = $location;
        }
		
        $form->add('location', EntityType::class, $formOptions);
    }
 
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor    = PropertyAccess::createPropertyAccessor();
 
        $equipment	= $accessor->getValue($data, $this->propertyPathToEquipment);
		$location	= ($equipment) ? $equipment->getLocation() : null;
        $branch_id = ($location) ? $location->getLocation()->getBranch()->getId() : null;
 
        $this->addLocationForm($form, $branch_id, $location);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        $branch_id = array_key_exists('branch', $data) ? $data['branch'] : null;
 
        $this->addLocationForm($form, $branch_id);
    }
}