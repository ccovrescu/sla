<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/10/2015
 * Time: 9:15 AM
 */
namespace Tlt\TicketBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Tlt\AdmnBundle\Entity\Announcer;

class TicketMappingToNumberTransformer implements DataTransformerInterface {
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (announcer) to a string (number).
     *
     * @param  Announcer|null $issue
     * @return string
     */
    public function transform($tcketmapping)
    {
        if (null === $tcketmapping) {
            return "";
        }

        return $tcketmapping->getId();
    }

    /**
     * Transforms a string (number) to an object (announcer).
     *
     * @param  string $number
     *
     * @return Announcer|null
     *
     * @throws TransformationFailedException if object (announcer) is not found.
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }

        $ticketmapping = $this->om
            ->getRepository('TltTicketBundle:TicketMapping')
            ->findOneBy(array('id' => $number))
        ;

        if (null === $ticketmapping) {
            throw new TransformationFailedException(sprintf(
                    'An tickeymapping with number "%s" does not exist!',
                    $number
                ));
        }

        return $ticketmapping;
    }
} 