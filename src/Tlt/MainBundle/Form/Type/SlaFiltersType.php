<?php
namespace Tlt\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\SecurityContext;

class SlaFiltersType extends AbstractType
{
    private $securityContext;


/*    public function __construct(SecurityContext $securityContext)
    {
		//echo "<script>alert('claudiu CONSTRUCT SlaFilters')</script>";

        $this->securityContext = $securityContext;
    }
*/
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityContext = $options['securityContext'];
        $this->securityContext = $securityContext;

        $userOwners = $this->securityContext->getToken()->getUser()->getOwners();
        $userDepartments = $this->securityContext->getToken()->getUser()->getDepartments();

        $builder
            ->add('owner', EntityType::class, array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
                    'label'			=>	'Entitatea',
                    'query_builder' => function (EntityRepository $repository) use ($userOwners) {
                        $qb = $repository->createQueryBuilder('ow')
                            ->andWhere('ow.id IN (:userOwners)')
                            ->setParameter('userOwners', $userOwners->toArray())
                            ->orderby('ow.name', 'ASC');

                        return $qb;
                    },
                )
            )
            ->add('department', EntityType::class, array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'label'			=>	'Tip Serviciu',
//                    'placeholder'   => '-- Toate --',
//                    'empty_data'    => null,
                    'query_builder' => function (EntityRepository $repository) use ($userDepartments) {
                        $qb = $repository->createQueryBuilder('dp')
                            ->andWhere('dp.id IN (:userDepartments)')
                            ->setParameter('userDepartments', $userDepartments->toArray())
                            ->orderby('dp.name', 'ASC');

                        return $qb;
                    },
                    'required'      => true
                )
            )
            ->add('start', DateType::class, array(
                    'widget' => "choice",
                    'format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016',
                        '2017',
						'2018',
                        '2019'
                    ),
                    'label' => 'De la:'
                )
            )
            ->add('end', DateType::class, array(
                    'widget' => "choice",
                    'format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016',
                        '2017',
						'2018',
                        '2019'
                    ),
                    'label' => 'Pana la:'
                )
            )
            ->add('is_closed', CheckboxType::class, array(
                    'label' => 'Include si tichete NEINCHISE',
                    'required' => false
                )
            )

            ->add('all_units', CheckboxType::class, array(
                    'label' => 'Listare doar pentru nr unitati # 0',
                    'required' => false
                )
            )
            ->add('Arata', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\MainBundle\Form\Model\SlaFilters',
                'securityContext'=>false,
            ))
            ->setRequired(array( 'securityContext' ));
    }

    public function getBlockPrefix()
    {
        return 'sla_filters';
    }
}