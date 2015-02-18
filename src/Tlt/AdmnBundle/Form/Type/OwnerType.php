<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OwnerType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', 'hidden')
			->add('name','text',array(
				'max_length' => 32,
				'label' => 'Denumirea entitatii'
				))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\Owner',
		));		
	}
	
	public function getName()
	{
		return 'owner';
	}
}