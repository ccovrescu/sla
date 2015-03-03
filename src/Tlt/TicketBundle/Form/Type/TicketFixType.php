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
        $builder->add('type', 'choice', array(
            'label' => 'Tip interventie',
            'empty_value' => '-- Selectati --',
            'choices' => array(
                '1' => 'mentenanta preventiva',
                '2' => 'adaptiva',
                '3' => 'corectiva',
                '4' => 'consumabile',
                '5' => 'diverse'
            ),
            'multiple' => false,
            'expanded' => false,
            'required' => true
        ));
        $builder->add(
			'compartment', 'text', array(
				'label' => 'Compartiment:'
			));
        $builder->add(
            'fixedBy', 'text', array(
                'label' => 'Persoana care rezolva:'
            ));
        $builder->add(
            'obs', 'textarea', array(
                'label' => 'Mod de rezolvare:'
            ));
        $builder->add(
            'resources', 'textarea', array(
                'label' => 'Resurse utilizate:',
                'required' => false
            ));
		$builder->add(
			'isReal', 'choice', array(
				'label' => 'Incidentul este real',
                'empty_value' => '-- Selectati --',
                'choices' => array(
                    '0' => 'Nu',
                    '1' => 'Da'
                ),
                'multiple' => false,
                'expanded' => false,
				'required' => true
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
				'label' => 'Data si ora rezolvarii:',
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