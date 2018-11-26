<?php
namespace Tlt\TicketBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Tlt\ProfileBundle\Entity\User;
use Tlt\TicketBundle\Entity\TicketReallocate;

class TicketReallocateType extends AbstractType {

    private $user;

/*    public function __construct(User $user = null)
    {
        $this->user	=	$user;
    }
*/

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
	    $user = $options['user'];
	    $this->user = $user;
        $userBranches		=	$this->user->getBranchesIds();

		$builder->add(
			'branch', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class'			=>	'Tlt\AdmnBundle\Entity\Branch',
				'choice_label'		=>	'name',
				'placeholder'	=>	'-- Alegeti o optiune --',
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
		$builder->add('salveaza', SubmitType::class);
		$builder->add('reseteaza', ResetType::class, array());
	}

	/**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class'	=>	'Tlt\TicketBundle\Entity\TicketAllocation',
            'user'=>false
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlockPrefix() {
		return 'ticketReallocate';
	}
}