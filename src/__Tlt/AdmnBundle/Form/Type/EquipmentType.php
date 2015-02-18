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


class EquipmentType extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', 'hidden')
			->add('owner','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Owner',
				'property' => 'name',
				'empty_value'	=> 'Alegeti o optiune',
				'label'	=> 'Entitatea',
				))
			->add('location','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Location',
				'property' => 'name',
				'empty_value'	=> 'Alegeti o optiune',
				'label' => 'Locatia',
				))
			// ->add('service','entity',array(
				// 'class' => 'Tlt\AdmnBundle\Entity\Service',
				// 'property' => 'name',
				// 'label'	=> 'Serviciul',
				// 'group_by' => 'department.name'
				// ))
				->add('name','text',array(
					'max_length' => 64,
					'label' => 'Denumirea echipamentului'
					))
				->add('total','text',array(
					'max_length' => 4,
					'label' => 'Cantitatea'
					))
				->add('inPam','checkbox',array(
					'required'	=> false,
					'label' => 'Face parte din PAM?'
					))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
		// $builder->addEventSubscriber(new AddOwnerFieldSubscriber('location'));
		$builder->addEventSubscriber(new AddBranchFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddLocationFieldSubscriber('location'));
		$builder->addEventSubscriber(new AddDepartmentFieldSubscriber('service'));
		$builder->addEventSubscriber(new AddServiceFieldSubscriber('service'));
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