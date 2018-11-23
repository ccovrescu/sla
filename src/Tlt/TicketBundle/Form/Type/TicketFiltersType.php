<?php
namespace Tlt\TicketBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Security\Core\Security;

class TicketFiltersType extends AbstractType
{
    private $securityContext;

/*    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }
*/
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $securityContext = $options['securityContext'];
        $this->securityContext = $securityContext;

        $builder
            ->add(
                'service_type',
                ChoiceType::class,
                array(
                    'label' => 'Tip serviciu',
                    'choices' => array_replace(
                        array('n/a'=>'0'),
                        $this->securityContext->getToken()->getUser()-> getDepartmentsArray()
                    ),
                    'multiple' => true,
                    'expanded' => true,
                    'choices_as_values'=>true
                )
            )
            ->add(
                'search',
                TextType::class,
                array(
                    'label' => 'Cauta',
                    'required' => false
                )
            )
            ->add(
                'isReal',
                ChoiceType::class,
                array(
                    'label' => 'Status',
                    'choices' => array(
                        'NU este real'=>'1'
                    ),
                    'multiple' => true,
                    'expanded' => true,
                    'choices_as_values'=>true
                )
            )
            ->add(
                'Arata',
                SubmitType::class
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Tlt\TicketBundle\Form\Type\Model\TicketFilters',
                'securityContext'=>false,
            ))
            ->setRequired(array( 'securityContext' ));
    }

    public function getBlockPrefix()
    {
        return 'ticket_filters';
    }
}