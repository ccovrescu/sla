<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tlt\AdmnBundle\Form\EventListener\AddBranchFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddDepartmentFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddLocationFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddOwnerFieldSubscriber;

use Tlt\AdmnBundle\Form\EventListener\AddServiceFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\SystemListener;


class EquipmentType extends AbstractType
{
	private $userBranches;
	private $userDepartments;


/*	public function __construct($userBranches, $userDepartments)
	{
		$this->userBranches = $userBranches;
		$this->userDepartments = $userDepartments;
	}
*/
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $userBranches = $options['branches'];
        $this->userBranches = $userBranches;
        $departments = $options['departments'];

/*        if (isset($userDepartments)) {
            var_dump($userDepartments) ;
            $this->$userDepartments = $userDepartments;
        }
*/
		$branches		=	$this->userBranches;
//		$departments	=	$this->$userDepartments;

//        var_dump($this->$userDepartments) ;
		
		$builder
			->add('id', HiddenType::class)
			->add('owner', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
										'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
										'choice_label'		=>	'name',
										'placeholder'	=>	'Alegeti o optiune',
										'label'			=>	'Entitate',
										'required'		=>	true
									)
				)
			->add('zoneLocation', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
												'class'			=>	'Tlt\AdmnBundle\Entity\ZoneLocation',
												'choice_label'		=>	'name',
												'placeholder'	=>	'Alegeti o optiune',
												'label'			=>	'Locatie',
												'group_by'		=>	'branch.name',
												'query_builder'	=>	function (EntityRepository $repository) use ($branches) {
																		$qb = $repository->createQueryBuilder('zoneLocation')
																			->where('zoneLocation.branch IN (:branches)')
																			->setParameter('branches', array_values($branches));
																			return $qb;
																	},
												'required'		=>	true
											)
				)
			->add('service', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
											'class' => 'Tlt\AdmnBundle\Entity\Service',
											'choice_label' => 'name',
											'label'	=> 'Serviciu',
											'placeholder'	=> 'Alegeti o optiune',
											'group_by' => 'department.name',
											'query_builder' => function (EntityRepository $repository) use ($departments) {
																	$qb = $repository->createQueryBuilder('service')
																		->where('service.department IN (:departments)')
																		->setParameter('departments', array_values($departments));
																		return $qb;
																},
											'required'	 	=>	true,
                                            'choices_as_values'=>true,
										)
				)
// introdus azi 18.07.2018
           ->add('system', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                    'class' => 'Tlt\AdmnBundle\Entity\System',
                    'choice_label' => 'name',
                    'label'	=> 'Sistem',
                    'placeholder'	=> 'Alegeti o optiune',
                    'group_by' => 'department.name',
                    'query_builder' => function (EntityRepository $repository) use ($departments) {
                        $qb = $repository->createQueryBuilder('system')
                            ->where('system.department in (:departments)')
                            ->setParameter('departments', array_values($departments) );
                        return $qb;
                    },
                    'required'	 	=>	true
                )
            )

//     $builder->addEventSubscriber(new SystemEditListener( $this->em, $this->user ));
/*            ->add('system', 'entity', array(
                    'class' => 'Tlt\AdmnBundle\Entity\System',
                    'property' => 'name',
                    'label'	=> 'Sistem implicit',
                    'empty_value'	=> 'Alegeti o optiune',
                    'group_by' => 'department.name',
                    'query_builder' => function (EntityRepository $repository) use ($options) {
                        $qb = $repository->createQueryBuilder('system')
                            ->where('system.id =:systems_id')
                            ->setParameter('systems_id', $options['systems']->getId() );
                        return $qb;
                    },
                    'required'	 	=>	true
                )
            )
*/
/*            ->add('system', 'entity', array(
                    'class' => 'Tlt\AdmnBundle\Entity\System',
                    'property' => 'name',
                    'label'	=> 'Sistem implicit',
                    'empty_value'	=> 'Alegeti o optiune',
                    'group_by' => 'department.name',
                    'query_builder' => function (EntityRepository $repository) use ($options) {
                        $qb = $repository->createQueryBuilder('system')
                            ->where('system.id in (:systems)')
                            ->setParameter('systems', $options['systems']->getId()) ;
                        return $qb;
                    },
                    'required'	 	=>	true
                )
            )
*/
// end introdus azi 18.07.2018
				->add('name',TextType::class,array(
                'attr' => array('maxlength' => 64),
					'label' => 'Denumire'
					))
				->add('total',TextType::class,array(
                    'attr' => array('maxlength' => 4),
					'label' => 'Cantitate'
					))
				->add('inPam',CheckboxType::class,array(
					'required'	=> false,
					'label' => 'Face parte din PAM?'
					))
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
		// $builder->addEventSubscriber(new AddOwnerFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddBranchFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddLocationFieldSubscriber('location'));
		// $builder->addEventSubscriber(new AddDepartmentFieldSubscriber('service'));
		// $builder->addEventSubscriber(new AddServiceFieldSubscriber('service'));
	}
		
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\Equipment',
            'service' => null,
            'systems'=>null,
            'branches' => false,
            'departments'  =>false,
		));
	}
	
	public function getBlockPrefix()
	{
		return 'equipment';
	}
}