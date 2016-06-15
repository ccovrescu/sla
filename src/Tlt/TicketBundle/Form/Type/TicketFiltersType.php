<?php
namespace Tlt\TicketBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\ORM\EntityRepository;

class TicketFiltersType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'service_type',
                'choice',
                array(
                    'label' => 'Tip serviciu',
                    'choices' => array_replace(
                        array('0'=>'n/a'),
                        $this->securityContext->getToken()->getUser()->getDepartmentsArray()
                    ),
                    'multiple' => true,
                    'expanded' => true,
                )
            )
            ->add(
                'search',
                'text',
                array(
                    'label' => 'Cauta',
                    'required' => false
                )
            )
            ->add(
                'isReal',
                'choice',
                array(
                    'label' => 'Status',
                    'choices' => array(
                        '1' => 'NU este real'
                    ),
                    'multiple' => true,
                    'expanded' => true
                )
            )
            ->add(
                'Arata',
                'submit'
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\TicketBundle\Form\Type\Model\TicketFilters',
            ));
    }

    public function getName()
    {
        return 'ticket_filters';
    }
}