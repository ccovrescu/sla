<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Tlt\AdmnBundle\Form\EventListener\AddOwnerFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddBranchFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddLocationFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddEquipmentFieldSubscriber;

class EquipmentSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->addEventSubscriber(new AddOwnerFieldSubscriber('equipment'));
		$builder->addEventSubscriber(new AddBranchFieldSubscriber('equipment'));
		$builder->addEventSubscriber(new AddLocationFieldSubscriber('equipment'));
		$builder->addEventSubscriber(new AddEquipmentFieldSubscriber('equipment'));
    }

    public function getName()
    {
        return 'registration';
    }
}