<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tlt\AdmnBundle\Form\EventListener\LocationListener;
use Tlt\AdmnBundle\Form\EventListener\BranchListener;
use Tlt\AdmnBundle\Form\EventListener\ServiceListener;
use Tlt\AdmnBundle\Form\EventListener\DepartmentListener;
use Tlt\AdmnBundle\Form\EventListener\OwnerListener;

class FilterType extends AbstractType
{
	private $user;
	
	public function __construct(\Tlt\ProfileBundle\Entity\User $user = null)
	{
		$this->user	=	$user;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        if (array_key_exists('department', $options) && $options['department'])
			$builder->addEventSubscriber(new DepartmentListener( $this->user ));
		if (array_key_exists('service', $options) && $options['service'])
            $builder->addEventSubscriber(new ServiceListener( $this->user ));
		
		if (array_key_exists('zone', $options) && $options['zone'])
			$builder->addEventSubscriber(new BranchListener( $this->user ));
		if (array_key_exists('zoneLocation', $options) && $options['zoneLocation'])
            $builder->addEventSubscriber(new LocationListener());
		if (array_key_exists('owner', $options) && $options['owner'])
			$builder->addEventSubscriber(new OwnerListener( $this->user ));
			
		$builder
			->add('Arata', 'submit');
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(
			array(
				'data_class' => 'Tlt\AdmnBundle\Entity\Filter',
				'validation_groups' => false,
				'zone' => false,
				'zoneLocation' => false,
				'department' => false,
				'service' => false,
				'owner' => false
		));
	}
	
	public function getName()
	{
		return 'filter';
	}
}