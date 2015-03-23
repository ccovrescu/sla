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

use Tlt\TicketBundle\Form\DataTransformer\TicketMappingToMappingTransformer;

class SpecialType extends AbstractType
{
    private $ticketId;

    public function __construct($ticketId = null)
    {
        $this->ticketId = $ticketId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ticketMappingToMappingTransformer = new TicketMappingToMappingTransformer($options['em'], $this->ticketId);
        $builder->addModelTransformer($ticketMappingToMappingTransformer);
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'mapping';
    }
}