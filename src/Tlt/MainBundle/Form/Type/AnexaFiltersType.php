<?php
namespace Tlt\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Security\Core\SecurityContext;

class AnexaFiltersType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userOwners = $this->securityContext->getToken()->getUser()->getOwners();
        $userDepartments = $this->securityContext->getToken()->getUser()->getDepartments();

        $builder
            ->add('year', ChoiceType::class, array(
                    'choices' => array(
                        '2014' => '2014',
                        '2015' => '2015',
                        '2016' => '2016',
                        '2017' => '2017',
						'2018' => '2018'
                    ),
                    'label' => 'Anul'
                )
            )
            ->add('owner', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
                    'label'			=>	'Entitatea',
                    'empty_value'   => 'Toate',
                    'empty_data'    => null,
                    'query_builder' => function (EntityRepository $repository) use ($userOwners) {
                        $qb = $repository->createQueryBuilder('ow')
                            ->andWhere('ow.id IN (:userOwners)')
                            ->setParameter('userOwners', $userOwners->toArray())
                            ->orderby('ow.name', 'ASC');

                        return $qb;
                    },
                    'required'      => false
                )
            )
            ->add('department', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'label'			=>	'Tip Serviciu',
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
            ->add('Arata', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\MainBundle\Form\Model\AnexaFilters',
            ));
    }

    public function getBlockPrefix()
    {
        return 'branch';
    }
}