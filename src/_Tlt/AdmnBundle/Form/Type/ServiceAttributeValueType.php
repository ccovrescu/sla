<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Tlt\AdmnBundle\Form\DataTransformer\EquipmentToArrayTransformer;


class ServiceAttributeValueType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', 'hidden')
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
			->add('service_attr','entity', array(
				'class' => 'Tlt\AdmnBundle\Entity\ServiceAttribute',
				'property' => 'name',
				'query_builder' => function(EntityRepository $er) use ($options){
					return $er->createQueryBuilder('sa')
								->where('sa.service=:service')
								->setParameter('service', $options['equipment']->getService());
					},
				'label' => 'Proprietatea',
				))
			->add('value','text',array(
				'max_length' => 64,
				'label' => 'Valoarea proprietatii'
				))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver
			->setDefaults(array(
				'data_class' => 'Tlt\AdmnBundle\Entity\ServiceAttributeValue',
				'equipment' => null
			));
	}
	
	public function getName()
	{
		return 'service_attribute_value';
	}	
}