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

class AnnouncerToNumberTransformer implements DataTransformerInterface {
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
    public function transform($announcer)
    {
        if (null === $announcer) {
            return "";
        }

        return $announcer->getId();
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

        $announcer = $this->om
            ->getRepository('TltAdmnBundle:Announcer')
            ->findOneBy(array('id' => $number))
        ;

        if (null === $announcer) {
            throw new TransformationFailedException(sprintf(
                    'An announcer with number "%s" does not exist!',
                    $number
                ));
        }

        return $announcer;
    }
} 