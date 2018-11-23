<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
 
class AddOwnerFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToEquipment;
 
    public function __construct($propertyPathToEquipment)
    {
        $this->propertyPathToEquipment = $propertyPathToEquipment;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }
 
    private function addOwnerForm($form, $owner = null)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Owner',
			'property'		=>	'name',
			// 'mapped'		=>	false,
            'placeholder'   => 'Alegeti o optiune',
            'label'         => 'Entitatea',
            'attr'          => array(
                'class' => 'owner_selector',
            ),
		);
 
        if ($owner) {
            $formOptions['data'] = $owner;
        }
		
        $form->add('owner', EntityType::class, $formOptions);
    }
 
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
 
        if (null === $data) {
            return;
        }
 
        $accessor = PropertyAccess::createPropertyAccessor();
		
        $equipment	= $accessor->getValue($data, $this->propertyPathToEquipment);
        $owner = ($equipment) ? $equipment->getOwner()->getId() : null;
		
		
		
        $this->addOwnerForm($form, $owner);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
		
        $this->addOwnerForm($form);
    }
}