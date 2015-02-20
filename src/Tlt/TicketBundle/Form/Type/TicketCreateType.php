<?php
namespace Tlt\TicketBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

use Tlt\ProfileBundle\Entity\User;

class TicketCreateType extends AbstractType {

    private $user;

    public function __construct(User $user = null)
    {
        $this->user	=	$user;
    }

    /**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
        $userBranches		=	$this->user->getBranchesIds();

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
                'class'         => 'TltAdmnBundle:Branch',
                'required'		=>	true,
                'empty_value'   => '-- Alegeti o optiune --',
                'label'         => 'Agentia/Centrul:',
                'query_builder' => function (EntityRepository $repository) use ($userBranches) {
                    $qb = $repository->createQueryBuilder('br')
                        ->andWhere('br.id IN (:userBranches)')
                        ->setParameter('userBranches', $userBranches)
                        ->orderby('br.name', 'ASC');

                    return $qb;
                }
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