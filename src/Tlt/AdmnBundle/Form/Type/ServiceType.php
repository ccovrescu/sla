<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', 'hidden')
			->add('name','text',array(
				'max_length' => 255,
				'label' => 'Denumirea echipamentului/utilizatorului'
				))
			->add('department','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Department',
				'property' => 'name',
				'label'		=> 'Departamentul'
				))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\Service',
		));		
	}
	
	public function getName()
	{
		return 'service';
	}
}