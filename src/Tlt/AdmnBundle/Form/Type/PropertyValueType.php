<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Tlt\AdmnBundle\Form\DataTransformer\EquipmentToArrayTransformer;


class PropertyValueType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', HiddenType::class)
			->add('equipment','entity', array(
				'class' => 'Tlt\AdmnBundle\Entity\Equipment',
				'property' => 'name',
				'query_builder' => function(EntityRepository $er) use ($options){
					return $er->createQueryBuilder('e')
								->where('e.id=:id')
								->setParameter('id', $options['equipment']->getId());
					},
				'label' => 'Echipamentul',
				'disabled' => true
				))
			->add('property','entity', array(
				'class' => 'Tlt\AdmnBundle\Entity\Property',
				'property' => 'name',
				'label' => 'Proprietatea',
				))
			->add('value',TextType::class,array(
				'max_length' => 64,
				'label' => 'Valoarea proprietatii'
				))
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver
			->setDefaults(array(
				'data_class' => 'Tlt\AdmnBundle\Entity\PropertyValue',
				'equipment' => null
			));
	}
	
	public function getBlockPrefix()
	{
		return 'property_value';
	}	
}