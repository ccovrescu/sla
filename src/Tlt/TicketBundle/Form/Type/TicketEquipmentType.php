<?php
namespace Tlt\TicketBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tlt\AdmnBundle\Form\EventListener\LocationListener;
use Tlt\AdmnBundle\Form\EventListener\BranchListener;
use Tlt\AdmnBundle\Form\EventListener\ServiceListener;
use Tlt\AdmnBundle\Form\EventListener\DepartmentListener;
use Tlt\AdmnBundle\Form\EventListener\OwnerListener;
use Tlt\TicketBundle\Form\EventListener\EquipmentListener;
use Tlt\ProfileBundle\Entity\User;


class TicketEquipmentType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var User
     */
    private $user;

    public function __construct(ObjectManager $em, User $user = null)
    {
        $this->em = $em;
        $this->user	=	$user;
    }

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
        if (array_key_exists('department', $options) && $options['department'])
            $builder->addEventSubscriber(new DepartmentListener( $this->em, $this->user, false));
        if (array_key_exists('service', $options) && $options['service'])
            $builder->addEventSubscriber(new ServiceListener( $this->em, $this->user, false));

        if (array_key_exists('zone', $options) && $options['zone'])
            $builder->addEventSubscriber(new BranchListener( $this->em, $this->user, false ));
        if (array_key_exists('zoneLocation', $options) && $options['zoneLocation'])
            $builder->addEventSubscriber(new LocationListener( $this->em, $this->user, false));
        if (array_key_exists('owner', $options) && $options['owner'])
            $builder->addEventSubscriber(new OwnerListener( $this->em, $this->user, false ));
        if (array_key_exists('equipment', $options) && $options['equipment'])
            $builder->addEventSubscriber(new EquipmentListener( $this->user, false ));

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
                'department' => false,
                'service' => false,
                'zone' => false,
                'zoneLocation' => false,
                'owner' => false,
                'equipment' => false
			));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'ticketEquipment';
	}
}