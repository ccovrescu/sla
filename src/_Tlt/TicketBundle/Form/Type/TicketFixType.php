<?php
namespace Tlt\TicketBundle\Form\Type;

use Tlt\TicketBundle\Entity\TicketFix;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketFixType extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add(
			'obs', 'textarea', array(
				'label' => 'Mod de rezolvare:'
			));
		$builder->add(
			'isReal', 'checkbox', array(
				'label'		=>	'Este REAL:',
				'required'	=>	false
			));
			
		if ($options['hasSystems']==false)
			$builder->add(
				'notAffectedReason', 'textarea', array(
					'label' => 'De ce nu a afectat:',
					'required' => true
				));
		
		$builder->add(
			'resolvedAt', 'datetime', array(
				'date_widget' => "single_text",
				'time_widget' => "single_text",
				'label' => 'Rezolvat la:',
				'data' => new \DateTime(),
			));
		$builder->add('salveaza', 'submit');
		$builder->add('reseteaza', 'reset', array());
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => '\Tlt\TicketBundle\Entity\TicketFix',
			'hasSystems' => true
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'ticketFix';
	}
}