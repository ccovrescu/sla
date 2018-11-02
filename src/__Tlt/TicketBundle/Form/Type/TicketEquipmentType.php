<?php
namespace Tlt\TicketBundle\Form\Type;

use Tlt\TicketBundle\Entity\TicketEquipment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tlt\AdmnBundle\Form\EventListener\AddOwnerFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddBranchFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddLocationFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddDepartmentFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddServiceFieldSubscriber;
use Tlt\AdmnBundle\Form\EventListener\AddEquipmentFieldSubscriber;
use Tlt\TicketBundle\Form\EventListener\AddSystemFieldSubscriber;


class TicketEquipmentType extends AbstractType {

	protected $ownerID;
	
	public function __construct($ownerID = null)
	{
		$this->ownerID = $ownerID;
	}
	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->addEventSubscriber(new AddBranchFieldSubscriber('equipment', $this->ownerID));
		$builder->addEventSubscriber(new AddLocationFieldSubscriber('equipment'));
		$builder->addEventSubscriber(new AddDepartmentFieldSubscriber('equipment'));
		$builder->addEventSubscriber(new AddServiceFieldSubscriber('equipment'));
		$builder->addEventSubscriber(new AddEquipmentFieldSubscriber('equipment'));
		
		$builder->add('salveaza', 'submit');
		$builder->add('reseteaza', 'reset', array());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver
			->setDefaults(array(
				'data_class'	=>	'Tlt\TicketBundle\Entity\TicketEquipment',
			));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'ticketEquipment';
	}
}