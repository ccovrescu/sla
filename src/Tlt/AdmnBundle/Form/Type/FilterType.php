<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Tlt\AdmnBundle\Form\EventListener\BranchListener;
use Tlt\AdmnBundle\Form\EventListener\DepartmentListener;
use Tlt\AdmnBundle\Form\EventListener\LocationListener;
use Tlt\AdmnBundle\Form\EventListener\OwnerListener;
use Tlt\AdmnBundle\Form\EventListener\ServiceListener;
use Tlt\AdmnBundle\Form\EventListener\SystemListener;
use Tlt\ProfileBundle\Entity\User;

class FilterType extends AbstractType
{
	private $user;

    /**
     * @var ObjectManager
     */
    private $em;
	
/*	public function __construct(ObjectManager $em, User $user = null)
	{
        $this->em   = $em;
		$this->user	=	$user;
	}
*/
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $user = $options['user'];
        $this->user = $user;
        $em = $options['em'];
        $this->em = $em;


        if (array_key_exists('department', $options) && $options['department'])
			$builder->addEventSubscriber(new DepartmentListener( $this->em, $this->user ));
		if (array_key_exists('service', $options) && $options['service'])
            $builder->addEventSubscriber(new ServiceListener( $this->em, $this->user ));
        if (array_key_exists('system', $options) && $options['system'])
            $builder->addEventSubscriber(new SystemListener( $this->em, $this->user ));
		if (array_key_exists('zone', $options) && $options['zone'])
			$builder->addEventSubscriber(new BranchListener($this->em, $this->user));
		if (array_key_exists('zoneLocation', $options) && $options['zoneLocation'])
            $builder->addEventSubscriber(new LocationListener($this->em));
		if (array_key_exists('owner', $options) && $options['owner'])
			$builder->addEventSubscriber(new OwnerListener( $this->em, $this->user ));
		$builder
			->add('Arata', SubmitType::class);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(
			array(
				'data_class' => 'Tlt\AdmnBundle\Entity\Filter',
				'validation_groups' => false,
				'zone' => false,
				'zoneLocation' => false,
				'department' => false,
				'service' => false,
				'system'=>false,
				'owner' => false,
                'user'=>false,
                'em'=>false,
		));
	}
	
	public function getBlockPrefix()
	{
		return 'filter';
	}
}