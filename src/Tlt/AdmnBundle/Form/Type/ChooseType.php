<?php
namespace Tlt\AdmnBundle\Form\Type;
 
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

// use Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;
// use Symfony\Component\Form\Exception\OutOfBoundsException;

class ChooseType extends AbstractType
{
	private $doctrine;
	// private $tokenStorage;
	
    public function __construct( /*TokenStorageInterface $tokenStorage,*/ RegistryInterface $doctrine)
    {
		// $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
    }
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		// $user = $this->tokenStorage->getToken()->getUser();
		
		if ($options['owner']['available']) {
			$builder
				->add('owner',ChoiceType::class,array(
					'choices' => $this->getOwners((isset($options['owner']['showAll']) ? $options['owner']['showAll'] : null)),
					'required' => 'false',
					'label' => 'Entitatea'
					));
		}

		if ($options['branch']['available']) {
			$builder
				->add('branch',ChoiceType::class,array(
					'choices' => $this->getBranches((isset($options['branch']['showAll']) ? $options['branch']['showAll'] : null)),
					'required' => 'false',
					'label' => 'Sucursala'
					));
		}
		
		if ($options['location']['available']) {
			$builder
				->add('location',ChoiceType::class,array(
					'choices' => array('0' => 'Toate'),
					'required' => 'false',
					'label' => 'Locatia'
					));
		}
		
		if ($options['department']['available']) {
			$builder
				->add('department',ChoiceType::class,array(
					'choices' => $this->getDepartments((isset($options['department']['showAll']) ? $options['department']['showAll'] : null)),
					'required' => 'false',
					'label' => 'Departamentul'
					));
		}
		
		if ($options['service']['available']) {
			$builder
				->add('service',ChoiceType::class,array(
					'choices' => $this->getServices( 0, (isset($options['service']['showAll']) ? $options['service']['showAll'] : null)),
					// 'choices' => array('0' => 'Toate'),
					'required' => 'false',
					'label' => 'Serviciul'
					));
		}
		
		if ($options['equipment']['available']) {
			$builder
				->add('equipment',ChoiceType::class,array(
					'choices' => array('0' => 'Toate'),
					'required' => 'false',
					'label' => 'Echipamentul'
					));
		}
		
		$builder
			->add('Arata', SubmitType::class);
		
		$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
			$form = $event->getForm();
			
			// Locatia
			try {
				if ($form->get('location')) {
					$formOptions = array(
						'choices'         => $this->getLocations($data['branch']),
						'required'   => 'false',
						'label'         => 'Locatia'
					);
			
					$form->remove('location');
					$form->add('location', ChoiceType::class, $formOptions);
				}
			} catch(\Symfony\Component\Form\Exception\OutOfBoundsException $e){
			}
			
			// Serviciul
			try {				
				if ($form->get('service')) {
					$formOptions = array(
						// 'choices'         => $this->getServices($data['department']),
						'choices'         => $this->getServices( $data['department'], (isset($options['service']['showAll']) ? $options['service']['showAll'] : null) ),
						'required'   => 'false',
						'label'         => 'Serviciul'
					);
			
					$form->remove('service');
					$form->add('service', ChoiceType::class, $formOptions);
				}
			} catch(\Symfony\Component\Form\Exception\OutOfBoundsException $e){
				// die ('ChooseType: eroare serviciu');
			}
			
			// Equipment
			try {				
				if ($form->get('equipment')) {
					$formOptions = array(
						'choices'         => $this->getEquipments($data['location'], $data['service']),
						'required'   => 'false',
						'label'         => 'Equipment'
					);
			
					$form->remove('equipment');
					$form->add('equipment', ChoiceType::class, $formOptions);
				}
			} catch(\Symfony\Component\Form\Exception\OutOfBoundsException $e){
			}
		});
	}
	 
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
			array(
				'data_class' => 'Tlt\AdmnBundle\Entity\Choose',
				'validation_groups' => false,
				'owner' => array(
					'available' => false,
					'all' => false
				),
				'branch' => array(
					'available' => false,
					'all' => false
				),
				'location' => array(
					'available' => false,
					'all' => false
				),
				'department' => array(
					'available' => false,
					'all' => false
				),
				'service' => array(
					'available' => false,
					'all' => false
				),
				'equipment' => array(
					'available' => false,
					'all' => false
				)
        ));
    }
 
    public function getBlockPrefix()
    {
        return 'choose';
    }

	private function getOwners($showAll = true)
	{
		$ows = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Owner')->findAll();
		
		if ($showAll)
			$owners = array('0' => 'Toate');
		else
			$owners = array();
		
		foreach ($ows as $ow)
			$owners[$ow->getId()] = $ow->getName();
			
		return $owners;
	}
	
	private function getBranches($showAll = true)
	{
		$acs = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Branch')->findAll();
		
		if ($showAll)
			$agencies_centers = array('0' => 'Toate');
		else
			$agencies_centers = array();
		
		foreach ($acs as $ac)
			$agencies_centers[$ac->getId()] = $ac->getName();
			
		return $agencies_centers;
	}
	
	private function getLocations($branch, $showAll = true)
	{
		// $locs = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Location')->findByBranch($branch);
		$locs = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Location')->findBy(
				array( 'branch' => $branch ),
				array( 'name'	=> 'ASC')
			);
		
		$locations = array('0' => 'Toate');		
		foreach ($locs as $loc)
			$locations[$loc->getId()] = $loc->getName();
			
		return $locations;
	}
	
	
	private function getDepartments($showAll = true)
	{
		$deps = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Department')->findAll();
		
		if ($showAll)
			$departments = array('0' => 'Toate');
		else
			$departments = array();
		
		foreach ($deps as $dep)
			$departments[$dep->getId()] = $dep->getName();
			
		return $departments;
	}
	
	private function getServices($department, $showAll = true)
	{
		if ($showAll)
			$services = array('0' => 'Toate');
		else {
			$services = array();
			
			if (is_null($department) || $department == 0)
				$department = 1;
		}
		
		if ($department > 0) {
		
			// $servs = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Service')->findByDepartment($department );
			$servs = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Service')->findBy(
				array( 'department' => $department ),
				array( 'name'	=> 'ASC')
			);
				
			foreach ($servs as $serv)
				$services[$serv->getId()] = $serv->getName();
		}
			
		return $services;
	}
	
	private function getEquipments($location, $service, $showAll = true)
	{
		$equips = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Equipment')->myFindBy(0, $location, 0, $service);
		// if ($location != 0)
			// if ($service != 0)
				// $equips = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Equipment')->findByLocationAndService($location, $service);
			// else
				// $equips = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Equipment')->findByLocation($location);
		// elseif ($service != 0)
			// $equips = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Equipment')->findByService($service);
		// else
			// $equips = $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\Equipment')->findAll();
		
		$equipments = array('0' => 'Toate');		
		foreach ($equips as $equip)
			$equipments[$equip->getId()] = $equip->getName();
			
		return $equipments;
	}
}