<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;

class FilterDSType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('department','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Department',
				'property' => 'name',
				'label'		=> 'Departamentul'
				))
			->add('Arata', 'submit');
		
		
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
			$data	=	$event->getData();
			$form	=	$event->getForm();
			
			$accessor	=	PropertyAccess::getPropertyAccessor();
			$department	=	$accessor->getValue($data, 'department');
			
			$this->addServiceForm($form, $department, null);
		});
		
		$builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
			$data	=	$event->getData();
			$form	=	$event->getForm();
			
			$department_id = array_key_exists('department', $data) ? $data['department'] : null;
			$service_id	= array_key_exists('service', $data) ? $data['service'] : null;
			
			$this->addServiceForm($form, $department_id, $service_id);
		});
	}
	
	private function addServiceForm($form, $department_id = null, $service_id = null )
	{
		$formOptions = array(
			'class' => 'Tlt\AdmnBundle\Entity\Service',
			'property' => 'name',
			'label'		=> 'Serviciul',
			'query_builder' => function (EntityRepository $repository) use ($department_id) {
									$qb = $repository->createQueryBuilder('sv')
													->where('sv.department = :department')
													->setParameter('department', $department_id);
													
									return $qb;
			}
		);
		
		$form->add('service', 'entity', $formOptions);
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\FilterDS',
		));		
	}
	
	public function getName()
	{
		return 'filterds';
	}
}