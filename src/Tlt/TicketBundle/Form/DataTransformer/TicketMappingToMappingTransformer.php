<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/19/2015
 * Time: 12:01 PM
 */
namespace Tlt\TicketBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\ORM\PersistentCollection;

use Tlt\AdmnBundle\Entity\Mapping;
use Tlt\TicketBundle\Entity\TicketMapping;

class TicketMappingToMappingTransformer implements DataTransformerInterface {
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var integer
     */
    private $ticketId;


    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, $ticketId = null)
    {
        $this->om = $om;
        $this->ticketId = $ticketId;
    }

    /**
     * Transforms an object (ticketMapping) to an object (mapping).
     *
     * @param  TicketMapping|null $ticketMapping
     * @return Mapping|null
     */
    public function transform($array)
    {
        $newArray = array();

        if (!($array instanceof PersistentCollection)) {
            return new ArrayCollection();
        }

        foreach ($array as $key => $value) {

            $newArray[] = $value->getMapping();
        }

        return new ArrayCollection($newArray);


//        if (null === $ticketMapping) {
//            return null;
//        }
//
//        return $ticketMapping->getMapping();
    }

    /**
     * Transforms an object (mapping) to an object (ticketMapping).
     *
     * @param  Array $mappings
     *
     * @return TicketMapping|null
     *
     * @throws TransformationFailedException if object (ticketEquipment) is not found.
     */
    public function reverseTransform($mappings)
    {
        $newArray = array();

        if (!$mappings) {
            return new ArrayCollection();
        }

        $ticket = $this->om
            ->getRepository('TltTicketBundle:Ticket')
            ->findOneById($this->ticketId);

//        if  (count($mappings)>0) {
            foreach ($mappings as $mapping) {

                $ticketMapping = $this->om
                    ->getRepository('TltTicketBundle:TicketMapping')
                    ->findOneBy(
                        array(
                            'ticket' => $ticket,
                            'mapping' => $mapping
                        )
                    );

                if (!is_null($ticketMapping)) {
                    $newArray[] = $ticketMapping;
                } else {
                    $ticketMapping = new TicketMapping();
                    $ticketMapping->setTicket($ticket);
                    $ticketMapping->setMapping($mapping);

                    $newArray[] = $ticketMapping;
                }
            }
//        } else {
//            /**
//             * Daca $mappings este un array gol, atunci intoarcem vechile mapari pentru a fi sterse.
//             */
//            $oldMappings = $this->om
//                ->getRepository('TltTicketBundle:TicketMapping')
//                ->findBy(
//                    array(
//                        'ticket' => $ticket
//                    )
//                );
//
//            return $oldMappings;
//        }

        return $newArray;
    }
}