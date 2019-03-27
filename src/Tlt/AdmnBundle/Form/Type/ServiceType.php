<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ServiceType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', HiddenType::class)
			->add('name',TextType::class,array(
                'attr' => array('maxlength' => 255),
				'label' => 'Denumirea Serviciului'
				))
			->add('department','Symfony\Bridge\Doctrine\Form\Type\EntityType',array(
				'class' => 'Tlt\AdmnBundle\Entity\Department',
				'choice_label' => 'name',
				'label'		=> 'Departamentul'
				))
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\Service',
		));		
	}
	
	public function getBlockPrefix()
	{
		return 'service';
	}
}