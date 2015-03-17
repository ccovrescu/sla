<?php
/**
 * Created by PhpStorm.
 * User: Catalin
 * Date: 3/10/2015
 * Time: 9:15 AM
 */
namespace Tlt\TicketBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Tlt\AdmnBundle\Entity\Equipment;

class EquipmentToNumberTransformer implements DataTransformerInterface {
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
     * Transforms an object (equipment) to a string (number).
     *
     * @param  Equipment|null $issue
     * @return string
     */
    public function transform($equipment)
    {
        if (null === $equipment) {
            return "";
        }

        return $equipment->getId();
    }

    /**
     * Transforms a string (number) to an object (equipment).
     *
     * @param  string $number
     *
     * @return Equipment|null
     *
     * @throws TransformationFailedException if object (equipment) is not found.
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }

        $equipment = $this->om
            ->getRepository('TltAdmnBundle:Equipment')
            ->findOneBy(array('id' => $number))
        ;

        if (null === $equipment) {
            throw new TransformationFailedException(sprintf(
                'An equipment with number "%s" does not exist!',
                $number
            ));
        }

        return $equipment;
    }
} 