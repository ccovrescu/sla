<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

class ServiceToSystemType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add(	'id', 'hidden'	)
			->add(	'service',
					'entity',
					array(
						'class' => 'Tlt\AdmnBundle\Entity\Service',
						'property' => 'name',
						'label'	=> 'Serviciu',
						'group_by' => 'department.name',
						'required'	 	=>	true
					)
				)
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
			
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
			$data	=	$event->getData();
			$form	=	$event->getForm();
			
			$accessor	=	PropertyAccess::getPropertyAccessor();
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
			'property' => 'name',
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
		
		$form->add('system', 'entity', $formOptions);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ServiceToSystem',
		));		
	}
	
	public function getName()
	{
		return 'serviceToSystem';
	}
}