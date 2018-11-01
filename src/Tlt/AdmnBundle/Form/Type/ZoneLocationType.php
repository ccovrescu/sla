<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

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
			->add('id', HiddenType::class)
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
			->add('salveaza', SubmitType::class)
			->add('reseteaza', ResetType::class, array());
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ZoneLocation',
		));		
	}
	
	public function getBlockPrefix()
	{
		return 'zoneLocation';
	}
}