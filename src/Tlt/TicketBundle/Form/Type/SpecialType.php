<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/19/2015
 * Time: 1:45 PM
 */

namespace Tlt\TicketBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tlt\TicketBundle\Form\DataTransformer\TicketMappingToMappingTransformer;

class SpecialType extends AbstractType
{
    private $ticketId;

/*    public function __construct($ticketId = null)
    {
        $this->ticketId = $ticketId;
    }
*/
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ticket_id = $options['tichetul'];
        $this->ticketId = $ticket_id;
//        echo "<script>alert('Aici Special');</script>";
//        echo "<script>alert($this->ticketId);</script>";
        $ticketMappingToMappingTransformer = new TicketMappingToMappingTransformer($options['em'], $this->ticketId);
        $builder->addModelTransformer($ticketMappingToMappingTransformer);
    }

    public function getParent()
    {
        return 'Symfony\Bridge\Doctrine\Form\Type\EntityType';
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver
            ->setDefaults(array(
                'tichetul'=>false,
            ))
            ->setRequired(array(
                'tichetul',
            ));
    }

    public function getBlockPrefix()
    {
        return 'mapping';
    }
}