<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;



class MappingType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('id', HiddenType::class)
			->add('equipment','Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'Tlt\AdmnBundle\Entity\Equipment',
				'choice_label' => 'name',
				'query_builder' => function(EntityRepository $er) use ($options){
					return $er->createQueryBuilder('e')
								->where('e.id=:id')
								->setParameter('id', $options['equipment']->getId());
					},
				'label' => 'Echipamentul',
				'disabled' => true
				))
			->add('system','Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'Tlt\AdmnBundle\Entity\System',
				'choice_label' => 'name',
                'placeholder' => 'Selecteaza un sistem',
				'query_builder' => function(EntityRepository $er) use ($options){
					return $er->createQueryBuilder('sys')
								->innerJoin('sys.serviceToSystems', 'sts')
								->where('sts.service = :service')
								->setParameter('service', $options['equipment']->getService()->getId());
					},
				'label' => 'Sistemul',
				))
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver
			->setDefaults(array(
				'data_class' => 'Tlt\AdmnBundle\Entity\Mapping',
				'equipment' => null
			));
	}
	
	public function getBlockPrefix()
	{
		return 'mapping';
	}	
}