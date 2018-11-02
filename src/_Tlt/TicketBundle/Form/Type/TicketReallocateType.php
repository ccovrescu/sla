<?php
namespace Tlt\TicketBundle\Form\Type;

use Tlt\TicketBundle\Entity\TicketReallocate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketReallocateType extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add(
			'owner', 'entity', array(
				'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
				'property'		=>	'name',
				'empty_value'	=>	'alegeti o optiune',
				'label'			=>	'Noua entitate:',
				'required'		=>	true
			));
		$builder->add('salveaza', 'submit');
		$builder->add('reseteaza', 'reset', array());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class'	=>	'Tlt\TicketBundle\Entity\TicketAllocation'
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'ticketReallocate';
	}
}