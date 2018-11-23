<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\PropertyAccess\PropertyAccess;

class ServiceToSystemType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add(	'id', HiddenType::class	)
			->add(	'service',
					'Symfony\Bridge\Doctrine\Form\Type\EntityType',
					array(
						'class' => 'Tlt\AdmnBundle\Entity\Service',
						'choice_label' => 'name',
						'label'	=> 'Serviciu',
						'group_by' => 'department.name',
						'required'	 	=>	true
					)
				)
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
			
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
			$data	=	$event->getData();
			$form	=	$event->getForm();
			
			$accessor	=	PropertyAccess::createPropertyAccessor();
			$service	=	$accessor->getValue($data, 'service');
			
			$this->addSystemForm($form, $service, null);
		});
		
		$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
			$data	=	$event->getData();
			$form	=	$event->getForm();
			
			$service_id = array_key_exists('service', $data) ? $data['service'] : null;
			$system_id	= array_key_exists('system', $data) ? $data['system'] : null;
			
			$this->addSystemForm($form, $service_id, $system_id);
		});
	}
	
	private function addSystemForm($form, $service_id = null, $system_id = null )
	{
		$formOptions = array(
			'class' => 'Tlt\AdmnBundle\Entity\System',
			'choice_label' => 'name',
			'label'		=> 'Sistemul',
			'query_builder' => function (EntityRepository $repository) use ($service_id) {
									$qb = $repository->createQueryBuilder('sys')
													->innerJoin('sys.department', 'd')
													->innerJoin('d.services', 'sv')
													->where('sv.id = :service')
													->setParameter('service', $service_id)
													->orderBy('sys.name', 'ASC');
													
									return $qb;
			}
		);
		
		$form->add('system', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', $formOptions);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ServiceToSystem',
		));		
	}
	
	public function getBlockPrefix()
	{
		return 'serviceToSystem';
	}
}