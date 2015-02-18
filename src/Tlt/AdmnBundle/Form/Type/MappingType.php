<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;



class MappingType extends AbstractType
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
			->add('system','entity', array(
				'class' => 'Tlt\AdmnBundle\Entity\System',
				'property' => 'name',
				'query_builder' => function(EntityRepository $er) use ($options){
					return $er->createQueryBuilder('sys')
								->innerJoin('sys.serviceToSystems', 'sts')
								->where('sts.service = :service')
								->setParameter('service', $options['equipment']->getService()->getId());
					},
				'label' => 'Sistemul',
				))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver
			->setDefaults(array(
				'data_class' => 'Tlt\AdmnBundle\Entity\Mapping',
				'equipment' => null
			));
	}
	
	public function getName()
	{
		return 'mapping';
	}	
}