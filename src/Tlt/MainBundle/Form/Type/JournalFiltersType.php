<?php
namespace Tlt\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\ORM\EntityRepository;

class JournalFiltersType extends AbstractType
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
            ->add('owner', 'entity', array(
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
            ->add('department', 'entity', array(
                    'class'			=>	'Tlt\AdmnBundle\Entity\Department',
                    'label'			=>	'Tip Serviciu',
                    'empty_value'   => '-- Toate --',
                    'empty_data'    => null,
                    'query_builder' => function (EntityRepository $repository) use ($userDepartments) {
                        $qb = $repository->createQueryBuilder('dp')
                            ->andWhere('dp.id IN (:userDepartments)')
                            ->setParameter('userDepartments', $userDepartments->toArray())
                            ->orderby('dp.name', 'ASC');

                        return $qb;
                    },
                    'required'      => false
                )
            )
            ->add('start', 'date', array(
                    'widget' => "choice",
                    'format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016'
                    ),
                    'label' => 'De la:'
                )
            )
            ->add('end', 'date', array(
                    'widget' => "choice",
                    'format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016'
                    ),
                    'label' => 'Pana la:'
                )
            )
            ->add('Arata', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\MainBundle\Form\Model\JournalFilters',
            ));
    }

    public function getName()
    {
        return 'journal_filters';
    }
}