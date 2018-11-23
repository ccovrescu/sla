<?php
namespace Tlt\AdmnBundle\Form\EventListener;
 
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
 
class AddBranchFieldSubscriber implements EventSubscriberInterface
{
    private $propertyPathToEquipment;
	private $ownerID;
 
    public function __construct($propertyPathToEquipment, $ownerID = null)
    {
        $this->propertyPathToEquipment = $propertyPathToEquipment;
		$this->ownerID = $ownerID;
    }
 
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }
 
    private function addBranchForm($form, $owner_id, $branch = null)
    {
        $formOptions = array(
            'class'         => 'Tlt\AdmnBundle\Entity\Branch',
            'placeholder'   => 'Alegeti o optiune',
            'label'         => 'Sucursala',
            'attr'          => array(
                'class' => 'branch_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($owner_id) {
                $qb = $repository->createQueryBuilder('branch')
                    ->innerJoin('branch.locations', 'location')
					->innerJoin('location.equipments', 'equipment');
				
				if ($owner_id != null)
                    $qb = $qb->where('equipment.owner = :owner')
						->setParameter('owner', $owner_id)
                ;
 
                return $qb;
            }			
        );
 
		if ($branch) {
            $formOptions['data'] = $branch;
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
		
		$owner_id = null;
		switch ($this->propertyPathToEquipment)
		{
			case 'location':
				$location    = $accessor->getValue($data, $this->propertyPathToEquipment);
				$branch = ($location) ? $location->getBranch() : null;
				break;
			default:
				$equipment    = $accessor->getValue($data, $this->propertyPathToEquipment);
				$location = ($equipment) ? $equipment->getLocation() : null;
				$branch = ($location) ? $location->getBranch() : null;
				
				$owner_id = ($equipment) ? $equipment->getOwner() : null;
				
				if ($this->ownerID)
					$owner_id = $this->ownerID;
					
				break;
		}
 
        $this->addBranchForm($form, $owner_id, $branch);
    }
 
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
		
		
		switch ($this->propertyPathToEquipment)
		{
			case 'location':
				$owner_id = null;
				break;
			default:
				if ($this->ownerID)
					$owner_id = $this->ownerID;
				else
					$owner_id = array_key_exists('owner', $data) ? $data['owner'] : null;
			break;
		}
		
        $this->addBranchForm($form, $owner_id);
    }
}