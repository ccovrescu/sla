<?php
namespace Tlt\TicketBundle\Form\Type;

use Tlt\TicketBundle\Entity\TicketCreate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketCreateType extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add(
			'occuredAt', 'datetime', array(
				'date_widget' => "single_text",
				'time_widget' => "single_text",
				'label' => 'Aparut la:',
				'data' => new \DateTime(),
			));
		$builder->add(
			'announcedAt', 'datetime', array(
				'date_widget' => "single_text",
				'time_widget' => "single_text",
				'label' => 'Anuntat la:',
				'data' => new \DateTime(),
			));
		$builder->add(
			'announcedBy', 'text', array(
				'max_length' => 128,
				'label' => 'Anuntat de:'			
			));
		$builder->add(
			'ticketAllocations', 'entity', array(
				'class'			=>	'Tlt\AdmnBundle\Entity\Owner',
				'property'		=>	'name',
				'empty_value'	=>	'alegeti o optiune',
				'label'			=>	'Entitatea:',
				'required'		=>	true
			));
		$builder->add(
			'description', 'textarea', array(
				'label' => 'Descriere:',
			));
		$builder->add('salveaza', 'submit');
		$builder->add('reseteaza', 'reset', array());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class'	=>	'Tlt\TicketBundle\Entity\TicketCreate'
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'ticketCreate';
	}
}