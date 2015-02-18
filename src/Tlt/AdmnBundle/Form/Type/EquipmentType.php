<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;


use Tlt\AdmnBundle\Form\EventListener\AddOwnerFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddBranchFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddLocationFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddDepartmentFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddServiceFieldSubscriber;

use Doctrine\ORM\EntityRepository;


class EquipmentType extends AbstractType
{
	private $userBranches;
	private $userDepartments;
	
	public function __construct($userBranches, $userDepartments)
	{
		$this->userBranches = $userBranches;
		$this->userDepartments = $userDepartments;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$branches		=	$this->userBranches;
		$departments	=	$this->userDepartments;
		
		$builder
			->add('id', 'hidden')
			->add('owner', 'entity', array(
										'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
										'property'		=>	'name',
										'empty_value'	=>	'Alegeti o optiune',
										'label'			=>	'Entitate',
										'required'		=>	true
									)
				)
			->add('zoneLocation', 'entity', array(
												'class'			=>	'Tlt\AdmnBundle\Entity\ZoneLocation',
												'property'		=>	'name',
												'empty_value'	=>	'Alegeti o optiune',
												'label'			=>	'Locatie',
												'group_by'		=>	'branch.name',
												'query_builder'	=>	function (EntityRepository $repository) use ($branches) {
																		$qb = $repository->createQueryBuilder('zoneLocation')
																			->where('zoneLocation.branch IN (:branches)')
																			->setParameter('branches', array_values($branches));
																			return $qb;
																	},
												'required'		=>	true
											)
				)
			->add('service', 'entity', array(
											'class' => 'Tlt\AdmnBundle\Entity\Service',
											'property' => 'name',
											'label'	=> 'Serviciu',
											'empty_value'	=> 'Alegeti o optiune',
											'group_by' => 'department.name',
											'query_builder' => function (EntityRepository $repository) use ($departments) {
																	$qb = $repository->createQueryBuilder('service')
																		->where('service.department IN (:departments)')
																		->setParameter('departments', array_values($departments));
																		return $qb;
																},
											'required'	 	=>	true
										)
				)
				->add('name','text',array(
					'max_length' => 64,
					'label' => 'Denumire'
					))
				->add('total','text',array(
					'max_length' => 4,
					'label' => 'Cantitate'
					))
				->add('inPam','checkbox',array(
					'required'	=> false,
					'label' => 'Face parte din PAM?'
					))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
		// $builder->addEventSubscriber(new AddOwnerFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddBranchFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddLocationFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddDepartmentFieldSubscriber('service'));
		// $builder->addEventSubscriber(new AddServiceFieldSubscriber('service'));
	}
		
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\Equipment',
		));
	}
	
	public function getName()
	{
		return 'equipment';
	}
}