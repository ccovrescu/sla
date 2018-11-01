<?php
namespace Tlt\AdmnBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Tlt\AdmnBundle\Entity\Equipment;

class ServiceAttributeValueType extends AbstractType
{
	private $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$equipment = $options['equipment'];
		
		$builder
			->add('id', HiddenType::class)
			->add('equipment',ChoiceType::class, array(
				'choices' => array($equipment->getId() => $equipment->getName()),
				'label' => 'Echipament:',
				'disabled' => true
				))
			->add('service_attr', ChoiceType::class, array(
				'choices' => $this->getServiceAttributes($equipment->getService()),
				'label' => 'Proprietatea:'
				))
			->add('value',TextType::class,array(
				'max_length' => 64,
				'label' => 'Denumire:'
				))
			->add('Salveaza', SubmitType::class);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ServiceAttributeValue',
			'equipment' => new Equipment()
		));		
	}
	
	public function getBlockPrefix()
	{
		return 'service_attribute_value';
	}
	
	public function getServiceAttributes($service)
	{
		return $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\ServiceAttribute')->findByService($service);
	}
}