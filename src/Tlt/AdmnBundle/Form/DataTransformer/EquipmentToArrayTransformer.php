<?php
namespace Tlt\AdmnBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Tlt\AdmnBundle\Entity\Equipment;

use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class EquipmentToArrayTransformer implements DataTransformerInterface
{
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
     * Transforms an object (equipment) to an integer.
     *
     * @param  Department|null $equipment
     * @return integer
     */
    public function transform($equipment)
    {
        if (null === $equipment) {
            return array();
        }
		
		return $equipment->getId();
    }

    /**
     * Transforms an array to an object (equipment).
     *
     * @param  string $number
     *
     * @return Department|null
     *
     * @throws TransformationFailedException if object (equipment) is not found.
     */
    public function reverseTransform($equipment_id)
    {
        if (!$equipment_id) {
            return null;
        }

        $equipment = $this->om
            ->getRepository('TltAdmnBundle:Equipment')
            ->findOneBy(array('id' => $equipment_id))
        ;

        if (null === $equipment) {
            throw new TransformationFailedException(sprintf(
                'An equipment with id "%s" does not exist!',
                $array[0]
            ));
        }

        return $equipment;
    }
}