<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class ZoneLocationType extends AbstractType
{
	private $user;
	
	public function __construct(\Tlt\ProfileBundle\Entity\User $user = null)
	{
		$this->user	=	$user;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$userBranches	=	$this->user->getBranchesIds();
		
		$builder
			->add('id', 'hidden')
			->add('branch','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Branch',
				'property' => 'name',
				'label'		=> 'Agentia/Centrul',
				'query_builder'	=>	function(EntityRepository $er) use ($options, $userBranches){
					return $er->createQueryBuilder('br')
								->where('br.id IN (:userBranches)')
								->setParameter('userBranches', $userBranches)
								->orderBy('br.name', 'ASC');
					}
				))
			->add('location','entity',array(
				'class' => 'Tlt\AdmnBundle\Entity\Location',
				'property' => 'name',
				'label'		=> 'Locatia',
				'query_builder'	=>	function(EntityRepository $er) use ($options){
					return $er->createQueryBuilder('l')
								->orderBy('l.name', 'ASC');
					}
				))
			->add('salveaza', 'submit')
			->add('reseteaza', 'reset', array());
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ZoneLocation',
		));		
	}
	
	public function getName()
	{
		return 'zoneLocation';
	}
}