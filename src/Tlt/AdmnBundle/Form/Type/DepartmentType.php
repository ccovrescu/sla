<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', HiddenType::class)
			->add('name',TextType::class,array(
				'max_length' => 255,
				'label' => 'Denumirea departamentului'
				))
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\Department',
		));		
	}
	
	public function getBlockPrefix()
	{
		return 'dep';
	}
}