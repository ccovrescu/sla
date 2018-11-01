<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\PropertyAccess\PropertyAccess;

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
			->add('Arata', SubmitType::class);
		
		
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
			$data	=	$event->getData();
			$form	=	$event->getForm();
			
			$accessor	=	PropertyAccess::createPropertyAccessor();
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
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\FilterDS',
		));		
	}
	
	public function getBlockPrefix()
	{
		return 'filterds';
	}
}