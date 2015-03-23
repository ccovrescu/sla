<?php
namespace Tlt\TicketBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

use Tlt\TicketBundle\Form\DataTransformer\EquipmentToNumberTransformer;

class TicketType extends AbstractType {

    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->securityContext->isGranted('ROLE_TICKET_INSERT')) {
            $userBranches = $this->securityContext->getToken()->getUser()->getBranchesIds();

            $builder->add(
                'announcedAt', 'datetime', array(
                    'date_widget' => "single_text",
                    'time_widget' => "single_text",
//                    'date_format'=> 'dd-MM-yyyy',
                    'label' => 'Anuntat la:',
                    'data' => new \DateTime(),
                ));
            $builder->add(
                'takenBy', 'text', array(
                    'max_length' => 128,
                    'label' => 'Preluat sesizarea:',
                    'required' => false
                ));
            $builder->add(
                'transmissionType', 'choice', array(
                    'label' => 'Mod de transmitere sesizare:',
                    'empty_value' => '-- Selectati --',
                    'choices' => array(
                        'telefon' => 'telefon',
                        'direct' => 'direct',
                        'autosesizare' => 'autosesizare',
                        'adresa' => 'adresa'
                    )
                ));
            $builder->add(
                'announcedBy', 'text', array(
                    'max_length' => 128,
                    'label' => 'Nume sesizant:',
                    'required' => false
                ));
            $builder->add(
                'contactInfo', 'text', array(
                    'max_length' => 128,
                    'label' => 'Date de contact:',
                    'required' => false
                ));
            $builder->add(
                'announcedTo', 'text', array(
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
                'description', 'textarea', array(
                    'label' => 'Descriere:',
                    'required' => false
                ));
        }


        if ($this->securityContext->isGranted('ROLE_TICKET_SOLVE')) {
            $builder->add(
                'isReal', 'choice', array(
                    'label' => 'Este real?',
                    'empty_value' => '-- Selectati --',
                    'choices' => array(
                        '0' => 'Nu',
                        '1' => 'Da'
                    )
                ));
            $builder->add(
                'notRealReason', 'textarea', array(
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
                'fixedBy', 'text', array(
                    'label' => 'Persoana care rezolva:',
                    'required' => false
                ));
            $builder->add(
                'compartment', 'text', array(
                    'max_length' => 128,
                    'label' => 'Compartimentul:',
                    'required' => false
                ));
            $builder->add(
                'fixedAt', 'datetime', array(
                    'time_widget' => "single_text",
                    'label' => 'Data si ora rezolvarii:',
//                    'data' => new \DateTime(),
//                    'view_timezone' => 'Europe/Bucharest',
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


            // this assumes that the entity manager was passed in as an option
            $entityManager = $options['em'];
            $transformer = new EquipmentToNumberTransformer($entityManager);

            $builder
                ->add(
                    $builder
                        ->create('equipment', 'text', array(
                            'label' => 'Echipament',
                            'read_only' => true,
                            'required' => false,
                        ))
                        ->addModelTransformer($transformer)
                );

            $builder->add(
                'fixedMode', 'textarea', array(
                    'label' => 'Mod de rezolvare:',
                    'required' => false
                ));
            $builder->add(
                'resources', 'textarea', array(
                    'label' => 'Resurse utilizate:',
                    'required' => false
                ));

            if ($this->securityContext->isGranted('ROLE_TICKET_CLOSE')) {
                $builder->add(
                    'isClosed', 'checkbox', array(
                        'label' => 'Da',
                        'required' => false
                    ));
            }
        }


        if ($this->securityContext->isGranted('ROLE_TICKET_INSERT')) {
            $builder->add('salveaza', 'submit', array());
            $builder->add('reseteaza', 'reset', array());
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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

//            $em = $form->getConfig()->getOption('em');

            if (array_key_exists('isReal', $data)) {
                if( $data['isReal'] == 1) {
                     $form->remove('salveaza');

                     $form->add('salveaza', 'submit', array(
                         'validation_groups' => array('solve')
                     ));
                } else {
                    $form->remove('salveaza');

                    $form->add('salveaza', 'submit', array(
                        'validation_groups' => array('not-real')
                    ));
                }

                $equipment_id = array_key_exists('equipment', $data) ? $data['equipment'] : null;
                $ticket_id = ($form->getData()->getId() != null ? $form->getData()->getId() : null);
                $this->addMappingsField($form, $equipment_id, $ticket_id);
            } else {
                $form->remove('salveaza');

                $form->add('salveaza', 'submit', array(
                    'validation_groups' => array('insert')
                ));
            }
        });
    }

    public function addMappingsField($form, $equipment_id, $ticket_id)
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

                return $qb;
            }
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
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
    public function getName() {
        return 'ticket';
    }
}