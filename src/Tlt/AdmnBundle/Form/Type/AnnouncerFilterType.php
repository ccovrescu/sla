<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\SecurityContext;


class AnnouncerFilterType extends AbstractType
{
    private $securityContext;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $em, SecurityContext $securityContext)
    {
        $this->em   = $em;
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('status', ChoiceType::class, array(
                'choices'  => array('1' => 'Activ', '0' => 'Inactiv'),
                'required' => true,
            ));

//        $builder
//            ->add('branch','entity',array(
//                    'class' => 'Tlt\AdmnBundle\Entity\Branch',
//                    'label' => 'Agentie/Centru',
//                    'required' => false,
//                    'empty_value' => 'Toate',
//                    'query_builder'	=>	function (EntityRepository $repository) {
//                                            $qb = $repository->createQueryBuilder('b')
//                                                        ->where('b.id IN (:branches)')
//                                                        ->setParameter('branches', array_values($this->securityContext->getToken()->getUser()->getBranches()->toArray()))
//                                                        ->orderBy('b.name');
//
//                                                        return $qb;
//                                        },
//                ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data	=	$event->getData();
                $form	=	$event->getForm();

                $accessor	=	PropertyAccess::createPropertyAccessor();
                $branch	=	$accessor->getValue($data, 'branch');

                $this->addBranchForm($form, $branch);
            });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data	=	$event->getData();
                $form	=	$event->getForm();

                $branch_id = array_key_exists('branch', $data) ? $data['branch'] : null;

                $this->addBranchForm($form, $branch_id);
            });

        $builder
            ->add('Arata', SubmitType::class);
    }

    private function addBranchForm($form, $branch_id = null )
    {
        $formOptions = array(
            'class' => 'Tlt\AdmnBundle\Entity\Branch',
            'label' => 'Agentie/Centru',
            'required' => false,
            'empty_value' => 'Toate',
            'query_builder'	=>	function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('b')
                    ->where('b.id IN (:branches)')
                    ->setParameter('branches', array_values($this->securityContext->getToken()->getUser()->getBranches()->toArray()))
                    ->orderBy('b.name');

                return $qb;
            },
        );

        if($branch_id != null)
        {
            $branch = $this->em
                ->getRepository('TltAdmnBundle:Branch')
                ->find($branch_id);

            if ($branch != null)
                $formOptions['data'] = $branch;

        } else {
            $formOptions['data'] = null;
        }

        $form->add('branch', 'entity', $formOptions);
   }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Tlt\AdmnBundle\Entity\AnnouncerFilter',
            ));
    }

    public function getBlockPrefix()
    {
        return 'announcer_filter';
    }
}