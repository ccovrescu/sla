<?php
namespace Tlt\TicketBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Tlt\TicketBundle\Form\DataTransformer\SystemToNumberTransformer;

class TicketSystemType extends AbstractType
{
	protected $equipment;
	
	public function __construct ($equipment = null)
	{
		$this->equipment = $equipment;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$equipment = $this->equipment;
		
		$builder
			->add('system', 'entity', array(
				'label'			=> 'Sistem',
				'empty_value'	=> 'alege o optiune',
				'required'      => true,
				'class'         => 'Tlt\AdmnBundle\Entity\System',
				'property'      => 'name',
				'query_builder' => function(EntityRepository $er) use ($equipment){
					return $er->createQueryBuilder('system')
							->leftJoin('system.mappings', 'mapping')
							->where('mapping.equipment=:equipment')
							->orderBy('system.name', 'ASC')
							->setParameter('equipment', $equipment->getEquipment()->getId());
					}
			))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class'      => '\Tlt\TicketBundle\Entity\TicketSystem',
		));		
	}
	
	public function getName()
	{
		return 'ticketSystem';
	}
}