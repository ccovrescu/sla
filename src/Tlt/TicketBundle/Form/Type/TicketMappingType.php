<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/19/2015
 * Time: 1:45 PM
 */

namespace Tlt\TicketBundle\Form\Type;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tlt\TicketBundle\Form\DataTransformer\TicketMappingToMappingTransformer;

class TicketMappingType extends AbstractType
{
   private $ticketId;
   private $equipmentId;

    public function __construct($ticketId = null, $equipmentId=null)
    {
        $this->ticketId = $ticketId;
        $this->equipmentId = $equipmentId;
        $ticketId=$this->ticketId;
        $equipmentId=$this->equipmentId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ticketMappingToMappingTransformer = new TicketMappingToMappingTransformer($options['em'], $this->ticketId);
        $builder->addModelTransformer($ticketMappingToMappingTransformer);

        $builder->add('ticketMapping', 'entity', array(
            'class'     => 'Tlt\AdmnBundle\Entity\Mapping',
            'property' => 'system.name',
            'label'		=> 'Sisteme afectate',
            'by_reference' => false,
            'expanded'  => true,
            'multiple' => true,
//            'read_only' => true,
            'query_builder' => function (EntityRepository $repository) use ($options) {
                $qb = $repository->createQueryBuilder('mp')
                    ->where('mp.equipment = :equipment')
                    ->setParameter('equipment', $options['equipment']->getId())
                    ->orderBy('mp.system', 'ASC');

                return $qb;
            }
        ));
        $builder->add('totalaffected',  'entity', array(
                'class'=> 'Tlt\TicketBundle\Entity\TickeMapping',
                'property'=>'totalaffected',
                'label'=>'Afectate Total',
                'expanded'  => true,
                'multiple' => true,
                'query_builder' => function (EntityRepository $repository) use ($options) {
                    $qb = $repository->createQueryBuilder('ttm')
                        ->innerJoin('ttm.mapping', 'mp')
                        ->innerJoin('mp.system', 'system')
                        ->where('ttm.ticket=:ticket_id')
                        ->setParameter('ticket_id', $options['ticket']->getId())
                        ->orderBy('mp.system', 'ASC');

                    return $qb;
                }
            )
        );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'ticket' => null,
            'equipment' => null,
            'system' => null
        ));
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getBlockPrefix()
    {
        return 'mapping';
    }
}