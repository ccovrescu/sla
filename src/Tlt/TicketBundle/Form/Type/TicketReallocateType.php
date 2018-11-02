<?php
namespace Tlt\TicketBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

use Tlt\TicketBundle\Entity\TicketReallocate;
use Tlt\ProfileBundle\Entity\User;

class TicketReallocateType extends AbstractType {

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
			'branch', 'entity', array(
				'class'			=>	'Tlt\AdmnBundle\Entity\Branch',
				'property'		=>	'name',
				'empty_value'	=>	'-- Alegeti o optiune --',
				'label'			=>	'Agentia/Centrul:',
				'required'		=>	true

/*                'query_builder' => function (EntityRepository $repository) use ($userBranches) {
                                        $qb = $repository->createQueryBuilder('br')
                                                            ->andWhere('br.id IN (:userBranches)')
                                                            ->setParameter('userBranches', $userBranches)
                                                            ->orderby('br.name', 'ASC');

                                        return $qb;
                                    }*/

                )
        );
		$builder->add('salveaza', 'submit');
		$builder->add('reseteaza', 'reset', array());
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class'	=>	'Tlt\TicketBundle\Entity\TicketAllocation'
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'ticketReallocate';
	}
}