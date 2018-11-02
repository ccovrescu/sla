<?php
namespace Tlt\AdmnBundle\Form\Type;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
			->add('id', 'hidden')
			->add('equipment','choice', array(
				'choices' => array($equipment->getId() => $equipment->getName()),
				'label' => 'Echipament:',
				'disabled' => true
				))
			->add('service_attr', 'choice', array(
				'choices' => $this->getServiceAttributes($equipment->getService()),
				'label' => 'Proprietatea:'
				))
			->add('value','text',array(
				'max_length' => 64,
				'label' => 'Denumire:'
				))
			->add('Salveaza', 'submit');
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Tlt\AdmnBundle\Entity\ServiceAttributeValue',
			'equipment' => new Equipment()
		));		
	}
	
	public function getName()
	{
		return 'service_attribute_value';
	}
	
	public function getServiceAttributes($service)
	{
		return $this->doctrine->getRepository('Tlt\AdmnBundle\Entity\ServiceAttribute')->findByService($service);
	}
}