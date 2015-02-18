<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceAttributeType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', 'hidden')
			->add('service','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Service',
				'property' => 'name',
				'label' => 'Serviciul',
				'group_by' => 'department.name',
				))
			->add('name','text',array(
				'max_length' => 64,
				'label' => 'Denumirea proprietatii'
				))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ServiceAttribute',
		));		
	}
	
	public function getName()
	{
		return 'service_attribute';
	}
}