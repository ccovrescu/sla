<?php
namespace Tlt\TicketBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\Entity;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;
use Tlt\TicketBundle\Form\DataTransformer\AnnouncerToNumberTransformer;
use Tlt\TicketBundle\Form\DataTransformer\EquipmentToNumberTransformer;
use Tlt\TicketBundle\Form\Type\TicketMappingType;

class TicketType extends AbstractType {

    private $securityContext;
    private $entityManager;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // this assumes that the entity manager was passed in as an option
        $entityManager = $options['em'];
        $this->entityManager=$entityManager;

        if ($this->securityContext->isGranted('ROLE_TICKET_INSERT')) {
            $userBranches = $this->securityContext->getToken()->getUser()->getBranchesIds();

            $builder
                ->add(
                'announcedAt', DateTimeType::class, array(
                    'date_widget' => "choice",
                    'time_widget' => "choice",
                    'date_format'=> 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016',
                        '2017',
						'2018'
                    ),
//                    'label' => 'Anuntat la:',
//                    'data' => new \DateTime(),
                ));
            $builder->add(
                'takenBy', TextType::class, array(
                    'max_length' => 128,
                    'label' => 'Preluat sesizarea:',
                    'required' => false
                ));
            $builder->add(
                'transmissionType', 'entity', array(
                    'class' => 'TltTicketBundle:TransmissionType',
                    'property' => 'name',
                    'empty_value' => '-- Selectati --',
                    'label' => 'Mod de transmitere sesizare:'
                ));

            $anTransformer = new AnnouncerToNumberTransformer($entityManager);
            $builder
                ->add(
                    $builder
                        ->create('announcedBy', 'text', array(
                                'label' => 'Nume sesizant:',
                                'required' => false,
                            ))
                        ->addModelTransformer($anTransformer)
                );

            $builder->add(
                'contactInfo', TextType::class, array(
                    'max_length' => 128,
                    'label' => 'Date de contact ale sesizantului:',
                    'required' => false
                ));
            $builder->add(
                'announcedTo', TextType::class, array(
                    'max_length' => 128,
                    'label' => 'Persoana anuntata:',
                    'required' => false
                ));
            $builder->add(
                'ticketAllocations', 'entity', array(
                    'class' => 'TltAdmnBundle:Branch',
//                    'empty_value' => '-- Alegeti o optiune --',
                    'label' => 'Agentia/Centrul:',
                    'query_builder' => function (EntityRepository $repository) use ($userBranches) {
                        $qb = $repository->createQueryBuilder('br')
                            ->andWhere('br.id IN (:userBranches)')
                            ->setParameter('userBranches', $userBranches)
                            ->orderby('br.name', 'ASC');

                        return $qb;
                    }
                ));
            $builder->add(
                'description', TextareaType::class, array(
                    'label' => 'Descriere:',
                    'required' => false
                ));
        }


        if ($this->securityContext->isGranted('ROLE_TICKET_SOLVE')) {
            $builder->add(
                'isReal', ChoiceType::class, array(
                    'label' => 'Este real?',
                    'empty_value' => '-- Selectati --',
                    'choices' => array(
                        '0' => 'Nu',
                        '1' => 'Da'
                    ),
                    'required' => false
                ));
            $builder->add(
                'notRealReason', TextareaType::class, array(
                    'label' => 'De ce nu este real:',
                    'required' => false
                ));
            $builder->add(
                'ticketType', 'entity', array(
                    'class' => 'Tlt\TicketBundle\Entity\TicketType',
                    'property' => 'name',
                    'label' => 'Tip interventie',
                    'empty_value' => '-- Selectati --',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false
                ));
            $builder->add(
                'fixedBy', TextType::class, array(
                    'label' => 'Persoana care rezolva:',
                    'required' => false
                ));
            $builder->add(
                'compartment', TextType::class, array(
                    'max_length' => 128,
                    'label' => 'Compartimentul:',
                    'required' => false
                ));
            $builder->add(
                'fixedAt', DateTimeType::class, array(
                    'date_widget' => "choice",
                    'time_widget' => 'choice',
                    'date_format' => 'dd.MM.yyyy',
                    'years' => array(
                        '2015',
                        '2016',
                        '2017',
						'2018'
                    ),
//                    'label' => 'Data si ora rezolvarii:',
                ));
            $builder->add(
                'oldness', 'entity', array(
                    'label' => 'Vechime echipament',
                    'class' => 'Tlt\TicketBundle\Entity\Oldness',
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => true,
                ));
            $builder->add(
                'backupSolution', 'entity', array(
                    'label' => 'S-a asigurat solutie de rezerva?',
                    'class' => 'Tlt\TicketBundle\Entity\BackupSolution',
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => true,
                ));
            $builder->add(
                'emergency', 'entity', array(
                    'label' => 'Urgenta',
                    'class' => 'Tlt\TicketBundle\Entity\Emergency',
                    'property' => 'name',
                    'multiple' => false,
                    'expanded' => true,
                ));


            $eqTransformer = new EquipmentToNumberTransformer($entityManager);
            $builder
                ->add(
                    $builder
                        ->create('equipment', 'hidden', array(
                            'label' => 'Echipament:',
                            'required' => false,
                        ))
                        ->addModelTransformer($eqTransformer)
                );

            $builder->add(
                'fixedMode', TextareaType::class, array(
                    'label' => 'Mod de rezolvare:',
                    'required' => false
                ));
            $builder->add(
                'resources', TextareaType::class, array(
                    'label' => 'Resurse utilizate:',
                    'required' => false
                ));

            if ($this->securityContext->isGranted('ROLE_TICKET_CLOSE')) {
                $builder->add(
                    'isClosed', CheckboxType::class, array(
                        'label' => 'Da',
                        'required' => false
                    ));
            }
        }


        if ($this->securityContext->isGranted('ROLE_TICKET_INSERT')) {
            $builder->add('salveaza', SubmitType::class, array());
            $builder->add('reseteaza', ResetType::class, array());
        }


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            /**
             * Daca este NULL, atunci se introduce un tichet nou, si in acest caz
             * eliminam campurile care nu ne intereseaza.
             */
            if ($data->getId() === null)
            {
                $form->remove('isReal');
                $form->remove('notRealReason');
                $form->remove('ticketType');
                $form->remove('fixedBy');
                $form->remove('compartment');
                $form->remove('fixedAt');
                $form->remove('oldness');
                $form->remove('backupSolution');
                $form->remove('emergency');
                $form->remove('equipment');
                $form->remove('fixedMode');
                $form->remove('resources');
                $form->remove('isClosed');
            }
            else {
                /**
                 * In acest caz este vorba fie de rezolvarea unui tichet deja existent,
                 * fie este vorba de inchiderea unui tichet.
                 */
                $form->remove('announcedAt');
                $form->remove('announcedBy');
                $form->remove('contactInfo');
                $form->remove('ticketAllocations');
                $form->remove('takenBy');
                $form->remove('transmissionType');
                $form->remove('announcedTo');
                $form->remove('description');

                /**
                 * Daca tichetul a fost inchis, nu se mai face nici o modificare.
                 */
                if ($data->getIsClosed() == 1) {
                    $form->remove('isReal');
                    $form->remove('ticketType');
                    $form->remove('fixedAt');
                    $form->remove('fixedBy');
                    $form->remove('compartment');
                    $form->remove('oldness');
                    $form->remove('backupSolution');
                    $form->remove('emergency');
                    $form->remove('equipment');
                    $form->remove('fixedMode');
                    $form->remove('resources');
                    $form->remove('isClosed');
                }



                $equipment_id = ($data->getEquipment() != null ? $data->getEquipment()->getId() : null);
                $ticket_id = ($data->getId() != null ? $data->getId() : null);
                $this->addMappingsField($form, $equipment_id, $ticket_id);

            }
        });
/*
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            echo "<script>alert('pOST SET DATA');</script>";
            $var=$data->getTicketMapping();
            $campuri=array();
            $campuri = $form->all();
            $keys = array_keys($campuri);
           print_r($keys);
            echo "<br>";
            $datele=$form->getData();
            $totalafect = $datele->istotalAffected();
            $totalafect = $form->get('total_afectate')->getData();
//            print_r ($totalafect) ;

            foreach ( $totalafect as $total_afectate )
            {
                print_r ($total_afectate);
                echo "<br>";
            }
            foreach($totalafect as $x => $x_value) {
                echo "Key=" . $x . ", Value=" . $x_value;
                echo "<br>";
            }

            });

  */
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
//                echo "<script>alert('PRE_SUBMIT');</script>";
//            $em = $form->getConfig()->getOption('em');

/*            echo "<script>alert('pre submit');</script>";
            $var=$data['total_afectate'];
            print_r($var) ;
            die();
*/
            if (array_key_exists('isReal', $data)) {
                if( $data['isReal'] == 1) {
                     $form->remove('salveaza');

                     $form->add('salveaza', SubmitType::class, array(
                         'validation_groups' => array('solve')
                     ));
                } else {
                    $form->remove('salveaza');

                    $form->add('salveaza', SubmitType::class, array(
                        'validation_groups' => array('not-real')
                    ));
                }

//                var_dump($data['equipment']);die();

                $equipment_id = array_key_exists('equipment', $data) ? $data['equipment'] : null;
                $ticket_id = ($form->getData()->getId() != null ? $form->getData()->getId() : null);
                $this->addMappingsField($form, $equipment_id, $ticket_id);


            } else {
                $form->remove('salveaza');

                $form->add('salveaza', SubmitType::class, array(
                    'validation_groups' => array('insert')
                ));
            }
        });
    }


    public function addMappingsFieldBradescu($form, $equipment_id, $ticket_id)
    {
        $form->add('ticketMapping', new SpecialType($ticket_id), array(
            'class'     => 'Tlt\AdmnBundle\Entity\Mapping',
            'property' => 'system.name',
            'label'		=> 'Sisteme afectate',
            'by_reference' => false,
            'expanded'  => true,
            'multiple' => true,
//            'read_only' => true,
            'query_builder' => function (EntityRepository $repository) use ($equipment_id) {
                $qb = $repository->createQueryBuilder('mp')
                    ->where('mp.equipment = :equipment')
                    ->setParameter('equipment', $equipment_id)
                    ->orderBy('mp.system', 'ASC');

//                echo "<script>alert('Aici addMapping');</script>";

                return $qb;
            }
        ));
    }

    public function addMappingsField($form, $equipment_id, $ticket_id)
    {
 /*       $form->add('ticketMapping', new TicketMappingType($ticket_id), array(
                'equipment' => $equipment_id,
                'ticket' => $ticket_id)
        );
        */

/*            $form->add('ticketMapping', 'TicketMappingType', array(
                    'class'=>'TicketMappingType'
            ));

       $form->get('ticketMapping')
           ->addModelTransformer(new IssueToNumberTransformer($this->entityManager)); // finally we apply the transformer
*/

        $form->add('ticketMapping', new SpecialType($ticket_id), array(
            'class'     => 'Tlt\AdmnBundle\Entity\Mapping',
            'property' => 'system.name',
            'label'		=> 'Sisteme afectate',
            'by_reference' => false,
            'expanded'  => true,
            'multiple' => true,
//            'read_only' => true,
            'query_builder' => function (EntityRepository $repository) use ($equipment_id) {
                $qb = $repository->createQueryBuilder('mp')
                    ->where('mp.equipment = :equipment')
                    ->setParameter('equipment', $equipment_id)
                    ->orderBy('mp.system', 'ASC');

//                echo "<script>alert('Aici addMapping');</script>";

                return $qb;
            }
        ));

/*
              $form->add('total_afectate', 'entity', array(
                   'class'=> 'Tlt\TicketBundle\Entity\TicketMapping',
                       'label'=>'Afectate Total',
                  'property' => 'totalaffected',
                  'property_path'=>'totalaffected',
                      'by_reference' => false,
                       'expanded'  => true,
                       'multiple' => true,
                       'query_builder' => function (EntityRepository $repository) use ($ticket_id, $equipment_id) {
                               $qb = $repository->createQueryBuilder('ttm')
                                   ->addSelect('ttm')
                                   ->leftJoin('ttm.mapping', 'mp')
                                   ->leftJoin('mp.system','system')
                                   ->where('mp.equipment = :equipment')
                                   ->andWhere('ttm.ticket=:ticket')
                                   ->setParameter('equipment', $equipment_id)
                                   ->setParameter('ticket', $ticket_id)
                                   ->orderBy('mp.system', 'ASC');
                                return $qb;
                            }
                        )
                    );

*/
        $form->add('total_afectate', new SpecialType($ticket_id), array(
            'class'     => 'Tlt\AdmnBundle\Entity\Mapping',
            'property' => 'system.name',
            'label'		=> 'Sisteme TOTAL afectate',
            'property_path'=>'totalaffected',
            'by_reference' => false,
            'required'=>true,
            'expanded'  => true,
            'multiple' => true,
//            'read_only' => true,
            'query_builder' => function (EntityRepository $repository) use ($equipment_id) {
                $qb = $repository->createQueryBuilder('mp')
                    ->where('mp.equipment = :equipment')
                    ->setParameter('equipment', $equipment_id)
                    ->orderBy('mp.system', 'ASC');

//                echo "<script>alert('Aici addMapping');</script>";

                return $qb;
            }
        ));


            // sfarsit adaugat 28.08.2018

    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver
            ->setDefaults(array(
                'data_class'	=>	'Tlt\TicketBundle\Entity\Ticket',
            ))
            ->setRequired(array(
                'em',
            ))
            ->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager',
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix() {
        return 'ticket';
    }
}